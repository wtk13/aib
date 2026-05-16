<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pricing_suggestions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('quote_id')->constrained()->cascadeOnDelete();
            $table->decimal('suggested_total', 10, 2);
            $table->jsonb('breakdown')->default('[]');
            $table->text('reasoning')->nullable();
            $table->decimal('confidence', 3, 2)->nullable();
            $table->string('prompt_version')->nullable();
            $table->foreignId('ai_usage_log_id')->nullable()->constrained('ai_usage_logs')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();
            $table->index('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pricing_suggestions');
    }
};
