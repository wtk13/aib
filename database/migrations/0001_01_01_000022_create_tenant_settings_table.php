<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_settings', function (Blueprint $table) {
            $table->foreignId('tenant_id')->primary()->constrained()->cascadeOnDelete();
            $table->foreignId('origin_address_id')->nullable()->constrained('addresses')->nullOnDelete();
            $table->decimal('fuel_rate_pln_per_km', 5, 2)->default(1.80);
            $table->unsignedTinyInteger('default_vat_rate')->default(23);
            $table->boolean('is_vat_payer')->default(false);
            $table->string('quote_number_pattern')->default('{YYYY}/{MM}/{seq:003}');
            $table->decimal('ai_monthly_cap_pln', 8, 2)->default(50);
            $table->string('ai_alerts_email')->nullable();
            $table->boolean('whisper_cleanup_enabled')->default(false);
            $table->jsonb('pdf_branding')->default('{}');
            $table->string('locale', 5)->default('pl');
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_settings');
    }
};
