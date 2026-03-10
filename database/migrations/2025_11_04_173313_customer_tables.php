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
        Schema::create('customer_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('label')->nullable(); // "Home", "Office"
            $table->string('name')->nullable();
            $table->string('line1');
            $table->string('line2')->nullable();
            $table->string('city');
            $table->string('state_code')->nullable(); // for PK null; for US we store code
            $table->string('postal_code')->nullable(); // for PK null; for US we store postal code
            $table->string('country_code', 2);
            $table->string('phone', 14)->nullable();
            $table->timestampsTz();

            $table->foreign('country_code')->references('code')->on('countries');
            $table->foreign('state_code')->references('code')->on('states');
        });

        Schema::create('customer_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->boolean('marketing_email_opt_in')->default(false)->comment('Stay compliant (CAN-SPAM/CPRA best practices)');
            $table->timestampTz('marketing_email_consented_at')->nullable();
            $table->timestampTz('marketing_email_revoked_at')->nullable();
            $table->string('marketing_email_consent_ip', 45)->nullable();
            $table->string('marketing_email_consent_source', 100)->nullable();

            $table->boolean('marketing_sms_opt_in')->default(false);
            $table->timestampTz('marketing_sms_consented_at')->nullable();
            $table->timestampTz('marketing_sms_revoked_at')->nullable();
            $table->string('marketing_sms_consent_ip', 45)->nullable();
            $table->string('marketing_sms_consent_source', 100)->nullable();

            $table->string('preferred_currency', 3); // auto set; display only
            $table->foreignId('default_shipping_address_id')->nullable()->constrained('customer_addresses')->nullOnDelete();
            $table->foreignId('default_billing_address_id')->nullable()->constrained('customer_addresses')->nullOnDelete();
            $table->timestampsTz();
        });

        Schema::create('customer_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();

            // Core lifetime counts
            $table->unsignedInteger('total_orders_count')->default(0);
            $table->unsignedInteger('completed_orders_count')->default(0);
            $table->unsignedInteger('cancelled_orders_count')->default(0);
            $table->unsignedInteger('returned_orders_count')->default(0);
            $table->unsignedInteger('refunded_orders_count')->default(0);

            // Useful customer timeline stats
            $table->timestampTz('first_order_at')->nullable();
            $table->timestampTz('last_order_at')->nullable();

            // Revenue stats
            // Adjust precision if you expect very large lifetime values
            $table->decimal('gross_revenue', 12, 2)->default(0);
            $table->decimal('net_revenue', 12, 2)->default(0);
            $table->decimal('average_order_value', 12, 2)->default(0);

            $table->timestampsTz();

            // Helpful indexes for admin filters / segmentation
            $table->index('total_orders_count');
            $table->index('completed_orders_count');
            $table->index('cancelled_orders_count');
            $table->index('returned_orders_count');
            $table->index('refunded_orders_count');
            $table->index('first_order_at');
            $table->index('last_order_at');
            $table->index('gross_revenue');
            $table->index('net_revenue');
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
        Schema::dropIfExists('customer_stats');
        Schema::dropIfExists('customer_accounts');
        Schema::dropIfExists('customer_addresses');
    }
};
