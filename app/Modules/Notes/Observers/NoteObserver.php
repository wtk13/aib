<?php

namespace App\Modules\Notes\Observers;

use App\Modules\Notes\Jobs\EmbedNoteJob;
use App\Modules\Notes\Models\Note;
use App\Modules\Tenancy\Models\Tenant;

class NoteObserver
{
    public function saved(Note $note): void
    {
        if (empty($note->body)) {
            return;
        }

        // Only dispatch when there is a tenant context (not during seeding/tests without tenant)
        if (Tenant::currentId() === null) {
            return;
        }

        // Skip silently if no API key is configured (e.g., test environment)
        if (empty(config('services.openai.key'))) {
            return;
        }

        EmbedNoteJob::dispatch($note->id);
    }
}
