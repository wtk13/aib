<?php

namespace App\Modules\ClientChat\Services;

use App\Modules\AI\Models\AIUsageLog;
use App\Modules\AI\Services\AnthropicClient;
use App\Modules\ClientChat\Models\ChatMessage;
use App\Modules\ClientChat\Models\ChatSession;
use App\Modules\Crm\Models\Client;
use App\Modules\Quoting\Models\Quote;
use App\Modules\Scheduling\Models\Job;
use Illuminate\Support\Facades\Auth;

class ClientChatService
{
    private const MODEL = 'claude-haiku-4-5-20251001';

    private const PROMPT_VERSION = 'chat_v1';

    public function __construct(
        private readonly AnthropicClient $claude,
        private readonly RagRetriever $rag,
    ) {}

    public function ask(ChatSession $session, string $question): ChatMessage
    {
        $client = Client::where('id', $session->client_id)
            ->where('tenant_id', $session->tenant_id)
            ->firstOrFail();
        $tenantId = $session->tenant_id;

        // Fetch prior history before storing the new user message (avoids sending it twice)
        $prior = ChatMessage::where('session_id', $session->id)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->reverse()
            ->map(fn ($m) => ['role' => $m->role, 'content' => $m->content])
            ->values()
            ->all();

        // Store user message
        ChatMessage::create([
            'session_id' => $session->id,
            'role' => 'user',
            'content' => $question,
            'citations' => [],
        ]);

        // Build context
        $notes = $this->rag->retrieve($session->client_id, $tenantId, $question);
        $context = $this->buildContext($client, $notes, $tenantId);

        // Final message list: prior history + current question
        $history = array_merge($prior, [['role' => 'user', 'content' => $question]]);

        $system = file_get_contents(base_path('app/Prompts/chat_v1.md'))."\n\n".$context;

        $result = $this->claude->messages(
            model: self::MODEL,
            system: $system,
            messages: $history,
            maxTokens: 512,
        );

        if ($result === null) {
            $content = __('chat.error.ai_unavailable');
            $citations = [];
            $logId = null;
        } else {
            $content = $result['content'];
            $citations = $this->extractCitations($content, $notes);

            $costUsd = ($result['input_tokens'] / 1_000_000 * 1.0) + ($result['output_tokens'] / 1_000_000 * 5.0);
            $costPln = $costUsd * config('services.anthropic.pln_usd_rate', 4.0);

            $log = AIUsageLog::create([
                'user_id' => Auth::id(),
                'feature' => 'client_chat',
                'provider' => 'anthropic',
                'model' => self::MODEL,
                'prompt_version' => self::PROMPT_VERSION,
                'input_tokens' => $result['input_tokens'],
                'output_tokens' => $result['output_tokens'],
                'cost_pln' => round($costPln, 4),
                'latency_ms' => $result['latency_ms'],
                'status' => 'ok',
            ]);

            $logId = $log->id;
        }

        return ChatMessage::create([
            'session_id' => $session->id,
            'role' => 'assistant',
            'content' => $content,
            'citations' => $citations,
            'ai_usage_log_id' => $logId,
        ]);
    }

    public function getOrCreateSession(int $clientId, int $tenantId): ChatSession
    {
        return ChatSession::firstOrCreate(
            ['client_id' => $clientId, 'tenant_id' => $tenantId, 'user_id' => Auth::id()],
            ['title' => null],
        );
    }

    /**
     * @param  array<array{id: int, body: string, created_at: string}>  $notes
     * @return array<array{note_id: int}>
     */
    private function extractCitations(string $content, array $notes): array
    {
        $noteIds = array_column($notes, 'id');
        $citations = [];

        preg_match_all('/\[notatka #(\d+)\]/i', $content, $matches);

        foreach (($matches[1] ?? []) as $id) {
            if (in_array((int) $id, $noteIds, true)) {
                $citations[] = ['note_id' => (int) $id];
            }
        }

        return array_values(array_unique($citations, SORT_REGULAR));
    }

    private function buildContext(Client $client, array $notes, int $tenantId): string
    {
        $parts = [];

        // Client info
        $parts[] = "## Client\nName: {$client->name}";

        // Notes wrapped in XML delimiters; body is XML-escaped and length-capped to prevent injection
        if (! empty($notes)) {
            $noteXml = array_map(function ($n) {
                $safeBody = htmlspecialchars(mb_substr((string) $n['body'], 0, 1000), ENT_XML1 | ENT_QUOTES, 'UTF-8');

                return "<note id=\"{$n['id']}\" date=\"{$n['created_at']}\">{$safeBody}</note>";
            }, $notes);
            $parts[] = '## Notatki (najnowsze/najbardziej trafne)'."\n".implode("\n", $noteXml);
        }

        // Recent jobs
        $jobs = Job::where('client_id', $client->id)
            ->where('tenant_id', $tenantId)
            ->latest('starts_at')
            ->limit(5)
            ->get(['starts_at', 'status', 'service_type_key']);

        if ($jobs->isNotEmpty()) {
            $jobLines = $jobs->map(fn ($j) => "- {$j->starts_at} | {$j->service_type_key} | {$j->status}")->join("\n");
            $parts[] = "## Ostatnie zlecenia\n{$jobLines}";
        }

        // Recent quotes
        $quotes = Quote::where('client_id', $client->id)
            ->where('tenant_id', $tenantId)
            ->latest('issued_at')
            ->limit(3)
            ->get(['number', 'issued_at', 'total', 'status']);

        if ($quotes->isNotEmpty()) {
            $quoteLines = $quotes->map(fn ($q) => "- {$q->number} | {$q->issued_at} | {$q->total} PLN | {$q->status}")->join("\n");
            $parts[] = "## Ostatnie wyceny\n{$quoteLines}";
        }

        return implode("\n\n", $parts);
    }
}
