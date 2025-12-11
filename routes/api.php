<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\PermissionController;
use App\Http\Controllers\Auth\RegistrationController;
use App\Http\Controllers\Auth\RoleController;
use App\Http\Controllers\Auth\UserController;
use App\Http\Controllers\Catalog\AttributeController;
use App\Http\Controllers\Catalog\AttributeOptionController;
use App\Http\Controllers\Catalog\CategoryController;
use App\Http\Controllers\Catalog\CategoryProductController;
use App\Http\Controllers\Catalog\CollectionController;
use App\Http\Controllers\Catalog\CollectionProductController;
use App\Http\Controllers\Catalog\ProductBundleController;
use App\Http\Controllers\Catalog\ProductController;
use App\Http\Controllers\Catalog\ProductMediaController;
use App\Http\Controllers\Catalog\ProductPriceController;
use App\Http\Controllers\Catalog\ProductVariantController;
use App\Http\Controllers\Catalog\ProductVariantMediaController;
use App\Http\Controllers\Catalog\ProductVariantPriceController;
use App\Http\Controllers\Catalog\RelatedProductController;
use App\Http\Controllers\Core\CountryController;
use App\Http\Controllers\Core\CurrencyController;
use App\Http\Controllers\Core\CustomerGroupController;
use App\Http\Controllers\Core\InvoiceStatusController;
use App\Http\Controllers\Core\OrderStatusController;
use App\Http\Controllers\Core\PaymentMethodController;
use App\Http\Controllers\Core\PaymentStatusController;
use App\Http\Controllers\Core\RefundStatusController;
use App\Http\Controllers\Core\ShipmentMethodController;
use App\Http\Controllers\Core\ShipmentStatusController;
use App\Http\Controllers\Core\StateController;
use App\Http\Controllers\Core\StockMovementTypeController;
use App\Http\Controllers\Customer\AddressController;
use App\Http\Controllers\Customer\CustomerProfileController;
use App\Http\Controllers\Customer\FavoriteController;
use App\Http\Controllers\Customer\RecentlyViewedController;
use App\Http\Controllers\Customer\WishlistController;
use App\Http\Controllers\Customer\WishlistItemController;
use App\Http\Controllers\Engagement\LookbookController;
use App\Http\Controllers\Engagement\LookbookItemController;
use App\Http\Controllers\Engagement\LookbookItemProductController;
use App\Http\Controllers\Engagement\TestimonialController;
use App\Http\Controllers\Inventory\StockBackInSubscriptionController;
use App\Http\Controllers\Inventory\StockBatchController;
use App\Http\Controllers\Inventory\StockController;
use App\Http\Controllers\Inventory\StockLevelController;
use App\Http\Controllers\Inventory\StockMovementController;
use App\Http\Controllers\Order\CartController;
use App\Http\Controllers\Order\OrderController;
use App\Http\Controllers\Payment\InvoiceController;
use App\Http\Controllers\Payment\PaymentAttemptController;
use App\Http\Controllers\Payment\PaymentController;
use App\Http\Controllers\Payment\RefundController;
use App\Http\Controllers\Promotion\PromotionController;
use App\Http\Controllers\Promotion\PromotionCouponController;
use App\Http\Controllers\Promotion\PromotionRedemptionController;
use App\Http\Controllers\Shipping\ShipmentController;
use App\Http\Controllers\Tax\TaxCalculationController;
use App\Http\Controllers\Tax\TaxClassController;
use App\Http\Controllers\Tax\TaxRateController;
use App\Http\Controllers\Tax\TaxRuleController;
use App\Support\Permissions as P;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public endpoints
|--------------------------------------------------------------------------
|
| The following routes are mostly customer facing storefront or readonly.
|
*/

// Login and password flows
Route::post('/login', [AuthController::class, 'loginToken']); // PAT
Route::post('/register', [RegistrationController::class, 'register'])->middleware('throttle:10,1');
Route::post('/forgot-password', [PasswordResetController::class, 'sendLink'])->middleware('throttle:6,1');
Route::post('/reset-password', [PasswordResetController::class, 'reset'])->middleware('throttle:6,1');

// Core tables read only operations
Route::get('countries', [CountryController::class, 'index']);
Route::get('countries/{country}', [CountryController::class, 'show']);
Route::get('states', [StateController::class, 'index']);
Route::get('states/{state}', [StateController::class, 'show']);
Route::get('currencies', [CurrencyController::class, 'index']);
Route::get('currencies/{currency}', [CurrencyController::class, 'show']);
Route::get('customer-groups', [CustomerGroupController::class, 'index']);
Route::get('customer-groups/{customer_group}', [CustomerGroupController::class, 'show']);
Route::get('order-statuses', [OrderStatusController::class, 'index']);
Route::get('order-statuses/{order_status}', [OrderStatusController::class, 'show']);
Route::get('shipment-statuses', [ShipmentStatusController::class, 'index']);
Route::get('shipment-statuses/{shipment_status}', [ShipmentStatusController::class, 'show']);
Route::get('payment-statuses', [PaymentStatusController::class, 'index']);
Route::get('payment-statuses/{payment_status}', [PaymentStatusController::class, 'show']);
Route::get('invoice-statuses', [InvoiceStatusController::class, 'index']);
Route::get('invoice-statuses/{invoice_status}', [InvoiceStatusController::class, 'show']);
Route::get('refund-statuses', [RefundStatusController::class, 'index']);
Route::get('refund-statuses/{refund_status}', [RefundStatusController::class, 'show']);
Route::get('payment-methods', [PaymentMethodController::class, 'index']);
Route::get('payment-methods/{payment_method}', [PaymentMethodController::class, 'show']);
Route::get('shipment-methods', [ShipmentMethodController::class, 'index']);
Route::get('shipment-methods/{shipment_method}', [ShipmentMethodController::class, 'show']);
Route::get('stock-movement-types', [StockMovementTypeController::class, 'index']);
Route::get('stock-movement-types/{stock_movement_type}', [StockMovementTypeController::class, 'show']);

// Catalog - Read only, used by frontend (PLP/PDP, filters, menus).
Route::get('attributes', [AttributeController::class, 'index']); // maybe optional // ?filter[type.eq]=select&filter[active.eq]=1
Route::get('attributes/{code}', [AttributeController::class, 'showByCode']); // by code instead of id, if you choose
Route::get('attributes/{code}/options', [AttributeOptionController::class, 'indexByAttribute']); // color options, etc.
Route::get('categories', [CategoryController::class, 'index']); // category tree / flat list
Route::get('categories/{slug}', [CategoryController::class, 'showBySlug']); // details + children
Route::get('categories/{slug}/products', [ProductController::class, 'indexByCategory']); // PLP by category
Route::get('collections', [CollectionController::class, 'index']); // list active collections
Route::get('collections/{slug}', [CollectionController::class, 'showBySlug']);
Route::get('collections/{slug}/products', [ProductController::class, 'indexByCollection']);
Route::get('products', [ProductController::class, 'index']); // Product listing. Typical filters: ?filter[type.eq]=simple&filter[active.eq]=1&filter[price.gte]=1000 etc.
Route::get('products/{slug}', [ProductController::class, 'showBySlug']); // PDP
Route::get('products/{slug}/variants', [ProductVariantController::class, 'indexByProductSlug']); // variant matrix on PDP
Route::get('products/{slug}/media', [ProductMediaController::class, 'indexByProductSlug']); // gallery
Route::get('products/{slug}/related', [RelatedProductController::class, 'indexByProductSlug']); // maybe optional
Route::get('product-variants/{id}', [ProductVariantController::class, 'show']); // rarely needed on storefront
Route::get('product-variants/{id}/media', [ProductVariantMediaController::class, 'indexByVariant']);
Route::get('product-variants/{id}/prices', [ProductVariantPriceController::class, 'indexByVariant']);

// Customer
Route::get('wishlists/shared/{share_token}', [WishlistController::class, 'showByShareToken']);

// Engagement
Route::get('testimonials', [TestimonialController::class, 'index']);
Route::get('lookbooks', [LookbookController::class, 'index']);
Route::get('lookbooks/{slug}', [LookbookController::class, 'showBySlug']);
Route::get('lookbooks/{slug}/items', [LookbookController::class, 'items']);
Route::get('lookbook-items/{item}', [LookbookItemController::class, 'show']);
Route::get('lookbook-items/{item}/products', [LookbookItemProductController::class, 'index']);

// Inventory
Route::get('stocks', [StockController::class, 'index']);
Route::get('stocks/{stock}', [StockController::class, 'show']);
Route::get('stock-levels', [StockLevelController::class, 'index']);
Route::get('stock-levels/{stock_level}', [StockLevelController::class, 'show']);
Route::get('stock-batches', [StockBatchController::class, 'index']);
Route::get('stock-batches/{stock_batch}', [StockBatchController::class, 'show']);
Route::get('stock-movements', [StockMovementController::class, 'index']);
Route::get('stock-movements/{stock_movement}', [StockMovementController::class, 'show']);
Route::post('stock-back-in-subscriptions', [StockBackInSubscriptionController::class, 'store']); // create requires auth or email

// Order
Route::get('cart', [CartController::class, 'show']);
Route::post('cart/items', [CartController::class, 'addItem']);
Route::put('cart/items/{item}', [CartController::class, 'updateItem']);
Route::delete('cart/items/{item}', [CartController::class, 'removeItem']);
Route::delete('cart/clear', [CartController::class, 'clear']);
Route::post('cart/checkout', [OrderController::class, 'checkout']);

// Payment
Route::get('invoices', [InvoiceController::class, 'indexCustomer']);
Route::get('invoices/{invoice}', [InvoiceController::class, 'showCustomer']);
Route::get('orders/{order}/payment-attempts', [PaymentAttemptController::class, 'indexForOrder']);
Route::post('orders/{order}/payment-attempts', [PaymentAttemptController::class, 'storeForOrder']);
// Refunds (customer self-service — optional; can be no-op now or restricted)
// Route::post('orders/{order}/refunds', [RefundController::class, 'storeCustomer']);

// Promotion
Route::get('promotions', [PromotionController::class, 'indexPublic']);
Route::get('promotions/{promotion}', [PromotionController::class, 'showPublic']);

/*
|--------------------------------------------------------------------------
| Sanctum protected APIs - Auth & Access Control & Monitoring
|--------------------------------------------------------------------------
|
| The following routes are about login and passoword flows
|
*/

Route::middleware(['auth:sanctum'])->group(function () {

    // Auth
    Route::post('logout', [AuthController::class, 'logoutToken']);
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'send'])->middleware('throttle:6,1');
    Route::get('/verify-email/{id}/{hash}', [EmailVerificationController::class, 'verify'])->middleware(['signed', 'throttle:6,1']);
    Route::get('/user', [AuthController::class, 'user']); // Who am I

    // Activity logs
    Route::get('/activity-logs', [ActivityLogController::class, 'index']);      // list + filters
    Route::get('/activity-logs/filters', [ActivityLogController::class, 'filters']); // dropdowns
    Route::get('/activity-logs/subject', [ActivityLogController::class, 'subject']); // subject timeline
    Route::get('/activity-logs/{id}', [ActivityLogController::class, 'show']);   // single detail

    // Admin Users
    Route::get('admin/users', [UserController::class, 'index'])->middleware('permission:'.P::USER_VIEW);
    Route::post('admin/users', [UserController::class, 'store'])->middleware('permission:'.P::USER_CREATE);
    Route::get('admin/users/{user}', [UserController::class, 'show'])->middleware('permission:'.P::USER_VIEW);
    Route::put('admin/users/{user}', [UserController::class, 'update'])->middleware('permission:'.P::USER_UPDATE);
    Route::delete('admin/users/{user}', [UserController::class, 'destroy'])->middleware('permission:'.P::USER_DESTROY);

    // Manage user roles & permissions
    Route::put('admin/users/{user}/roles', [UserController::class, 'syncRoles'])->middleware('permission:'.P::USER_UPDATE);
    Route::put('admin/users/{user}/permissions', [UserController::class, 'syncPermissions'])->middleware('permission:'.P::USER_UPDATE);
    Route::patch('admin/users/{user}/status', [UserController::class, 'toggleStatus'])->middleware('permission:'.P::USER_UPDATE);

    // Roles
    Route::get('roles', [RoleController::class, 'index'])->middleware('permission:'.P::ROLE_VIEW);
    Route::post('roles', [RoleController::class, 'store'])->middleware('permission:'.P::ROLE_CREATE);
    Route::get('roles/{role}', [RoleController::class, 'show'])->middleware('permission:'.P::ROLE_VIEW);
    Route::put('roles/{role}', [RoleController::class, 'update'])->middleware('permission:'.P::ROLE_UPDATE);
    Route::delete('roles/{role}', [RoleController::class, 'destroy'])->middleware('permission:'.P::ROLE_DESTROY);
    Route::put('roles/{role}/permissions', [RoleController::class, 'syncPermissions'])->middleware('permission:'.P::ROLE_UPDATE);

    // Permissions (readonly)
    Route::get('permissions', [PermissionController::class, 'index'])->middleware('permission:'.P::ROLE_CREATE);
});

/*
|--------------------------------------------------------------------------
| Sanctum protected APIs - Catalog
|--------------------------------------------------------------------------
|
| These routes are for admin with permissions.
|
*/

Route::middleware('auth:sanctum')->group(function () {

    // Attributes
    Route::post('attributes', [AttributeController::class, 'store'])->middleware('permission:'.P::ATTR_CREATE);
    Route::put('attributes/{attribute}', [AttributeController::class, 'update'])->middleware('permission:'.P::ATTR_UPDATE);
    Route::delete('attributes/{attribute}', [AttributeController::class, 'destroy'])->middleware('permission:'.P::ATTR_DESTROY);
    Route::post('attribute-options', [AttributeOptionController::class, 'store'])->middleware('permission:'.P::ATTROPT_CREATE);
    Route::put('attribute-options/{attribute_option}', [AttributeOptionController::class, 'update'])->middleware('permission:'.P::ATTROPT_UPDATE);
    Route::delete('attribute-options/{attribute_option}', [AttributeOptionController::class, 'destroy'])->middleware('permission:'.P::ATTROPT_DESTROY);

    // Categories
    Route::post('categories', [CategoryController::class, 'store'])->middleware('permission:'.P::CAT_CAT_CREATE);
    Route::put('categories/{category}', [CategoryController::class, 'update'])->middleware('permission:'.P::CAT_CAT_UPDATE);
    Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->middleware('permission:'.P::CAT_CAT_DESTROY);

    // Collections
    Route::post('collections', [CollectionController::class, 'store'])->middleware('permission:'.P::CAT_COLL_CREATE);
    Route::put('collections/{collection}', [CollectionController::class, 'update'])->middleware('permission:'.P::CAT_COLL_UPDATE);
    Route::delete('collections/{collection}', [CollectionController::class, 'destroy'])->middleware('permission:'.P::CAT_COLL_DESTROY);

    // Products
    Route::post('products', [ProductController::class, 'store'])->middleware('permission:'.P::CAT_PROD_CREATE);
    Route::put('products/{product}', [ProductController::class, 'update'])->middleware('permission:'.P::CAT_PROD_UPDATE);
    Route::delete('products/{product}', [ProductController::class, 'destroy'])->middleware('permission:'.P::CAT_PROD_DESTROY);

    // Product Media
    Route::post('products/{product}/media', [ProductMediaController::class, 'store'])->middleware('permission:'.P::CAT_PMEDIA_CREATE);
    Route::put('product-media/{media}', [ProductMediaController::class, 'update'])->middleware('permission:'.P::CAT_PMEDIA_UPDATE);
    Route::delete('product-media/{media}', [ProductMediaController::class, 'destroy'])->middleware('permission:'.P::CAT_PMEDIA_DESTROY);

    // Product level prices
    Route::post('products/{product}/prices', [ProductPriceController::class, 'store'])->middleware('permission:'.P::CAT_PPRICE_CREATE);
    Route::put('products/{product}/prices/{currency_code}', [ProductPriceController::class, 'update'])->middleware('permission:'.P::CAT_PPRICE_UPDATE);
    Route::delete('products/{product}/prices/{currency_code}', [ProductPriceController::class, 'destroy'])->middleware('permission:'.P::CAT_PPRICE_DESTROY);

    // Product variants
    Route::post('products/{product}/variants', [ProductVariantController::class, 'store'])->middleware('permission:'.P::CAT_PVAR_CREATE);
    Route::put('product-variants/{variant}', [ProductVariantController::class, 'update'])->middleware('permission:'.P::CAT_PVAR_UPDATE);
    Route::delete('product-variants/{variant}', [ProductVariantController::class, 'destroy'])->middleware('permission:'.P::CAT_PVAR_DESTROY);

    // Product variant media
    Route::post('product-variants/{variant}/media', [ProductVariantMediaController::class, 'store'])->middleware('permission:'.P::CAT_PVMEDIA_CREATE);
    Route::put('product-variant-media/{media}', [ProductVariantMediaController::class, 'update'])->middleware('permission:'.P::CAT_PVMEDIA_UPDATE);
    Route::delete('product-variant-media/{media}', [ProductVariantMediaController::class, 'destroy'])->middleware('permission:'.P::CAT_PVMEDIA_DESTROY);

    // Product variant prices
    Route::post('product-variants/{variant}/prices', [ProductVariantPriceController::class, 'store'])->middleware('permission:'.P::CAT_PVPRICE_CREATE);
    Route::put('product-variants/{variant}/prices/{currency_code}', [ProductVariantPriceController::class, 'update'])->middleware('permission:'.P::CAT_PVPRICE_UPDATE);
    Route::delete('product-variants/{variant}/prices/{currency_code}', [ProductVariantPriceController::class, 'destroy'])->middleware('permission:'.P::CAT_PVPRICE_DESTROY);

    // Product bundles - mapping bundle product -> child variants
    Route::post('product-bundles', [ProductBundleController::class, 'store'])->middleware('permission:'.P::CAT_PBUNDLE_CREATE);
    Route::put('product-bundles/{bundle}', [ProductBundleController::class, 'update'])->middleware('permission:'.P::CAT_PBUNDLE_UPDATE);
    Route::delete('product-bundles/{bundle}', [ProductBundleController::class, 'destroy'])->middleware('permission:'.P::CAT_PBUNDLE_DESTROY);

    // Related products
    Route::post('related-products', [RelatedProductController::class, 'store'])->middleware('permission:'.P::CAT_RELATED_CREATE);
    Route::delete('related-products/{product}/{related_product}', [RelatedProductController::class, 'destroy'])->middleware('permission:'.P::CAT_RELATED_DESTROY);

    // Category <-> Product pivot
    Route::post('category-products', [CategoryProductController::class, 'store'])->middleware('permission:'.P::CAT_CATPROD_CREATE);
    Route::delete('category-products/{category}/{product}', [CategoryProductController::class, 'destroy'])->middleware('permission:'.P::CAT_CATPROD_DESTROY);

    // Collection <-> Product pivot
    Route::post('collection-products', [CollectionProductController::class, 'store'])->middleware('permission:'.P::CAT_COLPROD_CREATE);
    Route::delete('collection-products/{collection}/{product}', [CollectionProductController::class, 'destroy'])->middleware('permission:'.P::CAT_COLPROD_DESTROY);
});

/*
|--------------------------------------------------------------------------
| Sanctum protected APIs - Core
|--------------------------------------------------------------------------
|
| These routes are barley used for managing status tables
|
*/

Route::middleware('auth:sanctum')->group(function () {

    // Countries
    Route::post('countries', [CountryController::class, 'store'])->middleware('permission:'.P::CTRY_CREATE);
    Route::put('countries/{country}', [CountryController::class, 'update'])->middleware('permission:'.P::CTRY_UPDATE);
    Route::delete('countries/{country}', [CountryController::class, 'destroy'])->middleware('permission:'.P::CTRY_DESTROY);

    // States
    Route::post('states', [StateController::class, 'store'])->middleware('permission:'.P::STATE_CREATE);
    Route::put('states/{state}', [StateController::class, 'update'])->middleware('permission:'.P::STATE_UPDATE);
    Route::delete('states/{state}', [StateController::class, 'destroy'])->middleware('permission:'.P::STATE_DESTROY);

    // Currencies
    Route::post('currencies', [CurrencyController::class, 'store'])->middleware('permission:'.P::CURR_CREATE);
    Route::put('currencies/{currency}', [CurrencyController::class, 'update'])->middleware('permission:'.P::CURR_UPDATE);
    Route::delete('currencies/{currency}', [CurrencyController::class, 'destroy'])->middleware('permission:'.P::CURR_DESTROY);

    // Customer Groups
    Route::post('customer-groups', [CustomerGroupController::class, 'store'])->middleware('permission:'.P::CGRP_CREATE);
    Route::put('customer-groups/{customer_group}', [CustomerGroupController::class, 'update'])->middleware('permission:'.P::CGRP_UPDATE);
    Route::delete('customer-groups/{customer_group}', [CustomerGroupController::class, 'destroy'])->middleware('permission:'.P::CGRP_DESTROY);

    // Order Statuses
    Route::post('order-statuses', [OrderStatusController::class, 'store'])->middleware('permission:'.P::ORST_CREATE);
    Route::put('order-statuses/{order_status}', [OrderStatusController::class, 'update'])->middleware('permission:'.P::ORST_UPDATE);
    Route::delete('order-statuses/{order_status}', [OrderStatusController::class, 'destroy'])->middleware('permission:'.P::ORST_DESTROY);

    // Shipment Statuses
    Route::post('shipment-statuses', [ShipmentStatusController::class, 'store'])->middleware('permission:'.P::SHST_CREATE);
    Route::put('shipment-statuses/{shipment_status}', [ShipmentStatusController::class, 'update'])->middleware('permission:'.P::SHST_UPDATE);
    Route::delete('shipment-statuses/{shipment_status}', [ShipmentStatusController::class, 'destroy'])->middleware('permission:'.P::SHST_DESTROY);

    // Payment Statuses
    Route::post('payment-statuses', [PaymentStatusController::class, 'store'])->middleware('permission:'.P::PYST_CREATE);
    Route::put('payment-statuses/{payment_status}', [PaymentStatusController::class, 'update'])->middleware('permission:'.P::PYST_UPDATE);
    Route::delete('payment-statuses/{payment_status}', [PaymentStatusController::class, 'destroy'])->middleware('permission:'.P::PYST_DESTROY);

    // Invoice Statuses
    Route::post('invoice-statuses', [InvoiceStatusController::class, 'store'])->middleware('permission:'.P::IVST_CREATE);
    Route::put('invoice-statuses/{invoice_status}', [InvoiceStatusController::class, 'update'])->middleware('permission:'.P::IVST_UPDATE);
    Route::delete('invoice-statuses/{invoice_status}', [InvoiceStatusController::class, 'destroy'])->middleware('permission:'.P::IVST_DESTROY);

    // Refund Statuses
    Route::post('refund-statuses', [RefundStatusController::class, 'store'])->middleware('permission:'.P::RFST_CREATE);
    Route::put('refund-statuses/{refund_status}', [RefundStatusController::class, 'update'])->middleware('permission:'.P::RFST_UPDATE);
    Route::delete('refund-statuses/{refund_status}', [RefundStatusController::class, 'destroy'])->middleware('permission:'.P::RFST_DESTROY);

    // Payment Methods
    Route::post('payment-methods', [PaymentMethodController::class, 'store'])->middleware('permission:'.P::PYMT_CREATE);
    Route::put('payment-methods/{payment_method}', [PaymentMethodController::class, 'update'])->middleware('permission:'.P::PYMT_UPDATE);
    Route::delete('payment-methods/{payment_method}', [PaymentMethodController::class, 'destroy'])->middleware('permission:'.P::PYMT_DESTROY);

    // Shipment Methods
    Route::post('shipment-methods', [ShipmentMethodController::class, 'store'])->middleware('permission:'.P::SHMT_CREATE);
    Route::put('shipment-methods/{shipment_method}', [ShipmentMethodController::class, 'update'])->middleware('permission:'.P::SHMT_UPDATE);
    Route::delete('shipment-methods/{shipment_method}', [ShipmentMethodController::class, 'destroy'])->middleware('permission:'.P::SHMT_DESTROY);

    // Stock Movement Types
    Route::post('stock-movement-types', [StockMovementTypeController::class, 'store'])->middleware('permission:'.P::SMT_CREATE);
    Route::put('stock-movement-types/{stock_movement_type}', [StockMovementTypeController::class, 'update'])->middleware('permission:'.P::SMT_UPDATE);
    Route::delete('stock-movement-types/{stock_movement_type}', [StockMovementTypeController::class, 'destroy'])->middleware('permission:'.P::SMT_DESTROY);
});

/*
|--------------------------------------------------------------------------
| Sanctum protected APIs - Customer
|--------------------------------------------------------------------------
|
| These routes are for admin with permissions.
|
*/

Route::middleware('auth:sanctum')->group(function () {

    // Customer profile (1–1 with user)
    Route::get('customer/profile', [CustomerProfileController::class, 'show']);
    Route::put('customer/profile', [CustomerProfileController::class, 'upsert']);

    // Addresses for current user
    Route::get('addresses', [AddressController::class, 'index']);
    Route::post('addresses', [AddressController::class, 'store']);
    Route::put('addresses/{address}', [AddressController::class, 'update']);
    Route::delete('addresses/{address}', [AddressController::class, 'destroy']);

    // Favorites for current user
    Route::get('favorites', [FavoriteController::class, 'index']);
    Route::post('favorites', [FavoriteController::class, 'store']);      // body: product_id, product_variant_id?
    Route::delete('favorites/{favorite}', [FavoriteController::class, 'destroy']);

    // Wishlists for current user
    Route::get('wishlists', [WishlistController::class, 'index']);
    Route::post('wishlists', [WishlistController::class, 'store']);
    Route::get('wishlists/{wishlist}', [WishlistController::class, 'show']);  // includes items
    Route::put('wishlists/{wishlist}', [WishlistController::class, 'update']);
    Route::delete('wishlists/{wishlist}', [WishlistController::class, 'destroy']);

    // Wishlist items for a given wishlist
    Route::post('wishlists/{wishlist}/items', [WishlistItemController::class, 'store']);
    Route::delete('wishlists/{wishlist}/items/{item}', [WishlistItemController::class, 'destroy']);

    // Recently viewed (per user)
    Route::get('recently-viewed', [RecentlyViewedController::class, 'index']);
    Route::post('recently-viewed', [RecentlyViewedController::class, 'store']);    // track a view
    Route::delete('recently-viewed/{entry}', [RecentlyViewedController::class, 'destroy']);
});

/*
|--------------------------------------------------------------------------
| Sanctum protected APIs - Engagement
|--------------------------------------------------------------------------
|
| These routes are for admin with permissions.
|
*/

Route::middleware('auth:sanctum')->group(function () {

    // Testimonials
    Route::post('testimonials', [TestimonialController::class, 'store'])->middleware('permission:'.P::ENG_TEST_CREATE);
    Route::put('testimonials/{testimonial}', [TestimonialController::class, 'update'])->middleware('permission:'.P::ENG_TEST_UPDATE);
    Route::delete('testimonials/{testimonial}', [TestimonialController::class, 'destroy'])->middleware('permission:'.P::ENG_TEST_DESTROY);

    // Lookbooks
    Route::post('lookbooks', [LookbookController::class, 'store'])->middleware('permission:'.P::ENG_LBK_CREATE);
    Route::put('lookbooks/{lookbook}', [LookbookController::class, 'update'])->middleware('permission:'.P::ENG_LBK_UPDATE);
    Route::delete('lookbooks/{lookbook}', [LookbookController::class, 'destroy'])->middleware('permission:'.P::ENG_LBK_DESTROY);

    // Lookbook items
    Route::get('lookbooks/{lookbook}/items', [LookbookItemController::class, 'indexByLookbook'])->middleware('permission:'.P::ENG_LBKITEM_VIEW);
    Route::post('lookbooks/{lookbook}/items', [LookbookItemController::class, 'store'])->middleware('permission:'.P::ENG_LBKITEM_CREATE);
    Route::put('lookbook-items/{item}', [LookbookItemController::class, 'update'])->middleware('permission:'.P::ENG_LBKITEM_UPDATE);
    Route::delete('lookbook-items/{item}', [LookbookItemController::class, 'destroy'])->middleware('permission:'.P::ENG_LBKITEM_DESTROY);

    // Lookbook item products
    Route::post('lookbook-items/{item}/products', [LookbookItemProductController::class, 'store'])->middleware('permission:'.P::ENG_LBKITEMPROD_CREATE);
    Route::put('lookbook-item-products/{attachment}', [LookbookItemProductController::class, 'update'])->middleware('permission:'.P::ENG_LBKITEMPROD_UPDATE);
    Route::delete('lookbook-item-products/{attachment}', [LookbookItemProductController::class, 'destroy'])->middleware('permission:'.P::ENG_LBKITEMPROD_DESTROY);

});

/*
|--------------------------------------------------------------------------
| Sanctum protected APIs - Inventory
|--------------------------------------------------------------------------
|
| These routes are for admin with permissions.
|
*/

Route::middleware('auth:sanctum')->group(function () {

    // Stocks
    Route::post('stocks', [StockController::class, 'store'])->middleware('permission:'.P::INVT_STOCK_CREATE);
    Route::put('stocks/{stock}', [StockController::class, 'update'])->middleware('permission:'.P::INVT_STOCK_UPDATE);
    Route::delete('stocks/{stock}', [StockController::class, 'destroy'])->middleware('permission:'.P::INVT_STOCK_DESTROY);

    // Stock levels
    Route::post('stock-levels', [StockLevelController::class, 'store'])->middleware('permission:'.P::INVT_STOCKLVL_CREATE);
    Route::put('stock-levels/{stock_level}', [StockLevelController::class, 'update'])->middleware('permission:'.P::INVT_STOCKLVL_UPDATE);
    Route::delete('stock-levels/{stock_level}', [StockLevelController::class, 'destroy'])->middleware('permission:'.P::INVT_STOCKLVL_DESTROY);

    // Stock batches
    Route::post('stock-batches', [StockBatchController::class, 'store'])->middleware('permission:'.P::INVT_STOCKBATCH_CREATE);
    Route::put('stock-batches/{stock_batch}', [StockBatchController::class, 'update'])->middleware('permission:'.P::INVT_STOCKBATCH_UPDATE);
    Route::delete('stock-batches/{stock_batch}', [StockBatchController::class, 'destroy'])->middleware('permission:'.P::INVT_STOCKBATCH_DESTROY);

    // Stock movements (create only, immutable)
    Route::post('stock-movements', [StockMovementController::class, 'store'])->middleware('permission:'.P::INVT_STOCKMOVE_CREATE);

    // Back in stock subscriptions (admin listing + delete)
    Route::get('stock-back-in-subscriptions', [StockBackInSubscriptionController::class, 'index'])->middleware('permission:'.P::INVT_BACKINSTOCK_VIEW);
    Route::get('stock-back-in-subscriptions/{stock_back_in_subscription}', [StockBackInSubscriptionController::class, 'show'])->middleware('permission:'.P::INVT_BACKINSTOCK_VIEW);
    Route::delete('stock-back-in-subscriptions/{stock_back_in_subscription}', [StockBackInSubscriptionController::class, 'destroy'])->middleware('permission:'.P::INVT_BACKINSTOCK_DESTROY);
});

/*
|--------------------------------------------------------------------------
| Sanctum protected APIs - Order
|--------------------------------------------------------------------------
|
| These routes are for admin with permissions.
|
*/

Route::middleware('auth:sanctum')->group(function () {

    // customer's own orders
    Route::get('orders', [OrderController::class, 'indexCustomer']);
    Route::get('orders/{order}', [OrderController::class, 'showCustomer']);

    // admin order management
    Route::get('admin/orders', [OrderController::class, 'indexAdmin'])->middleware('permission:'.P::ORD_INDEX);
    Route::get('admin/orders/{order}', [OrderController::class, 'showAdmin'])->middleware('permission:'.P::ORD_VIEW);
    Route::patch('admin/orders/{order}/status', [OrderController::class, 'updateStatus'])->middleware('permission:'.P::ORD_UPDATE);
});

/*
|--------------------------------------------------------------------------
| Sanctum protected APIs - Payment
|--------------------------------------------------------------------------
|
| These routes are for admin with permissions.
|
*/

Route::middleware('auth:sanctum')->group(function () {

    // Invoices (admin)
    Route::get('admin/invoices', [InvoiceController::class, 'index'])->middleware('permission:'.P::PAY_INV_LIST);
    Route::get('admin/invoices/{invoice}', [InvoiceController::class, 'show'])->middleware('permission:'.P::PAY_INV_VIEW);
    Route::patch('admin/invoices/{invoice}', [InvoiceController::class, 'update'])->middleware('permission:'.P::PAY_INV_UPDATE);

    // Payments (admin)
    Route::get('admin/payments', [PaymentController::class, 'index'])->middleware('permission:'.P::PAY_PAY_LIST);
    Route::get('admin/payments/{payment}', [PaymentController::class, 'show'])->middleware('permission:'.P::PAY_PAY_VIEW);
    Route::post('admin/payments/cod-collection', [PaymentController::class, 'codCollection'])->middleware('permission:'.P::PAY_PAY_COD_COLLECT);

    // Payment attempts (admin)
    Route::get('admin/payment-attempts', [PaymentAttemptController::class, 'index'])->middleware('permission:'.P::PAY_ATT_LIST);
    Route::get('admin/payment-attempts/{attempt}', [PaymentAttemptController::class, 'show'])->middleware('permission:'.P::PAY_ATT_VIEW);

    // Refunds (admin)
    Route::get('admin/refunds', [RefundController::class, 'index'])->middleware('permission:'.P::PAY_REFUND_LIST);
    Route::get('admin/refunds/{refund}', [RefundController::class, 'show'])->middleware('permission:'.P::PAY_REFUND_VIEW);
    Route::post('admin/orders/{order}/refunds', [RefundController::class, 'store'])->middleware('permission:'.P::PAY_REFUND_CREATE);
    Route::patch('admin/refunds/{refund}', [RefundController::class, 'update'])->middleware('permission:'.P::PAY_REFUND_UPDATE);
});
// TODO
// Payment webhooks (public, secured by provider secret)
// Route::post('payment/webhook/{provider}', [PaymentWebhookController::class, 'handle']);

/*
|--------------------------------------------------------------------------
| Sanctum protected APIs - Promotion
|--------------------------------------------------------------------------
|
| These routes are for admin with permissions.
|
*/

Route::middleware('auth:sanctum')->group(function () {

    Route::get('admin/promotions', [PromotionController::class, 'index'])->middleware('permission:'.P::PROMO_VIEW);
    Route::get('admin/promotions/{promotion}', [PromotionController::class, 'show'])->middleware('permission:'.P::PROMO_VIEW);
    Route::post('promotions', [PromotionController::class, 'store'])->middleware('permission:'.P::PROMO_CREATE);
    Route::put('promotions/{promotion}', [PromotionController::class, 'update'])->middleware('permission:'.P::PROMO_UPDATE);
    Route::delete('promotions/{promotion}', [PromotionController::class, 'destroy'])->middleware('permission:'.P::PROMO_DESTROY);

    // Promotion coupons
    Route::post('promotions/{promotion}/coupons', [PromotionCouponController::class, 'store'])->middleware('permission:'.P::PROMO_COUPON_CREATE);
    Route::put('promotion-coupons/{coupon}', [PromotionCouponController::class, 'update'])->middleware('permission:'.P::PROMO_COUPON_UPDATE);
    Route::delete('promotion-coupons/{coupon}', [PromotionCouponController::class, 'destroy'])->middleware('permission:'.P::PROMO_COUPON_DESTROY);

    // Promotion redemptions listing (admin analytics)
    Route::get('promotions/{promotion}/redemptions', [PromotionRedemptionController::class, 'indexByPromotion'])->middleware('permission:'.P::PROMO_REDEMPTION_VIEW);
});

/*
|--------------------------------------------------------------------------
| Sanctum protected APIs - Shipping
|--------------------------------------------------------------------------
|
| These routes are for admin with permissions.
|
*/

Route::middleware('auth:sanctum')->group(function () {

    // Customer-facing: view shipment for own order
    Route::get('orders/{order}/shipment', [ShipmentController::class, 'showForCustomer']);

    // Admin Shipping APIs
    Route::get('admin/shipments', [ShipmentController::class, 'index'])->middleware('permission:'.P::SHIP_VIEW);
    Route::get('admin/shipments/{shipment}', [ShipmentController::class, 'show'])->middleware('permission:'.P::SHIP_VIEW);
    Route::post('admin/orders/{order}/shipments', [ShipmentController::class, 'store'])->middleware('permission:'.P::SHIP_CREATE);
    Route::patch('admin/shipments/{shipment}', [ShipmentController::class, 'update'])->middleware('permission:'.P::SHIP_UPDATE);
    Route::patch('admin/shipments/{shipment}/status', [ShipmentController::class, 'updateStatus'])->middleware('permission:'.P::SHIP_UPDATE_STATUS);
});

/*
|--------------------------------------------------------------------------
| Sanctum protected APIs - Tax
|--------------------------------------------------------------------------
|
| These routes are for admin with permissions.
|
*/

Route::middleware('auth:sanctum')->group(function () {
    // Tax Classes
    Route::get('tax-classes', [TaxClassController::class, 'index']);
    Route::get('tax-classes/{tax_class}', [TaxClassController::class, 'show']);
    Route::post('tax-classes', [TaxClassController::class, 'store'])->middleware('permission:'.P::TAX_CLASS_CREATE);
    Route::put('tax-classes/{tax_class}', [TaxClassController::class, 'update'])->middleware('permission:'.P::TAX_CLASS_UPDATE);
    Route::delete('tax-classes/{tax_class}', [TaxClassController::class, 'destroy'])->middleware('permission:'.P::TAX_CLASS_DESTROY);

    // Tax Rules
    Route::get('tax-rules', [TaxRuleController::class, 'index']);
    Route::get('tax-rules/{tax_rule}', [TaxRuleController::class, 'show']);
    Route::post('tax-rules', [TaxRuleController::class, 'store'])->middleware('permission:'.P::TAX_RULE_CREATE);
    Route::put('tax-rules/{tax_rule}', [TaxRuleController::class, 'update'])->middleware('permission:'.P::TAX_RULE_UPDATE);
    Route::delete('tax-rules/{tax_rule}', [TaxRuleController::class, 'destroy'])->middleware('permission:'.P::TAX_RULE_DESTROY);

    // Tax Rates
    Route::get('tax-rates', [TaxRateController::class, 'index']);
    Route::get('tax-rates/{tax_rate}', [TaxRateController::class, 'show']);
    Route::post('tax-rates', [TaxRateController::class, 'store'])->middleware('permission:'.P::TAX_RATE_CREATE);
    Route::put('tax-rates/{tax_rate}', [TaxRateController::class, 'update'])->middleware('permission:'.P::TAX_RATE_UPDATE);
    Route::delete('tax-rates/{tax_rate}', [TaxRateController::class, 'destroy'])->middleware('permission:'.P::TAX_RATE_DESTROY);

    // Tax Calculations (matrix)
    Route::get('tax-calculations', [TaxCalculationController::class, 'index']);
    Route::get('tax-calculations/{tax_calculation}', [TaxCalculationController::class, 'show']);
    Route::post('tax-calculations', [TaxCalculationController::class, 'store'])->middleware('permission:'.P::TAX_CALC_CREATE);
    Route::put('tax-calculations/{tax_calculation}', [TaxCalculationController::class, 'update'])->middleware('permission:'.P::TAX_CALC_UPDATE);
    Route::delete('tax-calculations/{tax_calculation}', [TaxCalculationController::class, 'destroy'])->middleware('permission:'.P::TAX_CALC_DESTROY);
});
