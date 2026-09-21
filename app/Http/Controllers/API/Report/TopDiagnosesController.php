<?php

namespace App\Http\Controllers\API\Report;

use App\Http\Controllers\Controller;
use App\Services\Reports\TopDiagnosesExport;
use App\Services\Reports\TopDiagnosesReport;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TopDiagnosesController extends Controller
{
    public function __invoke(Request $request, TopDiagnosesReport $report, TopDiagnosesExport $export)
    {
        abort_unless($request->user()->can('View Report'), 403);
        $criteria = $request->validate([
            'start_date' => ['required', 'date_format:Y-m-d', 'before_or_equal:end_date'],
            'end_date' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'format' => ['sometimes', 'in:csv,pdf,docx'],
        ]);
        $start = Carbon::parse($criteria['start_date'])->startOfDay();
        // An exclusive next-day boundary includes the entire selected end date.
        $end = Carbon::parse($criteria['end_date'])->addDay()->startOfDay()->min(now());
        $data = $report->generate($start->toDateTimeString(), $end->toDateTimeString());
        $format = $criteria['format'] ?? null;
        if ($format === null) {
            return response()->json(['data' => $data, 'start_date' => $criteria['start_date'], 'end_date' => $criteria['end_date']]);
        }

        $rows = $export->rows($data);
        $period = $criteria['start_date'].' to '.$criteria['end_date'];
        $filename = 'top-diagnoses-'.$criteria['start_date'].'-to-'.$criteria['end_date'];
        if ($format === 'csv') {
            return response()->streamDownload(function () use ($rows, $export) {
                $stream = fopen('php://output', 'w');
                fwrite($stream, "\xEF\xBB\xBF");
                fputcsv($stream, TopDiagnosesExport::HEADERS, ',', '"', '');
                foreach ($rows as $row) {
                    fputcsv($stream, array_map([$export, 'csvCell'], $row), ',', '"', '');
                }
                fclose($stream);
            }, $filename.'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
        }
        if ($format === 'pdf') {
            return Pdf::loadView('reports.top-diagnoses', [
                'rows' => $rows, 'headers' => TopDiagnosesExport::HEADERS, 'period' => $period,
            ])->setPaper('a4', 'landscape')->download($filename.'.pdf');
        }

        return response($export->word($rows, $period), 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'Content-Disposition' => 'attachment; filename="'.$filename.'.docx"',
        ]);
    }
}
