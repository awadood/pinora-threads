<?php

use App\Models\OrderStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('cookie_key')->unique(); // server+cookie persistence
            $table->char('currency_code', 3);
            $table->string('shipping_method_code')->nullable();
            $table->timestampTz('expires_at')->nullable(); // housekeeping
            $table->timestampTz('checked_out_at')->nullable(); // conversion marker
            $table->timestampsTz();

            $table->foreign('currency_code')->references('code')->on('currencies');
            $table->foreign('shipping_method_code')->references('code')->on('shipment_methods');
        });

        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('quantity');
            $table->timestampsTz();

            $table->unique(['cart_id', 'product_id']);
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('number')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('cart_id')->nullable()->constrained()->nullOnDelete();
            $table->uuid('guest_token')->nullable()->unique(); // anonymous user can view via token link
            $table->string('currency_code', 3);
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone', 14);
            /**
             * claim/link state machine
             * - new: no account exists; prompt set password
             * - pending: email belongs to an account but not logged in/verified
             * - claimed: attached to a user_id
             */
            $table->enum('claim_status', ['new', 'pending', 'claimed']);
            $table->string('order_status_code')->default(OrderStatus::PENDING);
            $table->foreignId('billing_address_id')->nullable()->constrained('customer_addresses');
            $table->foreignId('shipping_address_id')->nullable()->constrained('customer_addresses');
            $table->jsonb('shipping_address')->nullable();
            $table->jsonb('billing_address')->nullable();

            $table->boolean('tax_inclusive')->default(false); // US=false, PK=true
            $table->decimal('items_subtotal', 12, 2);
            $table->decimal('total_discount', 12, 2);
            $table->decimal('total_tax', 12, 2);
            $table->decimal('total_shipping', 12, 2);
            $table->decimal('total', 12, 2); // payable
            $table->jsonb('discount')->nullable(); // applied discount
            $table->jsonb('shipment')->nullable(); // shipment summary
            $table->jsonb('promotions')->nullable(); // applied promotions
            $table->jsonb('taxes')->nullable(); // applied taxes

            $table->string('payment_method')->nullable();     // 'stripe','paypal','cod', etc.
            $table->string('payment_txn_id')->nullable();     // gateway transaction id
            $table->string('idempotency_key')->nullable()->unique();

            $table->string('shipping_method')->nullable();    // 'self','pickup','courier'
            $table->string('tracking_number')->nullable();
            $table->string('carrier')->nullable();            // 'USPS','UPS','TCS', etc.

            $table->timestampTz('paid_at')->nullable();
            $table->timestampTz('shipped_at')->nullable();
            $table->timestampTz('delivered_at')->nullable();
            $table->timestampTz('cancelled_at')->nullable();
            $table->timestampTz('refunded_at')->nullable();
            $table->timestampsTz();

            $table->foreign('currency_code')->references('code')->on('currencies');
            $table->foreign('order_status_code')->references('code')->on('order_statuses');

            $table->index(['number']);
            $table->index(['customer_email']);
            $table->index(['order_status_code', 'created_at']);
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained();
            $table->foreignId('product_id')->constrained();
            $table->string('product_name');
            $table->string('sku');
            $table->jsonb('product');
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 12, 2);
            $table->decimal('subtotal', 12, 2); // unit*qty
            $table->decimal('discount', 12, 2);
            $table->decimal('tax', 12, 2);
            $table->decimal('total', 12, 2); // subtotal - discount + tax
            $table->timestampsTz();

            $table->index(['order_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('carts');
    }
};
