<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vertical_presets', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('version')->default('1');
            $table->jsonb('vocabulary')->default('{}');
            $table->jsonb('custom_fields_schema')->default('{}');
            $table->jsonb('service_types')->default('[]');
            $table->jsonb('quote_template')->default('{}');
            $table->jsonb('ai_hints')->default('{}');
            $table->string('pdf_template_key')->default('generic_v1');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vertical_presets');
    }
};
