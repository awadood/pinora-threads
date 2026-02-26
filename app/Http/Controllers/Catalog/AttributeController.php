<?php

namespace App\Http\Controllers\Catalog;

use App\Http\Requests\Catalog\AttributeRequest;
use App\Http\Resources\Catalog\AttributeResource;
use App\Models\Attribute;
use App\Repositories\Catalog\Contracts\IAttributeRepository;
use App\Support\QueryFilterable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * AttributeController
 *
 * Manage product attributes (e.g. color, size).
 *
 * @author Abdul Wadood
 */
class AttributeController extends Controller
{
    use QueryFilterable;

    public function __construct(protected IAttributeRepository $attributes)
    {
        $this->allowedFilters = ['code', 'label', 'type', 'active'];
        $this->likeFilters = ['code', 'label'];
        $this->allowedSorts = ['code', 'label'];
    }

    public function index(Request $request)
    {
        $query = $this->applySorting(
            $this->applyFilters($this->attributes->query()->with('options.thumbnailMedia.asset.renditions'), $request),
            $request
        );

        return AttributeResource::collection($query->get());
    }

    public function show(Attribute $attribute)
    {
        $attribute->loadMissing('options.thumbnailMedia.asset.renditions');

        return AttributeResource::make($attribute);
    }

    public function store(AttributeRequest $request)
    {
        $attribute = $this->attributes->create($request->validated());

        return AttributeResource::make($attribute)->response()->setStatusCode(201);
    }

    public function update(AttributeRequest $request, Attribute $attribute)
    {
        $attribute->update($request->validated());

        return AttributeResource::make($attribute);
    }

    public function destroy(Attribute $attribute)
    {
        $this->attributes->disableIfNotDestroy($attribute);

        return response()->json([], 204);
    }
}
