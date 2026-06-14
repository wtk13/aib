<?php

namespace App\Modules\Quoting\Filament\Resources\QuoteResource\Pages;

use App\Modules\Quoting\Filament\Resources\QuoteResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListQuotes extends ListRecords
{
    protected static string $resource = QuoteResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
