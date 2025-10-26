<?php

namespace App\Repository\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class IBaseRepository
 *
 * @author Abdul Wadood
 */
interface IBaseRepository
{
    /**
     * @param  string[]  $columns
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function all(array $columns = ['*']): Collection|static;

    public function find($id): ?Model;

    public function create(array $attributes): Model;

    public function destroy($ids): int;

    /**
     * @throws \Exception
     */
    public function disableIfNotDestroy($entity): int;

    /**
     * It will fetch the records for the given client_id and criteria. If the
     * criteria is an empty error, all the records will fetched against the
     * given client_id.
     *
     * @param  int  $clientId
     * @param  array  $criteria  the conditions combined with or operator
     * @return \Illuminate\Database\Eloquent\Collection|$this
     */
    public function search(array $criteria): Collection|static;
}
