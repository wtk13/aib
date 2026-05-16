<?php

namespace App\Modules\Crm\Filament\Resources\ClientResource\Pages;

use App\Modules\Crm\Filament\Resources\ClientResource;
use App\Modules\Crm\Models\Address;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditClient extends EditRecord
{
    protected static string $resource = ClientResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
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

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // addr_* fields are dehydrated(false) — read from raw Livewire state.
        $raw      = $this->form->getRawState();
        $line1    = $raw['addr_line1'] ?? '';
        $postcode = $raw['addr_postcode'] ?? '';
        $city     = $raw['addr_city'] ?? '';

        $addressData = ['line1' => $line1, 'postcode' => $postcode, 'city' => $city];
        $client      = $this->getRecord();

        if ($client->address) {
            $client->address->update($addressData);
        } elseif (! empty($line1) || ! empty($city)) {
            $address            = Address::create($addressData);
            $data['address_id'] = $address->id;
        }

        return $data;
    }
}
