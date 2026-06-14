<?php

namespace App\Modules\Employees\Filament\Resources\EmployeeResource\Pages;

use App\Modules\Employees\Filament\Resources\EmployeeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEmployee extends EditRecord
{
    protected static string $resource = EmployeeResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
