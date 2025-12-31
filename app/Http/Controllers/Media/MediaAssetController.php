<?php

namespace App\Http\Controllers\Media;

use App\Http\Controllers\Controller;
use App\Models\MediaAsset;
use App\Repositories\Media\Contracts\IMediaAssetRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MediaAssetController extends Controller
{
    public function __construct(private readonly IMediaAssetRepository $assets) {}

    public function index(Request $request)
    {
        $filters = $request->validate([
            'type' => ['nullable', Rule::in(['image', 'video'])],
            'q' => ['nullable', 'string', 'max:255'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:200'],
        ]);

        $perPage = (int) ($filters['per_page'] ?? 20);

        return $this->assets->paginateForAdmin($filters, $perPage);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(['image', 'video'])],
            'disk' => ['sometimes', 'string', 'max:50'],
            'key' => ['required', 'string', 'max:255'],

            'mime_type' => ['nullable', 'string', 'max:100'],
            'bytes' => ['nullable', 'integer', 'min:0'],
            'width' => ['nullable', 'integer', 'min:0'],
            'height' => ['nullable', 'integer', 'min:0'],

            'alt_text' => ['nullable', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            'caption' => ['nullable', 'string', 'max:255'],
            'checksum' => ['nullable', 'string', 'max:64'],
        ]);

        $asset = $this->assets->register($data);

        return response()->json($asset, 201);
    }

    public function show(MediaAsset $media_asset)
    {
        $asset = $this->assets->findWithDetails($media_asset->id);

        return response()->json($asset);
    }

    public function destroy(MediaAsset $media_asset)
    {
        $deleted = $this->assets->deleteIfOrphan($media_asset);

        return response()->json(['deleted' => $deleted]);
    }

    public function presign(Request $request)
    {
        $data = $request->validate([
            'filename' => ['required', 'string', 'max:255'],
            'mime_type' => ['required', 'string', 'max:100'],
        ]);

        $disk = 's3';
        $env = app()->environment();
        $type = Str::startsWith($data['mime_type'], 'video/') ? 'video' : 'image';
        $extension = Str::lower(pathinfo($data['filename'], PATHINFO_EXTENSION));
        $extension = $extension !== '' ? $extension : ($type === 'video' ? 'mp4' : 'jpg');

        $key = sprintf(
            '%s/assets/%s/%s/%s.%s',
            $env,
            $type,
            now()->format('Y/m'),
            (string) Str::uuid(),
            $extension
        );

        // Create presigned PUT URL (valid for 10 minutes)
        $client = Storage::disk($disk)->getClient();
        $bucket = config("filesystems.disks.$disk.bucket");

        $command = $client->getCommand('PutObject', [
            'Bucket' => $bucket,
            'Key' => $key,
            'ContentType' => $data['mime_type'],
        ]);

        $presigned = $client->createPresignedRequest($command, '+10 minutes');

        return response()->json([
            'disk' => $disk,
            'key' => $key,
            'upload_url' => (string) $presigned->getUri(),
            'headers' => [
                'Content-Type' => $data['mime_type'],
            ],
            'expires_in' => 600,
        ]);
    }
}
