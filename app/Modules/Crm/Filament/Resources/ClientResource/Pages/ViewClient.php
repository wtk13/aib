<?php

namespace App\Modules\Crm\Filament\Resources\ClientResource\Pages;

use App\Modules\Crm\Filament\Resources\ClientResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewClient extends ViewRecord
{
    protected static string $resource = ClientResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\EditAction::make()];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $address = $this->getRecord()->address;

        if ($address) {
            $data['addr_line1']    = $address->line1;
            $data['addr_postcode'] = $address->postcode;
            $data['addr_city']     = $address->city;
        }

        return $data;
    }
}
