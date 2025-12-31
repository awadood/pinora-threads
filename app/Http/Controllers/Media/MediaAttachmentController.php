<?php

namespace App\Http\Controllers\Media;

use App\Http\Controllers\Controller;
use App\Http\Resources\Media\MediaAttachmentResource;
use App\Models\MediaAttachment;
use App\Repositories\Media\Contracts\IMediaAttachmentRepository;
use App\Support\OwnerTypeResolver;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class MediaAttachmentController extends Controller
{
    public function __construct(private readonly IMediaAttachmentRepository $attachments) {}

    public function store(Request $request)
    {
        $data = $request->validate([
            'media_asset_id' => ['required', 'integer', 'exists:media_assets,id'],
            'owner_type' => ['required', 'string', 'max:50'],
            'owner_id' => ['required', 'integer'],
            'role' => ['required', 'string', 'max:50'],
            'is_primary' => ['nullable', 'boolean'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'caption' => ['nullable', 'string', 'max:255'],
        ]);

        if ($data['role'] !== 'gallery') {
            return response()->json(['message' => 'Use replace-single for this role.'], 422);
        }

        $resolved = OwnerTypeResolver::validateOwner($data['owner_type'], (int) $data['owner_id']);
        OwnerTypeResolver::assertRoleAllowed($resolved['owner_type_key'], $data['role']);

        $data['owner_type'] = $resolved['owner_type'];
        $data['owner_id'] = $resolved['owner_id'];

        $attachment = $this->attachments->attach($data);

        return response()->json(MediaAttachmentResource::make($attachment), 201);
    }

    public function show(string $owner_type, int $owner_id)
    {
        $resolved = OwnerTypeResolver::validateOwner($owner_type, $owner_id);

        $grouped = $this->attachments->listForOwner($resolved['owner_type'], $resolved['owner_id']);

        $roles = $grouped->map(fn ($items) => MediaAttachmentResource::collection($items)->resolve());

        return response()->json([
            'owner_type' => $resolved['owner_type_key'],
            'owner_id' => $resolved['owner_id'],
            'roles' => $roles,
        ]);
    }

    public function update(Request $request, MediaAttachment $media_attachment)
    {
        $data = $request->validate([
            'position' => ['nullable', 'integer', 'min:1'],
            'is_primary' => ['nullable', 'boolean'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'caption' => ['nullable', 'string', 'max:255'],
        ]);

        $attachment = $this->attachments->updateAttachment($media_attachment, $data);

        return MediaAttachmentResource::make($attachment);
    }

    public function destroy(MediaAttachment $media_attachment)
    {
        $deleted = $this->attachments->detach($media_attachment);

        return response()->json(['deleted' => $deleted]);
    }

    public function setPrimary(MediaAttachment $media_attachment)
    {
        $attachment = $this->attachments->setPrimary($media_attachment);

        return MediaAttachmentResource::make($attachment);
    }

    public function reorder(Request $request)
    {
        $data = $request->validate([
            'owner_type' => ['required', 'string', 'max:50'],
            'owner_id' => ['required', 'integer'],
            'role' => ['required', 'string', 'max:50'],
            'attachment_ids_ordered' => ['required', 'array', 'min:1'],
            'attachment_ids_ordered.*' => ['integer'],
        ]);

        $resolved = OwnerTypeResolver::validateOwner($data['owner_type'], (int) $data['owner_id']);
        OwnerTypeResolver::assertRoleAllowed($resolved['owner_type_key'], $data['role']);

        $updated = $this->attachments->reorder(
            $resolved['owner_type'],
            $resolved['owner_id'],
            $data['role'],
            $data['attachment_ids_ordered'],
        );

        return response()->json(['updated' => $updated]);
    }

    public function replaceSingle(Request $request)
    {
        $data = $request->validate([
            'media_asset_id' => ['required', 'integer', 'exists:media_assets,id'],
            'owner_type' => ['required', 'string', 'max:50'],
            'owner_id' => ['required', 'integer'],
            'role' => ['required', 'string', 'max:50'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'caption' => ['nullable', 'string', 'max:255'],
        ]);

        if ($data['role'] === 'gallery') {
            throw ValidationException::withMessages(['role' => 'Use POST /media-attachments for gallery.']);
        }

        $resolved = OwnerTypeResolver::validateOwner($data['owner_type'], (int) $data['owner_id']);
        OwnerTypeResolver::assertRoleAllowed($resolved['owner_type_key'], $data['role']);

        $data['owner_type'] = $resolved['owner_type'];
        $data['owner_id'] = $resolved['owner_id'];

        $attachment = $this->attachments->replaceSingle($data);

        return MediaAttachmentResource::make($attachment);
    }
}
