<?php

namespace App\Http\Controllers\API\Accountants;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class AccountantReportController extends Controller
{
    public function reportPerMonthly()
    {
        $monthlyData = DB::table('document_forms')
            ->selectRaw("TO_CHAR(created_at, 'YYYY-MM') AS month, SUM(amount) AS total_amount")
            ->whereNotNull('created_at')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return response([
            'monthlyData' => $monthlyData,
        ]);

    }

    public function reportPerWeekly()
    {
        $weeklyData = DB::table('document_forms')
            ->selectRaw("TO_CHAR(created_at, 'IYYY-IW') AS week, SUM(amount) AS total_amount")
            ->whereNotNull('created_at')
            ->groupBy('week')
            ->orderBy('week')
            ->get();

        return response([
            'weeklyData' => $weeklyData,
        ]);

    }


    public function reportPerDocumentType()
    {
        $documentTypeSummary = DB::table('document_forms')
            ->join('document_types', 'document_forms.document_type_id', '=', 'document_types.document_type_id')
            ->select('document_types.document_type_name', DB::raw('COUNT(document_forms.document_form_id) as total'))
            ->groupBy('document_types.document_type_name')
            ->orderByDesc('total')
            ->get();


        return response([
            'documentTypeSummary' => $documentTypeSummary,
        ]);

    }


    public function reportBySourceType()
    {
        $sourceSummary = DB::table('document_forms')
            ->join('source_types', 'document_forms.source_type_id', '=', 'source_types.source_type_id')
            ->join('sources', 'source_types.source_id', '=', 'sources.source_id')
            ->select('sources.source_name', DB::raw('COUNT(document_forms.document_form_id) as total'))
            ->groupBy('sources.source_name')
            ->orderByDesc('total')
            ->get();




        return response([
            'sourceSummary' => $sourceSummary,
        ]);

    }

}