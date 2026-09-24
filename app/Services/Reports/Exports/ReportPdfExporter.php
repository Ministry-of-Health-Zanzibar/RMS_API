<?php

namespace App\Services\Reports\Exports;

use App\Services\Reports\OfficialReportBranding;
use Barryvdh\DomPDF\Facade\Pdf;

final class ReportPdfExporter
{
    public function __construct(private readonly OfficialReportBranding $branding)
    {
    }

    public function download(array $report, string $filename)
    {
        $branding = $this->branding->data();

        $columnCounts = [count($report['columns'])];
        foreach ($report['sections'] ?? [] as $section) {
            if (($section['kind'] ?? null) === 'table') {
                $columnCounts[] = count($section['columns'] ?? []);
            }
        }

        $orientation = max($columnCounts) > 7 ? 'landscape' : 'portrait';

        return Pdf::loadView('reports.official-report', [
            'report' => $report,
            'branding' => $branding,
        ])->setPaper('a4', $orientation)->download($filename);
    }
}
