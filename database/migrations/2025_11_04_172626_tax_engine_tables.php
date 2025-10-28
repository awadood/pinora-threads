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
        Schema::create('tax_classes', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestampsTz();
        });

        Schema::create('tax_rules', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->unsignedSmallInteger('priority');
            $table->unsignedSmallInteger('position');
            $table->boolean('calculate_subtotal')->default(false);
            $table->boolean('applies_to_shipping')->default(false);
            $table->boolean('active')->default(true);
            $table->timestampsTz();
        });

        Schema::create('tax_rates', function (Blueprint $table) {
            $table->id();
            $table->string('code')->comment('e.g. US-CA-*-Rate 1, US-NY-*-Rate 1');
            $table->decimal('amount', 12, 2);
            $table->boolean('percentage');
            $table->boolean('refundable')->default(true);
            $table->string('country_code');
            $table->string('state_code')->nullable()->comment('it is null for PK');
            $table->string('zipcode');
            $table->boolean('zip_is_range')->nullable();
            $table->string('zip_from')->nullable();
            $table->string('zip_to')->nullable();
            $table->boolean('active')->default(true);
            $table->timestampsTz();

            $table->foreign('country_code')->references('code')->on('countries');
            $table->foreign('state_code')->references('code')->on('states');
        });

        Schema::create('tax_calculations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tax_rate_id')->constrained();
            $table->foreignId('tax_rule_id')->constrained();
            $table->foreignId('user_tax_class_id')->constrained('tax_classes');
            $table->foreignId('product_tax_class_id')->constrained('tax_classes');

            $table->unique(['tax_rate_id', 'tax_rule_id', 'user_tax_class_id', 'product_tax_class_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_calculations');
        Schema::dropIfExists('tax_rates');
        Schema::dropIfExists('tax_rules');
        Schema::dropIfExists('tax_classes');
    }
};
