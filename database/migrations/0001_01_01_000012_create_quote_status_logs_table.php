<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quote_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('from_status');
            $table->string('to_status');
            $table->timestamp('transitioned_at')->useCurrent();
            $table->foreignId('transitioned_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->jsonb('meta')->default('{}');
            $table->index('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_status_logs');
    }
};
