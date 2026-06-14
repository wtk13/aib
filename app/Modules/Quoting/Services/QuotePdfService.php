<?php

namespace App\Modules\Quoting\Services;

use App\Modules\Quoting\Models\Quote;
use App\Modules\Tenancy\Models\Tenant;
use Barryvdh\DomPDF\Facade\Pdf;

class QuotePdfService
{
    public function generate(Quote $quote): string
    {
        $quote->load(['client', 'items', 'tenant']);

        $tenant = $quote->tenant ?? Tenant::find($quote->tenant_id);
        $tenantName = $tenant?->name ?? 'TBA';

        $unitLabels = [
            'm2' => 'm²',
            'h' => 'godz.',
            'piece' => 'szt.',
            'flat' => 'ryczałt',
        ];

        $pdf = Pdf::loadView('pdf.quote', compact('quote', 'tenant', 'tenantName', 'unitLabels'))
            ->setPaper('a4', 'portrait');

        return $pdf->output();
    }

    public function download(Quote $quote): \Symfony\Component\HttpFoundation\Response
    {
        $content = $this->generate($quote);
        $filename = 'wycena-' . $quote->number . '.pdf';
        $filename = str_replace('/', '-', $filename);

        return response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
