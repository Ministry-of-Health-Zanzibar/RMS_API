<?php

namespace App\Http\Controllers\API\Accountants;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class AccountantReportController extends Controller
{
    public function reportPerMonthly()
    {
        $user = auth()->user();
        if (!$user->hasRole('ROLE ACCOUNTANT') || !$user->can('View Report')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }


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
        $user = auth()->user();
        if (!$user->hasRole('ROLE ACCOUNTANT') || !$user->can('View Report')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

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

        $user = auth()->user();
        if (!$user->hasRole('ROLE ACCOUNTANT') || !$user->can('View Report')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

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

        $user = auth()->user();
        if (!$user->hasRole('ROLE ACCOUNTANT') || !$user->can('View Report')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

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


    public function getDocumentFormsReport(Request $request)
    {
        $user = auth()->user();
        if (!$user->hasRole('ROLE ACCOUNTANT') || !$user->can('View Report')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }



        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $report = DB::table('document_forms')
            ->join('source_types', 'document_forms.source_type_id', '=', 'source_types.source_type_id')
            ->join('sources', 'source_types.source_id', '=', 'sources.source_id')
            ->join('categories', 'document_forms.category_id', '=', 'categories.category_id')
            ->join('document_types', 'document_forms.document_type_id', '=', 'document_types.document_type_id')
            ->select(
                'document_forms.document_form_id',
                'document_forms.document_form_code',
                'document_forms.payee_name',
                'document_forms.amount',
                'document_forms.tin_number',
                'document_forms.year',
                'document_forms.created_at',
                'sources.source_name',
                'source_types.source_type_name',
                'categories.category_name',
                'document_types.document_type_name',
            )
            ->whereBetween('document_forms.created_at', [$startDate, $endDate])
            ->orderBy('document_forms.created_at', 'desc')
            ->get();

        return response()->json([
            'data' => $report,
            'statusCode' => 200,
        ]);
    }


    public function searchReportByParameter(Request $request)
    {
        $user = auth()->user();
        if (!$user->hasRole('ROLE ACCOUNTANT') || !$user->can('View Report')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $query = DB::table('document_forms')
            ->join('source_types', 'document_forms.source_type_id', '=', 'source_types.source_type_id')
            ->join('sources', 'source_types.source_id', '=', 'sources.source_id')
            ->join('document_types', 'document_forms.document_type_id', '=', 'document_types.document_type_id')
            ->join('categories', 'document_forms.category_id', '=', 'categories.category_id')
            ->select(
                'document_forms.*',
                'sources.source_name',
                'source_types.source_type_name',
                'document_types.document_type_name',
                'categories.category_name'
            );

        // Apply filters only if they exist in the request
        if ($request->filled('document_form_code')) {
            $query->where('document_forms.document_form_code', 'ILIKE', '%' . $request->document_form_code . '%');
        }

        if ($request->filled('payee_name')) {
            $query->where('document_forms.payee_name', 'ILIKE', '%' . $request->payee_name . '%');
        }

        if ($request->filled('amount')) {
            $query->where('document_forms.amount', $request->amount);
        }

        if ($request->filled('tin_number')) {
            $query->where('document_forms.tin_number', 'ILIKE', '%' . $request->tin_number . '%');
        }

        if ($request->filled('year')) {
            $query->where('document_forms.year', $request->year);
        }

        if ($request->filled('source_name')) {
            $query->where('sources.source_name', 'ILIKE', '%' . $request->source_name . '%');
        }

        if ($request->filled('source_type_name')) {
            $query->where('source_types.source_type_name', 'ILIKE', '%' . $request->source_type_name . '%');
        }

        if ($request->filled('document_type_name')) {
            $query->where('document_types.document_type_name', 'ILIKE', '%' . $request->document_type_name . '%');
        }

        if ($request->filled('category_name')) {
            $query->where('categories.category_name', 'ILIKE', '%' . $request->category_name . '%');
        }

        $results = $query->get();

        return response()->json([
            'data' => $results,
            'statusCode' => 200
        ]);
    }


}