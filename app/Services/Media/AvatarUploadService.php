<?php

namespace App\Services\Media;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AvatarUploadService
{
    protected string $storageDir;
    protected string $publicUrlPrefix;

    public function __construct()
    {
        $this->storageDir = public_path('uploads/avatars');
        $this->publicUrlPrefix = '/uploads/avatars';
        $this->ensureSecureStorageDirectory();
    }

    /**
     * Process and securely store a user avatar with re-encoding sanitization.
     */
    public function uploadAvatar(User $user, UploadedFile $file): ?string
    {
        if (!$file->isValid()) {
            return null;
        }

        // 1. Verify MIME type via finfo
        $realMime = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $file->getRealPath());
        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];

        if (!in_array($realMime, $allowedMimes, true)) {
            Log::warning('Rejected avatar upload with invalid MIME type', [
                'user_id' => $user->id,
                'detected_mime' => $realMime,
            ]);
            return null;
        }

        // 2. Decode raw image into GD memory (strips any embedded EXIF / polyglot payloads)
        $rawContent = file_get_contents($file->getRealPath());
        if ($rawContent === false) {
            return null;
        }

        $sourceImage = @imagecreatefromstring($rawContent);
        if ($sourceImage === false) {
            Log::warning('Rejected corrupted or fake image avatar', ['user_id' => $user->id]);
            return null;
        }

        // 3. Calculate square crop dimensions
        $width = imagesx($sourceImage);
        $height = imagesy($sourceImage);

        if ($width <= 0 || $height <= 0) {
            imagedestroy($sourceImage);
            return null;
        }

        $targetSize = 256;
        $targetImage = imagecreatetruecolor($targetSize, $targetSize);

        // Preserve alpha transparency
        imagealphablending($targetImage, false);
        imagesavealpha($targetImage, true);
        $transparent = imagecolorallocatealpha($targetImage, 255, 255, 255, 127);
        imagefilledrectangle($targetImage, 0, 0, $targetSize, $targetSize, $transparent);
        imagealphablending($targetImage, true);

        $minDim = min($width, $height);
        $srcX = (int) (($width - $minDim) / 2);
        $srcY = (int) (($height - $minDim) / 2);

        imagecopyresampled(
            $targetImage,
            $sourceImage,
            0, 0,
            $srcX, $srcY,
            $targetSize, $targetSize,
            $minDim, $minDim
        );

        // 4. Generate random secure filename
        $filename = 'avatar_' . $user->id . '_' . Str::random(16) . '.webp';
        $destinationPath = $this->storageDir . DIRECTORY_SEPARATOR . $filename;

        // 5. Delete previous avatar if locally stored
        $this->deleteOldAvatar($user->avatar_url);

        // 6. Save re-encoded sanitized WebP
        if (function_exists('imagewebp')) {
            imagewebp($targetImage, $destinationPath, 88);
        } else {
            // Fallback to JPEG if WebP is unsupported
            $filename = 'avatar_' . $user->id . '_' . Str::random(16) . '.jpg';
            $destinationPath = $this->storageDir . DIRECTORY_SEPARATOR . $filename;
            imagejpeg($targetImage, $destinationPath, 90);
        }

        imagedestroy($sourceImage);
        imagedestroy($targetImage);

        // Set safe file permissions (read-only for web server)
        @chmod($destinationPath, 0644);

        $avatarUrl = $this->publicUrlPrefix . '/' . $filename;
        $user->avatar_url = $avatarUrl;
        $user->save();

        return $avatarUrl;
    }

    /**
     * Remove the current user avatar and delete file from disk.
     */
    public function removeAvatar(User $user): void
    {
        if ($user->avatar_url) {
            $this->deleteOldAvatar($user->avatar_url);
            $user->avatar_url = null;
            $user->save();
        }
    }

    /**
     * Delete existing custom avatar file safely.
     */
    protected function deleteOldAvatar(?string $avatarUrl): void
    {
        if (empty($avatarUrl) || !str_starts_with($avatarUrl, $this->publicUrlPrefix)) {
            return;
        }

        $basename = basename($avatarUrl);
        // Ensure no path traversal
        if ($basename === '.' || $basename === '..' || str_contains($basename, '/')) {
            return;
        }

        $filePath = $this->storageDir . DIRECTORY_SEPARATOR . $basename;
        if (File::exists($filePath)) {
            File::delete($filePath);
        }
    }

    /**
     * Ensure avatars upload directory exists and has .htaccess blocking script execution.
     */
    protected function ensureSecureStorageDirectory(): void
    {
        if (!File::isDirectory($this->storageDir)) {
            File::makeDirectory($this->storageDir, 0755, true);
        }

        $htaccessPath = $this->storageDir . DIRECTORY_SEPARATOR . '.htaccess';
        if (!File::exists($htaccessPath)) {
            $htaccessContent = <<<HTACCESS
# Block PHP and script execution in upload directory
<FilesMatch "(?i)\.(php|phtml|php3|php4|php5|php7|phps|pl|py|jsp|asp|htm|html|shtml|sh|cgi|exe)$">
    Order Deny,Allow
    Deny from all
</FilesMatch>
Options -ExecCGI -Indexes
HTACCESS;
            File::put($htaccessPath, $htaccessContent);
        }
    }
}
