<?php

namespace App\Modules\Crm\Filament\Resources\ClientResource\Pages;

use App\Modules\Crm\Filament\Resources\ClientResource;
use App\Modules\Crm\Models\Address;
use Filament\Resources\Pages\CreateRecord;

class CreateClient extends CreateRecord
{
    protected static string $resource = ClientResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // addr_* fields are dehydrated(false) so they are stripped from $data by
        // Filament's dehydration pass. Read them from the raw Livewire state instead.
        $raw  = $this->form->getRawState();
        $addressData = $this->extractAddressData($raw);

        if ($addressData !== null) {
            $address            = Address::create($addressData);
            $data['address_id'] = $address->id;
        }

        return $data;
    }

    private function extractAddressData(array $raw): ?array
    {
        $line1    = $raw['addr_line1'] ?? '';
        $postcode = $raw['addr_postcode'] ?? '';
        $city     = $raw['addr_city'] ?? '';

        if (empty($line1)) {
            return null;
        }

        return ['line1' => $line1, 'postcode' => $postcode, 'city' => $city];
    }
}
