<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->char('ulid', 26)->unique();
            $table->string('slug')->unique();
            $table->string('firma_name');
            $table->string('nip', 10)->nullable();
            $table->string('regon', 14)->nullable();
            $table->foreignId('preset_id')->nullable()->constrained('vertical_presets')->nullOnDelete();
            $table->string('preset_version')->nullable();
            $table->decimal('ai_monthly_cap_pln', 8, 2)->default(50);
            $table->decimal('ai_monthly_used_pln', 8, 2)->default(0);
            $table->boolean('is_vat_payer')->default(false);
            $table->unsignedTinyInteger('default_vat_rate')->default(23);
            $table->decimal('fuel_rate_pln_per_km', 5, 2)->default(1.80);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
