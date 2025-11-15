<?php

namespace App\Http\Controllers\Core;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

/**
 * Shared CRUD + explicit filter/sort for small lookup tables.
 * Children must set: $modelClass, $resourceClass, $allowedFilters, $allowedSorts.
 * Children only implement `rules()` and thin wrappers for show/update/destroy to keep type-safe bindings.
 *
 * @author Abdul Wadood
 */
abstract class BaseLookupController extends Controller
{
    /** @var class-string<Model> */
    protected string $modelClass;

    /** @var class-string<\Illuminate\Http\Resources\Json\JsonResource> */
    protected string $resourceClass;

    /** @var string[] columns allowed for filtering (keys like 'name', 'code', 'active', 'sort_order') */
    protected array $allowedFilters = [];

    /** @var string[] columns that allow LIKE operator */
    protected array $likeFilters = [];

    /** @var string[] columns allowed for sorting */
    protected array $allowedSorts = [];

    /** Supported filter operators */
    protected array $ops = ['eq', 'ne', 'like', 'in', 'gte', 'gt', 'lte', 'lt'];

    /**
     * One rule set; you can switch by method or by $model if needed.
     */
    abstract protected function rules(Request $request, ?Model $model = null): array;

    protected function baseQuery(): Builder
    {
        /** @var Model $m */
        $m = new $this->modelClass;

        return $m->newQuery();
    }

    /**
     * Parse filters from query string:
     *   ?filter[name.like]=stan
     *   ?filter[code.in]=PK,US
     *   ?filter[active]=1
     */
    protected function applyFilters(Builder $query, Request $request): Builder
    {
        $filters = $request->query('filter', []);
        if (! is_array($filters)) {
            return $query;
        }

        foreach ($filters as $key => $raw) {
            // key may be "name.like" or just "name"
            [$column, $op] = array_pad(explode('.', (string) $key, 2), 2, 'eq');

            if (! in_array($column, $this->allowedFilters, true)) {
                continue; // ignore unsafe column
            }
            if (! in_array($op, $this->ops, true)) {
                $op = 'eq';
            }

            // normalize value(s)
            if ($op === 'in') {
                $values = is_array($raw) ? $raw : explode(',', (string) $raw);
                $values = array_values(array_filter(array_map('trim', $values), fn ($v) => $v !== ''));
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
     * Sorting: ?sort=code,-name
     */
    protected function applySorting(Builder $query, Request $request): Builder
    {
        $sortRaw = (string) $request->query('sort', '');
        if ($sortRaw === '') {
            // default to first allowed column asc, if any
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

    public function index(Request $request)
    {
        $query = $this->applySorting($this->applyFilters($this->baseQuery(), $request), $request);
        $items = $query->get();

        $resource = $this->resourceClass;

        return $resource::collection($items);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules($request, null));

        /** @var Model $created */
        $created = ($this->modelClass)::create($validated);

        $resource = $this->resourceClass;

        return (new $resource($created))->response()->setStatusCode(201);
    }

    protected function performShow(Model $model)
    {
        $resource = $this->resourceClass;

        return new $resource($model);
    }

    protected function performUpdate(Request $request, Model $model)
    {
        $validated = $request->validate($this->rules($request, $model));
        $model->fill($validated)->save();

        $resource = $this->resourceClass;

        return new $resource($model);
    }

    protected function performDestroy(Model $model): JsonResponse
    {
        try {
            $model->delete();

            return response()->json([], 204);
        } catch (Throwable $e) {
            // FK violation codes: PGSQL=23503, MySQL=23000/1451, etc. We’ll fallback by feature, not code.
            // If the table has 'active' column, flip to false.
            $table = $model->getTable();
            if (Schema::hasColumn($table, 'active')) {
                $model->forceFill(['active' => false])->save();

                return response()->json([], 204);
            }
            throw $e;
        }
    }
}
