<?php

namespace App\Modules\Integrations\Geocoding;

use App\Jobs\TenantAwareJob;
use App\Modules\Crm\Models\Address;

class GeocodeAddressJob extends TenantAwareJob
{
    public function __construct(
        public readonly int $addressId,
    ) {
        parent::__construct();
    }

    protected function execute(): void
    {
        $address = Address::find($this->addressId);

        if ($address === null) {
            return;
        }

        (new GeocodingService())->geocode($address);
    }
}
