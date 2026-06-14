<?php

namespace App\Modules\Notes\Jobs;

use App\Jobs\TenantAwareJob;
use App\Modules\Notes\Models\Note;
use App\Modules\Notes\Models\NoteEmbedding;
use App\Modules\Notes\Services\EmbeddingService;
use Illuminate\Support\Facades\DB;

class EmbedNoteJob extends TenantAwareJob
{
    public int $tries = 3;

    public int $backoff = 60;

    public $afterCommit = true;

    public function __construct(
        public readonly int $noteId,
    ) {
        parent::__construct();
    }

    protected function execute(): void
    {
        $note = Note::find($this->noteId);

        if ($note === null || empty($note->body)) {
            return;
        }

        $text = trim(($note->body_cleaned ?? '') ?: $note->body);

        $floats = app(EmbeddingService::class)->embed($text);

        if ($floats === null) {
            throw new \RuntimeException("Embedding returned null for note {$this->noteId}");
        }

        $pgVector = EmbeddingService::vectorToPostgres($floats);

        $existing = NoteEmbedding::where('note_id', $this->noteId)->first();

        if ($existing) {
            DB::statement(
                'UPDATE note_embeddings SET embedding = ?, model = ? WHERE note_id = ? AND tenant_id = ?',
                [$pgVector, EmbeddingService::MODEL, $this->noteId, $note->tenant_id],
            );
        } else {
            DB::statement(
                'INSERT INTO note_embeddings (tenant_id, note_id, model, embedding, created_at)
                 VALUES (?, ?, ?, ?, NOW())',
                [$note->tenant_id, $this->noteId, EmbeddingService::MODEL, $pgVector],
            );
        }
    }
}
