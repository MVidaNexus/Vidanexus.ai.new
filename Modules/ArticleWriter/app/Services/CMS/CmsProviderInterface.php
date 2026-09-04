<?php

namespace Modules\ArticleWriter\Services\CMS;

use Modules\ArticleWriter\Models\ArticleHistory;
use Modules\ArticleWriter\Models\UserCmsConnection;

interface CmsProviderInterface
{
    /**
     * Unique platform key (e.g. 'wordpress', 'ghost', 'shopify').
     */
    public function getPlatform(): string;

    /**
     * Human-readable platform name.
     */
    public function getName(): string;

    /**
     * Test connection credentials and return user info or permissions.
     *
     * @return array ['success' => bool, 'message' => string, 'data' => array]
     */
    public function testConnection(UserCmsConnection $connection): array;

    /**
     * Fetch available categories from the remote CMS.
     *
     * @return array List of ['id' => ..., 'name' => ..., 'slug' => ...]
     */
    public function getCategories(UserCmsConnection $connection): array;

    /**
     * Publish or save article as draft to the CMS.
     *
     * @param UserCmsConnection $connection
     * @param ArticleHistory $article
     * @param array $options ['title' => ..., 'excerpt' => ..., 'content' => ..., 'tags' => [...], 'category_id' => ..., 'status' => 'draft']
     * @return array ['success' => bool, 'message' => string, 'post_id' => string|int, 'post_url' => string, 'edit_url' => string]
     */
    public function publishArticle(UserCmsConnection $connection, ArticleHistory $article, array $options = []): array;
}
