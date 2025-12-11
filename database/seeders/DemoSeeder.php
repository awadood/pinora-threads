<?php

namespace Database\Seeders;

use App\Models\CustomerGroup;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CoreTablesSeeder::class,
            TaxSeeder::class,
        ]);

        DB::transaction(function () {
            $this->seedUsersAndGroups();
            $this->seedProfilesAndAddresses();
            $this->seedCatalog();
            $this->seedInventory();
            $this->seedPromotions();
            $this->seedOrderFlow();
        });
    }

    protected function seedUsersAndGroups(): void
    {
        User::factory()->create([
            'name' => 'Administrator',
            'email' => 'admin@pinorathreads.com',
        ]);

        $usUserId = User::factory()->create([
            'name' => 'US Customer',
            'email' => 'us.customer@example.com',
        ])->id;

        $pkUserId = User::factory()->create([
            'name' => 'PK Customer',
            'email' => 'pk.customer@example.com',
        ])->id;

        $generalId = CustomerGroup::factory()->create(['name' => 'General', 'code' => 'general'])->id;
        $vipId = CustomerGroup::factory()->create(['name' => 'VIP', 'code' => 'vip'])->id;

        // Use updateOrInsert (no unique index required on the pivot)
        DB::table('customer_group_user')->updateOrInsert(
            ['customer_group_id' => $generalId, 'user_id' => $usUserId],
            []
        );
        DB::table('customer_group_user')->updateOrInsert(
            ['customer_group_id' => $vipId, 'user_id' => $pkUserId],
            []
        );

        $this->memo('us_user_id', $usUserId);
        $this->memo('pk_user_id', $pkUserId);
    }

    protected function seedProfilesAndAddresses(): void
    {
        $usUserId = $this->memo('us_user_id');
        $pkUserId = $this->memo('pk_user_id');

        // Replace upsert with updateOrInsert (no unique on user_id)
        DB::table('customer_profiles')->updateOrInsert(
            ['user_id' => $usUserId],
            [
                'tax_class_id' => 1,
                'marketing_email_opt_in' => true,
                'marketing_sms_opt_in' => false,
                'preferred_currency' => 'USD',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        DB::table('customer_profiles')->updateOrInsert(
            ['user_id' => $pkUserId],
            [
                'tax_class_id' => 1,
                'marketing_email_opt_in' => false,
                'marketing_sms_opt_in' => true,
                'preferred_currency' => 'PKR',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        $usBillingId = DB::table('addresses')->insertGetId([
            'user_id' => $usUserId,
            'label' => 'Home',
            'name' => 'US Customer',
            'line1' => '123 Market St',
            'line2' => null,
            'city' => 'San Francisco',
            'state_code' => 'CA',
            'postal_code' => '94105',
            'country_code' => 'US',
            'phone' => '+14155550100',
            'default_shipping' => true,
            'default_billing' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $pkBillingId = DB::table('addresses')->insertGetId([
            'user_id' => $pkUserId,
            'label' => 'Home',
            'name' => 'PK Customer',
            'line1' => 'House 10, Street 2',
            'line2' => null,
            'city' => 'Islamabad',
            'state_code' => null,
            'postal_code' => null,
            'country_code' => 'PK',
            'phone' => '+923001112222',
            'default_shipping' => true,
            'default_billing' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->memo('us_billing_id', $usBillingId);
        $this->memo('pk_billing_id', $pkBillingId);
    }

    // --- Everything below remains the same as you already have (catalog, inventory,
    //     promotions, order flow, helpers). Omitted here for brevity. ---

    // ---------------------------------------------------------------------
    // Catalog
    // ---------------------------------------------------------------------

    protected function seedCatalog(): void
    {
        // Attributes
        $colorAttrId = DB::table('attributes')->insertGetId([
            'code' => 'color',
            'label' => 'Color',
            'type' => 'select',
            'active' => true,
        ]);
        $sizeAttrId = DB::table('attributes')->insertGetId([
            'code' => 'size',
            'label' => 'Size',
            'type' => 'select',
            'active' => true,
        ]);

        $redId = DB::table('attribute_options')->insertGetId([
            'attribute_id' => $colorAttrId,
            'value' => 'Red',
            'sort' => 1,
        ]);
        $blueId = DB::table('attribute_options')->insertGetId([
            'attribute_id' => $colorAttrId,
            'value' => 'Blue',
            'sort' => 2,
        ]);

        $sizeSId = DB::table('attribute_options')->insertGetId([
            'attribute_id' => $sizeAttrId,
            'value' => 'S',
            'sort' => 1,
        ]);
        $sizeMId = DB::table('attribute_options')->insertGetId([
            'attribute_id' => $sizeAttrId,
            'value' => 'M',
            'sort' => 2,
        ]);

        // Categories & Collections
        $womenCatId = DB::table('categories')->insertGetId([
            'name' => 'Women',
            'slug' => 'women',
            'parent_id' => null,
            'sort' => 1,
            'active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $newArrivalsId = DB::table('collections')->insertGetId([
            'name' => 'New Arrivals',
            'slug' => 'new-arrivals',
            'sort' => 1,
            'notes' => null,
            'active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Product (variable) + 2 variants
        $productId = DB::table('products')->insertGetId([
            'sku' => 'PINFASH-001',
            'name' => 'Velvet Two-Tone Dress',
            'slug' => 'velvet-two-tone-dress',
            'type' => 'variable',
            'description' => 'Premium velvet, two-color options.',
            'tax_class_id' => 1, // Standard
            'active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('category_product')->insert([
            'category_id' => $womenCatId,
            'product_id' => $productId,
        ]);
        DB::table('collection_product')->insert([
            'collection_id' => $newArrivalsId,
            'product_id' => $productId,
            'sort' => 1,
        ]);

        // Product-level media
        DB::table('product_media')->insert([
            'product_id' => $productId,
            'type' => 'image',
            'url' => 'https://cdn.example.com/p/velvet/hero.jpg',
            'position' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Variants (default: Red / S), second: Blue / M
        $v1Id = DB::table('product_variants')->insertGetId([
            'product_id' => $productId,
            'sku' => 'PINFASH-001-RED-S',
            'title' => 'Red / S',
            'description' => null,
            'default' => true,
            'active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $v2Id = DB::table('product_variants')->insertGetId([
            'product_id' => $productId,
            'sku' => 'PINFASH-001-BLU-M',
            'title' => 'Blue / M',
            'description' => null,
            'default' => false,
            'active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('product_variant_attributes')->insert([
            ['product_variant_id' => $v1Id, 'attribute_id' => $colorAttrId, 'option_id' => $redId,  'value' => null],
            ['product_variant_id' => $v1Id, 'attribute_id' => $sizeAttrId,  'option_id' => $sizeSId, 'value' => null],
            ['product_variant_id' => $v2Id, 'attribute_id' => $colorAttrId, 'option_id' => $blueId, 'value' => null],
            ['product_variant_id' => $v2Id, 'attribute_id' => $sizeAttrId,  'option_id' => $sizeMId, 'value' => null],
        ]);

        // Prices (product-level and variant-level)
        DB::table('product_prices')->insert([
            ['product_id' => $productId, 'currency_code' => 'USD', 'amount' => 129.00, 'compare_at' => 149.00],
            ['product_id' => $productId, 'currency_code' => 'PKR', 'amount' => 42000.00, 'compare_at' => 48000.00],
        ]);
        DB::table('product_variant_prices')->insert([
            ['product_variant_id' => $v1Id, 'currency_code' => 'USD', 'amount' => 129.00, 'compare_at' => 149.00],
            ['product_variant_id' => $v1Id, 'currency_code' => 'PKR', 'amount' => 42000.00, 'compare_at' => 48000.00],
            ['product_variant_id' => $v2Id, 'currency_code' => 'USD', 'amount' => 129.00, 'compare_at' => null],
            ['product_variant_id' => $v2Id, 'currency_code' => 'PKR', 'amount' => 42000.00, 'compare_at' => null],
        ]);

        // Variant media (override)
        DB::table('product_variant_media')->insert([
            'product_variant_id' => $v1Id,
            'type' => 'image',
            'url' => 'https://cdn.example.com/p/velvet/red_1.jpg',
            'position' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Remember for later steps
        $this->memo('product_id', $productId);
        $this->memo('variant_red_s_id', $v1Id);
        $this->memo('variant_blue_m_id', $v2Id);
    }

    // ---------------------------------------------------------------------
    // Inventory
    // ---------------------------------------------------------------------

    protected function seedInventory(): void
    {
        $pkStockId = DB::table('stocks')->insertGetId([
            'title' => 'PK Main',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $usStockId = DB::table('stocks')->insertGetId([
            'title' => 'US NJ',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $v1 = $this->memo('variant_red_s_id');

        DB::table('stock_levels')->insert([
            'stock_id' => $pkStockId,
            'variant_id' => $v1,
            'quantity' => 25,
            'notify_below' => 5,
            'allow_backorder' => false,
            'promised_at' => null,
            'restock_eta' => Carbon::now()->addDays(20),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $batchId = DB::table('stock_batches')->insertGetId([
            'stock_id' => $pkStockId,
            'variant_id' => $v1,
            'received_at' => Carbon::now()->subDays(2)->toDateString(),
            'currency_code' => 'PKR',
            'unit_cost' => 28000.00,
            'qty_received' => 25,
            'qty_remaining' => 25,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('stock_movements')->insert([
            'stock_id' => $pkStockId,
            'variant_id' => $v1,
            'stock_movement_type_code' => 'purchase',
            'quantity_delta' => 25,
            'stock_batch_id' => $batchId,
            'order_id' => null,
            'performed_by' => null,
            'reason' => 'Initial stock',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->memo('pk_stock_id', $pkStockId);
        $this->memo('us_stock_id', $usStockId);
    }

    // ---------------------------------------------------------------------
    // Promotions
    // ---------------------------------------------------------------------

    protected function seedPromotions(): void
    {
        $promoId = DB::table('promotions')->insertGetId([
            'title' => 'Welcome 10% OFF',
            'from_date' => Carbon::now()->subDay(),
            'to_date' => Carbon::now()->addMonth(),
            'applies_via' => 'coupon',
            'usage_per_user' => 1,
            'rules' => json_encode(['type' => 'percent', 'value' => 10]),
            'sort_order' => 1,
            'active' => true,
            'status' => 'ongoing',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('promotion_coupons')->insert([
            'promotion_id' => $promoId,
            'code' => 'WELCOME10',
            'usage_limit' => 1000,
            'usage_per_user' => 1,
            'expiry' => Carbon::now()->addMonths(6),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->memo('promo_id', $promoId);
    }

    // ---------------------------------------------------------------------
    // Order flow (minimal)
    // ---------------------------------------------------------------------

    protected function seedOrderFlow(): void
    {
        $userId = $this->memo('pk_user_id');
        $address = DB::table('addresses')->where('user_id', $userId)->first();
        $variant = $this->memo('variant_red_s_id');

        $orderId = DB::table('orders')->insertGetId([
            'number' => now()->timestamp, // 10-digit
            'user_id' => $userId,
            'currency_code' => 'PKR',
            'order_status_code' => 'paid',
            'billing_address_id' => $address->id,
            'shipping_address_id' => $address->id,
            'shipping_address' => json_encode($this->addressSnapshot($address)),
            'billing_address' => json_encode($this->addressSnapshot($address)),
            'tax_inclusive' => true,
            'items_subtotal' => 42000.00,
            'total_discount' => 4200.00,
            'total_tax' => 0.00,
            'total_shipping' => 300.00,
            'total' => 38100.00,
            'discount' => json_encode([['code' => 'WELCOME10', 'amount' => 4200.00]]),
            'shipment' => json_encode(['method' => 'self', 'charge' => 300.00]),
            'promotions' => json_encode(['ids' => [$this->memo('promo_id')]]),
            'taxes' => json_encode([]),
            'payment_method' => 'payfast',
            'payment_txn_id' => 'PF-'.Str::upper(Str::random(10)),
            'idempotency_key' => Str::uuid()->toString(),
            'shipping_method' => 'self',
            'tracking_number' => null,
            'carrier' => null,
            'paid_at' => now(),
            'shipped_at' => null,
            'delivered_at' => null,
            'cancelled_at' => null,
            'refunded_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Order item snapshot
        $variantRow = DB::table('product_variants')->find($variant);
        $productRow = DB::table('products')->find($variantRow->product_id);

        DB::table('order_items')->insert([
            'order_id' => $orderId,
            'product_id' => $productRow->id,
            'product_variant_id' => $variantRow->id,
            'product_name' => $productRow->name,
            'sku' => $variantRow->sku,
            'variant' => json_encode(['color' => 'Red', 'size' => 'S']),
            'quantity' => 1,
            'unit_price' => 42000.00,
            'subtotal' => 42000.00,
            'discount' => 4200.00,
            'tax' => 0.00,
            'total' => 37800.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Invoice
        $invoiceId = DB::table('invoices')->insertGetId([
            'order_id' => $orderId,
            'number' => now()->timestamp + 100, // simple demo
            'currency_code' => 'PKR',
            'amount_due' => 38100.00,
            'invoice_status_code' => 'paid',
            'issued_at' => now(),
            'due_at' => null,
            'paid_at' => now(),
            'meta' => json_encode(['note' => 'Demo invoice']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Payment (sale)
        DB::table('payments')->insert([
            'order_id' => $orderId,
            'invoice_id' => $invoiceId,
            'currency_code' => 'PKR',
            'payment_method_code' => 'payfast',
            'action' => 'sale',
            'payment_status_code' => 'succeeded',
            'amount' => 38100.00,
            'gateway_txn_id' => 'PF-'.Str::upper(Str::random(12)),
            'idempotency_key' => Str::uuid()->toString(),
            'processed_at' => now(),
            'request_payload' => json_encode([]),
            'response_payload' => json_encode([]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Shipment (self delivery)
        DB::table('shipments')->insert([
            'order_id' => $orderId,
            'stock_id' => $this->memo('pk_stock_id'),
            'shipment_method_code' => 'self',
            'carrier' => null,
            'tracking_number' => null,
            'tracking_url' => null,
            'shipment_status_code' => 'out_for_delivery',
            'currency_code' => 'PKR',
            'shipping_charge' => 300.00,
            'shipping_cost' => 150.00,
            'shipping_tax' => 0.00,
            'shipped_at' => now(),
            'delivered_at' => null,
            'returned_at' => null,
            'label_url' => null,
            'carrier_payload' => json_encode([]),
            'notes' => json_encode(['driver' => 'Ali']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    // ---------------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------------

    protected array $memo = [];

    protected function memo(string $key, $value = null)
    {
        if (func_num_args() === 2) {
            $this->memo[$key] = $value;

            return $value;
        }

        return $this->memo[$key] ?? null;
    }

    protected function addressSnapshot(object $addr): array
    {
        return [
            'name' => $addr->name,
            'line1' => $addr->line1,
            'line2' => $addr->line2,
            'city' => $addr->city,
            'state_code' => $addr->state_code,
            'postal_code' => $addr->postal_code,
            'country_code' => $addr->country_code,
            'phone' => $addr->phone,
        ];
    }
}
