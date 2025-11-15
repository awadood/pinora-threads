<?php

namespace App\Http\Controllers\Catalog;

use App\Http\Requests\Catalog\ProductMediaRequest;
use App\Http\Resources\Catalog\ProductMediaResource;
use App\Models\Product;
use App\Models\ProductMedia;
use App\Repositories\Catalog\Contracts\IProductMediaRepository;
use App\Support\QueryFilterable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * ProductMediaController
 *
 * Manage product-level media gallery.
 *
 * @author Abdul Wadood
 */
class ProductMediaController extends Controller
{
    use QueryFilterable;

    protected IProductMediaRepository $mediaRepo;

    public function __construct(IProductMediaRepository $mediaRepo)
    {
        $this->mediaRepo = $mediaRepo;

        $this->allowedFilters = ['product_id', 'type'];
        $this->likeFilters = [];
        $this->allowedSorts = ['position', 'id'];
    }

    public function indexByProductSlug(string $slug, Request $request)
    {
        $query = $this->mediaRepo->query()
            ->select('product_media.*')
            ->join('products', 'products.id', '=', 'product_media.product_id')
            ->where('products.slug', $slug);

        $query = $this->applySorting($this->applyFilters($query, $request), $request);

        return ProductMediaResource::collection($query->get());
    }

    public function store(ProductMediaRequest $request, Product $product)
    {
        $data = $request->validated();
        $data['product_id'] = $product->id;

        $media = $this->mediaRepo->create($data);

        return (new ProductMediaResource($media))->response()->setStatusCode(201);
    }

    public function update(ProductMediaRequest $request, ProductMedia $media)
    {
        $media->fill($request->validated())->save();

        return new ProductMediaResource($media);
    }

    public function destroy(ProductMedia $media)
    {
        $this->mediaRepo->disableIfNotDestroy($media);

        return response()->json([], 204);
    }
}
