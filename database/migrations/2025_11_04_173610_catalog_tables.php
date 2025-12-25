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
            $table->string('slug')->unique();
            $table->enum('type', ['simple', 'variable', 'bundle']);
            $table->text('description')->nullable();
            $table->foreignId('tax_class_id')->constrained();
            $table->boolean('active')->default(true);
            $table->timestampsTz();
        });

        // Product prices
        Schema::create('product_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('currency_code', 3);
            $table->decimal('amount', 12, 2); // the price will always be saved like 10.00 or 9.99
            $table->decimal('compare_at', 12, 2)->nullable(); // Original price to show strikethrough and compute discount %
            $table->timestampsTz();

            $table->unique(['product_id', 'currency_code']);

            $table->foreign('currency_code')->references('code')->on('currencies');
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
            $table->id();
            $table->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
            $table->string('currency_code', 3);
            $table->decimal('amount', 12, 2); // the price will always be saved like 10.00 or 9.99
            $table->decimal('compare_at', 12, 2)->nullable(); // Original price to show strikethrough and compute discount %
            $table->timestampsTz();

            $table->unique(['product_variant_id', 'currency_code']);

            $table->foreign('currency_code')->references('code')->on('currencies');
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

        /**
         * --------------------------------------------------------------------------
         * Media Asset System
         * --------------------------------------------------------------------------
         *
         * Purpose
         * - Provide one unified, reusable media model for the entire platform:
         *   products, variants, categories, collections, and future content
         *   (lookbooks, banners, testimonials).
         *
         * Design Principles
         * - Store a single canonical asset once (image/video).
         * - Attach assets to any entity via polymorphic attachments.
         * - Assign explicit "roles" to attachments so storefront/admin use cases are
         *   deterministic (no implicit guessing from position alone).
         * - Support ordered galleries, primary selection, and SEO/Accessibility fields.
         * - Support renditions (thumbnails/sizes) for performance.
         *
         * System Role Constants (v1 — do not invent new roles in v1)
         * - Product:
         *   - thumbnail  : single primary (PLP cards, cart line item, admin tables)
         *   - gallery    : ordered list (PDP gallery)
         *   - hero       : optional, single primary (PDP header / merchandising)
         *   - og_image   : single primary (Open Graph / social sharing)
         *
         * - Variant:
         *   - thumbnail  : single primary (variant override for PLP/cart)
         *   - gallery    : ordered list (variant-specific PDP gallery)
         *
         * - Category:
         *   - thumbnail  : single primary (category tiles / navigation)
         *   - hero       : single primary (category landing header/banner)
         *   - og_image   : single primary (Open Graph / social sharing)
         *
         * - Collection:
         *   - hero       : single primary (collection header/banner)
         *   - og_image   : single primary (Open Graph / social sharing)
         *
         * Selection Rules (storefront)
         * - Use variant media when available; otherwise fallback to product media.
         * - Use `role` to decide usage, `is_primary` for the main pick, and `position`
         *   for deterministic ordering within the same role.
         */
        Schema::create('media_assets', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['image', 'video']);

            // Storage identity (canonical/original)
            $table->string('disk')->default('s3');    // e.g. s3
            $table->string('key');                    // object key for original
            $table->string('cdn_url')->nullable();    // optional cached public URL

            // Technical metadata
            $table->string('mime_type', 100)->nullable();
            $table->unsignedInteger('bytes')->nullable();
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();

            // SEO / accessibility defaults (can be overridden per attachment)
            $table->string('alt_text')->nullable();
            $table->string('title')->nullable();
            $table->string('caption')->nullable();

            // Integrity / de-dupe
            $table->string('checksum', 64)->nullable(); // sha256 recommended

            $table->timestampsTz();

            $table->unique(['disk', 'key']);

            $table->index(['type']);
        });

        Schema::create('media_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_asset_id')->constrained('media_assets')->cascadeOnDelete();

            // Polymorphic owner
            $table->string('owner_type'); // model class
            $table->unsignedBigInteger('owner_id');

            // Purpose of attachment
            $table->string('role', 50); // thumbnail, gallery, hero, swatch, og_image, banner, etc.

            // Ordering + primary selection
            $table->unsignedSmallInteger('position')->default(0);
            $table->boolean('is_primary')->default(false);

            // Per-context overrides
            $table->string('alt_text')->nullable();
            $table->string('caption')->nullable();

            $table->timestampsTz();

            $table->index(['owner_type', 'owner_id', 'role']);

            $table->unique(['owner_type', 'owner_id', 'role', 'position']);
        });

        Schema::create('media_renditions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('media_asset_id')->constrained('media_assets')->cascadeOnDelete();

            // Named sizes/derivatives (your code will standardize these)
            $table->string('profile', 50); // e.g. thumb_sm, thumb_md, plp_480w, pdp_1200w

            $table->string('disk')->default('s3');
            $table->string('key'); // object key for rendition
            $table->string('cdn_url')->nullable();

            $table->string('mime_type', 100)->nullable();
            $table->unsignedInteger('bytes')->nullable();
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();

            $table->timestampsTz();

            $table->unique(['media_asset_id', 'profile']);
        });

        Schema::create('media_videos', function (Blueprint $table) {
            $table->foreignId('media_asset_id')->primary()->constrained('media_assets')->cascadeOnDelete();
            $table->string('provider', 30)->nullable();  // youtube, vimeo, s3
            $table->string('external_id')->nullable();   // youtube/vimeo id if applicable
            $table->unsignedInteger('duration_seconds')->nullable();

            // Poster image (optional, but required for good UX)
            $table->foreignId('poster_media_asset_id')->nullable()->constrained('media_assets')->nullOnDelete();

            // Playback flags (important for banners/hero)
            $table->boolean('autoplay')->default(false);
            $table->boolean('muted')->default(true);
            $table->boolean('loop')->default(false);

            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media_videos');
        Schema::dropIfExists('media_renditions');
        Schema::dropIfExists('media_attachments');
        Schema::dropIfExists('media_assets');
        Schema::dropIfExists('collection_product');
        Schema::dropIfExists('category_product');
        Schema::dropIfExists('related_products');
        Schema::dropIfExists('product_bundles');
        Schema::dropIfExists('product_variant_prices');
        Schema::dropIfExists('product_variant_attributes');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('product_prices');
        Schema::dropIfExists('products');
        Schema::dropIfExists('collections');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('attribute_options');
        Schema::dropIfExists('attributes');
    }
};
