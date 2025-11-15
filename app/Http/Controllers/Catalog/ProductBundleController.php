<?php

namespace App\Http\Controllers\Catalog;

use App\Http\Requests\Catalog\ProductBundleRequest;
use App\Http\Resources\Catalog\ProductBundleResource;
use App\Models\ProductBundle;
use App\Repositories\Catalog\Contracts\IProductBundleRepository;
use Illuminate\Routing\Controller;

/**
 * ProductBundleController
 *
 * Manage bundle compositions (bundle product -> child variants).
 *
 * @author Abdul Wadood
 */
class ProductBundleController extends Controller
{
    protected IProductBundleRepository $bundles;

    public function __construct(IProductBundleRepository $bundles)
    {
        $this->bundles = $bundles;
    }

    public function store(ProductBundleRequest $request)
    {
        $bundle = $this->bundles->create($request->validated());

        return (new ProductBundleResource($bundle))->response()->setStatusCode(201);
    }

    public function update(ProductBundleRequest $request, ProductBundle $bundle)
    {
        $bundle->fill($request->validated())->save();

        return new ProductBundleResource($bundle);
    }

    public function destroy(ProductBundle $bundle)
    {
        $this->bundles->disableIfNotDestroy($bundle);

        return response()->json([], 204);
    }
}
