<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->text('body')->nullable();
            $table->text('body_cleaned')->nullable();
            $table->string('audio_path')->nullable();
            $table->unsignedSmallInteger('audio_duration_seconds')->nullable();
            $table->string('status')->default('ready');
            $table->string('source')->default('text');
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
            $table->index(['tenant_id', 'client_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notes');
    }
};
