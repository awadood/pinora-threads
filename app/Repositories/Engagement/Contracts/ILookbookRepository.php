<?php

namespace App\Repositories\Engagement\Contracts;

use App\Models\Lookbook;
use App\Repositories\IBaseRepository;
use Illuminate\Database\Eloquent\Collection;

/**
 * ILookbookRepository
 *
 * Repository contract for managing lookbooks (campaigns).
 *
 * @author Abdul Wadood
 */
interface ILookbookRepository extends IBaseRepository
{
    /**
     * Find a lookbook by its unique slug.
     */
    public function findBySlug(string $slug): ?Lookbook;

    /**
     * Return active & published lookbooks for storefront.
     *
     * @return Collection<int, Lookbook>
     */
    public function getPublicLookbooks(): Collection;
}
