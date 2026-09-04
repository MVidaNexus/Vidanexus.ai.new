<?php

namespace Modules\ArticleWriter\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class UserCmsConnection extends Model
{
    use HasFactory;

    protected $table = 'user_cms_connections';

    protected $fillable = [
        'user_id',
        'platform',
        'name',
        'site_url',
        'username',
        'api_key',
        'default_status',
        'default_category_id',
        'settings',
        'last_tested_at',
        'last_synced_at',
        'is_active',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
        'last_tested_at' => 'datetime',
        'last_synced_at' => 'datetime',
    ];

    protected $hidden = [
        'api_key',
    ];

    /**
     * User relationship.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Automatically encrypt the API key / Application password when saving.
     */
    public function setApiKeyAttribute($value)
    {
        if (!empty($value)) {
            $this->attributes['api_key'] = Crypt::encryptString(trim($value));
        }
    }

    /**
     * Get the decrypted API key safely.
     */
    public function getDecryptedApiKey(): ?string
    {
        if (empty($this->attributes['api_key'])) {
            return null;
        }

        try {
            return Crypt::decryptString($this->attributes['api_key']);
        } catch (\Throwable $e) {
            Log::error('[UserCmsConnection] Failed to decrypt API key for connection #' . $this->id, [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Normalized Site URL (no trailing slash).
     */
    public function getNormalizedUrlAttribute(): string
    {
        return rtrim(trim($this->site_url), '/');
    }
}
