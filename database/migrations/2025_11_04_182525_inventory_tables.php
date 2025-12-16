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
        // Stocks
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // e.g., "PK Main", "US NJ"
            $table->timestampsTz();
        });

        // Current levels per stock × variant
        Schema::create('stock_levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_id')->constrained('stocks')->cascadeOnDelete();
            $table->foreignId('variant_id')->constrained('product_variants')->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(0);
            $table->unsignedInteger('notify_below')->default(50);
            $table->boolean('allow_backorder')->default(false)->comment('Can we sell it now even if out of stock?');
            $table->timestampTz('promised_at')->nullable()->comment('When can the buyer expect it?');
            $table->timestampTz('restock_eta')->nullable()->comment('When do we expect to receive it?');
            $table->timestampsTz();

            $table->unique(['stock_id', 'variant_id']);

            $table->index(['stock_id', 'updated_at']);
        });

        // Costed purchase batches (for FIFO/Avg and audits)
        Schema::create('stock_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_id')->constrained('stocks')->cascadeOnDelete();
            $table->foreignId('variant_id')->constrained('product_variants')->cascadeOnDelete();
            $table->date('received_at');
            $table->string('currency_code', 3);
            $table->decimal('unit_cost', 12, 2);
            $table->unsignedInteger('qty_received');
            $table->unsignedInteger('qty_remaining'); // decrement on sales if using FIFO
            $table->timestampsTz();

            $table->foreign('currency_code')->references('code')->on('currencies');

            $table->index(['stock_id', 'variant_id', 'received_at']);
        });

        // Immutable movement log (every stock change)
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_id')->constrained('stocks')->cascadeOnDelete();
            $table->foreignId('variant_id')->constrained('product_variants')->cascadeOnDelete();
            $table->string('stock_movement_type_code');
            $table->integer('quantity_delta')->comment('positive value for moving in and negative for moving out');
            $table->foreignId('stock_batch_id')->nullable()->constrained()->nullOnDelete(); // link for FIFO
            $table->foreignId('order_id')->nullable()->constrained('orders')->comment('add when orders exist');
            $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reason')->nullable();
            $table->timestampsTz();

            $table->foreign('stock_movement_type_code')->references('code')->on('stock_movement_types');

            $table->index(['stock_id', 'variant_id', 'created_at']);
        });

        // Back-in-stock subscriptions
        Schema::create('stock_back_in_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('variant_id')->constrained('product_variants')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('email')->nullable();
            $table->timestampTz('notified_at')->nullable();
            $table->timestampsTz();

            $table->unique(['variant_id', 'user_id', 'email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_back_in_subscriptions');
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('stock_batches');
        Schema::dropIfExists('stock_levels');
        Schema::dropIfExists('stocks');
    }
};
