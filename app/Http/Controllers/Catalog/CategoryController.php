<?php

namespace App\Http\Controllers\Catalog;

use App\Http\Requests\Catalog\CategoryRequest;
use App\Http\Resources\Catalog\CategoryResource;
use App\Http\Resources\Catalog\ProductResource;
use App\Models\Category;
use App\Repositories\Catalog\Contracts\ICategoryRepository;
use App\Repositories\Catalog\Contracts\IProductRepository;
use App\Support\QueryFilterable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * CategoryController
 *
 * Manage product categories and expose category listings.
 *
 * @author Abdul Wadood
 */
class CategoryController extends Controller
{
    use QueryFilterable;

    protected ICategoryRepository $categories;

    protected IProductRepository $products;

    public function __construct(ICategoryRepository $categories, IProductRepository $products)
    {
        $this->categories = $categories;
        $this->products = $products;

        $this->allowedFilters = ['name', 'slug', 'parent_id', 'active'];
        $this->likeFilters = ['name', 'slug'];
        $this->allowedSorts = ['sort', 'name'];
    }

    public function index(Request $request)
    {
        $query = $this->applySorting(
            $this->applyFilters($this->categories->query(), $request),
            $request
        );

        return CategoryResource::collection($query->get());
    }

    public function showBySlug(string $slug)
    {
        $category = $this->categories->query()->where('slug', $slug)->firstOrFail();

        return new CategoryResource($category);
    }

    public function indexByCategory(string $slug, Request $request)
    {
        $category = $this->categories->query()->where('slug', $slug)->firstOrFail();

        $query = $this->products->query()
            ->select('products.*')
            ->join('category_product', 'category_product.product_id', '=', 'products.id')
            ->where('category_product.category_id', $category->id);

        $query = $this->applySorting($query, $request);

        return ProductResource::collection($query->get());
    }

    public function store(CategoryRequest $request)
    {
        $category = $this->categories->create($request->validated());

        return (new CategoryResource($category))->response()->setStatusCode(201);
    }

    public function update(CategoryRequest $request, Category $category)
    {
        $category->fill($request->validated())->save();

        return new CategoryResource($category);
    }

    public function destroy(Category $category)
    {
        $this->categories->disableIfNotDestroy($category);

        return response()->json([], 204);
    }
}
