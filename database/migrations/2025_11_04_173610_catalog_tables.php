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
        Schema::create('attributes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('label');
            $table->enum('type', ['text', 'select'])->default('text');
            $table->boolean('active')->default(true);
            $table->timestampsTz();
        });

        // Attribute options for selectable atributes
        Schema::create('attribute_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attribute_id')->constrained()->cascadeOnDelete();
            $table->string('value');
            $table->unsignedSmallInteger('sort');
            $table->timestampsTz();

            $table->unique(['attribute_id', 'value']);
        });

        // Categories
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_og_image')->nullable();
            $table->string('slug')->unique();
            $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->boolean('active')->default(true);
            $table->timestampsTz();
        });

        // Collections (manual curation)
        Schema::create('collections', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_og_image')->nullable();
            $table->string('slug')->unique();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->string('notes')->nullable();
            $table->boolean('active')->default(true);
            $table->timestampsTz();
        });

        // Products
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique();
            $table->string('name');
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_og_image')->nullable();
            $table->string('slug')->unique();
            $table->enum('type', ['simple', 'variable', 'bundle']);
            $table->text('description')->nullable();
            $table->foreignId('tax_class_id')->constrained();
            $table->boolean('active')->default(true);
            $table->timestampsTz();
        });

        // Product prices
        Schema::create('product_prices', function (Blueprint $table) {
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('currency_code', 3);
            $table->decimal('amount', 12, 2); // the price will always be saved like 10.00 or 9.99
            $table->decimal('compare_at', 12, 2)->nullable(); // Original price to show strikethrough and compute discount %

            $table->primary(['product_id', 'currency_code']);

            $table->foreign('currency_code')->references('code')->on('currencies');
        });

        // Product media (shared gallery)
        Schema::create('product_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['image', 'video']);
            $table->string('url'); // S3/CloudFront URL
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestampsTz();

            $table->unique(['product_id', 'position']); // deterministic order
        });

        // Variants (simple/variable/bundle product will have atleast one variant)
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained();
            $table->string('sku')->unique();
            $table->string('title'); // title for PLP/PDP. e.g., Valvet fabric with local design
            $table->text('description')->nullable(); // detailed description for PLP/PDP. if null, inherit
            $table->boolean('default')->default(false);
            $table->boolean('active')->default(false);
            $table->timestampsTz();

            $table->index(['product_id', 'active']);
        });

        // Only variants can have different attributes to describe prouduct
        Schema::create('product_variant_attributes', function (Blueprint $table) {
            $table->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attribute_id')->constrained()->cascadeOnDelete();
            $table->foreignId('option_id')->nullable()->constrained('attribute_options')->cascadeOnDelete();
            $table->string('value')->nullable();

            $table->primary(['product_variant_id', 'attribute_id']);

            // these indexes are only useful when filters are applied
            $table->index(['attribute_id', 'option_id']);
            $table->index(['attribute_id', 'value']);
        });

        // Pricing per variant per currency (admin-set, no FX)
        Schema::create('product_variant_prices', function (Blueprint $table) {
            $table->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
            $table->string('currency_code', 3);
            $table->decimal('amount', 12, 2); // the price will always be saved like 10.00 or 9.99
            $table->decimal('compare_at', 12, 2)->nullable(); // Original price to show strikethrough and compute discount %

            $table->primary(['product_variant_id', 'currency_code']);

            $table->foreign('currency_code')->references('code')->on('currencies');
        });

        // Media for variant that can override product media
        Schema::create('product_variant_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['image', 'video']);
            $table->string('url'); // S3/CloudFront URL
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestampsTz();

            $table->unique(['product_variant_id', 'position']); // deterministic order
        });

        // Bundles: a product of type "bundle" maps to the variants
        Schema::create('product_bundles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->timestampsTz();

            $table->unique(['product_id', 'product_variant_id']);
        });

        // Related products (manual)
        Schema::create('related_products', function (Blueprint $table) {
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('related_product_id')->constrained('products')->cascadeOnDelete();

            $table->primary(['product_id', 'related_product_id']);
        });

        // Product Category (many-to-many)
        Schema::create('category_product', function (Blueprint $table) {
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();

            $table->primary(['category_id', 'product_id']);
        });

        // Product Collection (many-to-many)
        Schema::create('collection_product', function (Blueprint $table) {
            $table->foreignId('collection_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('sort')->default(0);

            $table->primary(['collection_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collection_product');
        Schema::dropIfExists('category_product');
        Schema::dropIfExists('related_products');
        Schema::dropIfExists('product_bundles');
        Schema::dropIfExists('product_variant_media');
        Schema::dropIfExists('product_variant_prices');
        Schema::dropIfExists('product_variant_attributes');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('product_media');
        Schema::dropIfExists('product_prices');
        Schema::dropIfExists('products');
        Schema::dropIfExists('collections');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('attribute_options');
        Schema::dropIfExists('attributes');
    }
};
