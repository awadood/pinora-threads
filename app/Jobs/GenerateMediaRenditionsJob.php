<?php

namespace App\Jobs;

use App\Models\MediaAsset;
use App\Models\MediaRendition;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\ImageManager;

/**
 * GenerateMediaRenditionsJob
 *
 * Generates deterministic derived images (renditions) from a canonical MediaAsset.
 *
 * Key principles (LOCKED)
 * - One canonical MediaAsset row per original upload.
 * - N derived files per MediaAsset live in media_renditions (not new media_assets).
 * - Profiles are an allow-list defined in config/media.php (no arbitrary sizes).
 * - Resize strategy: scale down by WIDTH, preserve aspect ratio, never upscale.
 * - Rendition keys are deterministic siblings of original key:
 *     {originalBase}__{profile}.{ext}
 *
 * Notes
 * - This job is safe to run multiple times (idempotent per profile).
 * - If you want higher concurrency safety, add a unique DB constraint (already exists):
 *     unique(media_asset_id, profile)
 */
class GenerateMediaRenditionsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $mediaAssetId) {}

    public function handle(): void
    {
        return ; // do nothing for now.
        $asset = MediaAsset::query()->find($this->mediaAssetId);

        if (! $asset || $asset->type !== 'image') {
            return;
        }

        $profiles = config('media.renditions', []);
        if (! is_array($profiles) || empty($profiles)) {
            return;
        }

        $disk = $asset->disk ?: 's3';
        $storage = Storage::disk($disk);

        if (! $storage->exists($asset->key)) {
            Log::warning('Media renditions: original missing on disk', [
                'media_asset_id' => $asset->id,
                'disk' => $disk,
                'key' => $asset->key,
            ]);

            return;
        }

        $originalBytes = $storage->get($asset->key);

        // Intervention Image v3.11 (no ImageManagerStatic)
        $manager = new ImageManager(new GdDriver);

        foreach ($profiles as $profile => $cfg) {
            $profile = is_string($profile) ? trim($profile) : '';
            if ($profile === '') {
                continue;
            }

            $width = (int) Arr::get($cfg, 'width', 0);
            if ($width <= 0) {
                continue;
            }

            $format = Str::lower((string) Arr::get($cfg, 'format', 'webp')); // webp|jpg|png
            $quality = (int) Arr::get($cfg, 'quality', 82);

            // Idempotent write: skip if already exists.
            // (DB unique(media_asset_id, profile) is the hard safety net.)
            $exists = MediaRendition::query()
                ->where('media_asset_id', $asset->id)
                ->where('profile', $profile)
                ->exists();

            if ($exists) {
                continue;
            }

            // Read & scale down (width only, preserve aspect ratio, prevent upscaling)
            $image = $manager->read($originalBytes)->scaleDown(width: $width);

            $encoded = $this->encode($image, $format, $quality);
            $mime = $this->mimeFor($format);

            $rendKey = $this->renditionKeyFromOriginal($asset->key, $profile, $format);

            // Upload derived file
            $storage->put($rendKey, $encoded, [
                'visibility' => 'public',
                'ContentType' => $mime,
            ]);

            // Persist DB record (use firstOrCreate to be concurrency-tolerant)
            MediaRendition::query()->firstOrCreate(
                [
                    'media_asset_id' => $asset->id,
                    'profile' => $profile,
                ],
                [
                    'disk' => $disk,
                    'key' => $rendKey,
                    'mime_type' => $mime,
                    'bytes' => strlen($encoded),
                    'width' => $image->width(),
                    'height' => $image->height(),
                ]
            );
        }
    }

    /**
     * Deterministic rendition key next to the original:
     * {originalBase}__{profile}.{ext}
     */
    private function renditionKeyFromOriginal(string $originalKey, string $profile, string $format): string
    {
        $originalKey = Str::of($originalKey)->ltrim('/')->toString();

        $dotPos = Str::of($originalKey)->rpos('.');
        $base = $dotPos === false
            ? $originalKey
            : Str::of($originalKey)->substr(0, $dotPos)->toString();

        $ext = $format === 'jpeg' ? 'jpg' : $format;

        return "{$base}__{$profile}.{$ext}";
    }

    private function encode($image, string $format, int $quality): string
    {
        return match ($format) {
            'jpg', 'jpeg' => (string) $image->toJpeg(quality: $quality),
            'png' => (string) $image->toPng(),
            default => (string) $image->toWebp(quality: $quality),
        };
    }

    private function mimeFor(string $format): string
    {
        return match ($format) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            default => 'image/webp',
        };
    }
}
