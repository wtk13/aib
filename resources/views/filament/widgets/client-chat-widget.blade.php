<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            {{ __('chat.heading') }}
        </x-slot>

        <x-slot name="headerEnd">
            @if(!empty($messages))
                <x-filament::button
                    wire:click="clearChat"
                    color="gray"
                    size="sm"
                >
                    {{ __('chat.clear') }}
                </x-filament::button>
            @endif
        </x-slot>

        {{-- Message list --}}
        <div
            class="flex flex-col gap-3 max-h-96 overflow-y-auto pr-1 mb-4"
            x-ref="messageList"
            x-init="$watch('$wire.messages', () => $nextTick(() => { $el.scrollTop = $el.scrollHeight }))"
        >
            @forelse($messages as $message)
                <div @class([
                    'flex',
                    'justify-end' => $message['role'] === 'user',
                    'justify-start' => $message['role'] === 'assistant',
                ])>
                    <div @class([
                        'max-w-[85%] rounded-lg px-3 py-2 text-sm',
                        'bg-primary-600 text-white' => $message['role'] === 'user',
                        'bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-gray-100' => $message['role'] === 'assistant',
                    ])>
                        <div class="whitespace-pre-wrap">{{ $message['content'] }}</div>

                        @if(!empty($message['citations']))
                            <div class="mt-1 flex flex-wrap gap-1">
                                @foreach($message['citations'] as $citation)
                                    @if(!empty($citation['note_id']))
                                        <span class="text-xs opacity-70 underline">
                                            {{ __('chat.citation', ['id' => $citation['note_id']]) }}
                                        </span>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-400 dark:text-gray-500 text-center py-6">
                    {{ __('chat.empty_state') }}
                </p>
            @endforelse

            @if($loading)
                <div class="flex justify-start">
                    <div class="bg-gray-100 dark:bg-gray-800 rounded-lg px-3 py-2 text-sm text-gray-500">
                        {{ __('chat.thinking') }}
                    </div>
                </div>
            @endif
        </div>

        {{-- Input --}}
        <div class="flex gap-2">
            <input
                type="text"
                wire:model.defer="question"
                wire:keydown.enter="send"
                placeholder="{{ __('chat.placeholder') }}"
                class="flex-1 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 px-3 py-2 text-sm text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-primary-500"
                @if($loading) disabled @endif
            />
            <x-filament::button
                wire:click="send"
                wire:loading.attr="disabled"
                size="sm"
            >
                {{ __('chat.send') }}
            </x-filament::button>
        </div>

        <p class="mt-2 text-xs text-gray-400 dark:text-gray-500">
            {{ __('chat.disclaimer') }}
        </p>
    </x-filament::section>
</x-filament-widgets::widget>
