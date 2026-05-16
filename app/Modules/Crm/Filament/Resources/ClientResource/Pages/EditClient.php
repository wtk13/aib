<?php

namespace App\Modules\Crm\Filament\Resources\ClientResource\Pages;

use App\Modules\Crm\Filament\Resources\ClientResource;
use App\Modules\Crm\Models\Address;
use App\Modules\Crm\Models\Client;
use App\Modules\Integrations\Geocoding\GeocodeAddressJob;
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

    protected function afterSave(): void
    {
        /** @var Client $record */
        $record = $this->getRecord();
        $addressId = $record->address_id;

        if ($addressId !== null) {
            GeocodeAddressJob::dispatch($addressId);
        }
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // addr_* fields are dehydrated(false) — read from raw Livewire state.
        $raw = $this->form->getRawState();
        $line1 = $raw['addr_line1'] ?? '';
        $postcode = $raw['addr_postcode'] ?? '';
        $city = $raw['addr_city'] ?? '';

        $addressData = ['line1' => $line1, 'postcode' => $postcode, 'city' => $city];
        /** @var Client $client */
        $client = $this->getRecord();

        if ($client->address) {
            $client->address->update($addressData);
        } elseif (! empty($line1)) {
            $address = Address::create($addressData);
            $data['address_id'] = $address->id;
        }

        return $data;
    }
}
