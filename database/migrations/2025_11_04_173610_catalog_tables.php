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
            $table->string('slug')->unique();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->string('description')->nullable();
            $table->boolean('active')->default(true);
            $table->timestampsTz();
        });

        // Products
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->enum('type', ['simple', 'variable', 'bundle']);
            $table->text('description')->nullable();
            $table->foreignId('tax_class_id')->constrained();
            $table->boolean('active')->default(true);
            $table->timestampTz('published_at')->nullable();
            $table->timestampTz('first_published_at')->nullable();
            $table->timestampsTz();

            $table->index(['active', 'published_at']);
        });

        // Variants (simple/variable/bundle product will have atleast one variant)
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained();
            $table->string('sku')->unique();
            $table->string('name')->nullable(); // specific variant name for PDP. if null, inherit.
            $table->text('description')->nullable(); // detailed variant description for PLP/PDP. if null, inherit
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
            $table->timestampsTz();

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

            $table->index(['currency_code', 'amount']);

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
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('related_product_id')->constrained('products')->cascadeOnDelete();

            $table->unique(['product_id', 'related_product_id']);
        });

        // Product Category (many-to-many)
        Schema::create('category_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();

            $table->unique(['category_id', 'product_id']);
        });

        // Product Collection (many-to-many)
        Schema::create('collection_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collection_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('sort')->default(0);

            $table->unique(['collection_id', 'product_id']);
        });

        // Collection Country (many-to-many)
        Schema::create('collection_country', function (Blueprint $table) {
            $table->id();
            $table->string('country_code', 2);
            $table->foreignId('collection_id')->constrained()->cascadeOnDelete();

            $table->foreign('country_code')->references('code')->on('countries');

            $table->unique(['country_code', 'collection_id']);
        });

        /**
         * --------------------------------------------------------------------------
         * Media Asset System (v1)
         * --------------------------------------------------------------------------
         *
         * Goals
         * - Canonical MediaAsset (image/video) stored once (S3 key).
         * - Attach assets to entities via MediaAttachment records.
         * - Deterministic usage via explicit role + is_primary + position.
         * - Renditions table stores derived sizes/profiles for performance.
         *
         * API Contract
         * - Client uses short keys for owner_type:
         *     variant | category | collection
         * - DB stores owner_type as fully-qualified model class:
         *     App\Models\ProductVariant, ...
         *
         * Roles (v1)
         * - variant:   thumbnail, gallery, hero, og_image
         * - category:  thumbnail, hero, og_image
         * - collection: thumbnail, hero, og_image
         *
         * Ordering + Primary
         * - position is server-controlled for ordered roles (gallery).
         * - Single-slot roles are unique per owner_type/owner_id/role.
         * - Gallery enforces exactly one primary when any records exist.
         */
        Schema::create('media_assets', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['image', 'video']);

            // Storage identity (canonical/original)
            $table->string('disk')->default('s3');    // e.g. s3
            $table->string('key');                    // object key for original

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
            $table->string('role', 50); // thumbnail, gallery, hero, og_image, banner, etc.

            // Ordering + primary selection
            $table->unsignedSmallInteger('position')->default(1);
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

        /**
         * Merchandising Sections (v1)
         *
         * Goals
         * - One unified merchandising system for home/landing blocks.
         * - Homogeneous sections: a section contains only products OR only collections OR only categories.
         * - Two population modes:
         *   1) curated: admin assigns ordered items
         *   2) query: admin stores normalized PLP query config; backend executes via existing PLP engine
         * - Country scoping: optional. Storefront resolves "country-specific" first, then falls back to global.
         * - Scheduling: starts_at / ends_at
         *
         * query_payload (LOCKED)
         * - Stored structure is normalized output derived from ProductFilters::parse(...storefront profile).
         * - Admin does NOT submit query_payload directly.
         *
         * meta (LOCKED)
         * - Flexible JSON for future presentation fields (headline/cta/layout) without schema changes.
         */
        Schema::create('merch_sections', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // e.g. home_featured_products, home_new_arrivals
            $table->string('name'); // Admin-facing label
            $table->string('surface', 50)->default('home'); // Used for grouping/admin UX (home, plp, pdp, etc.)

            $table->enum('item_type', ['product', 'collection', 'category']); // Homogeneous constraint
            $table->enum('mode', ['curated', 'query'])->default('curated'); // Unified system with two strategies
            $table->unsignedSmallInteger('default_limit')->default(8); // Default item count returned to storefront
            $table->string('country_code', 2)->nullable(); // Optional country scoping (StoreContext country code)

            // Scheduling
            $table->timestampTz('starts_at')->nullable();
            $table->timestampTz('ends_at')->nullable();

            $table->unsignedSmallInteger('sort')->default(0); // Admin ordering of sections (not items)
            $table->boolean('active')->default(true);
            $table->json('query_payload')->nullable(); // Normalized listing config (ONLY for mode=query)
            $table->json('meta')->nullable();
            $table->timestampsTz();

            $table->index(['surface', 'active']);
            $table->index(['country_code', 'surface', 'active']);

            $table->foreign('country_code')->references('code')->on('countries');
        });

        Schema::create('merch_section_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('merch_section_id')->constrained('merch_sections')->cascadeOnDelete();

            $table->enum('item_type', ['product', 'collection', 'category']); // Repeated for safety; must match section.item_type
            $table->unsignedBigInteger('item_id');
            $table->unsignedSmallInteger('position')->default(1); // Ordered list for curated mode
            $table->boolean('active')->default(true);
            $table->timestampsTz();

            $table->unique(['merch_section_id', 'item_type', 'item_id']);

            $table->index(['merch_section_id', 'active', 'position']);
            $table->index(['item_type', 'item_id']);
        });

        /**
         * SEO
         */
        Schema::create('seo_meta', function (Blueprint $table) {
            $table->id();

            // Polymorphic owner
            $table->string('owner_type');          // e.g. App\Models\Product
            $table->unsignedBigInteger('owner_id');

            // Core SEO
            $table->string('meta_title', 255)->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_robots', 50)->nullable();   // e.g. "index,follow" "noindex,nofollow"
            $table->string('canonical_url', 1024)->nullable();

            // Open Graph
            $table->string('og_title', 255)->nullable();
            $table->text('og_description')->nullable();
            $table->string('og_type', 30)->nullable();       // product, website, article, etc.
            $table->string('og_url', 1024)->nullable();
            $table->foreignId('og_image_id')->nullable()->constrained('media_assets')->nullOnDelete();

            // Twitter
            $table->string('twitter_card', 40)->nullable();  // summary, summary_large_image
            $table->string('twitter_title', 255)->nullable();
            $table->text('twitter_description')->nullable();
            $table->foreignId('twitter_image_id')->nullable()->constrained('media_assets')->nullOnDelete();

            // Schema.org (cached payload)
            $table->string('schema_type', 60)->nullable();    // Product, CollectionPage, Article...
            $table->json('schema_payload')->nullable();       // cached/normalized schema data

            // Optional: freeform extras for future without migrations
            $table->json('extra')->nullable();

            $table->timestampsTz();

            // Constraints & indexes
            $table->unique(['owner_type', 'owner_id']); // one seo record per owner
            $table->index(['owner_type', 'owner_id']);
            $table->index(['meta_robots']);
            $table->index(['og_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('merch_section_items');
        Schema::dropIfExists('merch_sections');
        Schema::dropIfExists('media_videos');
        Schema::dropIfExists('media_renditions');
        Schema::dropIfExists('media_attachments');
        Schema::dropIfExists('media_assets');
        Schema::dropIfExists('collection_country');
        Schema::dropIfExists('collection_product');
        Schema::dropIfExists('category_product');
        Schema::dropIfExists('related_products');
        Schema::dropIfExists('product_bundles');
        Schema::dropIfExists('product_variant_prices');
        Schema::dropIfExists('product_variant_attributes');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('products');
        Schema::dropIfExists('collections');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('attribute_options');
        Schema::dropIfExists('attributes');
    }
};
