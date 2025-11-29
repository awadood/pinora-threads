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
        // Favorites
        Schema::create('favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained()->cascadeOnDelete();
            $table->timestampsTz();

            $table->unique(['user_id', 'product_id', 'product_variant_id']);

            $table->index(['user_id', 'created_at']);
        });

        // Wishlists
        Schema::create('wishlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained(); // owner
            $table->string('title')->default('My Wishlist');
            $table->boolean('public')->default(false);
            $table->string('share_token')->nullable()->unique()->comment('UUID or hash for shareable link');
            $table->timestampsTz();

            $table->unique(['user_id', 'title']); // one list name per user
        });

        // Wishlist items
        Schema::create('wishlist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wishlist_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained()->cascadeOnDelete();
            $table->timestampsTz();

            $table->unique(['wishlist_id', 'product_id', 'product_variant_id']);
        });

        // recently_viewed
        Schema::create('recently_viewed', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained()->cascadeOnDelete();
            $table->timestampTz('viewed_at')->useCurrent();
            $table->timestampsTz();

            $table->index(['user_id', 'viewed_at']);
            $table->index(['product_id']);
        });

        // Testimonials
        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->string('author_name');
            $table->text('content'); // the quote
            $table->unsignedTinyInteger('rating'); // optional 1..5 if you want stars on homepage
            $table->string('photo_url')->nullable(); // avatar/selfie or product-in-use photo (S3)
            $table->unsignedSmallInteger('sort_order')->default(0); // manual ordering
            $table->timestampTz('published_at')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'archived'])->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampsTz();

            $table->index(['status', 'sort_order']);
            $table->index(['published_at']);
        });

        // Lookbooks (campaigns)
        Schema::create('lookbooks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_og_image')->nullable();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('cover_image')->nullable();              // hero banner (S3 URL)
            $table->boolean('active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestampTz('published_at')->nullable();
            $table->timestampsTz();

            $table->index(['active', 'sort_order']);
            $table->index(['published_at']);
        });

        // Lookbook items (styled looks)
        Schema::create('lookbook_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lookbook_id')->constrained()->cascadeOnDelete();
            $table->string('title')->nullable();                    // e.g., "Velvet Gala Look"
            $table->string('image_url');                            // full look image (S3 URL)
            $table->text('notes')->nullable();                      // styling notes / credits
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestampsTz();

            $table->index(['lookbook_id', 'sort_order']);
        });

        // Link look items to products/variants - Lets each styled look point to multiple products; optionally to a specific variant.
        Schema::create('lookbook_item_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lookbook_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestampsTz();

            // Prevent duplicate attachments of the same product/variant to the same look
            $table->unique(['lookbook_item_id', 'product_id', 'product_variant_id']);

            $table->index(['lookbook_item_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lookbook_item_products');
        Schema::dropIfExists('lookbook_items');
        Schema::dropIfExists('lookbooks');
        Schema::dropIfExists('testimonials');
        Schema::dropIfExists('recently_viewed');
        Schema::dropIfExists('wishlist_items');
        Schema::dropIfExists('wishlists');
        Schema::dropIfExists('favorites');
    }
};
