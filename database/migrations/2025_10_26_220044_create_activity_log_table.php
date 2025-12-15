<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::connection(config('activitylog.database_connection'))
            ->create(config('activitylog.table_name'), function (Blueprint $table) {
                $table->id();
                $table->string('log_name')->nullable();
                $table->text('description');
                $table->string('subject_type')->nullable();
                $table->string('subject_id')->nullable();
                $table->string('event')->nullable();
                $table->string('causer_type')->nullable();
                $table->string('causer_id')->nullable();
                $table->json('properties')->nullable();
                $table->uuid('batch_uuid')->nullable()->after('properties');
                $table->timestampsTz();

                $table->index(['subject_type', 'subject_id'], 'subject');
                $table->index(['causer_type', 'causer_id'], 'causer');
                $table->index('log_name');
            });
    }

    public function down()
    {
        Schema::connection(config('activitylog.database_connection'))->dropIfExists(config('activitylog.table_name'));
    }
};
