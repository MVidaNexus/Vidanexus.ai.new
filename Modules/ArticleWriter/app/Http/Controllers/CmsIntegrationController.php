<?php

namespace Modules\ArticleWriter\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\ArticleWriter\Models\ArticleHistory;
use Modules\ArticleWriter\Models\UserCmsConnection;
use Modules\ArticleWriter\Services\CMS\CmsManager;

class CmsIntegrationController extends Controller
{
    protected CmsManager $cmsManager;

    public function __construct(CmsManager $cmsManager)
    {
        $this->cmsManager = $cmsManager;
    }

    /**
     * List user's CMS connections.
     */
    public function index(): JsonResponse
    {
        $connections = UserCmsConnection::where('user_id', auth()->id())
            ->latest()
            ->get()
            ->map(function ($c) {
                return [
                    'id' => $c->id,
                    'name' => $c->name ?: parse_url($c->site_url, PHP_URL_HOST) ?: $c->site_url,
                    'platform' => $c->platform,
                    'site_url' => $c->site_url,
                    'username' => $c->username,
                    'default_status' => $c->default_status,
                    'last_tested_at' => $c->last_tested_at?->diffForHumans(),
                    'last_synced_at' => $c->last_synced_at?->diffForHumans(),
                    'is_active' => (bool) $c->is_active,
                    'settings' => $c->settings ?? [],
                ];
            });

        return response()->json([
            'status' => 'success',
            'connections' => $connections,
            'supported_platforms' => $this->cmsManager->getSupportedPlatforms(),
        ]);
    }

    /**
     * Save a new CMS connection.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'nullable|string|max:100',
            'site_url' => 'required|url|max:500',
            'username' => 'required|string|max:100',
            'api_key' => 'required|string|max:500',
            'platform' => 'nullable|string|in:wordpress,ghost,shopify,webhook',
            'verify_ssl' => 'nullable|boolean',
        ]);

        $platform = $request->input('platform', 'wordpress');
        $siteUrl = rtrim(trim($request->input('site_url')), '/');
        $name = trim($request->input('name') ?? '');
        if (empty($name)) {
            $name = parse_url($siteUrl, PHP_URL_HOST) ?: 'موقع ووردبريس';
        }

        $verifySsl = $request->boolean('verify_ssl', true);

        // Build temporary connection to test credentials before saving
        $tempConnection = new UserCmsConnection([
            'user_id' => auth()->id(),
            'platform' => $platform,
            'name' => $name,
            'site_url' => $siteUrl,
            'username' => trim($request->input('username')),
            'api_key' => trim($request->input('api_key')),
            'default_status' => 'draft',
            'settings' => [
                'verify_ssl' => $verifySsl,
            ],
        ]);

        // Test credentials
        $test = $this->cmsManager->testConnection($tempConnection);

        if (!$test['success']) {
            return response()->json([
                'status' => 'error',
                'message' => $test['message'] ?? 'فشل الاتصال بموقع ووردبريس. يرجى التحقق من صحة البيانات.',
            ], 422);
        }

        // Save connection
        $tempConnection->last_tested_at = now();
        $tempConnection->save();

        return response()->json([
            'status' => 'success',
            'message' => 'تم التحقق من الاتصال وحفظ الموقع بنجاح!',
            'connection' => [
                'id' => $tempConnection->id,
                'name' => $tempConnection->name,
                'platform' => $tempConnection->platform,
                'site_url' => $tempConnection->site_url,
                'username' => $tempConnection->username,
                'default_status' => $tempConnection->default_status,
                'is_active' => true,
            ],
            'test_info' => $test,
        ]);
    }

    /**
     * Test connection (either an existing connection or test credentials on the fly).
     */
    public function testConnection(Request $request): JsonResponse
    {
        if ($request->has('connection_id')) {
            $connection = UserCmsConnection::where('user_id', auth()->id())
                ->findOrFail($request->input('connection_id'));
        } else {
            $request->validate([
                'site_url' => 'required|url|max:500',
                'username' => 'required|string|max:100',
                'api_key' => 'required|string|max:500',
                'platform' => 'nullable|string|in:wordpress,ghost,shopify',
                'verify_ssl' => 'nullable|boolean',
            ]);

            $connection = new UserCmsConnection([
                'user_id' => auth()->id(),
                'platform' => $request->input('platform', 'wordpress'),
                'site_url' => trim($request->input('site_url')),
                'username' => trim($request->input('username')),
                'api_key' => trim($request->input('api_key')),
                'settings' => [
                    'verify_ssl' => $request->boolean('verify_ssl', true),
                ],
            ]);
        }

        $result = $this->cmsManager->testConnection($connection);

        if ($result['success'] && $connection->exists) {
            $connection->update(['last_tested_at' => now()]);
        }

        return response()->json([
            'status' => $result['success'] ? 'success' : 'error',
            'message' => $result['message'],
            'data' => $result['data'] ?? null,
        ], $result['success'] ? 200 : 422);
    }

    /**
     * Fetch categories from a WordPress connection.
     */
    public function categories(int $id): JsonResponse
    {
        $connection = UserCmsConnection::where('user_id', auth()->id())
            ->where('is_active', true)
            ->findOrFail($id);

        $categories = $this->cmsManager->getCategories($connection);

        return response()->json([
            'status' => 'success',
            'categories' => $categories,
        ]);
    }

    /**
     * Publish article to user's CMS as Draft.
     */
    public function publish(int $id, Request $request): JsonResponse
    {
        $request->validate([
            'connection_id' => 'required|integer',
            'title' => 'nullable|string|max:500',
            'excerpt' => 'nullable|string|max:2000',
            'content' => 'nullable|string',
            'tags' => 'nullable', // Array or comma-separated string
            'category_id' => 'nullable|integer',
            'status' => 'nullable|string|in:draft,pending,publish',
            'slug' => 'nullable|string|max:255',
            'include_featured_image' => 'nullable|boolean',
            'featured_image_url' => 'nullable|string',
            'featured_image_path' => 'nullable|string',
        ]);

        $article = ArticleHistory::where('user_id', auth()->id())->findOrFail($id);

        $connection = UserCmsConnection::where('user_id', auth()->id())
            ->where('is_active', true)
            ->findOrFail($request->input('connection_id'));

        $rawTags = $request->input('tags') ?: ($article->seo_data['tags'] ?? []);
        $articleController = app(ArticleWriterController::class);
        $cleanTags = $articleController->cleanAndNormalizeTags(
            $rawTags,
            $article->seo_data['focus_keyword'] ?? null,
            $article->topic,
            $article->title
        );

        $options = [
            'title' => $request->input('title') ?: $article->title,
            'excerpt' => $request->input('excerpt') ?: $article->meta_description,
            'content' => $request->input('content') ?: $article->content,
            'tags' => $cleanTags,
            'category_id' => $request->input('category_id'),
            'status' => $request->input('status', 'draft'),
            'slug' => $request->input('slug'),
            'focus_keyword' => $article->seo_data['focus_keyword'] ?? $article->topic,
            'include_featured_image' => $request->boolean('include_featured_image', true),
            'featured_image_url' => $request->input('featured_image_url'),
            'featured_image_path' => $request->input('featured_image_path'),
        ];

        $result = $this->cmsManager->publishArticle($connection, $article, $options);

        if (!$result['success']) {
            return response()->json([
                'status' => 'error',
                'message' => $result['message'],
            ], 422);
        }

        return response()->json([
            'status' => 'success',
            'message' => $result['message'],
            'post_id' => $result['post_id'] ?? null,
            'post_url' => $result['post_url'] ?? null,
            'edit_url' => $result['edit_url'] ?? null,
            'status_name' => $result['status'] ?? 'draft',
            'tags_count' => $result['tags_attached'] ?? 0,
            'featured_media_id' => $result['featured_media_id'] ?? null,
        ]);
    }

    /**
     * Delete CMS connection.
     */
    public function destroy(int $id): JsonResponse
    {
        $connection = UserCmsConnection::where('user_id', auth()->id())->findOrFail($id);
        $connection->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'تم حذف ربط الموقع بنجاح.',
        ]);
    }
}
