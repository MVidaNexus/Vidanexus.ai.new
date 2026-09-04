<?php

namespace Modules\ArticleWriter\Services\CMS\Providers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\ArticleWriter\Models\ArticleHistory;
use Modules\ArticleWriter\Models\UserCmsConnection;
use Modules\ArticleWriter\Services\CMS\CmsProviderInterface;

class WordPressProvider implements CmsProviderInterface
{
    protected const USER_AGENT = 'VidaNexus-AI/1.0 (WordPress CMS Connector; +https://vidanexus.ai)';
    protected const TIMEOUT = 30;

    public function getPlatform(): string
    {
        return 'wordpress';
    }

    public function getName(): string
    {
        return 'WordPress';
    }

    /**
     * Clean and normalize base URL.
     */
    protected function getBaseUrl(UserCmsConnection $connection): string
    {
        $url = rtrim(trim($connection->site_url), '/');
        // If user accidentally entered /wp-json or /wp-admin, clean it
        $url = preg_replace('#/(wp-json(/.*)?|wp-admin(/.*)?)$#i', '', $url);
        return $url;
    }

    /**
     * Build authenticated HTTP client.
     */
    protected function client(UserCmsConnection $connection)
    {
        $username = trim($connection->username ?? '');
        $appPassword = $connection->getDecryptedApiKey() ?? '';

        $verifySsl = (bool) ($connection->settings['verify_ssl'] ?? true);

        return Http::withBasicAuth($username, $appPassword)
            ->withHeaders([
                'User-Agent' => self::USER_AGENT,
                'Accept' => 'application/json',
            ])
            ->timeout(self::TIMEOUT)
            ->withOptions([
                'verify' => $verifySsl,
            ]);
    }

    /**
     * Test connection to WordPress REST API.
     */
    public function testConnection(UserCmsConnection $connection): array
    {
        $baseUrl = $this->getBaseUrl($connection);

        if (empty($baseUrl)) {
            return [
                'success' => false,
                'message' => 'رابط موقع ووردبريس غير صحيح أو فارغ.',
            ];
        }

        if (empty($connection->username) || empty($connection->getDecryptedApiKey())) {
            return [
                'success' => false,
                'message' => 'اسم المستخدم وكلمة مرور التطبيق (Application Password) مطلوبان.',
            ];
        }

        try {
            // First check user profile & capabilities via /wp-json/wp/v2/users/me?context=edit
            $response = $this->client($connection)->get("{$baseUrl}/wp-json/wp/v2/users/me", [
                'context' => 'edit',
            ]);

            if ($response->successful()) {
                $user = $response->json();
                $displayName = $user['name'] ?? $connection->username;
                $roles = implode(', ', $user['roles'] ?? ['Author']);

                return [
                    'success' => true,
                    'message' => "تم الاتصال بنجاح بموقع ووردبريس! مرحباً بك ({$displayName}) - الرتبة: {$roles}.",
                    'data' => [
                        'user_id' => $user['id'] ?? null,
                        'name' => $displayName,
                        'roles' => $user['roles'] ?? [],
                        'site_url' => $baseUrl,
                    ],
                ];
            }

            $status = $response->status();
            $body = $response->json();
            $wpMessage = $body['message'] ?? null;

            if ($status === 401) {
                return [
                    'success' => false,
                    'message' => 'فشلت المصادقة (401): اسم المستخدم أو كلمة مرور التطبيق (Application Password) غير صحيحة. يرجى التأكد من نسخ كلمة المرور من لوحة ووردبريس > الأعضاء > حسابك الشخصي > كلمات مرور التطبيقات.',
                ];
            }

            if ($status === 403) {
                return [
                    'success' => false,
                    'message' => 'تم رفض الوصول (403): الحساب الحالي لا يملك الصلاحيات الكافية لإدارة المقالات عبر REST API.',
                ];
            }

            if ($status === 404) {
                return [
                    'success' => false,
                    'message' => 'لم يتم العثور على مسار REST API (404). يرجى التأكد من تفعيل الروابط الدائمة (Permalinks) في ووردبريس وعدم حظر /wp-json بواسطة إضافات الحماية.',
                ];
            }

            return [
                'success' => false,
                'message' => $wpMessage ? "استجابة ووردبريس: {$wpMessage}" : "فشل الاتصال برمز استجابة ({$status}).",
            ];

        } catch (\Throwable $e) {
            Log::warning('[WordPressProvider] Connection test exception', [
                'site_url' => $baseUrl,
                'error' => $e->getMessage(),
            ]);

            $msg = $e->getMessage();
            if (str_contains($msg, 'SSL certificate') || str_contains($msg, 'cURL error 60')) {
                return [
                    'success' => false,
                    'message' => 'تعذر التحقق من شهادة أمان SSL الخاصة بموقعك. يمكنك تجربة تفعيل خيار "تجاوز فحص شهادة SSL" إذا كان الموقع تجريبياً.',
                ];
            }

            if (str_contains($msg, 'timed out') || str_contains($msg, 'cURL error 28')) {
                return [
                    'success' => false,
                    'message' => 'انتهت مهلة الاتصال بالموقع (Timeout). يرجى التأكد من استقرار خادم موقعك وعدم حظره للطلبات الخارجية.',
                ];
            }

            return [
                'success' => false,
                'message' => 'تعذر الاتصال بموقع ووردبريس: ' . $msg,
            ];
        }
    }

    /**
     * Fetch categories from WordPress.
     */
    public function getCategories(UserCmsConnection $connection): array
    {
        $baseUrl = $this->getBaseUrl($connection);

        try {
            $response = $this->client($connection)->get("{$baseUrl}/wp-json/wp/v2/categories", [
                'per_page' => 100,
                '_fields' => 'id,name,slug,count,parent',
            ]);

            if (!$response->successful()) {
                Log::warning('[WordPressProvider] Failed to fetch categories', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return [];
            }

            $categories = $response->json();
            if (!is_array($categories)) {
                return [];
            }

            return array_map(function ($cat) {
                return [
                    'id' => $cat['id'],
                    'name' => html_entity_decode($cat['name'] ?? '', ENT_QUOTES, 'UTF-8'),
                    'slug' => $cat['slug'] ?? '',
                    'count' => $cat['count'] ?? 0,
                    'parent' => $cat['parent'] ?? 0,
                ];
            }, $categories);

        } catch (\Throwable $e) {
            Log::error('[WordPressProvider] Exception fetching categories: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Resolve or create tags in WordPress, returning an array of tag IDs.
     */
    protected function resolveTagIds(UserCmsConnection $connection, array $tags): array
    {
        if (empty($tags)) {
            return [];
        }

        $baseUrl = $this->getBaseUrl($connection);
        $resolvedIds = [];

        foreach ($tags as $rawTag) {
            $tag = trim(strip_tags((string) $rawTag));
            if (empty($tag)) {
                continue;
            }

            try {
                // 1. Search for existing tag
                $searchRes = $this->client($connection)->get("{$baseUrl}/wp-json/wp/v2/tags", [
                    'search' => $tag,
                    'per_page' => 10,
                    '_fields' => 'id,name,slug',
                ]);

                if ($searchRes->successful()) {
                    $found = $searchRes->json();
                    if (is_array($found)) {
                        foreach ($found as $item) {
                            if (mb_strtolower($item['name']) === mb_strtolower($tag) || mb_strtolower($item['slug']) === mb_strtolower($tag)) {
                                $resolvedIds[] = (int) $item['id'];
                                continue 2; // Tag resolved, continue outer loop
                            }
                        }
                    }
                }

                // 2. Not found, create tag in WordPress
                $createRes = $this->client($connection)->post("{$baseUrl}/wp-json/wp/v2/tags", [
                    'name' => $tag,
                ]);

                if ($createRes->successful()) {
                    $newTag = $createRes->json();
                    if (!empty($newTag['id'])) {
                        $resolvedIds[] = (int) $newTag['id'];
                        continue;
                    }
                }

                // 3. If creation returned "term_exists"
                if ($createRes->status() === 400) {
                    $err = $createRes->json();
                    if (isset($err['code']) && $err['code'] === 'term_exists' && !empty($err['data']['term_id'])) {
                        $resolvedIds[] = (int) $err['data']['term_id'];
                        continue;
                    }
                }

            } catch (\Throwable $e) {
                Log::notice('[WordPressProvider] Tag resolution non-fatal warning for tag "' . $tag . '": ' . $e->getMessage());
            }
        }

        return array_values(array_unique($resolvedIds));
    }

    /**
     * Publish article to WordPress as Draft.
     */
    public function publishArticle(UserCmsConnection $connection, ArticleHistory $article, array $options = []): array
    {
        $baseUrl = $this->getBaseUrl($connection);

        $title = trim($options['title'] ?? $article->title ?? $article->topic ?? 'Untitled Article');
        $content = trim($options['content'] ?? $article->content ?? '');
        $excerpt = trim($options['excerpt'] ?? $article->meta_description ?? '');
        $status = trim($options['status'] ?? $connection->default_status ?? 'draft');
        
        // Ensure status is valid
        if (!in_array($status, ['draft', 'pending', 'publish'])) {
            $status = 'draft';
        }

        // Slug
        $slug = null;
        if (!empty($options['slug'])) {
            $slug = trim($options['slug']);
        } elseif (!empty($article->seo_data['slug_ar']) && $article->language === 'ar') {
            $slug = $article->seo_data['slug_ar'];
        } elseif (!empty($article->seo_data['slug_en'])) {
            $slug = $article->seo_data['slug_en'];
        }

        // Tags
        $rawTags = $options['tags'] ?? ($article->seo_data['tags'] ?? []);
        if (is_string($rawTags)) {
            $rawTags = array_map('trim', explode(',', $rawTags));
        }
        $tagIds = $this->resolveTagIds($connection, (array) $rawTags);

        // Category
        $categoryId = !empty($options['category_id']) 
            ? (int) $options['category_id'] 
            : (!empty($connection->default_category_id) ? (int) $connection->default_category_id : null);

        // Build Payload
        $payload = [
            'title' => $title,
            'content' => $content,
            'excerpt' => $excerpt,
            'status' => $status,
        ];

        if (!empty($slug)) {
            $payload['slug'] = $slug;
        }

        if (!empty($categoryId)) {
            $payload['categories'] = [$categoryId];
        }

        if (!empty($tagIds)) {
            $payload['tags'] = $tagIds;
        }

        // SEO Plugins Meta support (Yoast / Rank Math)
        $focusKeyword = $options['focus_keyword'] ?? ($article->seo_data['focus_keyword'] ?? $article->topic ?? '');
        if (!empty($focusKeyword) || !empty($excerpt)) {
            $payload['meta'] = [
                '_yoast_wpseo_focuskw' => $focusKeyword,
                '_yoast_wpseo_metadesc' => $excerpt,
                'rank_math_focus_keyword' => $focusKeyword,
                'rank_math_description' => $excerpt,
            ];
        }

        try {
            $response = $this->client($connection)->post("{$baseUrl}/wp-json/wp/v2/posts", $payload);

            if ($response->successful()) {
                $post = $response->json();
                $postId = $post['id'] ?? null;
                $postLink = $post['link'] ?? "{$baseUrl}/?p={$postId}";
                $editUrl = "{$baseUrl}/wp-admin/post.php?post={$postId}&action=edit";

                // Update connection timestamp
                $connection->update([
                    'last_synced_at' => now(),
                ]);

                // Update article history with CMS sync log
                $seoData = $article->seo_data ?? [];
                $seoData['cms_sync'] = [
                    'platform' => 'wordpress',
                    'connection_id' => $connection->id,
                    'connection_name' => $connection->name ?: $baseUrl,
                    'post_id' => $postId,
                    'post_url' => $postLink,
                    'edit_url' => $editUrl,
                    'status' => $status,
                    'tags_count' => count($tagIds),
                    'synced_at' => now()->toIso8601String(),
                ];
                $article->update(['seo_data' => $seoData]);

                return [
                    'success' => true,
                    'message' => $status === 'draft' 
                        ? 'تم حفظ المقال بنجاح كمسودة (Draft) في ووردبريس مع العنوان والوصف والمحتوى والوسوم!' 
                        : 'تم إرسال المقال بنجاح إلى ووردبريس!',
                    'post_id' => $postId,
                    'post_url' => $postLink,
                    'edit_url' => $editUrl,
                    'status' => $status,
                    'tags_attached' => count($tagIds),
                ];
            }

            $body = $response->json();
            $errorMsg = $body['message'] ?? 'فشل إنشاء المنشور في ووردبريس كود: ' . $response->status();

            return [
                'success' => false,
                'message' => $errorMsg,
            ];

        } catch (\Throwable $e) {
            Log::error('[WordPressProvider] Publish post failed: ' . $e->getMessage(), [
                'connection_id' => $connection->id,
                'article_id' => $article->id,
            ]);

            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء الاتصال بووردبريس: ' . $e->getMessage(),
            ];
        }
    }
}
