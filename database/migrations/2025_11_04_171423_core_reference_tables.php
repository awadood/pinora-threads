<?php

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
        // Countries we sell/ship to (US, PK)
        Schema::create('countries', function (Blueprint $table) {
            $table->string('code', 2)->primary(); // 'US', 'PK'
            $table->string('name');
        });

        // US states for tax rules (PK doesn’t need per-province rules for now)
        Schema::create('states', function (Blueprint $table) {
            $table->string('code', 2)->primary(); // 'CA', 'TX', etc.
            $table->string('name');
            $table->string('country_code', 2);

            $table->foreign('country_code')->references('code')->on('countries');
        });

        // Currencies (USD, PKR)
        Schema::create('currencies', function (Blueprint $table) {
            $table->string('code', 3)->primary(); // 'USD','PKR'
            $table->string('name');
        });

        // Order Statuses
        Schema::create('order_statuses', function (Blueprint $table) {
            $table->string('code')->primary();
            $table->string('name');
            $table->unsignedSmallInteger('sort_order');
            $table->boolean('active')->default(true);
            $table->timestampsTz();
        });

        // Shipment Statuses
        Schema::create('shipment_statuses', function (Blueprint $table) {
            $table->string('code')->primary();
            $table->string('name');
            $table->unsignedSmallInteger('sort_order');
            $table->boolean('active')->default(true);
            $table->timestampsTz();
        });

        // Payment Statuses
        Schema::create('payment_statuses', function (Blueprint $table) {
            $table->string('code')->primary();
            $table->string('name');
            $table->unsignedSmallInteger('sort_order');
            $table->boolean('active')->default(true);
            $table->timestampsTz();
        });

        // Refund Statuses
        Schema::create('refund_statuses', function (Blueprint $table) {
            $table->string('code')->primary();
            $table->string('name');
            $table->unsignedSmallInteger('sort_order');
            $table->boolean('active')->default(true);
            $table->timestampsTz();
        });

        // Invoice Statuses
        Schema::create('invoice_statuses', function (Blueprint $table) {
            $table->string('code')->primary();
            $table->string('name');
            $table->unsignedSmallInteger('sort_order');
            $table->boolean('active')->default(true);
            $table->timestampsTz();
        });

        // Payment Methods
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->string('code')->primary();
            $table->string('name');
            $table->unsignedSmallInteger('sort_order');
            $table->boolean('active')->default(true);
            $table->timestampsTz();
        });

        // Shipment Methods
        Schema::create('shipment_methods', function (Blueprint $table) {
            $table->string('code')->primary();
            $table->string('name');
            $table->unsignedSmallInteger('sort_order');
            $table->boolean('active')->default(true);
            $table->timestampsTz();
        });

        // Stock Movement Types
        Schema::create('stock_movement_types', function (Blueprint $table) {
            $table->string('code')->primary();
            $table->string('name');
            $table->unsignedSmallInteger('sort_order');
            $table->boolean('active')->default(true);
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movement_types');
        Schema::dropIfExists('shipment_methods');
        Schema::dropIfExists('payment_methods');
        Schema::dropIfExists('invoice_statuses');
        Schema::dropIfExists('refund_statuses');
        Schema::dropIfExists('payment_statuses');
        Schema::dropIfExists('shipment_statuses');
        Schema::dropIfExists('order_statuses');
        Schema::dropIfExists('currencies');
        Schema::dropIfExists('states');
        Schema::dropIfExists('countries');
    }
};
