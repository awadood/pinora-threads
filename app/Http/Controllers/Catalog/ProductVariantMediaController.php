<?php

namespace App\Http\Controllers\Catalog;

use App\Http\Requests\Catalog\ProductVariantMediaRequest;
use App\Http\Resources\Catalog\ProductVariantMediaResource;
use App\Models\ProductVariant;
use App\Models\ProductVariantMedia;
use App\Repositories\Catalog\Contracts\IProductVariantMediaRepository;
use App\Support\QueryFilterable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * ProductVariantMediaController
 *
 * Manage variant-specific gallery media.
 *
 * @author Abdul Wadood
 */
class ProductVariantMediaController extends Controller
{
    use QueryFilterable;

    protected IProductVariantMediaRepository $mediaRepo;

    public function __construct(IProductVariantMediaRepository $mediaRepo)
    {
        $this->mediaRepo = $mediaRepo;

        $this->allowedFilters = ['product_variant_id', 'type'];
        $this->likeFilters = [];
        $this->allowedSorts = ['position', 'id'];
    }

    public function indexByVariant(ProductVariant $id, Request $request)
    {
        $query = $this->mediaRepo->query()->where('product_variant_id', $id->id);
        $query = $this->applySorting($this->applyFilters($query, $request), $request);

        return ProductVariantMediaResource::collection($query->get());
    }

    public function store(ProductVariantMediaRequest $request, ProductVariant $variant)
    {
        $data = $request->validated();
        $data['product_variant_id'] = $variant->id;

        $media = $this->mediaRepo->create($data);

        return (new ProductVariantMediaResource($media))->response()->setStatusCode(201);
    }

    public function update(ProductVariantMediaRequest $request, ProductVariantMedia $media)
    {
        $media->fill($request->validated())->save();

        return new ProductVariantMediaResource($media);
    }

    public function destroy(ProductVariantMedia $media)
    {
        $this->mediaRepo->disableIfNotDestroy($media);

        return response()->json([], 204);
    }
}
