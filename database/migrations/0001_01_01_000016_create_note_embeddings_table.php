<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('note_embeddings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('note_id')->constrained()->cascadeOnDelete();
            $table->string('model', 64)->default('text-embedding-3-small');
            $table->timestamp('created_at')->useCurrent();
            $table->unique('note_id');
            $table->index('tenant_id');
        });

        DB::statement('ALTER TABLE note_embeddings ADD COLUMN embedding vector(1536) NOT NULL');
        DB::statement('CREATE INDEX note_embeddings_ivfflat ON note_embeddings USING ivfflat (embedding vector_cosine_ops) WITH (lists = 100)');
    }

    public function down(): void
    {
        Schema::dropIfExists('note_embeddings');
    }
};
