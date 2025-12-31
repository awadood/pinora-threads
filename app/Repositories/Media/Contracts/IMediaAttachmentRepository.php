<?php

namespace App\Repositories\Media\Contracts;

use App\Models\MediaAttachment;
use App\Repositories\IBaseRepository;
use Illuminate\Support\Collection;

interface IMediaAttachmentRepository extends IBaseRepository
{
    /**
     * Attach an asset to an owner with role/position/primary and optional overrides.
     *
     * Semantics:
     * - Creates media_attachments row.
     * - Does not delete/modify media_assets.
     */
    public function attach(array $data): MediaAttachment;

    /**
     * Update attachment overrides (alt_text/caption) and optionally position.
     *
     * Semantics:
     * - Updates only media_attachments fields.
     */
    public function updateAttachment(MediaAttachment $attachment, array $data): MediaAttachment;

    /**
     * Set attachment as primary for (owner_type, owner_id, role).
     *
     * Semantics:
     * - Sets this row is_primary=true.
     * - Sets all other rows in same group is_primary=false.
     * - Atomic.
     */
    public function setPrimary(MediaAttachment $attachment): MediaAttachment;

    /**
     * Reorder attachments for one owner+role by rewriting position = 1..N.
     *
     * Semantics:
     * - Affects only the specified owner_type, owner_id, role.
     * - Atomic.
     */
    public function reorder(string $ownerType, int $ownerId, string $role, array $attachmentIdsOrdered): int;

    /**
     * Replace a single-role attachment (thumbnail/hero/og_image) for an owner.
     *
     * Semantics:
     * - Deletes existing attachments for that owner+role.
     * - Creates one attachment with position=1 and is_primary=true.
     * - Atomic.
     */
    public function replaceSingle(array $data): MediaAttachment;

    /**
     * List attachments for a given owner, grouped by role for admin UI consumption.
     */
    public function listForOwner(string $ownerType, int $ownerId): Collection;

    /**
     * Detach (delete) a media attachment row.
     *
     * Semantics:
     * - Deletes media_attachments record only.
     * - Does not delete underlying media_asset.
     */
    public function detach(MediaAttachment $attachment): int;
}
