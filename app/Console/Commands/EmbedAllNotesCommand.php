<?php

namespace App\Console\Commands;

use App\Modules\Notes\Jobs\EmbedNoteJob;
use App\Modules\Notes\Models\Note;
use App\Modules\Tenancy\Models\Tenant;
use Illuminate\Console\Command;

class EmbedAllNotesCommand extends Command
{
    protected $signature = 'notes:embed-all {--tenant= : Only embed notes for this tenant ID}';

    protected $description = 'Backfill embeddings for all notes that have a body';

    public function handle(): int
    {
        $tenantId = $this->option('tenant');

        $query = Note::withoutGlobalScopes()
            ->whereNotNull('body')
            ->whereNull('deleted_at');

        if ($tenantId) {
            $query->where('tenant_id', (int) $tenantId);
        }

        $total = $query->count();
        $this->info("Dispatching embedding jobs for {$total} notes…");

        try {
            $query->chunkById(100, function ($notes) {
                foreach ($notes as $note) {
                    $tenant = Tenant::withoutGlobalScopes()->find($note->tenant_id);
                    if ($tenant) {
                        Tenant::setCurrent($tenant);
                        EmbedNoteJob::dispatch($note->id);
                    }
                }
            });
        } finally {
            Tenant::clear();
        }

        $this->info('Done. Jobs dispatched to queue.');

        return self::SUCCESS;
    }
}
