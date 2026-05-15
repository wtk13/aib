<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quote_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('position')->default(0);
            $table->string('description');
            $table->string('unit')->default('piece');
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('rate', 10, 2)->default(0);
            $table->decimal('discount_pct', 5, 2)->default(0);
            $table->unsignedTinyInteger('vat_pct')->default(23);
            $table->decimal('line_total', 10, 2)->default(0);
            $table->string('service_type_key')->nullable();
            $table->string('source')->default('manual');
            $table->timestamps();
            $table->index('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_items');
    }
};
