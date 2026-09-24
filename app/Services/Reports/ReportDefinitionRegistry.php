<?php

namespace App\Services\Reports;

use InvalidArgumentException;

final class ReportDefinitionRegistry
{
    public const TOP_DIAGNOSES = 'top_diagnoses';

    public const REFERRALS_BY_HOSPITAL = 'referrals_by_hospital';

    /**
     * The public report catalogue. Keep database names out of this structure;
     * it is also returned to the frontend to build the filter experience.
     */
    public function all(): array
    {
        return [
            self::TOP_DIAGNOSES => [
                'key' => self::TOP_DIAGNOSES,
                'name' => 'Top Diagnoses',
                'description' => 'Rank medical-board diagnoses with optional referral and patient-level analysis.',
                'metric' => 'Unique patients per medical-board diagnosis. A patient is counted once per diagnosis in the selected period.',
                'filters' => [
                    'date_range',
                    'result_limit',
                    'gender',
                    'age',
                    'location',
                    'diagnosis',
                    'source_hospital',
                    'referral_hospital',
                    'referral_status',
                    'referral_type',
                    'patient_history_status',
                    'detail_level',
                ],
                'group_by' => [
                    ['value' => 'diagnosis', 'label' => 'Diagnosis'],
                ],
                'detail_levels' => [
                    ['value' => 'summary', 'label' => 'Summary only'],
                    ['value' => 'breakdown', 'label' => 'Summary + breakdown'],
                    ['value' => 'details', 'label' => 'Summary + patient details'],
                ],
                'exports' => ['xlsx', 'pdf', 'docx'],
            ],
            self::REFERRALS_BY_HOSPITAL => [
                'key' => self::REFERRALS_BY_HOSPITAL,
                'name' => 'Referral Analysis Report',
                'description' => 'Generate a dynamic Ministry-style referral analysis from the selected period and filters.',
                'metric' => 'One filtered referral record is counted once. Patient, diagnosis, destination, trend, gender and status sections use definitions appropriate to each measure.',
                'filters' => [
                    'date_range',
                    'result_limit',
                    'group_by',
                    'detail_level',
                    'source_hospital',
                    'referral_hospital',
                    'referral_status',
                    'referral_type',
                    'gender',
                    'age',
                    'location',
                    'diagnosis',
                    'patient_search',
                ],
                'group_by' => [
                    ['value' => 'destination', 'label' => 'Receiving hospital'],
                    ['value' => 'month', 'label' => 'Month'],
                    ['value' => 'quarter', 'label' => 'Quarter'],
                    ['value' => 'year', 'label' => 'Year'],
                    ['value' => 'gender', 'label' => 'Gender'],
                    ['value' => 'diagnosis', 'label' => 'Diagnosis'],
                    ['value' => 'status', 'label' => 'Referral status'],
                ],
                'detail_levels' => [
                    ['value' => 'summary', 'label' => 'Summary only'],
                    ['value' => 'breakdown', 'label' => 'Summary + breakdown'],
                    ['value' => 'details', 'label' => 'Summary + patient details'],
                ],
                'exports' => ['xlsx', 'pdf', 'docx'],
            ],
        ];
    }

    public function get(string $key): array
    {
        $definition = $this->all()[$key] ?? null;

        if ($definition === null) {
            throw new InvalidArgumentException('Unsupported report type.');
        }

        return $definition;
    }
}
