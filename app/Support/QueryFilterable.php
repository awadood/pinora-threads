<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * Trait QueryFilterable
 *
 * Reusable filter/sort helpers for index endpoints.
 * Supports query patterns like:
 *   ?filter[name.like]=velvet
 *   ?filter[active.eq]=1
 *   ?filter[type.in]=simple,bundle
 *   ?sort=slug,-name
 *
 * @author Abdul Wadood
 */
trait QueryFilterable
{
    /**
     * @var array<int,string> Column names that are allowed to be filtered.
     */
    protected array $allowedFilters = [];

    /**
     * @var array<int,string> Columns that support LIKE operator.
     */
    protected array $likeFilters = [];

    /**
     * @var array<int,string> Columns that are allowed for sorting.
     */
    protected array $allowedSorts = [];

    /**
     * @var array<int,string> Supported filter operators.
     */
    protected array $ops = ['eq', 'ne', 'like', 'in', 'gte', 'gt', 'lte', 'lt'];

    /**
     * Apply filter[]= conditions to a query builder.
     */
    protected function applyFilters(Builder $query, Request $request): Builder
    {
        $filters = $request->query('filter', []);
        if (! is_array($filters)) {
            return $query;
        }

        foreach ($filters as $key => $raw) {
            [$column, $op] = array_pad(explode('.', (string) $key, 2), 2, 'eq');

            if (! in_array($column, $this->allowedFilters, true)) {
                continue;
            }

            if (! in_array($op, $this->ops, true)) {
                continue;
            }

            if ($op === 'in') {
                $values = is_array($raw) ? $raw : explode(',', (string) $raw);
                $values = array_values(array_filter(array_map('trim', $values), static fn ($v) => $v !== ''));
                if (count($values) === 0) {
                    continue;
                }

                $query->whereIn($column, $values);

                continue;
            }

            $value = is_array($raw) ? Arr::first($raw) : $raw;

            switch ($op) {
                case 'ne':
                    $query->where($column, '!=', $value);
                    break;

                case 'like':
                    if (in_array($column, $this->likeFilters, true) && is_string($value)) {
                        $query->where($column, 'like', '%'.$value.'%');
                    }
                    break;

                case 'gte':
                    $query->where($column, '>=', $value);
                    break;

                case 'gt':
                    $query->where($column, '>', $value);
                    break;

                case 'lte':
                    $query->where($column, '<=', $value);
                    break;

                case 'lt':
                    $query->where($column, '<', $value);
                    break;

                case 'eq':
                default:
                    $query->where($column, $value);
                    break;
            }
        }

        return $query;
    }

    /**
     * Apply sort=column,-other_column semantics.
     */
    protected function applySorting(Builder $query, Request $request): Builder
    {
        $sortRaw = (string) $request->query('sort', '');
        if ($sortRaw === '') {
            if (! empty($this->allowedSorts)) {
                $query->orderBy($this->allowedSorts[0], 'asc');
            }

            return $query;
        }

        $parts = array_values(array_filter(array_map('trim', explode(',', $sortRaw))));
        foreach ($parts as $part) {
            $direction = Str::startsWith($part, '-') ? 'desc' : 'asc';
            $column = ltrim($part, '-');

            if (in_array($column, $this->allowedSorts, true)) {
                $query->orderBy($column, $direction);
            }
        }

        return $query;
    }
}
