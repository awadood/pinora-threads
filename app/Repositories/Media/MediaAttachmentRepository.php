<?php

namespace App\Repositories\Media;

use App\Models\MediaAttachment;
use App\Repositories\BaseRepository;
use App\Repositories\Media\Contracts\IMediaAttachmentRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MediaAttachmentRepository extends BaseRepository implements IMediaAttachmentRepository
{
    protected string $modelClass = MediaAttachment::class;

    /**
     * Override: media_attachments has no 'active' column.
     */
    public function create(array $attributes): MediaAttachment
    {
        return $this->query()->create($attributes);
    }

    public function attach(array $data): MediaAttachment
    {
        return DB::transaction(function () use ($data) {
            $ownerType = $data['owner_type'];
            $ownerId = (int) $data['owner_id'];
            $role = $data['role'];

            // Backend controls position for ordered roles
            $position = $data['position'] ?? null;
            if ($role === 'gallery' && $position === null) {
                $max = (int) $this->query()
                    ->where('owner_type', $ownerType)
                    ->where('owner_id', $ownerId)
                    ->where('role', $role)
                    ->max('position');

                $position = $max + 1;
            }

            $isPrimary = Arr::exists($data, 'is_primary') ? (bool) $data['is_primary'] : false;

            if ($role === 'gallery') {
                $hasAny = $this->query()
                    ->where('owner_type', $ownerType)
                    ->where('owner_id', $ownerId)
                    ->where('role', $role)
                    ->exists();

                if (! $hasAny) {
                    $isPrimary = true; // first gallery item becomes primary
                }
            }

            /** @var MediaAttachment $attachment */
            $attachment = $this->create([
                'media_asset_id' => $data['media_asset_id'],
                'owner_type' => $ownerType,
                'owner_id' => $ownerId,
                'role' => $role,
                'position' => $position ?? 1,
                'is_primary' => $isPrimary,
                'alt_text' => $data['alt_text'] ?? null,
                'caption' => $data['caption'] ?? null,
            ]);

            if ($attachment->is_primary) {
                $this->enforceSinglePrimary($attachment);
            }

            return $attachment->fresh(['asset.renditions', 'asset.video']);
        });
    }

    public function updateAttachment(MediaAttachment $attachment, array $data): MediaAttachment
    {
        $attachment->fill([
            'alt_text' => $data['alt_text'] ?? $attachment->alt_text,
            'caption' => $data['caption'] ?? $attachment->caption,
            'position' => $data['position'] ?? $attachment->position,
            'is_primary' => array_key_exists('is_primary', $data) ? (bool) $data['is_primary'] : $attachment->is_primary,
        ]);

        return DB::transaction(function () use ($attachment) {
            $attachment->save();

            if ($attachment->is_primary) {
                $this->enforceSinglePrimary($attachment);
            }

            return $attachment->fresh(['asset.renditions', 'asset.video']);
        });
    }

    public function setPrimary(MediaAttachment $attachment): MediaAttachment
    {
        return DB::transaction(function () use ($attachment) {
            $attachment->is_primary = true;
            $attachment->save();

            $this->enforceSinglePrimary($attachment);

            return $attachment->fresh(['asset.renditions', 'asset.video']);
        });
    }

    public function reorder(string $ownerType, int $ownerId, string $role, array $attachmentIdsOrdered): int
    {
        $ids = collect($attachmentIdsOrdered)->map(fn ($v) => (int) $v)->filter(fn ($v) => $v > 0)->values();

        if ($ids->isEmpty()) {
            return 0;
        }

        return DB::transaction(function () use ($ownerType, $ownerId, $role, $ids) {
            // Lock the set we are reordering to prevent concurrent reorders from interleaving
            $existing = $this->query()
                ->where('owner_type', $ownerType)
                ->where('owner_id', $ownerId)
                ->where('role', $role)
                ->lockForUpdate()
                ->pluck('id')
                ->map(fn ($v) => (int) $v)
                ->values();

            // Must be an exact permutation of existing IDs (no missing, no extras)
            $sortedIds = $ids->sort()->values()->all();
            $sortedExisting = $existing->sort()->values()->all();
            if ($ids->count() !== $existing->count() || $sortedIds !== $sortedExisting) {
                throw ValidationException::withMessages([
                    'attachment_ids_ordered' => 'attachment_ids_ordered must include exactly all attachments for this owner/role.',
                ]);
            }

            $updated = 0;
            foreach ($ids as $i => $id) {
                $updated += (int) $this->query()->where('id', $id)->update(['position' => $i + 1]);
            }

            return $updated;
        });
    }

    public function replaceSingle(array $data): MediaAttachment
    {
        return DB::transaction(function () use ($data) {
            $ownerType = $data['owner_type'];
            $ownerId = (int) $data['owner_id'];
            $role = $data['role'];

            $this->query()
                ->where('owner_type', $ownerType)
                ->where('owner_id', $ownerId)
                ->where('role', $role)
                ->delete();

            /** @var MediaAttachment $attachment */
            $attachment = $this->create([
                'media_asset_id' => $data['media_asset_id'],
                'owner_type' => $ownerType,
                'owner_id' => $ownerId,
                'role' => $role,
                'position' => 1,
                'is_primary' => true,
                'alt_text' => $data['alt_text'] ?? null,
                'caption' => $data['caption'] ?? null,
            ]);

            return $attachment->fresh(['asset.renditions', 'asset.video']);
        });
    }

    public function listForOwner(string $ownerType, int $ownerId): Collection
    {
        return $this->query()
            ->with(['asset.renditions', 'asset.video'])
            ->where('owner_type', $ownerType)
            ->where('owner_id', $ownerId)
            ->orderBy('role')
            ->orderBy('position')
            ->get()
            ->groupBy('role');
    }

    public function detach(MediaAttachment $attachment): int
    {
        return DB::transaction(function () use ($attachment) {
            $ownerType = $attachment->owner_type;
            $ownerId = (int) $attachment->owner_id;
            $role = $attachment->role;
            $wasPrimary = (bool) $attachment->is_primary;

            $deleted = $attachment->delete() ? 1 : 0;

            if ($deleted && $wasPrimary) {
                /** @var MediaAttachment|null $next */
                $next = $this->query()
                    ->where('owner_type', $ownerType)
                    ->where('owner_id', $ownerId)
                    ->where('role', $role)
                    ->orderBy('position')
                    ->first();

                if ($next) {
                    $next->forceFill(['is_primary' => true])->save();
                    $this->enforceSinglePrimary($next);
                }
            }

            if ($deleted && $role === 'gallery') {
                $this->normalizePositions($ownerType, $ownerId, $role);
            }

            return $deleted;
        });
    }

    private function enforceSinglePrimary(MediaAttachment $attachment): void
    {
        $this->query()
            ->where('owner_type', $attachment->owner_type)
            ->where('owner_id', $attachment->owner_id)
            ->where('role', $attachment->role)
            ->where('id', '!=', $attachment->id)
            ->update(['is_primary' => false]);
    }

    private function normalizePositions(string $ownerType, int $ownerId, string $role): void
    {
        $ids = $this->query()
            ->where('owner_type', $ownerType)
            ->where('owner_id', $ownerId)
            ->where('role', $role)
            ->orderBy('position')
            ->pluck('id')
            ->map(fn ($v) => (int) $v)
            ->all();

        foreach ($ids as $i => $id) {
            $this->query()->where('id', $id)->update(['position' => $i + 1]);
        }
    }
}
