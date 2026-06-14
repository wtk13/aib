<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenant_settings', function (Blueprint $table) {
            $table->decimal('fuel_rate_pln_per_km', 5, 2)->default(0)->change();
        });

        DB::table('tenant_settings')->update(['fuel_rate_pln_per_km' => 0]);
    }

    public function down(): void
    {
        Schema::table('tenant_settings', function (Blueprint $table) {
            $table->decimal('fuel_rate_pln_per_km', 5, 2)->default(1.80)->change();
        });
    }
};
