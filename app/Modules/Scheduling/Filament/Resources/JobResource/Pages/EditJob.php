<?php

namespace App\Modules\Scheduling\Filament\Resources\JobResource\Pages;

use App\Modules\Scheduling\Filament\Resources\JobResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditJob extends EditRecord
{
    protected static string $resource = JobResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
