<?php

namespace App\Providers;

use App\Repositories\BaseRepository;
use App\Repositories\Catalog\AttributeOptionRepository;
use App\Repositories\Catalog\AttributeRepository;
use App\Repositories\Catalog\CategoryProductRepository;
use App\Repositories\Catalog\CategoryRepository;
use App\Repositories\Catalog\CollectionProductRepository;
use App\Repositories\Catalog\CollectionRepository;
use App\Repositories\Catalog\Contracts\IAttributeOptionRepository;
use App\Repositories\Catalog\Contracts\IAttributeRepository;
use App\Repositories\Catalog\Contracts\ICategoryProductRepository;
use App\Repositories\Catalog\Contracts\ICategoryRepository;
use App\Repositories\Catalog\Contracts\ICollectionProductRepository;
use App\Repositories\Catalog\Contracts\ICollectionRepository;
use App\Repositories\Catalog\Contracts\IProductBundleRepository;
use App\Repositories\Catalog\Contracts\IProductMediaRepository;
use App\Repositories\Catalog\Contracts\IProductPriceRepository;
use App\Repositories\Catalog\Contracts\IProductRepository;
use App\Repositories\Catalog\Contracts\IProductVariantMediaRepository;
use App\Repositories\Catalog\Contracts\IProductVariantPriceRepository;
use App\Repositories\Catalog\Contracts\IProductVariantRepository;
use App\Repositories\Catalog\Contracts\IRelatedProductRepository;
use App\Repositories\Catalog\ProductBundleRepository;
use App\Repositories\Catalog\ProductMediaRepository;
use App\Repositories\Catalog\ProductPriceRepository;
use App\Repositories\Catalog\ProductRepository;
use App\Repositories\Catalog\ProductVariantMediaRepository;
use App\Repositories\Catalog\ProductVariantPriceRepository;
use App\Repositories\Catalog\ProductVariantRepository;
use App\Repositories\Catalog\RelatedProductRepository;
use App\Repositories\IBaseRepository;
use App\Support\Roles;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public $bindings = [

        IBaseRepository::class => BaseRepository::class,

        // Catalog
        IAttributeRepository::class => AttributeRepository::class,
        IAttributeOptionRepository::class => AttributeOptionRepository::class,
        ICategoryRepository::class => CategoryRepository::class,
        ICollectionRepository::class => CollectionRepository::class,
        IProductRepository::class => ProductRepository::class,
        IProductVariantRepository::class => ProductVariantRepository::class,
        IProductMediaRepository::class => ProductMediaRepository::class,
        IProductVariantMediaRepository::class => ProductVariantMediaRepository::class,
        IProductPriceRepository::class => ProductPriceRepository::class,
        IProductVariantPriceRepository::class => ProductVariantPriceRepository::class,
        IProductBundleRepository::class => ProductBundleRepository::class,
        IRelatedProductRepository::class => RelatedProductRepository::class,
        ICategoryProductRepository::class => CategoryProductRepository::class,
        ICollectionProductRepository::class => CollectionProductRepository::class,

        // Content

        // Core

        // Customer

        // Inventory

        // Order

        // Payment

        // Promotion

        // Shipping

        // Tax
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(function ($user, $ability) {
            return $user->hasRole(Roles::SUPER_ADMIN) ? true : null;
        });
    }
}
