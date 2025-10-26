<?php

namespace App\Repository;

use App\Repository\Contracts\IBaseRepository;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class BaseRepository
 *
 * @author Abdul Wadood
 */
abstract class BaseRepository implements IBaseRepository
{
    private const FOREIGN_KEY_VIOLATION = 23503;

    protected string $model;

    /**
     * {@inheritDoc}
     */
    public function all(array $columns = ['*']): Collection|static
    {
        return call_user_func($this->model.'::all', $columns);
    }

    /**
     * {@inheritDoc}
     */
    public function find($id): ?Model
    {
        return call_user_func($this->model.'::find', $id);
    }

    /**
     * {@inheritDoc}
     */
    public function create(array $attributes): Model
    {
        if (! array_key_exists('active', $attributes)) {
            $attributes['active'] = true;
        }

        return call_user_func($this->model.'::create', $attributes);
    }

    /**
     * {@inheritDoc}
     */
    public function destroy($ids): int
    {
        return call_user_func($this->model.'::destroy', $ids);
    }

    /**
     * {@inheritDoc}
     */
    public function disableIfNotDestroy($entity): int
    {
        try {
            return call_user_func($this->model.'::destroy', $entity->id);
        } catch (Exception $exception) {
            if ($exception->getCode() == self::FOREIGN_KEY_VIOLATION) {
                $entity->update(['active' => false]);

                return 1;
            }

            throw $exception;
        }

        return 0;
    }

    /**
     * {@inheritDoc}
     */
    public function search(array $criteria): Collection|static
    {
        $builder = $this->model::where(function ($query) use ($criteria) {
            foreach ($criteria as $condition) {
                $query->orWhere($condition['col'], $condition['op'], $condition['value']);
            }
        });

        return $builder->get();
    }
}
