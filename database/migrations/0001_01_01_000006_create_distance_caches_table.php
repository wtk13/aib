<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('distance_caches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('origin_address_id')->constrained('addresses')->cascadeOnDelete();
            $table->foreignId('destination_address_id')->constrained('addresses')->cascadeOnDelete();
            $table->unsignedInteger('distance_meters');
            $table->unsignedInteger('duration_seconds');
            $table->jsonb('raw_response')->nullable();
            $table->timestamp('computed_at')->useCurrent();
            $table->unique(['tenant_id', 'origin_address_id', 'destination_address_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('distance_caches');
    }
};
