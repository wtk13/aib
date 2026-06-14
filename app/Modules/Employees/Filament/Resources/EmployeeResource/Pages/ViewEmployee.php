<?php

namespace App\Modules\Employees\Filament\Resources\EmployeeResource\Pages;

use App\Modules\Employees\Filament\Resources\EmployeeResource;
use App\Modules\Employees\Filament\Resources\EmployeeResource\Widgets\EmployeeEarningsStatsWidget;
use App\Modules\Employees\Filament\Resources\EmployeeResource\Widgets\EmployeeReportWidget;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewEmployee extends ViewRecord
{
    protected static string $resource = EmployeeResource::class;

    protected function getHeaderActions(): array
    {
        return [EditAction::make()];
    }

    protected function getHeaderWidgets(): array
    {
        return [EmployeeEarningsStatsWidget::class];
    }

    protected function getFooterWidgets(): array
    {
        return [EmployeeReportWidget::class];
    }

    public function getWidgetData(): array
    {
        return ['record' => $this->getRecord()];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Section::make()->schema([
                TextEntry::make('name')
                    ->label(__('employee.fields.name')),
                IconEntry::make('is_active')
                    ->label(__('employee.fields.is_active'))
                    ->boolean(),
            ])->columns(2),
        ]);
    }
}
