<?php

namespace App\Services\Engagement;

use App\Models\Lookbook;
use App\Repositories\Engagement\Contracts\ILookbookItemRepository;
use App\Repositories\Engagement\Contracts\ILookbookRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * LookbookService
 *
 * Orchestrates lookbook and item queries for storefront and admin.
 *
 * @author Abdul Wadood
 */
class LookbookService
{
    public function __construct(
        protected ILookbookRepository $lookbooks,
        protected ILookbookItemRepository $items
    ) {}

    /**
     * Public listing of lookbooks for storefront.
     *
     * @return Collection<int, Lookbook>
     */
    public function getPublicLookbooks(): Collection
    {
        return $this->lookbooks->getPublicLookbooks();
    }

    public function findBySlugOrFail(string $slug): Lookbook
    {
        $lookbook = $this->lookbooks->findBySlug($slug);

        if (! $lookbook) {
            throw (new ModelNotFoundException)->setModel(Lookbook::class, [$slug]);
        }

        return $lookbook;
    }

    public function getItemsByLookbookSlug(string $slug): Collection
    {
        $lookbook = $this->findBySlugOrFail($slug);

        return $this->items->getByLookbook($lookbook->getKey());
    }

    /**
     * Admin create.
     *
     * @param  array<string,mixed>  $data
     */
    public function create(array $data): Lookbook
    {
        /** @var Lookbook $lookbook */
        $lookbook = $this->lookbooks->create($data);

        return $lookbook;
    }

    /**
     * Admin update.
     *
     * @param  array<string,mixed>  $data
     */
    public function update(Lookbook $lookbook, array $data): Lookbook
    {
        $lookbook->fill($data);
        $lookbook->save();

        return $lookbook;
    }

    public function delete(Lookbook $lookbook): void
    {
        $lookbook->delete();
    }
}
