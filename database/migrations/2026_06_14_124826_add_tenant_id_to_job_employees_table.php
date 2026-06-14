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
        // Skip if column already exists (fresh installs have it from create migration)
        if (Schema::hasColumn('job_employees', 'tenant_id')) {
            return;
        }

        // Add nullable first so existing rows don't violate NOT NULL
        Schema::table('job_employees', function (Blueprint $table) {
            $table->unsignedBigInteger('tenant_id')->nullable()->after('id');
        });

        // Backfill tenant_id from the related job
        DB::statement('UPDATE job_employees je SET tenant_id = j.tenant_id FROM jobs j WHERE j.id = je.job_id');

        // Now enforce NOT NULL + FK
        Schema::table('job_employees', function (Blueprint $table) {
            $table->unsignedBigInteger('tenant_id')->nullable(false)->change();
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('job_employees', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropColumn('tenant_id');
        });
    }
};
