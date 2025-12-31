<?php

namespace App\Providers;

use App\Repositories\Auth\Contracts\IRoleRepository;
use App\Repositories\Auth\Contracts\IUserRepository;
use App\Repositories\Auth\RoleRepository;
use App\Repositories\Auth\UserRepository;
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
use App\Repositories\Catalog\Contracts\IProductPriceRepository;
use App\Repositories\Catalog\Contracts\IProductRepository;
use App\Repositories\Catalog\Contracts\IProductVariantPriceRepository;
use App\Repositories\Catalog\Contracts\IProductVariantRepository;
use App\Repositories\Catalog\Contracts\IRelatedProductRepository;
use App\Repositories\Catalog\ProductBundleRepository;
use App\Repositories\Catalog\ProductPriceRepository;
use App\Repositories\Catalog\ProductRepository;
use App\Repositories\Catalog\ProductVariantPriceRepository;
use App\Repositories\Catalog\ProductVariantRepository;
use App\Repositories\Catalog\RelatedProductRepository;
use App\Repositories\Customer\AddressRepository;
use App\Repositories\Customer\Contracts\IAddressRepository;
use App\Repositories\Customer\Contracts\ICustomerProfileRepository;
use App\Repositories\Customer\Contracts\IFavoriteRepository;
use App\Repositories\Customer\Contracts\IRecentlyViewedRepository;
use App\Repositories\Customer\Contracts\IWishlistItemRepository;
use App\Repositories\Customer\Contracts\IWishlistRepository;
use App\Repositories\Customer\CustomerProfileRepository;
use App\Repositories\Customer\FavoriteRepository;
use App\Repositories\Customer\RecentlyViewedRepository;
use App\Repositories\Customer\WishlistItemRepository;
use App\Repositories\Customer\WishlistRepository;
use App\Repositories\Engagement\Contracts\ILookbookItemProductRepository;
use App\Repositories\Engagement\Contracts\ILookbookItemRepository;
use App\Repositories\Engagement\Contracts\ILookbookRepository;
use App\Repositories\Engagement\Contracts\ITestimonialRepository;
use App\Repositories\Engagement\LookbookItemProductRepository;
use App\Repositories\Engagement\LookbookItemRepository;
use App\Repositories\Engagement\LookbookRepository;
use App\Repositories\Engagement\TestimonialRepository;
use App\Repositories\IBaseRepository;
use App\Repositories\Inventory\Contracts\IStockBackInSubscriptionRepository;
use App\Repositories\Inventory\Contracts\IStockBatchRepository;
use App\Repositories\Inventory\Contracts\IStockLevelRepository;
use App\Repositories\Inventory\Contracts\IStockMovementRepository;
use App\Repositories\Inventory\Contracts\IStockRepository;
use App\Repositories\Inventory\StockBackInSubscriptionRepository;
use App\Repositories\Inventory\StockBatchRepository;
use App\Repositories\Inventory\StockLevelRepository;
use App\Repositories\Inventory\StockMovementRepository;
use App\Repositories\Inventory\StockRepository;
use App\Repositories\Media\Contracts\IMediaAssetRepository;
use App\Repositories\Media\Contracts\IMediaAttachmentRepository;
use App\Repositories\Media\Contracts\IMediaRenditionRepository;
use App\Repositories\Media\Contracts\IMediaVideoRepository;
use App\Repositories\Media\MediaAssetRepository;
use App\Repositories\Media\MediaAttachmentRepository;
use App\Repositories\Media\MediaRenditionRepository;
use App\Repositories\Media\MediaVideoRepository;
use App\Repositories\Order\CartRepository;
use App\Repositories\Order\Contracts\ICartRepository;
use App\Repositories\Order\Contracts\IOrderRepository;
use App\Repositories\Order\OrderRepository;
use App\Repositories\Payment\Contracts\IInvoiceRepository;
use App\Repositories\Payment\Contracts\IPaymentAttemptRepository;
use App\Repositories\Payment\Contracts\IPaymentRepository;
use App\Repositories\Payment\Contracts\IRefundRepository;
use App\Repositories\Payment\InvoiceRepository;
use App\Repositories\Payment\PaymentAttemptRepository;
use App\Repositories\Payment\PaymentRepository;
use App\Repositories\Payment\RefundRepository;
use App\Repositories\Promotion\Contracts\IPromotionCouponRepository;
use App\Repositories\Promotion\Contracts\IPromotionRedemptionRepository;
use App\Repositories\Promotion\Contracts\IPromotionRepository;
use App\Repositories\Promotion\PromotionCouponRepository;
use App\Repositories\Promotion\PromotionRedemptionRepository;
use App\Repositories\Promotion\PromotionRepository;
use App\Repositories\Shipping\Contracts\IShipmentRepository;
use App\Repositories\Shipping\ShipmentRepository;
use App\Repositories\Tax\Contracts\ITaxCalculationRepository;
use App\Repositories\Tax\Contracts\ITaxClassRepository;
use App\Repositories\Tax\Contracts\ITaxRateRepository;
use App\Repositories\Tax\Contracts\ITaxRuleRepository;
use App\Repositories\Tax\TaxCalculationRepository;
use App\Repositories\Tax\TaxClassRepository;
use App\Repositories\Tax\TaxRateRepository;
use App\Repositories\Tax\TaxRuleRepository;
use App\Support\Roles;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public $bindings = [

        IBaseRepository::class => BaseRepository::class,
        IRoleRepository::class => RoleRepository::class,
        IUserRepository::class => UserRepository::class,

        // Catalog
        IAttributeRepository::class => AttributeRepository::class,
        IAttributeOptionRepository::class => AttributeOptionRepository::class,
        ICategoryRepository::class => CategoryRepository::class,
        ICollectionRepository::class => CollectionRepository::class,
        IProductRepository::class => ProductRepository::class,
        IProductVariantRepository::class => ProductVariantRepository::class,
        IProductPriceRepository::class => ProductPriceRepository::class,
        IProductVariantPriceRepository::class => ProductVariantPriceRepository::class,
        IProductBundleRepository::class => ProductBundleRepository::class,
        IRelatedProductRepository::class => RelatedProductRepository::class,
        ICategoryProductRepository::class => CategoryProductRepository::class,
        ICollectionProductRepository::class => CollectionProductRepository::class,

        // Customer
        IAddressRepository::class => AddressRepository::class,
        ICustomerProfileRepository::class => CustomerProfileRepository::class,
        IFavoriteRepository::class => FavoriteRepository::class,
        IRecentlyViewedRepository::class => RecentlyViewedRepository::class,
        IWishlistItemRepository::class => WishlistItemRepository::class,
        IWishlistRepository::class => WishlistRepository::class,

        // Engagement
        ITestimonialRepository::class => TestimonialRepository::class,
        ILookbookRepository::class => LookbookRepository::class,
        ILookbookItemRepository::class => LookbookItemRepository::class,
        ILookbookItemProductRepository::class => LookbookItemProductRepository::class,

        // Inventory
        IStockRepository::class => StockRepository::class,
        IStockLevelRepository::class => StockLevelRepository::class,
        IStockBatchRepository::class => StockBatchRepository::class,
        IStockMovementRepository::class => StockMovementRepository::class,
        IStockBackInSubscriptionRepository::class => StockBackInSubscriptionRepository::class,

        // Media
        IMediaAssetRepository::class => MediaAssetRepository::class,
        IMediaAttachmentRepository::class => MediaAttachmentRepository::class,
        IMediaRenditionRepository::class => MediaRenditionRepository::class,
        IMediaVideoRepository::class => MediaVideoRepository::class,

        // Order
        ICartRepository::class => CartRepository::class,
        IOrderRepository::class => OrderRepository::class,

        // Payment
        IInvoiceRepository::class => InvoiceRepository::class,
        IPaymentRepository::class => PaymentRepository::class,
        IPaymentAttemptRepository::class => PaymentAttemptRepository::class,
        IRefundRepository::class => RefundRepository::class,

        // Promotion
        IPromotionRepository::class => PromotionRepository::class,
        IPromotionCouponRepository::class => PromotionCouponRepository::class,
        IPromotionRedemptionRepository::class => PromotionRedemptionRepository::class,

        // Shipping
        IShipmentRepository::class => ShipmentRepository::class,

        // Tax
        ITaxClassRepository::class => TaxClassRepository::class,
        ITaxRuleRepository::class => TaxRuleRepository::class,
        ITaxRateRepository::class => TaxRateRepository::class,
        ITaxCalculationRepository::class => TaxCalculationRepository::class,
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

        JsonResource::withoutWrapping();
    }
}
