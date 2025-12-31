<?php

namespace App\Http\Controllers\Media;

use App\Http\Controllers\Controller;
use App\Models\MediaAsset;
use App\Repositories\Media\Contracts\IMediaRenditionRepository;
use Illuminate\Http\Request;

class MediaRenditionController extends Controller
{
    public function __construct(private readonly IMediaRenditionRepository $renditions) {}

    public function index(MediaAsset $media_asset)
    {
        return response()->json([
            'data' => $this->renditions->listForAsset($media_asset),
        ]);
    }

    /**
     * v1: bulk upsert renditions (worker pipeline can call this).
     */
    public function store(Request $request, MediaAsset $media_asset)
    {
        $data = $request->validate([
            'profiles' => ['required', 'array', 'min:1'],
            'profiles.*.profile' => ['required', 'string', 'max:50'],
            'profiles.*.disk' => ['sometimes', 'string', 'max:50'],
            'profiles.*.key' => ['required', 'string', 'max:255'],
            'profiles.*.mime_type' => ['nullable', 'string', 'max:100'],
            'profiles.*.bytes' => ['nullable', 'integer', 'min:0'],
            'profiles.*.width' => ['nullable', 'integer', 'min:0'],
            'profiles.*.height' => ['nullable', 'integer', 'min:0'],
        ]);

        $count = $this->renditions->upsertForAsset($media_asset, $data['profiles']);

        return response()->json(['upserted' => $count], 201);
    }
}
