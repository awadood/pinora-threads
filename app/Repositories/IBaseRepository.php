<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Base repository contract for simple CRUD and constrained searches.
 *
 * @author Abdul Wadood
 */
interface IBaseRepository
{
    /**
     * Base query builder for the model.
     */
    public function query(): Builder;

    public function all(array $columns = ['*']): Collection;

    public function find(int|string $id): ?Model;

    public function create(array $attributes): Model;

    public function destroy(int|string|array $ids): int;

    /**
     * It attempts to delete. On FK violation, fallback to setting
     * active to false if the model supports it.
     */
    public function disableIfNotDestroy(Model $entity): int;

    /**
     * Safe OR-combined search with whitelisted columns/operators.
     *
     * @param  array<int,array{col:string,op:string,value:mixed}>  $criteria
     * @param  array<int,string>  $columns
     * @param  array<int,string>  $with
     * @return Collection<int, Model>
     */
    public function search(array $criteria, array $with = [], array $columns = ['*']): Collection;
}
