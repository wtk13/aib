<?php

namespace App\Modules\ClientChat\Filament\Widgets;

use App\Modules\ClientChat\Models\ChatMessage;
use App\Modules\ClientChat\Models\ChatSession;
use App\Modules\ClientChat\Services\ClientChatService;
use App\Modules\Tenancy\Models\Tenant;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;
use Illuminate\Contracts\View\View;

class ClientChatWidget extends Widget
{
    protected static string $view = 'filament.widgets.client-chat-widget';

    protected static bool $isLazy = false;

    public int $clientId;

    public string $question = '';

    public ?int $sessionId = null;

    /** @var array<int, array{role: string, content: string, citations: array}> */
    public array $messages = [];

    public bool $loading = false;

    public function mount(int $clientId): void
    {
        $this->clientId = $clientId;
        $this->loadSession();
    }

    private function loadSession(): void
    {
        $tenantId = Tenant::currentId();

        if (! $tenantId) {
            return;
        }

        $session = ChatSession::where('client_id', $this->clientId)
            ->where('tenant_id', $tenantId)
            ->latest()
            ->first();

        if ($session) {
            $this->sessionId = $session->id;
            $this->messages = ChatMessage::where('session_id', $session->id)
                ->orderBy('created_at')
                ->get(['role', 'content', 'citations'])
                ->map(fn ($m) => [
                    'role'      => $m->role,
                    'content'   => $m->content,
                    'citations' => $m->citations ?? [],
                ])
                ->all();
        }
    }

    public function send(): void
    {
        $question = trim($this->question);

        if (empty($question)) {
            return;
        }

        $tenantId = Tenant::currentId();

        if (! $tenantId) {
            return;
        }

        $this->messages[] = ['role' => 'user', 'content' => $question, 'citations' => []];
        $this->question = '';
        $this->loading = true;

        try {
            /** @var ClientChatService $service */
            $service = app(ClientChatService::class);

            $session = $service->getOrCreateSession($this->clientId, $tenantId);
            $this->sessionId = $session->id;

            $response = $service->ask($session, $question);

            $this->messages[] = [
                'role'      => 'assistant',
                'content'   => $response->content,
                'citations' => $response->citations ?? [],
            ];
        } catch (\Throwable $e) {
            $this->messages[] = [
                'role'      => 'assistant',
                'content'   => __('chat.error.ai_unavailable'),
                'citations' => [],
            ];

            Notification::make()
                ->title(__('chat.error.ai_unavailable'))
                ->danger()
                ->send();
        } finally {
            $this->loading = false;
        }
    }

    public function clearChat(): void
    {
        $tenantId = Tenant::currentId();

        if ($this->sessionId && $tenantId) {
            ChatMessage::where('session_id', $this->sessionId)->delete();
            $this->messages = [];
        }
    }

    public function render(): View
    {
        return view(static::$view);
    }
}
