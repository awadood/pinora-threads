<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use Spatie\Permission\Models\Permission;

/**
 * Class PermissionTransformer
 * @package App\Transformers
 * @author Abdul Wadood
 */
class PermissionTransformer extends TransformerAbstract
{
    public function transform(Permission $permission): array
    {
        return [
            $permission->name,
        ];
    }
}
