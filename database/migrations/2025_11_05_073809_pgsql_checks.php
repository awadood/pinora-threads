<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Driver-guarded CHECK constraints
        if (Schema::getConnection()->getDriverName() === 'pgsql') {

            // ---- Billing

            // invoices, payments, payment_attempts, refunds, shipments
            DB::statement('ALTER TABLE invoices ADD CONSTRAINT invoices_amount_due_ck CHECK (amount_due >= 0)');
            DB::statement('ALTER TABLE payments ADD CONSTRAINT payments_amount_ck CHECK (amount >= 0)');
            DB::statement('ALTER TABLE payment_attempts ADD CONSTRAINT payment_attempts_amount_ck CHECK (amount >= 0)');
            DB::statement('ALTER TABLE refunds ADD CONSTRAINT refunds_amount_ck CHECK (amount >= 0)');
            DB::statement('ALTER TABLE shipments ADD CONSTRAINT shipments_money_ck 
                CHECK (shipping_charge >= 0 AND shipping_cost >= 0 AND shipping_tax >= 0)');

            // promotions
            DB::statement('ALTER TABLE promotions ADD CONSTRAINT promotions_dates_ck CHECK (to_date IS NULL OR to_date > from_date)');
            DB::statement('ALTER TABLE promotions ADD CONSTRAINT promotions_usage_per_user_ck 
                CHECK (usage_per_user IS NULL OR usage_per_user > 0)');
            DB::statement('ALTER TABLE promotion_coupons ADD CONSTRAINT promotion_coupons_expiry_ck
                CHECK (expiry IS NULL OR expiry > CURRENT_TIMESTAMP)');
            DB::statement('ALTER TABLE promotion_coupons ADD CONSTRAINT promotion_coupons_usage_limit_ck
                CHECK (usage_limit IS NULL OR usage_limit > 0)');
            DB::statement('ALTER TABLE promotion_coupons ADD CONSTRAINT promotion_coupons_usage_per_user_ck
                CHECK (usage_per_user IS NULL OR usage_per_user > 0)');
            DB::statement('ALTER TABLE promotion_redemptions ADD CONSTRAINT promotion_redemptions_amounts_ck
                CHECK (cart_amount >= 0 AND discount_amount >= 0 AND discount_amount <= cart_amount)');
            DB::statement('ALTER TABLE promotion_redemptions ADD CONSTRAINT promotion_redemptions_actor_ck
                CHECK (user_id IS NOT NULL OR order_id IS NOT NULL)');

            // ---- Catalog

            // Trigram index support for typo-tolerant search
            DB::statement('CREATE EXTENSION IF NOT EXISTS pg_trgm');

            // Product name/slug trigram indexes to help typo-tolerance
            DB::statement('CREATE INDEX products_name_trgm_idx ON products USING GIN (name gin_trgm_ops)');
            DB::statement('CREATE INDEX products_slug_trgm_idx ON products USING GIN (slug gin_trgm_ops)');

            // Hygiene: disallow self-relations and duplicate pairs (A,B) vs (B,A)
            DB::statement('ALTER TABLE related_products ADD CONSTRAINT related_products_no_self 
                CHECK (product_id <> related_product_id)');

            // Works with BIGINT ids (LEAST/GREATEST)
            DB::statement('CREATE UNIQUE INDEX related_products_unique_pair
                ON related_products (LEAST(product_id, related_product_id), GREATEST(product_id, related_product_id))');

            // ---- Engagement

            // testimonials
            DB::statement('ALTER TABLE testimonials ADD CONSTRAINT testimonials_rating_ck 
                CHECK ((rating IS NULL) OR (rating BETWEEN 1 AND 5))');
            DB::statement("ALTER TABLE testimonials ADD CONSTRAINT testimonials_publish_guard_ck
                CHECK (
                    (status = 'approved' AND published_at IS NOT NULL)
                    OR
                    (status <> 'approved' AND published_at IS NULL)
                )
            ");

            // ---- Media

            // Single-slot roles: only one attachment per role per owner
            DB::statement("CREATE UNIQUE INDEX media_attach_single_slot_unique
                ON media_attachments(owner_type, owner_id, role)
                WHERE role IN ('thumbnail','hero','og_image');
            ");

            // Gallery: enforce one primary at most
            DB::statement("CREATE UNIQUE INDEX media_attach_gallery_primary_unique
                ON media_attachments(owner_type, owner_id, role)
                WHERE role = 'gallery' AND is_primary = true;
            ");

            // ---- Orders

            // cart_items
            DB::statement('ALTER TABLE cart_items ADD CONSTRAINT cart_items_quantity_ck CHECK (quantity > 0)');

            // orders
            DB::statement('ALTER TABLE orders ADD CONSTRAINT orders_items_subtotal_ck CHECK (items_subtotal >= 0)');
            DB::statement('ALTER TABLE orders ADD CONSTRAINT orders_total_discount_ck CHECK (total_discount >= 0)');
            DB::statement('ALTER TABLE orders ADD CONSTRAINT orders_total_tax_ck CHECK (total_tax >= 0)');
            DB::statement('ALTER TABLE orders ADD CONSTRAINT orders_total_shipping_ck CHECK (total_shipping >= 0)');
            DB::statement('ALTER TABLE orders ADD CONSTRAINT orders_total_ck CHECK (total >= 0)');

            // order_items
            DB::statement('ALTER TABLE order_items ADD CONSTRAINT order_items_quantity_ck CHECK (quantity > 0)');
            DB::statement('ALTER TABLE order_items ADD CONSTRAINT order_items_unit_price_ck CHECK (unit_price >= 0)');
            DB::statement('ALTER TABLE order_items ADD CONSTRAINT order_items_subtotal_ck CHECK (subtotal >= 0)');
            DB::statement('ALTER TABLE order_items ADD CONSTRAINT order_items_discount_ck CHECK (discount >= 0)');
            DB::statement('ALTER TABLE order_items ADD CONSTRAINT order_items_tax_ck CHECK (tax >= 0)');
            DB::statement('ALTER TABLE order_items ADD CONSTRAINT order_items_total_ck CHECK (total >= 0)');

            // ---- Tax engine

            // percentage bounds (0..100) or non-negative flat
            DB::statement('ALTER TABLE tax_rates ADD CONSTRAINT tax_rates_amount_ck
                CHECK ((percentage = true AND amount >= 0 AND amount <= 100) OR (percentage = false AND amount >= 0))');

            // zip rules: either (range) or (single)
            DB::statement('ALTER TABLE tax_rates ADD CONSTRAINT tax_rates_zip_ck
                CHECK (
                    (zip_is_range IS TRUE AND zipcode IS NULL AND zip_from IS NOT NULL AND zip_to IS NOT NULL)
                    OR (
                        (zip_is_range IS FALSE OR zip_is_range IS NULL) 
                        AND zipcode IS NOT NULL 
                        AND zip_from IS NULL 
                        AND zip_to IS NULL
                    )
                )
            ');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop CHECKs first, then tables
        if (Schema::getConnection()->getDriverName() === 'pgsql') {

            // ---- Engagement

            // testimonials
            DB::statement('ALTER TABLE testimonials DROP CONSTRAINT IF EXISTS testimonials_rating_ck');
            DB::statement('ALTER TABLE testimonials DROP CONSTRAINT IF EXISTS testimonials_publish_guard_ck');

            // ---- Billing

            // invoices, payments, payment_attempts, refunds, shipments
            DB::statement('ALTER TABLE invoices DROP CONSTRAINT IF EXISTS invoices_amount_due_ck');
            DB::statement('ALTER TABLE payments DROP CONSTRAINT IF EXISTS payments_amount_ck');
            DB::statement('ALTER TABLE payment_attempts DROP CONSTRAINT IF EXISTS payment_attempts_amount_ck');
            DB::statement('ALTER TABLE refunds DROP CONSTRAINT IF EXISTS refunds_amount_ck');
            DB::statement('ALTER TABLE shipments DROP CONSTRAINT IF EXISTS shipments_money_ck');

            // promotions
            DB::statement('ALTER TABLE promotions DROP CONSTRAINT IF EXISTS promotions_dates_ck');
            DB::statement('ALTER TABLE promotions DROP CONSTRAINT IF EXISTS promotions_usage_per_user_ck');
            DB::statement('ALTER TABLE promotion_coupons DROP CONSTRAINT IF EXISTS promotion_coupons_expiry_ck');
            DB::statement('ALTER TABLE promotion_coupons DROP CONSTRAINT IF EXISTS promotion_coupons_usage_limit_ck');
            DB::statement('ALTER TABLE promotion_coupons DROP CONSTRAINT IF EXISTS promotion_coupons_usage_per_user_ck');
            DB::statement('ALTER TABLE promotion_redemptions DROP CONSTRAINT IF EXISTS promotion_redemptions_amounts_ck');
            DB::statement('ALTER TABLE promotion_redemptions DROP CONSTRAINT IF EXISTS promotion_redemptions_actor_ck');

            // ---- Media

            DB::statement('DROP INDEX IF EXISTS media_attach_single_slot_unique;');
            DB::statement('DROP INDEX IF EXISTS media_attach_gallery_primary_unique;');

            // ---- Orders

            // cart_items
            DB::statement('ALTER TABLE cart_items DROP CONSTRAINT IF EXISTS cart_items_quantity_ck');

            // orders
            DB::statement('ALTER TABLE orders DROP CONSTRAINT IF EXISTS orders_items_subtotal_ck');
            DB::statement('ALTER TABLE orders DROP CONSTRAINT IF EXISTS orders_total_discount_ck');
            DB::statement('ALTER TABLE orders DROP CONSTRAINT IF EXISTS orders_total_tax_ck');
            DB::statement('ALTER TABLE orders DROP CONSTRAINT IF EXISTS orders_total_shipping_ck');
            DB::statement('ALTER TABLE orders DROP CONSTRAINT IF EXISTS orders_total_ck');

            // order_items
            DB::statement('ALTER TABLE order_items DROP CONSTRAINT IF EXISTS order_items_quantity_ck');
            DB::statement('ALTER TABLE order_items DROP CONSTRAINT IF EXISTS order_items_unit_price_ck');
            DB::statement('ALTER TABLE order_items DROP CONSTRAINT IF EXISTS order_items_subtotal_ck');
            DB::statement('ALTER TABLE order_items DROP CONSTRAINT IF EXISTS order_items_discount_ck');
            DB::statement('ALTER TABLE order_items DROP CONSTRAINT IF EXISTS order_items_tax_ck');
            DB::statement('ALTER TABLE order_items DROP CONSTRAINT IF EXISTS order_items_total_ck');

            // ---- Catalog

            // Drop custom CHECKs / constraints
            DB::statement('ALTER TABLE related_products DROP CONSTRAINT IF EXISTS related_products_no_self');

            // Drop custom indexes (order: dependent objects first)
            DB::statement('DROP INDEX IF EXISTS related_products_unique_pair');
            DB::statement('DROP INDEX IF EXISTS products_name_trgm_idx');
            DB::statement('DROP INDEX IF EXISTS products_slug_trgm_idx');

            // ---- Tax engine

            DB::statement('ALTER TABLE tax_rates DROP CONSTRAINT IF EXISTS tax_rates_zip_ck');
            DB::statement('ALTER TABLE tax_rates DROP CONSTRAINT IF EXISTS tax_rates_amount_ck');
        }
    }
};
