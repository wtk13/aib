<?php

namespace App\Modules\ClientChat\Services;

use App\Modules\Notes\Models\Note;
use App\Modules\Notes\Services\EmbeddingService;
use Illuminate\Support\Facades\DB;

class RagRetriever
{
    private const TOP_K = 8;

    public function __construct(private readonly EmbeddingService $embeddings) {}

    /**
     * Return top-K notes for a client ordered by cosine similarity to the query.
     *
     * @return array<array{id: int, body: string, created_at: string}>
     */
    public function retrieve(int $clientId, int $tenantId, string $query): array
    {
        $floats = $this->embeddings->embed($query);

        if ($floats === null) {
            // Fall back to most-recent notes if embedding fails
            return $this->recentNotes($clientId, $tenantId);
        }

        $pgVector = EmbeddingService::vectorToPostgres($floats);

        $rows = DB::select(
            'SELECT n.id, n.body, n.created_at
             FROM note_embeddings ne
             JOIN notes n ON n.id = ne.note_id
             WHERE ne.tenant_id = ? AND n.client_id = ? AND n.deleted_at IS NULL
             ORDER BY ne.embedding <=> ?::vector
             LIMIT ?',
            [$tenantId, $clientId, $pgVector, self::TOP_K],
        );

        return array_map(fn ($r) => [
            'id' => $r->id,
            'body' => $r->body,
            'created_at' => $r->created_at,
        ], $rows);
    }

    /** @return array<array{id: int, body: string, created_at: string}> */
    private function recentNotes(int $clientId, int $tenantId): array
    {
        return Note::where('client_id', $clientId)
            ->where('tenant_id', $tenantId)
            ->whereNotNull('body')
            ->latest()
            ->limit(self::TOP_K)
            ->get(['id', 'body', 'created_at'])
            ->map(fn ($n) => ['id' => $n->id, 'body' => $n->body, 'created_at' => (string) $n->created_at])
            ->all();
    }
}
