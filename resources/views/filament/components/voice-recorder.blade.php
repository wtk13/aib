<div
    x-data="voiceRecorder({{ $clientId }}, '{{ route('note.audio.upload') }}', '{{ csrf_token() }}')"
    class="space-y-5 p-2"
>
    {{-- IDLE --}}
    <div x-show="state === 'idle'" class="flex flex-col items-center gap-4 py-4">
        <button
            type="button"
            @click="start()"
            class="flex h-20 w-20 items-center justify-center rounded-full bg-red-500 text-white shadow-lg transition hover:bg-red-600 active:scale-95"
        >
            <svg class="h-8 w-8" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 15a3 3 0 0 0 3-3V6a3 3 0 0 0-6 0v6a3 3 0 0 0 3 3Zm5-3a5 5 0 0 1-10 0H5a7 7 0 0 0 6 6.93V21h2v-2.07A7 7 0 0 0 19 12h-2Z"/>
            </svg>
        </button>
        <p class="text-sm text-gray-500">{{ __('note.recorder.tap_to_record') }}</p>
    </div>

    {{-- RECORDING --}}
    <div x-show="state === 'recording'" class="flex flex-col items-center gap-4 py-4">
        <div class="relative flex h-20 w-20 items-center justify-center">
            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-red-400 opacity-50"></span>
            <button
                type="button"
                @click="stop()"
                class="relative flex h-20 w-20 items-center justify-center rounded-full bg-red-600 text-white shadow-lg transition hover:bg-red-700 active:scale-95"
            >
                <svg class="h-8 w-8" fill="currentColor" viewBox="0 0 24 24">
                    <rect x="6" y="6" width="12" height="12" rx="2"/>
                </svg>
            </button>
        </div>
        <p class="font-mono text-xl font-semibold tabular-nums text-red-600" x-text="formatTime(elapsed)"></p>
        <p class="text-sm text-gray-500">{{ __('note.recorder.recording') }}</p>
        <p x-show="elapsed >= 540" class="text-xs text-amber-600">{{ __('note.recorder.max_warning') }}</p>
    </div>

    {{-- RECORDED --}}
    <div x-show="state === 'recorded'" class="space-y-4">
        <audio x-bind:src="audioUrl" controls class="w-full"></audio>
        <p class="text-center text-sm text-gray-500">
            {{ __('note.recorder.duration') }}:
            <span class="font-semibold" x-text="formatTime(elapsed)"></span>
        </p>
        <div class="flex gap-3">
            <button
                type="button"
                @click="reset()"
                class="flex-1 rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-700 transition hover:bg-gray-50"
            >
                {{ __('note.recorder.redo') }}
            </button>
            <button
                type="button"
                @click="upload()"
                class="flex-1 rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-primary-700"
            >
                {{ __('note.recorder.save') }}
            </button>
        </div>
    </div>

    {{-- UPLOADING --}}
    <div x-show="state === 'uploading'" class="flex flex-col items-center gap-3 py-6">
        <svg class="h-8 w-8 animate-spin text-primary-600" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
        </svg>
        <p class="text-sm text-gray-500">{{ __('note.recorder.uploading') }}</p>
    </div>

    {{-- DONE --}}
    <div x-show="state === 'done'" class="flex flex-col items-center gap-4 py-4">
        <div class="flex h-16 w-16 items-center justify-center rounded-full bg-green-100 text-green-600">
            <svg class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <p class="font-semibold text-gray-800">{{ __('note.recorder.saved') }}</p>
        <p class="text-center text-sm text-gray-500">{{ __('note.recorder.transcribing_soon') }}</p>
        <button
            type="button"
            @click="done()"
            class="rounded-lg bg-primary-600 px-6 py-2 text-sm font-semibold text-white transition hover:bg-primary-700"
        >
            {{ __('note.recorder.close') }}
        </button>
    </div>

    {{-- ERROR --}}
    <div x-show="state === 'error'" class="space-y-3 rounded-lg border border-red-200 bg-red-50 p-4">
        <p class="font-semibold text-red-700">{{ __('note.recorder.error_title') }}</p>
        <p class="text-sm text-red-600" x-text="errorMsg"></p>
        <button
            type="button"
            @click="reset()"
            class="rounded-lg border border-red-300 px-4 py-2 text-sm text-red-700 hover:bg-red-100"
        >
            {{ __('note.recorder.try_again') }}
        </button>
    </div>
</div>

<script>
function voiceRecorder(clientId, uploadUrl, csrfToken) {
    return {
        state: 'idle',
        mediaRecorder: null,
        stream: null,
        chunks: [],
        mimeType: '',
        audioBlob: null,
        audioUrl: null,
        elapsed: 0,
        timer: null,
        maxSeconds: 600,
        errorMsg: '',

        async start() {
            let stream = null;
            try {
                stream = await navigator.mediaDevices.getUserMedia({ audio: true, video: false });
                this.stream = stream;

                this.mimeType = ['audio/webm;codecs=opus', 'audio/webm', 'audio/mp4', 'audio/ogg;codecs=opus', '']
                    .find(t => t === '' || MediaRecorder.isTypeSupported(t));

                const options = this.mimeType ? { mimeType: this.mimeType } : {};
                this.mediaRecorder = new MediaRecorder(stream, options);
                this.chunks = [];
                this.elapsed = 0;

                this.mediaRecorder.ondataavailable = e => {
                    if (e.data && e.data.size > 0) this.chunks.push(e.data);
                };

                this.mediaRecorder.onstop = () => {
                    this.stream?.getTracks().forEach(t => t.stop());
                    this.stream = null;
                    const type = this.mimeType || 'audio/webm';
                    this.audioBlob = new Blob(this.chunks, { type });
                    this.audioUrl = URL.createObjectURL(this.audioBlob);
                    this.state = 'recorded';
                };

                this.mediaRecorder.start(500);
                this.state = 'recording';

                this.timer = setInterval(() => {
                    this.elapsed++;
                    if (this.elapsed >= this.maxSeconds) this.stop();
                }, 1000);
            } catch (e) {
                stream?.getTracks().forEach(t => t.stop());
                this.stream = null;
                this.errorMsg = e.name === 'NotAllowedError'
                    ? <?= json_encode(__('note.recorder.error_permission')) ?>
                    : <?= json_encode(__('note.recorder.error_generic')) ?> + ' ' + e.message;
                this.state = 'error';
            }
        },

        stop() {
            clearInterval(this.timer);
            this.timer = null;
            if (this.mediaRecorder && this.mediaRecorder.state !== 'inactive') {
                this.mediaRecorder.stop();
            }
        },

        async upload() {
            this.state = 'uploading';

            const ext = (this.audioBlob.type.includes('mp4') ? 'mp4'
                : this.audioBlob.type.includes('ogg') ? 'ogg' : 'webm');

            const form = new FormData();
            form.append('audio', this.audioBlob, `recording.${ext}`);
            form.append('client_id', clientId);
            form.append('duration', this.elapsed);
            form.append('_token', csrfToken);

            try {
                const res = await fetch(uploadUrl, { method: 'POST', body: form });
                if (!res.ok) {
                    const body = await res.text();
                    throw new Error(`HTTP ${res.status}: ${body.slice(0, 120)}`);
                }
                this.state = 'done';
            } catch (e) {
                this.errorMsg = <?= json_encode(__('note.recorder.error_upload')) ?> + ' ' + e.message;
                this.state = 'error';
            }
        },

        done() {
            window.location.reload();
        },

        reset() {
            clearInterval(this.timer);
            this.timer = null;
            this.stream?.getTracks().forEach(t => t.stop());
            this.stream = null;
            if (this.audioUrl) URL.revokeObjectURL(this.audioUrl);
            this.audioBlob = null;
            this.audioUrl = null;
            this.elapsed = 0;
            this.chunks = [];
            this.state = 'idle';
        },

        formatTime(s) {
            return `${Math.floor(s / 60)}:${String(s % 60).padStart(2, '0')}`;
        },
    };
}
</script>
