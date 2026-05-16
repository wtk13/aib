<?php

namespace App\Modules\Scheduling\Filament\Resources\JobResource\Pages;

use App\Modules\Scheduling\Filament\Resources\JobResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListJobs extends ListRecords
{
    protected static string $resource = JobResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
