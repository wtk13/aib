<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('geocoding_caches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('normalized_address');
            $table->decimal('lat', 10, 7);
            $table->decimal('lng', 10, 7);
            $table->string('provider')->default('google');
            $table->jsonb('raw_response')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['tenant_id', 'normalized_address']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('geocoding_caches');
    }
};
