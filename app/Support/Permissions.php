<?php

namespace App\Support;

/**
 * Centralized permission slugs for Spatie\Permission.
 * Keep names short but clear; values match your route middleware strings.
 *
 * @author Abdul Wadood
 */
final class Permissions
{
    /*
    |--------------------------------------------------------------------------
    | Core domain permissions
    |--------------------------------------------------------------------------
    |
    */

    public const USER_VIEW = 'admin.user.view';

    public const USER_CREATE = 'admin.user.create';

    public const USER_UPDATE = 'admin.user.update';

    public const USER_DESTROY = 'admin.user.destroy';

    public const ROLE_VIEW = 'admin.role.view';

    public const ROLE_CREATE = 'admin.role.create';

    public const ROLE_UPDATE = 'admin.role.update';

    public const ROLE_DESTROY = 'admin.role.destroy';

    /*
    |--------------------------------------------------------------------------
    | Core domain permissions
    |--------------------------------------------------------------------------
    |
    */

    // Countries
    public const CTRY_CREATE = 'core.country.create';

    public const CTRY_UPDATE = 'core.country.update';

    public const CTRY_DESTROY = 'core.country.destroy';

    // States
    public const STATE_CREATE = 'core.state.create';

    public const STATE_UPDATE = 'core.state.update';

    public const STATE_DESTROY = 'core.state.destroy';

    // Currencies
    public const CURR_CREATE = 'core.currency.create';

    public const CURR_UPDATE = 'core.currency.update';

    public const CURR_DESTROY = 'core.currency.destroy';

    // Customer Groups
    public const CGRP_CREATE = 'core.customer.group.create';

    public const CGRP_UPDATE = 'core.customer.group.update';

    public const CGRP_DESTROY = 'core.customer.group.destroy';

    // Order Statuses
    public const ORST_CREATE = 'core.order.status.create';

    public const ORST_UPDATE = 'core.order.status.update';

    public const ORST_DESTROY = 'core.order.status.destroy';

    // Shipment Statuses
    public const SHST_CREATE = 'core.shipment.status.create';

    public const SHST_UPDATE = 'core.shipment.status.update';

    public const SHST_DESTROY = 'core.shipment.status.destroy';

    // Payment Statuses
    public const PYST_CREATE = 'core.payment.status.create';

    public const PYST_UPDATE = 'core.payment.status.update';

    public const PYST_DESTROY = 'core.payment.status.destroy';

    // Invoice Statuses
    public const IVST_CREATE = 'core.invoice.status.create';

    public const IVST_UPDATE = 'core.invoice.status.update';

    public const IVST_DESTROY = 'core.invoice.status.destroy';

    // Refund Statuses
    public const RFST_CREATE = 'core.refund.status.create';

    public const RFST_UPDATE = 'core.refund.status.update';

    public const RFST_DESTROY = 'core.refund.status.destroy';

    // Payment Methods
    public const PYMT_CREATE = 'core.payment.method.create';

    public const PYMT_UPDATE = 'core.payment.method.update';

    public const PYMT_DESTROY = 'core.payment.method.destroy';

    // Shipment Methods
    public const SHMT_CREATE = 'core.shipment.method.create';

    public const SHMT_UPDATE = 'core.shipment.method.update';

    public const SHMT_DESTROY = 'core.shipment.method.destroy';

    // Stock Movement Types
    public const SMT_CREATE = 'core.stock.movement.type.create';

    public const SMT_UPDATE = 'core.stock.movement.type.update';

    public const SMT_DESTROY = 'core.stock.movement.type.destroy';

    /*
    |--------------------------------------------------------------------------
    | Catalog domain permissions
    |--------------------------------------------------------------------------
    |
    */

    // Catalog: Attributes
    public const ATTR_CREATE = 'catalog.attribute.create';

    public const ATTR_UPDATE = 'catalog.attribute.update';

    public const ATTR_DESTROY = 'catalog.attribute.destroy';

    public const ATTROPT_CREATE = 'catalog.attribute.option.create';

    public const ATTROPT_UPDATE = 'catalog.attribute.option.update';

    public const ATTROPT_DESTROY = 'catalog.attribute.option.destroy';

    // Catalog: Categories
    public const CAT_CAT_CREATE = 'catalog.category.create';

    public const CAT_CAT_UPDATE = 'catalog.category.update';

    public const CAT_CAT_DESTROY = 'catalog.category.destroy';

    // Catalog: Collections
    public const CAT_COLL_CREATE = 'catalog.collection.create';

    public const CAT_COLL_UPDATE = 'catalog.collection.update';

    public const CAT_COLL_DESTROY = 'catalog.collection.destroy';

    // Catalog: Products
    public const CAT_PROD_CREATE = 'catalog.product.create';

    public const CAT_PROD_UPDATE = 'catalog.product.update';

    public const CAT_PROD_DESTROY = 'catalog.product.destroy';

    // Catalog: Product media
    public const CAT_PMEDIA_CREATE = 'catalog.product.media.create';

    public const CAT_PMEDIA_UPDATE = 'catalog.product.media.update';

    public const CAT_PMEDIA_DESTROY = 'catalog.product.media.destroy';

    // Catalog: Product prices
    public const CAT_PPRICE_CREATE = 'catalog.product.price.create';

    public const CAT_PPRICE_UPDATE = 'catalog.product.price.update';

    public const CAT_PPRICE_DESTROY = 'catalog.product.price.destroy';

    // Catalog: Product variants
    public const CAT_PVAR_CREATE = 'catalog.product.variant.create';

    public const CAT_PVAR_UPDATE = 'catalog.product.variant.update';

    public const CAT_PVAR_DESTROY = 'catalog.product.variant.destroy';

    // Catalog: Variant media
    public const CAT_PVMEDIA_CREATE = 'catalog.product.variant.media.create';

    public const CAT_PVMEDIA_UPDATE = 'catalog.product.variant.media.update';

    public const CAT_PVMEDIA_DESTROY = 'catalog.product.variant.media.destroy';

    // Catalog: Variant prices
    public const CAT_PVPRICE_CREATE = 'catalog.product.variant.price.create';

    public const CAT_PVPRICE_UPDATE = 'catalog.product.variant.price.update';

    public const CAT_PVPRICE_DESTROY = 'catalog.product.variant.price.destroy';

    // Catalog: Bundles
    public const CAT_PBUNDLE_CREATE = 'catalog.product.bundle.create';

    public const CAT_PBUNDLE_UPDATE = 'catalog.product.bundle.update';

    public const CAT_PBUNDLE_DESTROY = 'catalog.product.bundle.destroy';

    // Catalog: Related products
    public const CAT_RELATED_CREATE = 'catalog.product.related.create';

    public const CAT_RELATED_DESTROY = 'catalog.product.related.destroy';

    // Catalog: Category <-> Product pivot
    public const CAT_CATPROD_CREATE = 'catalog.category.product.create';

    public const CAT_CATPROD_DESTROY = 'catalog.category.product.destroy';

    // Catalog: Collection <-> Product pivot
    public const CAT_COLPROD_CREATE = 'catalog.collection.product.create';

    public const CAT_COLPROD_DESTROY = 'catalog.collection.product.destroy';

    /*
    |--------------------------------------------------------------------------
    | Customer domain permissions
    |--------------------------------------------------------------------------
    |
    */

    // Authenticated customers will see their pages. If needed for admin, we will add permissions here.

    /*
    |--------------------------------------------------------------------------
    | Engagement domain permissions
    |--------------------------------------------------------------------------
    |
    */

    // Engagement - Testimonials
    public const ENG_TEST_CREATE = 'engagement.testimonial.create';

    public const ENG_TEST_UPDATE = 'engagement.testimonial.update';

    public const ENG_TEST_DESTROY = 'engagement.testimonial.destroy';

    // Engagement - Lookbooks
    public const ENG_LBK_CREATE = 'engagement.lookbook.create';

    public const ENG_LBK_UPDATE = 'engagement.lookbook.update';

    public const ENG_LBK_DESTROY = 'engagement.lookbook.destroy';

    // Engagement - Lookbook Items
    public const ENG_LBKITEM_VIEW = 'engagement.lookbook-item.view';

    public const ENG_LBKITEM_CREATE = 'engagement.lookbook-item.create';

    public const ENG_LBKITEM_UPDATE = 'engagement.lookbook-item.update';

    public const ENG_LBKITEM_DESTROY = 'engagement.lookbook-item.destroy';

    // Engagement - Lookbook Item Products
    public const ENG_LBKITEMPROD_CREATE = 'engagement.lookbook-item-product.create';

    public const ENG_LBKITEMPROD_UPDATE = 'engagement.lookbook-item-product.update';

    public const ENG_LBKITEMPROD_DESTROY = 'engagement.lookbook-item-product.destroy';

    /*
    |--------------------------------------------------------------------------
    | Inventory domain permissions
    |--------------------------------------------------------------------------
    |
    */

    public const INVT_STOCK_CREATE = 'inventory.stock.create';

    public const INVT_STOCK_UPDATE = 'inventory.stock.update';

    public const INVT_STOCK_DESTROY = 'inventory.stock.destroy';

    public const INVT_STOCKLVL_CREATE = 'inventory.stock_level.create';

    public const INVT_STOCKLVL_UPDATE = 'inventory.stock_level.update';

    public const INVT_STOCKLVL_DESTROY = 'inventory.stock_level.destroy';

    public const INVT_STOCKBATCH_CREATE = 'inventory.stock_batch.create';

    public const INVT_STOCKBATCH_UPDATE = 'inventory.stock_batch.update';

    public const INVT_STOCKBATCH_DESTROY = 'inventory.stock_batch.destroy';

    public const INVT_STOCKMOVE_CREATE = 'inventory.stock_movement.create';

    public const INVT_BACKINSTOCK_VIEW = 'inventory.back_in_stock.view';

    public const INVT_BACKINSTOCK_DESTROY = 'inventory.back_in_stock.destroy';

    /*
    |--------------------------------------------------------------------------
    | Order domain permissions
    |--------------------------------------------------------------------------
    |
    */

    public const ORD_INDEX = 'order.index';   // List/search all orders (admin)

    public const ORD_VIEW = 'order.view';    // View a specific order (admin)

    public const ORD_UPDATE = 'order.update';  // Update order status / meta (admin)

    /*
    |--------------------------------------------------------------------------
    | Payment domain permissions
    |--------------------------------------------------------------------------
    |
    */

    // Invoices
    public const PAY_INV_LIST = 'payment.invoice.list';

    public const PAY_INV_VIEW = 'payment.invoice.view';

    public const PAY_INV_UPDATE = 'payment.invoice.update';

    // Payments
    public const PAY_PAY_LIST = 'payment.payment.list';

    public const PAY_PAY_VIEW = 'payment.payment.view';

    public const PAY_PAY_COD_COLLECT = 'payment.payment.cod.collect';

    // Payment Attempts
    public const PAY_ATT_LIST = 'payment.attempt.list';

    public const PAY_ATT_VIEW = 'payment.attempt.view';

    // Refunds
    public const PAY_REFUND_LIST = 'payment.refund.list';

    public const PAY_REFUND_VIEW = 'payment.refund.view';

    public const PAY_REFUND_CREATE = 'payment.refund.create';

    public const PAY_REFUND_UPDATE = 'payment.refund.update';

    /*
    |--------------------------------------------------------------------------
    | Promotion domain permissions
    |--------------------------------------------------------------------------
    |
    */

    public const PROMO_VIEW = 'promotion.view';

    public const PROMO_CREATE = 'promotion.create';

    public const PROMO_UPDATE = 'promotion.update';

    public const PROMO_DESTROY = 'promotion.destroy';

    public const PROMO_COUPON_CREATE = 'promotion.coupon.create';

    public const PROMO_COUPON_UPDATE = 'promotion.coupon.update';

    public const PROMO_COUPON_DESTROY = 'promotion.coupon.destroy';

    public const PROMO_REDEMPTION_VIEW = 'promotion.redemption.view';

    /*
    |--------------------------------------------------------------------------
    | Shipping domain permissions
    |--------------------------------------------------------------------------
    |
    */

    public const SHIP_VIEW = 'shipping.shipment.view';

    public const SHIP_CREATE = 'shipping.shipment.create';

    public const SHIP_UPDATE = 'shipping.shipment.update';

    public const SHIP_UPDATE_STATUS = 'shipping.shipment.update_status';

    /*
    |--------------------------------------------------------------------------
    | Tax domain permissions
    |--------------------------------------------------------------------------
    |
    */

    public const TAX_CLASS_CREATE = 'tax.class.create';

    public const TAX_CLASS_UPDATE = 'tax.class.update';

    public const TAX_CLASS_DESTROY = 'tax.class.destroy';

    public const TAX_RULE_CREATE = 'tax.rule.create';

    public const TAX_RULE_UPDATE = 'tax.rule.update';

    public const TAX_RULE_DESTROY = 'tax.rule.destroy';

    public const TAX_RATE_CREATE = 'tax.rate.create';

    public const TAX_RATE_UPDATE = 'tax.rate.update';

    public const TAX_RATE_DESTROY = 'tax.rate.destroy';

    public const TAX_CALC_CREATE = 'tax.calculation.create';

    public const TAX_CALC_UPDATE = 'tax.calculation.update';

    public const TAX_CALC_DESTROY = 'tax.calculation.destroy';
}
