<?php

namespace App\Modules\Crm\Filament\Resources\ClientResource\RelationManagers;

use App\Modules\Notes\Jobs\TranscribeNoteJob;
use App\Modules\Notes\Models\Note;
use App\Modules\Tenancy\Models\Tenant;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class NoteRelationManager extends RelationManager
{
    protected static string $relationship = 'notes';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('note.relation_title');
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Textarea::make('body')
                ->label(__('note.fields.body'))
                ->rows(3)
                ->maxLength(5000),
            FileUpload::make('audio_path')
                ->label(__('note.fields.audio'))
                ->disk('local')
                ->directory('notes/audio/'.Tenant::currentId())
                ->acceptedFileTypes(['audio/mpeg', 'audio/mp4', 'audio/ogg', 'audio/webm', 'audio/wav', 'audio/x-m4a'])
                ->maxSize(50 * 1024)
                ->dehydrated(true)
                ->visible(fn ($record) => $record === null),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('body')
            ->modifyQueryUsing(fn (Builder $query) => $query->orderBy('created_at', 'desc'))
            ->columns([
                TextColumn::make('status_badge')
                    ->label('')
                    ->state(fn (Note $record): string => match ($record->status) {
                        'transcribing' => __('note.status.transcribing'),
                        'transcription_failed' => __('note.status.transcription_failed'),
                        'ready' => $record->audio_path ? __('note.status.ready') : '',
                        default => '',
                    })
                    ->width('120px'),
                TextColumn::make('body')
                    ->label(__('note.fields.body'))
                    ->wrap()
                    ->html()
                    ->state(fn (Note $record): string => implode('', array_filter([
                        $record->body ? '<p>'.e(mb_strimwidth($record->body, 0, 200, '…')).'</p>' : null,
                        $record->audio_path
                            ? view('filament.components.audio-player', ['noteId' => $record->id])->render()
                            : null,
                    ]))),
                TextColumn::make('createdByUser.name')
                    ->label(__('note.fields.author'))
                    ->default('—'),
                TextColumn::make('created_at')
                    ->label(__('note.fields.created_at'))
                    ->since()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->searchable()
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['created_by_user_id'] = Auth::id();

                        if (! empty($data['audio_path'])) {
                            $data['source'] = 'audio';
                            $data['status'] = 'transcribing';
                        } else {
                            $data['source'] = 'text';
                            $data['status'] = 'ready';
                        }

                        return $data;
                    })
                    ->after(function (Note $record): void {
                        if (! empty($record->audio_path)) {
                            TranscribeNoteJob::dispatch($record->id);
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->form([
                        Textarea::make('body')
                            ->label(__('note.fields.body'))
                            ->rows(3)
                            ->maxLength(5000),
                    ]),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([]);
    }
}
