<?php

namespace App\Http\Controllers\Media;

use App\Http\Controllers\Controller;
use App\Models\MediaAsset;
use App\Repositories\Media\Contracts\IMediaVideoRepository;
use Illuminate\Http\Request;

class MediaVideoController extends Controller
{
    public function __construct(private readonly IMediaVideoRepository $videos) {}

    public function update(Request $request, MediaAsset $media_asset)
    {
        $data = $request->validate([
            'provider' => ['nullable', 'string', 'max:30'],
            'external_id' => ['nullable', 'string', 'max:255'],
            'duration_seconds' => ['nullable', 'integer', 'min:0'],
            'poster_media_asset_id' => ['nullable', 'integer', 'exists:media_assets,id'],
            'autoplay' => ['nullable', 'boolean'],
            'muted' => ['nullable', 'boolean'],
            'loop' => ['nullable', 'boolean'],
        ]);

        $video = $this->videos->upsertForAsset($media_asset, $data);

        return response()->json($video);
    }
}
