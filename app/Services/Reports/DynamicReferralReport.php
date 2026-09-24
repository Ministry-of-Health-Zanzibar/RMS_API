<?php

namespace App\Services\Reports;

use App\Models\User;
use App\Support\Pagination;
use App\Support\ReportDataScope;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Builds the normalized referral report sections used by the web preview and
 * every exporter. The query is based on one filtered referral row per record;
 * diagnosis joins are introduced only inside the diagnosis/detail sections so
 * they cannot inflate referral or patient totals.
 */
final class DynamicReferralReport
{
    public function __construct(private readonly ReportDataScope $scope)
    {
    }

    public function generate(array $filters, User $user, bool $paginate): array
    {
        $base = $this->referralBaseQuery($filters, $user);
        $overview = $this->overview($base);
        $limit = $filters['result_limit'] ?? 'all';

        $destination = $this->destinationDistribution($base, $overview['total_referrals'], $limit);
        $monthlyTrend = $this->trendDistribution($base, 'month', $overview['total_referrals'], null);
        $gender = $this->genderDistribution($base, $overview['unique_patients']);
        $diagnosis = $this->diagnosisDistribution($base, $filters, $limit);
        $status = $this->statusDistribution($base, $overview['total_referrals']);

        $main = $this->mainTable(
            $filters['group_by'] ?? 'destination',
            $base,
            $filters,
            $overview,
            $destination,
            $monthlyTrend,
            $gender,
            $diagnosis,
            $status,
            $limit,
        );

        $qualityNotes = $this->qualityNotes($overview, $filters);
        $findings = $this->findings($destination['rows'], $monthlyTrend['rows'], $gender['rows'], $diagnosis['rows'], $status['rows']);
        $detail = $filters['detail_level'] ?? 'breakdown';

        $sections = [
            [
                'key' => 'executive_summary',
                'title' => 'Executive Summary',
                'kind' => 'text',
                'text' => $this->executiveSummary($overview, $filters),
            ],
            [
                'key' => 'overview',
                'title' => 'Report Overview',
                'kind' => 'metrics',
                'metrics' => $this->overviewMetrics($overview),
            ],
        ];

        if ($detail !== 'summary') {
            $groupBy = $filters['group_by'] ?? 'destination';
            $sections[] = $this->tableSection(
                'selected_analysis',
                $this->analysisTitle($groupBy),
                $main['columns'],
                $main['rows'],
                $main['pagination'],
            );

            // Keep the Ministry-style companion sections, but do not duplicate
            // the section already selected as the primary analysis.
            if ($groupBy !== 'destination') {
                $sections[] = $this->tableSection('destination_distribution', 'Referral Destination Distribution', $destination['columns'], $destination['rows']);
            }
            if ($groupBy !== 'month') {
                $sections[] = $this->tableSection('monthly_trend', 'Monthly Referral Trend', $monthlyTrend['columns'], $monthlyTrend['rows']);
            }
            if ($groupBy !== 'gender') {
                $sections[] = $this->tableSection('gender_distribution', 'Gender Distribution', $gender['columns'], $gender['rows']);
            }
            if ($groupBy !== 'diagnosis') {
                $sections[] = $this->tableSection('diagnosis_distribution', 'Diagnosis Distribution', $diagnosis['columns'], $diagnosis['rows']);
            }
            if ($groupBy !== 'status') {
                $sections[] = $this->tableSection('referral_status', 'Referral Status Distribution', $status['columns'], $status['rows']);
            }

            if ($findings !== []) {
                $sections[] = [
                    'key' => 'key_findings',
                    'title' => 'Key Findings',
                    'kind' => 'list',
                    'items' => $findings,
                ];
            }

            if ($qualityNotes !== []) {
                $sections[] = [
                    'key' => 'data_quality',
                    'title' => 'Data Quality and Interpretation Notes',
                    'kind' => 'list',
                    'items' => $qualityNotes,
                ];
            }
        }

        if ($detail === 'details') {
            $patientDetails = $this->patientDetails($base, $filters, $paginate);
            $sections[] = $this->tableSection(
                'patient_details',
                'Patient Details',
                $patientDetails['columns'],
                $patientDetails['rows'],
                $patientDetails['pagination'],
            );
        }

        return [
            'summary' => [
                'total_referrals' => $overview['total_referrals'],
                'unique_patients' => $overview['unique_patients'],
                'receiving_institutions' => $overview['receiving_institutions'],
                'records_with_diagnosis' => $overview['records_with_diagnosis'],
            ],
            'columns' => $main['columns'],
            'rows' => $main['rows'],
            'sections' => $sections,
            'findings' => $findings,
            'data_quality_notes' => $qualityNotes,
            'pagination' => $main['pagination'],
            'notes' => array_merge([
                'All sections use the same filtered referral-record population.',
                'Referral totals count referral records; patient totals count distinct patients.',
                'Diagnosis occurrences count diagnosis-referral associations and are not treated as patient counts.',
            ], $qualityNotes),
        ];
    }

    private function referralBaseQuery(array $filters, User $user): Builder
    {
        $query = DB::table('referrals as r')
            ->join('patients as p', 'p.patient_id', '=', 'r.patient_id')
            ->leftJoin('hospitals as h', function ($join): void {
                $join->on('h.hospital_id', '=', 'r.hospital_id')
                    ->whereNull('h.deleted_at');
            })
            ->whereNull('r.deleted_at')
            ->whereNull('p.deleted_at')
            ->where('r.created_at', '>=', $this->startAt($filters))
            ->where('r.created_at', '<', $this->endExclusive($filters));

        $this->scope->applyPatientScope($query, $user, 'p');

        $destinationIds = $this->effectiveHospitalIds($filters, $user);
        if ($destinationIds !== null) {
            if ($destinationIds === []) {
                $query->whereRaw('1 = 0');
            } else {
                $query->whereIn('r.hospital_id', $destinationIds);
            }
        }

        $sourceIds = $this->effectiveSourceHospitalIds($filters, $user);
        if ($sourceIds !== null) {
            if ($sourceIds === []) {
                $query->whereRaw('1 = 0');
            } else {
                $this->applyCreatedByHospitalConstraint($query, $sourceIds, 'r.created_by');
            }
        }

        if (($filters['referral_status'] ?? null) !== null) {
            $query->where('r.status', $filters['referral_status']);
        }

        if (($filters['referral_type_id'] ?? null) !== null) {
            $query->where('h.referral_type_id', $filters['referral_type_id']);
        }

        $this->applyPatientFilters($query, $filters, 'p');

        if (($filters['patient_search'] ?? null) !== null) {
            $term = mb_strtolower($filters['patient_search']);
            $query->where(function (Builder $search) use ($term): void {
                $search->whereRaw('LOWER(p.name) LIKE ?', [$term.'%'])
                    ->orWhereRaw('LOWER(p.phone) LIKE ?', [$term.'%'])
                    ->orWhereRaw('LOWER(p.matibabu_card) LIKE ?', [$term.'%'])
                    ->orWhereRaw('LOWER(p.zan_id) LIKE ?', [$term.'%']);
            });
        }

        if (($filters['diagnosis_id'] ?? null) !== null) {
            $query->whereExists(function (Builder $exists) use ($filters): void {
                $exists->selectRaw('1')
                    ->from('diagnosis_referral as diagnosis_filter')
                    ->whereColumn('diagnosis_filter.referral_id', 'r.referral_id')
                    ->where('diagnosis_filter.diagnosis_id', $filters['diagnosis_id']);
            });
        }

        return $query->select([
            'r.referral_id',
            'r.patient_id',
            'r.hospital_id as destination_hospital_id',
            'h.hospital_name as destination_hospital',
            'r.status as referral_status',
            'r.created_at as referred_date',
            'p.name as patient_name',
            'p.gender',
            'p.date_of_birth',
            'p.location_id',
        ]);
    }

    private function overview(Builder $base): array
    {
        $diagnosed = DB::table('diagnosis_referral as diagnosed_referral')
            ->select('diagnosed_referral.referral_id')
            ->distinct();

        $query = DB::query()
            ->fromSub($this->subquery($base), 'rr')
            ->leftJoinSub($diagnosed, 'diagnosed', 'diagnosed.referral_id', '=', 'rr.referral_id')
            ->selectRaw('COUNT(*) AS total_referrals')
            ->selectRaw('COUNT(DISTINCT rr.patient_id) AS unique_patients')
            ->selectRaw('COUNT(DISTINCT rr.destination_hospital_id) AS receiving_institutions')
            ->selectRaw('COUNT(DISTINCT diagnosed.referral_id) AS records_with_diagnosis')
            ->selectRaw('COUNT(*) - COUNT(DISTINCT diagnosed.referral_id) AS records_without_diagnosis')
            ->selectRaw('MIN(rr.referred_date) AS first_referral_date')
            ->selectRaw('MAX(rr.referred_date) AS latest_referral_date')
            ->selectRaw("COUNT(CASE WHEN rr.destination_hospital_id IS NULL THEN 1 END) AS missing_destination")
            ->selectRaw("COUNT(DISTINCT CASE WHEN rr.gender IS NULL OR TRIM(rr.gender) = '' THEN rr.patient_id END) AS missing_gender_patients")
            ->selectRaw("COUNT(CASE WHEN rr.referral_status IS NULL OR TRIM(rr.referral_status) = '' THEN 1 END) AS missing_status")
            ->first();

        return [
            'total_referrals' => (int) ($query->total_referrals ?? 0),
            'unique_patients' => (int) ($query->unique_patients ?? 0),
            'receiving_institutions' => (int) ($query->receiving_institutions ?? 0),
            'records_with_diagnosis' => (int) ($query->records_with_diagnosis ?? 0),
            'records_without_diagnosis' => (int) ($query->records_without_diagnosis ?? 0),
            'first_referral_date' => $this->formatDate($query->first_referral_date ?? null),
            'latest_referral_date' => $this->formatDate($query->latest_referral_date ?? null),
            'missing_destination' => (int) ($query->missing_destination ?? 0),
            'missing_gender_patients' => (int) ($query->missing_gender_patients ?? 0),
            'missing_status' => (int) ($query->missing_status ?? 0),
        ];
    }

    private function destinationDistribution(Builder $base, int $total, mixed $limit): array
    {
        $label = "COALESCE(rr.destination_hospital, 'Not recorded')";
        $query = DB::query()
            ->fromSub($this->subquery($base), 'rr')
            ->selectRaw($label.' AS destination_hospital')
            ->selectRaw('COUNT(*) AS referrals')
            ->selectRaw('COUNT(DISTINCT rr.patient_id) AS patients')
            ->groupByRaw($label)
            ->orderByDesc('referrals')
            ->orderBy('destination_hospital');

        $this->applyLimit($query, $limit);

        $rows = $query->get()->map(fn ($row): array => [
            'destination_hospital' => $row->destination_hospital,
            'referrals' => (int) $row->referrals,
            'patients' => (int) $row->patients,
            'percentage' => $this->percentage((int) $row->referrals, $total),
        ])->all();

        return [
            'columns' => [
                ['key' => 'destination_hospital', 'label' => 'Receiving hospital', 'type' => 'text'],
                ['key' => 'referrals', 'label' => 'Referrals', 'type' => 'integer'],
                ['key' => 'patients', 'label' => 'Unique patients', 'type' => 'integer'],
                ['key' => 'percentage', 'label' => 'Share of total', 'type' => 'percentage'],
            ],
            'rows' => $rows,
        ];
    }

    private function trendDistribution(Builder $base, string $groupBy, int $total, mixed $limit): array
    {
        $expression = $this->trendExpression($groupBy);
        $query = DB::query()
            ->fromSub($this->subquery($base), 'rr')
            ->selectRaw($expression.' AS period_key')
            ->selectRaw('COUNT(*) AS referrals')
            ->selectRaw('COUNT(DISTINCT rr.patient_id) AS patients')
            ->groupByRaw($expression)
            ->orderBy('period_key');

        if ($limit !== 'all' && is_int($limit)) {
            $query->limit($limit);
        }

        $rows = $query->get()->map(fn ($row): array => [
            'period' => $this->periodKeyLabel((string) $row->period_key, $groupBy),
            'referrals' => (int) $row->referrals,
            'patients' => (int) $row->patients,
            'percentage' => $this->percentage((int) $row->referrals, $total),
        ])->all();

        return [
            'columns' => [
                ['key' => 'period', 'label' => ucfirst($groupBy), 'type' => 'text'],
                ['key' => 'referrals', 'label' => 'Referrals', 'type' => 'integer'],
                ['key' => 'patients', 'label' => 'Unique patients', 'type' => 'integer'],
                ['key' => 'percentage', 'label' => 'Share of total', 'type' => 'percentage'],
            ],
            'rows' => $rows,
        ];
    }

    private function genderDistribution(Builder $base, int $totalPatients): array
    {
        $expression = "CASE WHEN LOWER(TRIM(COALESCE(rr.gender, ''))) IN ('male', 'm') THEN 'Male' WHEN LOWER(TRIM(COALESCE(rr.gender, ''))) IN ('female', 'f') THEN 'Female' ELSE 'Other / unknown' END";
        $query = DB::query()
            ->fromSub($this->subquery($base), 'rr')
            ->selectRaw($expression.' AS gender_group')
            ->selectRaw('COUNT(*) AS referrals')
            ->selectRaw('COUNT(DISTINCT rr.patient_id) AS patients')
            ->groupByRaw($expression)
            ->orderByDesc('patients')
            ->orderBy('gender_group')
            ->get();

        return [
            'columns' => [
                ['key' => 'gender', 'label' => 'Gender', 'type' => 'text'],
                ['key' => 'patients', 'label' => 'Unique patients', 'type' => 'integer'],
                ['key' => 'referrals', 'label' => 'Referrals', 'type' => 'integer'],
                ['key' => 'percentage', 'label' => 'Percentage', 'type' => 'percentage'],
            ],
            'rows' => $query->map(fn ($row): array => [
                'gender' => $row->gender_group,
                'patients' => (int) $row->patients,
                'referrals' => (int) $row->referrals,
                'percentage' => $this->percentage((int) $row->patients, $totalPatients),
            ])->all(),
        ];
    }

    private function diagnosisDistribution(Builder $base, array $filters, mixed $limit): array
    {
        $diagnosisBase = DB::query()
            ->fromSub($this->subquery($base), 'rr')
            ->join('diagnosis_referral as dr', 'dr.referral_id', '=', 'rr.referral_id')
            ->join('diagnoses as d', function ($join): void {
                $join->on('d.diagnosis_id', '=', 'dr.diagnosis_id')
                    ->whereNull('d.deleted_at');
            });

        if (($filters['diagnosis_id'] ?? null) !== null) {
            $diagnosisBase->where('d.diagnosis_id', $filters['diagnosis_id']);
        }

        $totalOccurrences = (int) (clone $diagnosisBase)->count('dr.diagnosis_id');
        $query = (clone $diagnosisBase)
            ->select('d.diagnosis_id', 'd.diagnosis_code', 'd.diagnosis_name')
            ->selectRaw('COUNT(*) AS occurrences')
            ->selectRaw('COUNT(DISTINCT rr.referral_id) AS referrals')
            ->selectRaw('COUNT(DISTINCT rr.patient_id) AS patients')
            ->selectRaw("COUNT(DISTINCT CASE WHEN LOWER(TRIM(COALESCE(rr.gender, ''))) IN ('male', 'm') THEN rr.patient_id END) AS male")
            ->selectRaw("COUNT(DISTINCT CASE WHEN LOWER(TRIM(COALESCE(rr.gender, ''))) IN ('female', 'f') THEN rr.patient_id END) AS female")
            ->groupBy('d.diagnosis_id', 'd.diagnosis_code', 'd.diagnosis_name')
            ->orderByDesc('occurrences')
            ->orderBy('d.diagnosis_name');

        $this->applyLimit($query, $limit);

        $rows = $query->get()->map(fn ($row): array => [
            'diagnosis_code' => $row->diagnosis_code,
            'diagnosis' => $row->diagnosis_name,
            'patients' => (int) $row->patients,
            'male' => (int) $row->male,
            'female' => (int) $row->female,
            'referrals' => (int) $row->referrals,
            'occurrences' => (int) $row->occurrences,
            'percentage' => $this->percentage((int) $row->occurrences, $totalOccurrences),
        ])->all();

        return [
            'columns' => [
                ['key' => 'diagnosis_code', 'label' => 'Diagnosis code', 'type' => 'text'],
                ['key' => 'diagnosis', 'label' => 'Diagnosis', 'type' => 'text'],
                ['key' => 'patients', 'label' => 'Unique patients', 'type' => 'integer'],
                ['key' => 'male', 'label' => 'Male', 'type' => 'integer'],
                ['key' => 'female', 'label' => 'Female', 'type' => 'integer'],
                ['key' => 'referrals', 'label' => 'Referrals', 'type' => 'integer'],
                ['key' => 'occurrences', 'label' => 'Occurrences', 'type' => 'integer'],
                ['key' => 'percentage', 'label' => 'Share of occurrences', 'type' => 'percentage'],
            ],
            'rows' => $rows,
            'total_occurrences' => $totalOccurrences,
        ];
    }

    private function statusDistribution(Builder $base, int $total): array
    {
        $query = DB::query()
            ->fromSub($this->subquery($base), 'rr')
            ->selectRaw("COALESCE(NULLIF(TRIM(rr.referral_status), ''), 'Not recorded') AS status")
            ->selectRaw('COUNT(*) AS referrals')
            ->selectRaw('COUNT(DISTINCT rr.patient_id) AS patients')
            ->groupByRaw("COALESCE(NULLIF(TRIM(rr.referral_status), ''), 'Not recorded')")
            ->orderByDesc('referrals')
            ->orderBy('status')
            ->get();

        return [
            'columns' => [
                ['key' => 'status', 'label' => 'Referral status', 'type' => 'text'],
                ['key' => 'referrals', 'label' => 'Number', 'type' => 'integer'],
                ['key' => 'patients', 'label' => 'Unique patients', 'type' => 'integer'],
                ['key' => 'percentage', 'label' => 'Percentage', 'type' => 'percentage'],
            ],
            'rows' => $query->map(fn ($row): array => [
                'status' => $row->status,
                'referrals' => (int) $row->referrals,
                'patients' => (int) $row->patients,
                'percentage' => $this->percentage((int) $row->referrals, $total),
            ])->all(),
        ];
    }

    private function mainTable(
        string $groupBy,
        Builder $base,
        array $filters,
        array $overview,
        array $destination,
        array $monthlyTrend,
        array $gender,
        array $diagnosis,
        array $status,
        mixed $limit,
    ): array {
        $table = match ($groupBy) {
            'month' => $this->trendDistribution($base, 'month', $overview['total_referrals'], $limit),
            'quarter' => $this->trendDistribution($base, 'quarter', $overview['total_referrals'], $limit),
            'year' => $this->trendDistribution($base, 'year', $overview['total_referrals'], $limit),
            'day' => $this->trendDistribution($base, 'day', $overview['total_referrals'], $limit),
            'gender' => $gender,
            'diagnosis' => $diagnosis,
            'status' => $status,
            default => $destination,
        };

        $total = count($table['rows']);

        return [
            'columns' => $table['columns'],
            'rows' => $table['rows'],
            'pagination' => [
                'current_page' => 1,
                'per_page' => max($total, 1),
                'from' => $total === 0 ? null : 1,
                'to' => $total === 0 ? null : $total,
                'total' => $total,
                'last_page' => 1,
                'has_more_pages' => false,
            ],
        ];
    }

    private function patientDetails(Builder $base, array $filters, bool $paginate): array
    {
        $diagnoses = DB::table('diagnosis_referral as dr')
            ->join('diagnoses as d', function ($join): void {
                $join->on('d.diagnosis_id', '=', 'dr.diagnosis_id')
                    ->whereNull('d.deleted_at');
            })
            ->when(($filters['diagnosis_id'] ?? null) !== null, function ($query) use ($filters): void {
                $query->where('d.diagnosis_id', $filters['diagnosis_id']);
            })
            ->select('dr.referral_id')
            ->selectRaw($this->diagnosisAggregationSql())
            ->groupBy('dr.referral_id');

        $query = DB::query()
            ->fromSub($this->subquery($base), 'rr')
            ->leftJoinSub($diagnoses, 'diagnosis_list', 'diagnosis_list.referral_id', '=', 'rr.referral_id')
            ->select(
                'rr.referral_id',
                'rr.patient_id',
                'rr.patient_name',
                'rr.gender',
                'rr.destination_hospital',
                'rr.referral_status',
                'rr.referred_date',
                'diagnosis_list.diagnoses',
            )
            ->orderBy('rr.referred_date')
            ->orderBy('rr.patient_name')
            ->orderBy('rr.referral_id');

        $total = (int) (clone $query)->count('rr.referral_id');
        $page = max((int) ($filters['page'] ?? 1), 1);
        $perPage = min(max((int) ($filters['per_page'] ?? Pagination::DEFAULT_PER_PAGE), 1), Pagination::MAX_PER_PAGE);
        $collection = $paginate
            ? $query->forPage($page, $perPage)->get()
            : $query->get();

        $rows = $collection->values()->map(function ($row, int $index) use ($paginate, $page, $perPage): array {
            return [
                'no' => ($paginate ? (($page - 1) * $perPage) : 0) + $index + 1,
                'patient_name' => $row->patient_name,
                'gender' => $this->genderLabel($row->gender),
                'patient_id' => (int) $row->patient_id,
                'diagnosis' => $row->diagnoses ?: 'No diagnosis recorded',
                'destination_hospital' => $row->destination_hospital ?: 'Not recorded',
                'referral_status' => $row->referral_status ?: 'Not recorded',
                'referred_date' => $this->formatDate($row->referred_date),
            ];
        })->all();

        return [
            'columns' => [
                ['key' => 'no', 'label' => 'No.', 'type' => 'integer'],
                ['key' => 'patient_name', 'label' => 'Patient name', 'type' => 'text'],
                ['key' => 'gender', 'label' => 'Gender', 'type' => 'text'],
                ['key' => 'patient_id', 'label' => 'Patient ID', 'type' => 'integer'],
                ['key' => 'diagnosis', 'label' => 'Diagnosis', 'type' => 'text'],
                ['key' => 'destination_hospital', 'label' => 'Receiving hospital', 'type' => 'text'],
                ['key' => 'referral_status', 'label' => 'Referral status', 'type' => 'text'],
                ['key' => 'referred_date', 'label' => 'Referral date', 'type' => 'date'],
            ],
            'rows' => $rows,
            'pagination' => [
                'current_page' => $paginate ? $page : 1,
                'per_page' => $paginate ? $perPage : max($total, 1),
                'from' => $total === 0 ? null : (($paginate ? $page - 1 : 0) * ($paginate ? $perPage : 0)) + 1,
                'to' => $total === 0 ? null : (($paginate ? $page - 1 : 0) * ($paginate ? $perPage : 0)) + count($rows),
                'total' => $total,
                'last_page' => $paginate ? max(1, (int) ceil($total / $perPage)) : 1,
                'has_more_pages' => $paginate && $page * $perPage < $total,
            ],
        ];
    }

    private function overviewMetrics(array $overview): array
    {
        return [
            ['key' => 'total_referrals', 'label' => 'Total referral records', 'value' => $overview['total_referrals'], 'type' => 'integer'],
            ['key' => 'unique_patients', 'label' => 'Unique patients', 'value' => $overview['unique_patients'], 'type' => 'integer'],
            ['key' => 'receiving_institutions', 'label' => 'Receiving institutions', 'value' => $overview['receiving_institutions'], 'type' => 'integer'],
            ['key' => 'records_with_diagnosis', 'label' => 'Records with diagnosis', 'value' => $overview['records_with_diagnosis'], 'type' => 'integer'],
            ['key' => 'first_referral_date', 'label' => 'First referral date', 'value' => $overview['first_referral_date'] ?? '—', 'type' => 'text'],
            ['key' => 'latest_referral_date', 'label' => 'Latest referral date', 'value' => $overview['latest_referral_date'] ?? '—', 'type' => 'text'],
        ];
    }

    private function executiveSummary(array $overview, array $filters): string
    {
        $records = $overview['total_referrals'];
        $patients = $overview['unique_patients'];
        $institutions = $overview['receiving_institutions'];
        $period = Carbon::parse($filters['start_date'])->format('d F Y').' to '.Carbon::parse($filters['end_date'])->format('d F Y');

        return "This report summarizes {$records} referral record".($records === 1 ? '' : 's')
            ." involving {$patients} unique patient".($patients === 1 ? '' : 's')
            ." between {$period}. The filtered records include {$institutions} receiving institution".($institutions === 1 ? '' : 's').'.';
    }

    private function findings(array $destinations, array $trend, array $gender, array $diagnoses, array $statuses): array
    {
        $findings = [];

        if (($destinations[0] ?? null) !== null) {
            $row = $destinations[0];
            $findings[] = $row['destination_hospital'].' received '.$row['referrals'].' referral'.($row['referrals'] === 1 ? '' : 's').' ('.$row['percentage'].'%).';
        }

        $highestTrend = collect($trend)->sortByDesc('referrals')->first();
        if ($highestTrend !== null) {
            $findings[] = $highestTrend['period'].' had the highest referral volume with '.$highestTrend['referrals'].' record'.($highestTrend['referrals'] === 1 ? '' : 's').'.';
        }

        if (($diagnoses[0] ?? null) !== null) {
            $row = $diagnoses[0];
            $findings[] = 'The most frequently recorded diagnosis was '.$row['diagnosis'].' with '.$row['occurrences'].' occurrence'.($row['occurrences'] === 1 ? '' : 's').'.';
        }

        $largestGender = collect($gender)->sortByDesc('patients')->first();
        if ($largestGender !== null) {
            $findings[] = $largestGender['gender'].' represented '.$largestGender['patients'].' unique patient'.($largestGender['patients'] === 1 ? '' : 's').' ('.$largestGender['percentage'].'%).';
        }

        $largestStatus = collect($statuses)->sortByDesc('referrals')->first();
        if ($largestStatus !== null) {
            $findings[] = 'The most common referral status was '.$largestStatus['status'].' with '.$largestStatus['referrals'].' record'.($largestStatus['referrals'] === 1 ? '' : 's').'.';
        }

        return $findings;
    }

    private function qualityNotes(array $overview, array $filters): array
    {
        $notes = [];

        if ($overview['records_without_diagnosis'] > 0) {
            $notes[] = $overview['records_without_diagnosis'].' referral record'.($overview['records_without_diagnosis'] === 1 ? ' has' : 's have').' no diagnosis information.';
        }

        if ($overview['missing_destination'] > 0) {
            $notes[] = $overview['missing_destination'].' referral record'.($overview['missing_destination'] === 1 ? ' has' : 's have').' no receiving hospital recorded.';
        }

        if ($overview['missing_gender_patients'] > 0) {
            $notes[] = $overview['missing_gender_patients'].' unique patient'.($overview['missing_gender_patients'] === 1 ? ' has' : 's have').' no gender recorded; these are retained as Other / unknown.';
        }

        if ($overview['missing_status'] > 0) {
            $notes[] = $overview['missing_status'].' referral record'.($overview['missing_status'] === 1 ? ' has' : 's have').' no status recorded.';
        }

        $start = Carbon::parse($filters['start_date']);
        $end = Carbon::parse($filters['end_date']);
        if ($start->day !== 1) {
            $notes[] = $start->format('F Y').' is a partial reporting month beginning '.$start->format('d F Y').'.';
        }
        if ($end->day !== $end->daysInMonth) {
            $notes[] = $end->format('F Y').' is a partial reporting month through '.$end->format('d F Y').'.';
        }

        return $notes;
    }

    private function tableSection(string $key, string $title, array $columns, array $rows, ?array $pagination = null): array
    {
        return [
            'key' => $key,
            'title' => $title,
            'kind' => 'table',
            'columns' => $columns,
            'rows' => $rows,
            'pagination' => $pagination,
        ];
    }

    private function analysisTitle(string $groupBy): string
    {
        return match ($groupBy) {
            'destination' => 'Referral Destination Distribution',
            'month' => 'Referral Trend by Month',
            'quarter' => 'Referral Trend by Quarter',
            'year' => 'Referral Trend by Year',
            'day' => 'Referral Trend by Day',
            'gender' => 'Referral Analysis by Gender',
            'diagnosis' => 'Referral Analysis by Diagnosis',
            'status' => 'Referral Analysis by Status',
            default => 'Referral Analysis',
        };
    }

    private function subquery(Builder $query): Builder
    {
        $subquery = clone $query;
        $subquery->reorder();

        return $subquery;
    }

    private function effectiveHospitalIds(array $filters, User $user): ?array
    {
        $selected = $filters['hospital_ids'] ?? [];
        $allowed = $this->scope->hospitalIds($user);

        if ($allowed === null) {
            return $selected === [] ? null : $selected;
        }

        if ($selected === []) {
            return $allowed;
        }

        return array_values(array_intersect($selected, $allowed));
    }

    private function effectiveSourceHospitalIds(array $filters, User $user): ?array
    {
        $selected = $filters['source_hospital_ids'] ?? [];
        $allowed = $this->scope->hospitalIds($user);

        if ($allowed === null) {
            return $selected === [] ? null : $selected;
        }

        if ($selected === []) {
            return $allowed;
        }

        return array_values(array_intersect($selected, $allowed));
    }

    private function applyCreatedByHospitalConstraint(Builder $query, array $hospitalIds, string $createdByColumn): void
    {
        $query->whereExists(function (Builder $source) use ($hospitalIds, $createdByColumn): void {
            $source->selectRaw('1')
                ->from('hospital_user as source_hospital_user')
                ->join('hospitals as source_hospital', 'source_hospital.hospital_id', '=', 'source_hospital_user.hospital_id')
                ->whereColumn('source_hospital_user.user_id', $createdByColumn)
                ->whereNull('source_hospital.deleted_at')
                ->whereIn('source_hospital_user.hospital_id', $hospitalIds);
        });
    }

    private function applyPatientFilters(Builder $query, array $filters, string $alias): void
    {
        if (($filters['gender'] ?? null) !== null) {
            $gender = strtolower($filters['gender']);
            if ($gender === 'male') {
                $query->whereRaw("LOWER(TRIM(COALESCE({$alias}.gender, ''))) IN ('male', 'm')");
            } elseif ($gender === 'female') {
                $query->whereRaw("LOWER(TRIM(COALESCE({$alias}.gender, ''))) IN ('female', 'f')");
            } else {
                $query->whereRaw("LOWER(TRIM(COALESCE({$alias}.gender, ''))) NOT IN ('male', 'm', 'female', 'f')");
            }
        }

        if (($filters['location_id'] ?? null) !== null) {
            $query->where("{$alias}.location_id", $filters['location_id']);
        }

        if (($filters['age_from'] ?? null) !== null || ($filters['age_to'] ?? null) !== null) {
            $expression = $this->ageExpression($alias);
            if (($filters['age_from'] ?? null) !== null) {
                $query->whereRaw("{$expression} >= ?", [$filters['age_from']]);
            }
            if (($filters['age_to'] ?? null) !== null) {
                $query->whereRaw("{$expression} <= ?", [$filters['age_to']]);
            }
        }
    }

    private function applyLimit(Builder $query, mixed $limit): void
    {
        if ($limit !== 'all' && is_int($limit)) {
            $query->limit($limit);
        }
    }

    private function trendExpression(string $groupBy): string
    {
        if (DB::getDriverName() === 'pgsql') {
            return match ($groupBy) {
                'day' => "TO_CHAR(DATE_TRUNC('day', rr.referred_date), 'YYYY-MM-DD')",
                'quarter' => "CONCAT(EXTRACT(YEAR FROM rr.referred_date)::int, '-Q', EXTRACT(QUARTER FROM rr.referred_date)::int)",
                'year' => "TO_CHAR(DATE_TRUNC('year', rr.referred_date), 'YYYY')",
                default => "TO_CHAR(DATE_TRUNC('month', rr.referred_date), 'YYYY-MM')",
            };
        }

        return match ($groupBy) {
            'day' => "DATE_FORMAT(rr.referred_date, '%Y-%m-%d')",
            'quarter' => "CONCAT(YEAR(rr.referred_date), '-Q', QUARTER(rr.referred_date))",
            'year' => "DATE_FORMAT(rr.referred_date, '%Y')",
            default => "DATE_FORMAT(rr.referred_date, '%Y-%m')",
        };
    }

    private function periodKeyLabel(string $key, string $groupBy): string
    {
        try {
            return match ($groupBy) {
                'day' => Carbon::createFromFormat('Y-m-d', $key)->format('d F Y'),
                'month' => Carbon::createFromFormat('Y-m', $key)->format('F Y'),
                default => $key,
            };
        } catch (\Throwable) {
            return $key;
        }
    }

    private function ageExpression(string $alias): string
    {
        if (DB::getDriverName() === 'pgsql') {
            return "CASE WHEN {$alias}.date_of_birth ~ '^\\d{4}-\\d{2}-\\d{2}$' THEN EXTRACT(YEAR FROM AGE(CURRENT_DATE, NULLIF({$alias}.date_of_birth, '')::date)) END";
        }

        return "TIMESTAMPDIFF(YEAR, STR_TO_DATE(NULLIF({$alias}.date_of_birth, ''), '%Y-%m-%d'), CURDATE())";
    }

    private function diagnosisAggregationSql(): string
    {
        if (DB::getDriverName() === 'pgsql') {
            return "STRING_AGG(DISTINCT CONCAT_WS(' — ', d.diagnosis_code, d.diagnosis_name), '; ') AS diagnoses";
        }

        return "GROUP_CONCAT(DISTINCT CONCAT_WS(' — ', d.diagnosis_code, d.diagnosis_name) ORDER BY d.diagnosis_name SEPARATOR '; ') AS diagnoses";
    }

    private function startAt(array $filters): string
    {
        return Carbon::parse($filters['start_date'])->startOfDay()->toDateTimeString();
    }

    private function endExclusive(array $filters): string
    {
        $end = Carbon::parse($filters['end_date'])->addDay()->startOfDay();

        return $end->greaterThan(now()) ? now()->toDateTimeString() : $end->toDateTimeString();
    }

    private function percentage(int $value, int $total): float
    {
        return $total > 0 ? round(($value / $total) * 100, 2) : 0.0;
    }

    private function formatDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return Carbon::parse((string) $value)->format('d F Y');
        } catch (\Throwable) {
            return (string) $value;
        }
    }

    private function genderLabel(mixed $value): string
    {
        return match (strtolower(trim((string) ($value ?? '')))) {
            'male', 'm' => 'Male',
            'female', 'f' => 'Female',
            default => 'Other / unknown',
        };
    }
}
