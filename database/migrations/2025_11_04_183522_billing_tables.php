<?php

use App\Models\RefundStatus;
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
        // Invoices
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained(); // snapshot reads from orders
            $table->bigInteger('number')->unique(); // display number (your format)
            $table->char('currency_code', 3);
            $table->decimal('amount_due', 12, 2); // total to collect for this invoice
            $table->string('invoice_status_code'); // e.g. issued, voided, paid
            $table->timestampTz('issued_at')->useCurrent();
            $table->timestampTz('due_at')->nullable();
            $table->timestampTz('paid_at')->nullable();
            $table->jsonb('meta')->nullable(); // optional (billing notes, PDF link, etc.)
            $table->timestampsTz();

            $table->foreign('currency_code')->references('code')->on('currencies');
            $table->foreign('invoice_status_code')->references('code')->on('invoice_statuses');

            $table->index(['invoice_status_code', 'order_id']);
            $table->index(['issued_at']);
        });

        // Payments - One row per successful money movement (auth, capture, sale, COD collection).
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained();
            $table->foreignId('invoice_id')->nullable()->constrained('invoices'); // present for COD, or if you issue invoices
            $table->char('currency_code', 3);
            $table->string('payment_method_code')->comment('expand as needed');
            $table->enum('action', ['auth', 'capture', 'sale', 'cod_collection'])->comment('US: auth->capture; PK: sale');
            $table->string('payment_status_code');
            $table->decimal('amount', 12, 2);
            $table->string('gateway_txn_id')->nullable(); // provider transaction id
            $table->string('idempotency_key')->nullable()->unique(); // prevent double posting
            $table->timestampTz('processed_at')->nullable();
            $table->jsonb('request_payload')->nullable(); // minimal audit
            $table->jsonb('response_payload')->nullable();
            $table->timestampsTz();

            $table->foreign('currency_code')->references('code')->on('currencies');
            $table->foreign('payment_method_code')->references('code')->on('payment_methods');
            $table->foreign('payment_status_code')->references('code')->on('payment_statuses');

            $table->index(['order_id', 'payment_status_code']);
            $table->index(['gateway_txn_id']);
            $table->index(['processed_at']);
        });

        // Payment Attempts - Every try (redirect, 3DS, fail, retry). Link to payment if it ultimately produced one.
        Schema::create('payment_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained();
            $table->foreignId('payment_id')->nullable()->constrained('payments');
            $table->char('currency_code', 3);
            $table->enum('method', ['stripe', 'paypal', 'payfast', 'cod', 'easypaisa', 'jazzcash']);
            $table->enum('action', ['auth', 'capture', 'sale', 'cod_collection']);
            $table->enum('status', ['pending', 'succeeded', 'failed', 'requires_action'])->default('pending');
            $table->decimal('amount', 12, 2)->default(0);

            $table->string('error_code')->nullable();
            $table->string('error_message')->nullable();
            $table->string('idempotency_key')->nullable()->unique();
            $table->string('remote_ip')->nullable();

            $table->jsonb('request_payload')->nullable();
            $table->jsonb('response_payload')->nullable();

            $table->timestampTz('attempted_at')->useCurrent();
            $table->timestampsTz();

            $table->foreign('currency_code')->references('code')->on('currencies');

            $table->index(['order_id', 'attempted_at']);
            $table->index(['status', 'attempted_at']);
        });

        // Refunds
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained();
            $table->foreignId('payment_id')->constrained('payments'); // refund against the captured/settled payment
            $table->char('currency_code', 3);
            $table->decimal('amount', 12, 2); // app enforces == order total (full) if required
            $table->string('refund_status_code')->default(RefundStatus::REQUESTED);
            $table->string('gateway_refund_id')->nullable();
            $table->string('reason')->nullable();
            $table->timestampTz('processed_at')->nullable();
            $table->string('idempotency_key')->nullable()->unique();
            $table->timestampsTz();

            $table->foreign('currency_code')->references('code')->on('currencies');
            $table->foreign('refund_status_code')->references('code')->on('refund_statuses');

            $table->index(['order_id', 'refund_status_code']);
            $table->index(['payment_id']);
            $table->index(['processed_at']);
        });

        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->unique();
            $table->foreignId('stock_id')->constrained();
            $table->string('shipment_method_code');

            // Carrier fields only relevant when method='courier'
            $table->string('carrier')->nullable(); // e.g., 'USPS','UPS','FEDEX','LEOPARDS','TCS'
            $table->string('tracking_number')->nullable();
            $table->string('tracking_url')->nullable();

            // Status lifecycle kept simple for both manual and courier paths
            $table->string('shipment_status_code');

            // Money snapshots (audit & reconciliation)
            $table->string('currency_code', 3);
            $table->decimal('shipping_charge', 12, 2)->comment('what customer paid (snapshot from order.total_shipping)');
            $table->decimal('shipping_cost', 12, 2)->comment('what we paid the carrier / internal cost');
            $table->decimal('shipping_tax', 12, 2)->comment('tax owed on shipping, if applicable (US-state rules)');

            // Timestamps across the journey
            $table->timestampTz('shipped_at')->nullable();
            $table->timestampTz('delivered_at')->nullable();
            $table->timestampTz('returned_at')->nullable();

            // Artifacts / audit blobs
            $table->string('label_url')->nullable();        // S3 link if you generate a label later
            $table->jsonb('carrier_payload')->nullable();   // raw courier response (when applicable)
            $table->jsonb('notes')->nullable();             // ops notes (driver name, attempt notes, etc.)

            $table->timestampsTz();

            $table->foreign('currency_code')->references('code')->on('currencies');
            $table->foreign('shipment_method_code')->references('code')->on('shipment_methods');
            $table->foreign('shipment_status_code')->references('code')->on('shipment_statuses');

            // Helpful indexes
            $table->index(['shipment_status_code', 'shipped_at']);
            $table->index(['tracking_number']);
            $table->index(['shipment_method_code', 'carrier']);
        });

        Schema::create('shipment_rates', function (Blueprint $table) {
            $table->id();
            $table->string('shipment_method_code');
            $table->string('currency_code', 3);
            $table->decimal('min_subtotal', 12, 2)->nullable();
            $table->decimal('max_subtotal', 12, 2)->nullable();
            $table->decimal('price', 12, 2);
            $table->boolean('active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestampsTz();

            $table->foreign('shipment_method_code')->references('code')->on('shipment_methods');
            $table->foreign('currency_code')->references('code')->on('currencies');

            $table->index(['shipment_method_code', 'currency_code', 'active']);
            $table->index(['currency_code', 'active']);

            $table->unique(['shipment_method_code', 'currency_code', 'min_subtotal', 'max_subtotal']);
            $table->unique(['shipment_method_code', 'currency_code', 'sort_order']);
        });

        Schema::create('cod_postal_codes', function (Blueprint $table) {
            $table->id();
            $table->string('country_code', 2);
            $table->string('postal_code');
            $table->decimal('cod_fee', 12, 2)->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->unique(['country_code', 'postal_code']);

            $table->index(['country_code', 'active']);
        });

        // Promotions
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->timestampTz('from_date');
            $table->timestampTz('to_date')->nullable();
            $table->enum('applies_via', ['auto', 'coupon'])->default('auto');
            $table->unsignedSmallInteger('usage_per_user')->nullable(); // null means unlimited use
            $table->jsonb('rules');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('active')->default(false);
            $table->enum('status', ['scheduled', 'ongoing', 'completed', 'paused']);
            $table->timestampsTz();

            $table->index(['active', 'from_date', 'to_date']);
        });

        Schema::create('promotion_coupons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promotion_id')->constrained();
            $table->string('code')->unique();
            $table->unsignedSmallInteger('usage_limit')->nullable();
            $table->unsignedSmallInteger('usage_per_user')->nullable();
            $table->timestampTz('expiry')->nullable();
            $table->timestampsTz();
        });

        Schema::create('promotion_redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promotion_id')->constrained();
            $table->foreignId('promotion_coupon_id')->nullable()->constrained();
            $table->foreignId('user_id')->nullable()->constrained();
            $table->foreignId('order_id')->nullable()->constrained();
            $table->timestampTz('redeemed_at')->useCurrent();
            $table->string('currency_code', 3);
            $table->decimal('cart_amount', 12, 2);
            $table->decimal('discount_amount', 12, 2);
            $table->string('idempotency_key')->nullable()->unique();
            $table->timestampsTz();

            $table->unique(['order_id', 'promotion_id']);

            $table->foreign('currency_code')->references('code')->on('currencies');

            $table->index(['promotion_id', 'promotion_coupon_id']);
            $table->index(['user_id', 'promotion_id']);
            $table->index(['redeemed_at']); // time-window reports
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotion_redemptions');
        Schema::dropIfExists('promotion_coupons');
        Schema::dropIfExists('promotions');
        Schema::dropIfExists('cod_postal_codes');
        Schema::dropIfExists('shipment_rates');
        Schema::dropIfExists('shipments');
        Schema::dropIfExists('refunds');
        Schema::dropIfExists('payment_attempts');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoices');
    }
};
