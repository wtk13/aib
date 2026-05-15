<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('note_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('note_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('mime', 127);
            $table->unsignedInteger('bytes');
            $table->timestamp('created_at')->useCurrent();
            $table->index('tenant_id');
        });
    }

    public function down(): void { Schema::dropIfExists('note_attachments'); }
};
