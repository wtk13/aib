<?php

namespace App\Modules\Notes\Jobs;

use App\Jobs\TenantAwareJob;
use App\Modules\Notes\Models\Note;
use App\Modules\Notes\Services\WhisperService;
use RuntimeException;

class TranscribeNoteJob extends TenantAwareJob
{
    public int $tries = 3;

    public int $backoff = 30;

    public $afterCommit = true;

    public function __construct(
        public readonly int $noteId,
    ) {
        parent::__construct();
    }

    protected function execute(): void
    {
        $note = Note::find($this->noteId);

        if ($note === null || empty($note->audio_path)) {
            return;
        }

        $transcript = app(WhisperService::class)->transcribe($note->audio_path);

        if ($transcript === null) {
            // Throw so the queue driver retries up to $tries times with $backoff delay.
            // The failed() hook marks the note only after all retries are exhausted.
            throw new RuntimeException("Whisper transcription returned null for note {$this->noteId}");
        }

        $note->update([
            'body' => $transcript,
            'status' => 'ready',
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        $note = Note::find($this->noteId);
        $note?->update(['status' => 'transcription_failed']);
    }
}
