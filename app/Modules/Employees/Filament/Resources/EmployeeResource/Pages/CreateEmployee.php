<?php

namespace App\Modules\Employees\Filament\Resources\EmployeeResource\Pages;

use App\Modules\Employees\Filament\Resources\EmployeeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEmployee extends CreateRecord
{
    protected static string $resource = EmployeeResource::class;
}
