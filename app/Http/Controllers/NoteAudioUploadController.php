<?php

namespace App\Http\Controllers;

use App\Modules\Crm\Models\Client;
use App\Modules\Notes\Jobs\TranscribeNoteJob;
use App\Modules\Notes\Models\Note;
use App\Modules\Tenancy\Models\Tenant;
use App\Modules\Tenancy\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;

class NoteAudioUploadController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'audio' => ['required', 'file', 'max:51200', 'mimes:webm,mp4,ogg,mpeg,wav,x-m4a'],
            'client_id' => ['required', 'integer'],
            'duration' => ['nullable', 'integer', 'min:1', 'max:3600'],
        ]);

        /** @var User $user */
        $user = Auth::user();
        $tenant = Tenant::bypass(fn () => Tenant::find($user->tenant_id));
        abort_if($tenant === null, 403);
        Tenant::setCurrent($tenant);

        $client = Client::findOrFail($request->integer('client_id'));

        /** @var UploadedFile $file */
        $file = $request->file('audio');
        $path = $file->store("notes/audio/{$tenant->id}", 'local');
        abort_if($path === false, 500, 'Audio storage failed.');

        $note = Note::create([
            'client_id' => $client->id,
            'audio_path' => $path,
            'audio_duration_seconds' => $request->integer('duration') ?: null,
            'source' => 'audio',
            'status' => 'transcribing',
            'created_by_user_id' => $user->id,
        ]);

        TranscribeNoteJob::dispatch($note->id);

        return response()->json(['ok' => true]);
    }
}
