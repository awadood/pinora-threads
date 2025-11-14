<?php

namespace App\Repository;

use App\Repository\Contracts\IBaseRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;

/**
 * BaseRepository with safe FK handling + criteria whitelisting.
 *
 * @author Abdul Wadood
 */
abstract class BaseRepository implements IBaseRepository
{
    private const FOREIGN_KEY_VIOLATION = 23503;

    /**
     * @var class-string<TModel>
     */
    protected string $model;

    /**
     * Columns you allow in search() to avoid accidental/unsafe queries.
     *
     * @var array<string, true>
     */
    protected array $allowedSearchColumns = [];

    /**
     * Operators you allow in search().
     *
     * @var array<string, true>
     */
    protected array $allowedOperators = [
        '=' => true, '!=' => true, '<' => true, '<=' => true, '>' => true, '>=' => true,
        'like' => true, 'ilike' => true, 'in' => true, 'not in' => true, 'between' => true,
    ];

    public function query(): Builder
    {
        /** @var Builder */
        return ($this->model)::query();
    }

    public function all(array $columns = ['*']): Collection
    {
        return $this->query()->get($columns);
    }

    public function find($id): ?Model
    {
        return $this->query()->find($id);
    }

    public function create(array $attributes): Model
    {
        if (! array_key_exists('active', $attributes)) {
            $attributes['active'] = true;
        }

        /** @var Model */
        $model = $this->query()->create($attributes);

        return $model;
    }

    public function destroy(int|string|array $ids): int
    {
        return ($this->model)::destroy($ids);
    }

    public function disableIfNotDestroy(Model $entity): int
    {
        try {
            return $entity->delete() ? 1 : 0;
        } catch (QueryException $exception) {
            if ($exception->getCode() == self::FOREIGN_KEY_VIOLATION) {
                if (in_array('active', $entity->getFillable(), true)) {
                    $entity->forceFill(['active' => false])->save();

                    return 1;
                }
            }

            throw $exception;
        }
    }

    public function search(array $criteria, array $columns = ['*']): Collection
    {
        $builder = $this->query();

        $builder->where(function (Builder $q) use ($criteria) {
            foreach ($criteria as $c) {
                $col = $c['col'] ?? null;
                $op = strtolower($c['op'] ?? '=');
                $val = $c['value'] ?? null;

                if (! $col || ! isset($this->allowedSearchColumns[$col]) || ! isset($this->allowedOperators[$op])) {
                    continue; // skip unsafe clauses silently; or throw if you prefer
                }

                if (in_array($op, ['in', 'not in'], true) && is_array($val)) {
                    $op === 'in'
                        ? $q->orWhereIn($col, $val)
                        : $q->orWhereNotIn($col, $val);
                } elseif ($op === 'between' && is_array($val) && count($val) === 2) {
                    $q->orWhereBetween($col, [$val[0], $val[1]]);
                } else {
                    $q->orWhere($col, $op, $val);
                }
            }
        });

        return $builder->get($columns);
    }
}
