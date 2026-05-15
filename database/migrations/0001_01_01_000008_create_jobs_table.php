<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('service_type_key');
            $table->jsonb('custom_fields')->default('{}');
            $table->string('recurrence_rule', 256)->nullable();
            $table->timestampTz('starts_at');
            $table->unsignedSmallInteger('duration_minutes')->default(60);
            $table->string('assigned_to', 128)->nullable();
            $table->string('status')->default('planned');
            $table->text('internal_notes')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->index(['tenant_id', 'starts_at']);
            $table->index(['tenant_id', 'client_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jobs');
    }
};
