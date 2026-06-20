<?php

namespace App\Modules\Quoting\Services;

use App\Modules\Quoting\Models\Quote;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\Response;

class QuotePdfService
{
    public function generate(Quote $quote): string
    {
        $quote->load(['client', 'items', 'tenant']);

        $tenant = $quote->tenant;
        $tenantName = $tenant?->name ?? 'TBA';

        $unitLabels = [
            'm2' => __('quote.unit.m2'),
            'h' => __('quote.unit.h'),
            'piece' => __('quote.unit.piece'),
            'flat' => __('quote.unit.flat'),
        ];

        $pdf = Pdf::loadView('pdf.quote', compact('quote', 'tenant', 'tenantName', 'unitLabels'))
            ->setPaper('a4', 'portrait');

        return $pdf->output();
    }

    public function download(Quote $quote): Response
    {
        $content = $this->generate($quote);
        $filename = __('quote.pdf_filename', ['number' => str_replace('/', '-', $quote->number)]);

        return response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }
}
