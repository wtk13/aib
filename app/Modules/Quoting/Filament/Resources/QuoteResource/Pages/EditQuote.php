<?php

namespace App\Modules\Quoting\Filament\Resources\QuoteResource\Pages;

use App\Modules\Quoting\Filament\Resources\QuoteResource;
use App\Modules\Quoting\Models\Quote;
use App\Modules\Quoting\Services\QuoteTotalsService;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditQuote extends EditRecord
{
    protected static string $resource = QuoteResource::class;

    protected function authorizeAccess(): void
    {
        parent::authorizeAccess();
        /** @var Quote $record */
        $record = $this->getRecord();
        abort_unless($record->isEditable(), 403);
    }

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }

    protected function afterSave(): void
    {
        /** @var Quote $record */
        $record = $this->getRecord();
        app(QuoteTotalsService::class)->recalculate($record);
    }
}
