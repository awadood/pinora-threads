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
        Schema::create('customer_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('tax_class_id')->constrained();
            $table->boolean('marketing_email_opt_in')->default(false)
                ->comment('Stay compliant (CAN-SPAM/CPRA best practices): only email people who consents.');
            $table->boolean('marketing_sms_opt_in')->default(false);
            $table->string('preferred_currency', 3); // auto set; display only
            $table->timestampsTz();
        });

        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->string('label')->nullable(); // "Home", "Office"
            $table->string('name')->nullable();
            $table->string('line1');
            $table->string('line2')->nullable();
            $table->string('city');
            $table->string('state_code')->nullable(); // for PK null; for US we store code
            $table->string('postal_code')->nullable(); // for PK null; for US we store postal code
            $table->string('country_code', 2);
            $table->string('phone')->nullable();
            $table->boolean('default_shipping')->default(false);
            $table->boolean('default_billing')->default(false);
            $table->timestampsTz();

            $table->foreign('country_code')->references('code')->on('countries');
            $table->foreign('state_code')->references('code')->on('states');
        });

        // Customer Groups (e.g., STANDARD, VIP, B2B)
        Schema::create('customer_groups', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();          // e.g., 'STANDARD', 'VIP', 'B2B'
            $table->string('name');                    // Display name
            $table->string('description')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('active')->default(true);
            $table->timestampsTz();

            $table->index(['active', 'sort_order']);
        });

        // User Group (many-to-many)
        Schema::create('customer_group_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_group_id')->constrained()->cascadeOnDelete();
            $table->timestampTz('assigned_at')->useCurrent();
            $table->timestampsTz();

            $table->unique(['user_id', 'customer_group_id']); // prevent duplicates

            $table->index(['customer_group_id', 'assigned_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_group_user');
        Schema::dropIfExists('customer_groups');
        Schema::dropIfExists('addresses');
        Schema::dropIfExists('customer_profiles');
    }
};
