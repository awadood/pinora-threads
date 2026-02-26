<?php

namespace App\Http\Controllers\Catalog;

use App\Http\Requests\Catalog\AttributeOptionRequest;
use App\Http\Resources\Catalog\AttributeOptionResource;
use App\Models\Attribute;
use App\Models\AttributeOption;
use App\Repositories\Catalog\Contracts\IAttributeOptionRepository;
use App\Support\QueryFilterable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * AttributeOptionController
 *
 * Manage options for selectable attributes (e.g. color: red/blue).
 *
 * @author Abdul Wadood
 */
class AttributeOptionController extends Controller
{
    use QueryFilterable;

    public function __construct(protected IAttributeOptionRepository $options)
    {
        $this->allowedFilters = ['attribute_id', 'value'];
        $this->likeFilters = ['value'];
        $this->allowedSorts = ['attribute_id', 'sort', 'value'];
    }

    public function indexByAttribute(Attribute $attribute, Request $request)
    {
        $query = $this->options->query()
            ->where('attribute_id', $attribute->id)
            ->with(['attribute', 'thumbnailMedia.asset.renditions']);
        $query = $this->applySorting($query, $request);

        return AttributeOptionResource::collection($query->get());
    }

    public function store(AttributeOptionRequest $request)
    {
        $option = $this->options->create($request->validated());

        $option->loadMissing(['attribute', 'thumbnailMedia.asset.renditions']);

        return AttributeOptionResource::make($option)->response()->setStatusCode(201);
    }

    public function update(AttributeOptionRequest $request, AttributeOption $attributeOption)
    {
        $attributeOption->update($request->validated());

        $attributeOption->loadMissing(['attribute', 'thumbnailMedia.asset.renditions']);

        return AttributeOptionResource::make($attributeOption);
    }

    public function destroy(AttributeOption $attribute_option)
    {
        $this->options->disableIfNotDestroy($attribute_option);

        return response()->json([], 204);
    }
}
