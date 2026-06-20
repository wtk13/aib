<?php

namespace App\Modules\Crm\Filament\Resources\ClientResource\Pages;

use App\Modules\ClientChat\Filament\Widgets\ClientChatWidget;
use App\Modules\Crm\Filament\Resources\ClientResource;
use App\Modules\Crm\Models\Client;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewClient extends ViewRecord
{
    protected static string $resource = ClientResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\EditAction::make()];
    }

    protected function getFooterWidgets(): array
    {
        return [ClientChatWidget::class];
    }

    public function getFooterWidgetsColumns(): int|string|array
    {
        return 1;
    }

    public function getWidgetData(): array
    {
        /** @var Client $record */
        $record = $this->getRecord();

        return ['clientId' => $record->id];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var Client $record */
        $record = $this->getRecord();
        $address = $record->address;

        if ($address) {
            $data['addr_line1'] = $address->line1;
            $data['addr_postcode'] = $address->postcode;
            $data['addr_city'] = $address->city;
        }

        return $data;
    }
}
