<?php

namespace Modules\ArticleWriter\Services\CMS;

use InvalidArgumentException;
use Modules\ArticleWriter\Models\ArticleHistory;
use Modules\ArticleWriter\Models\UserCmsConnection;
use Modules\ArticleWriter\Services\CMS\Providers\WordPressProvider;

class CmsManager
{
    /**
     * Array of registered providers.
     *
     * @var array<string, CmsProviderInterface>
     */
    protected array $providers = [];

    public function __construct()
    {
        // Register default providers
        $this->registerProvider(new WordPressProvider());
    }

    /**
     * Register a new CMS provider.
     */
    public function registerProvider(CmsProviderInterface $provider): self
    {
        $this->providers[$provider->getPlatform()] = $provider;
        return $this;
    }

    /**
     * Get provider by platform key.
     */
    public function provider(string $platform): CmsProviderInterface
    {
        $key = strtolower(trim($platform));

        if (!isset($this->providers[$key])) {
            throw new InvalidArgumentException("CMS platform [{$platform}] is not supported yet.");
        }

        return $this->providers[$key];
    }

    /**
     * Test connection for a given connection instance.
     */
    public function testConnection(UserCmsConnection $connection): array
    {
        return $this->provider($connection->platform)->testConnection($connection);
    }

    /**
     * Get categories for a given connection instance.
     */
    public function getCategories(UserCmsConnection $connection): array
    {
        return $this->provider($connection->platform)->getCategories($connection);
    }

    /**
     * Publish article to connection.
     */
    public function publishArticle(UserCmsConnection $connection, ArticleHistory $article, array $options = []): array
    {
        return $this->provider($connection->platform)->publishArticle($connection, $article, $options);
    }

    /**
     * Get list of all available CMS platforms.
     */
    public function getSupportedPlatforms(): array
    {
        $list = [];
        foreach ($this->providers as $platform => $provider) {
            $list[] = [
                'key' => $platform,
                'name' => $provider->getName(),
            ];
        }
        return $list;
    }
}
