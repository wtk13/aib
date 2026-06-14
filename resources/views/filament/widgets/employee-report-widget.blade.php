<x-filament-widgets::widget>
    <div class="space-y-6">
        <x-filament::section :heading="__('employee.report.daily_history')">
            @if($this->dailyHistory->isEmpty())
                <p class="text-sm text-gray-400">{{ __('employee.report.no_history') }}</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 text-left text-gray-500">
                                <th class="pb-2 pr-4 font-medium">{{ __('employee.report.date') }}</th>
                                <th class="pb-2 pr-4 font-medium">{{ __('employee.report.client') }}</th>
                                <th class="pb-2 pr-4 font-medium">{{ __('employee.report.service') }}</th>
                                <th class="pb-2 pr-4 text-right font-medium">{{ __('employee.report.hours') }}</th>
                                <th class="pb-2 text-right font-medium">{{ __('employee.report.payout') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($this->dailyHistory as $row)
                                <tr class="text-gray-700">
                                    <td class="py-2 pr-4 whitespace-nowrap">{{ \Carbon\Carbon::parse($row->starts_at)->format('d.m.Y') }}</td>
                                    <td class="py-2 pr-4">{{ $row->client_name }}</td>
                                    <td class="py-2 pr-4">{{ __('presets.cleaning.services.' . $row->service_type_key) }}</td>
                                    <td class="py-2 pr-4 text-right">{{ $row->hours_worked ? number_format((float) $row->hours_worked, 1, ',', '') : '—' }}</td>
                                    <td class="py-2 text-right font-medium whitespace-nowrap">PLN {{ number_format((float) $row->payout_pln, 2, ',', ' ') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-filament::section>

        <x-filament::section :heading="__('employee.report.monthly_history')">
            @if($this->monthlyHistory->isEmpty())
                <p class="text-sm text-gray-400">{{ __('employee.report.no_history') }}</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 text-left text-gray-500">
                                <th class="pb-2 pr-4 font-medium">{{ __('employee.report.month') }}</th>
                                <th class="pb-2 pr-4 text-right font-medium">{{ __('employee.report.job_count') }}</th>
                                <th class="pb-2 pr-4 text-right font-medium">{{ __('employee.report.total_hours') }}</th>
                                <th class="pb-2 text-right font-medium">{{ __('employee.report.total_payout') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($this->monthlyHistory as $row)
                                <tr class="text-gray-700">
                                    <td class="py-2 pr-4">{{ $row->month_label }}</td>
                                    <td class="py-2 pr-4 text-right">{{ $row->job_count }}</td>
                                    <td class="py-2 pr-4 text-right">{{ $row->total_hours ? number_format((float) $row->total_hours, 1, ',', '') : '—' }}</td>
                                    <td class="py-2 text-right font-medium whitespace-nowrap">PLN {{ number_format((float) $row->total_payout, 2, ',', ' ') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-filament::section>
    </div>
</x-filament-widgets::widget>
