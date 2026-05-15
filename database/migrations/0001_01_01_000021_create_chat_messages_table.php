<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('chat_sessions')->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('role');
            $table->text('content');
            $table->jsonb('citations')->default('[]');
            $table->foreignId('ai_usage_log_id')->nullable()->constrained('ai_usage_logs')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();
            $table->index('tenant_id');
        });
    }

    public function down(): void { Schema::dropIfExists('chat_messages'); }
};
