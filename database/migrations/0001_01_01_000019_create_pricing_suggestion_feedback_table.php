<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pricing_suggestion_feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('suggestion_id')->constrained('pricing_suggestions')->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('decision');
            $table->decimal('final_total', 10, 2)->nullable();
            $table->decimal('diff_pct', 6, 2)->nullable();
            $table->timestamp('recorded_at')->useCurrent();
            $table->index('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pricing_suggestion_feedback');
    }
};
