<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Database\Query\Builder;

final class ReportDataScope
{
    public function isHospitalScoped(User $user): bool
    {
        return $user->hasRole('ROLE HOSPITAL USER') && ! SuperAdminAccess::allowed($user);
    }

    public function hospitalIds(User $user): ?array
    {
        if (! $this->isHospitalScoped($user)) {
            return null;
        }

        return $user->hospitals()->pluck('hospitals.hospital_id')->map(static fn ($id): int => (int) $id)->all();
    }

    public function applyHospitalScope(Builder $query, User $user, string $column = 'r.hospital_id'): void
    {
        $hospitalIds = $this->hospitalIds($user);

        if ($hospitalIds === null) {
            return;
        }

        if ($hospitalIds === []) {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->whereIn($column, $hospitalIds);
    }

    /**
     * Patient histories do not carry a hospital_id. For a hospital-scoped
     * account, the existing system identifies the facility through the
     * creator's hospital assignment, so apply that scope at the SQL level.
     */
    public function applyPatientScope(Builder $query, User $user, string $patientAlias = 'p'): void
    {
        $hospitalIds = $this->hospitalIds($user);

        if ($hospitalIds === null) {
            return;
        }

        if ($hospitalIds === []) {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->whereExists(function (Builder $exists) use ($hospitalIds, $patientAlias): void {
            $exists->selectRaw('1')
                ->from('hospital_user as report_creator_hospital')
                ->whereColumn('report_creator_hospital.user_id', $patientAlias.'.created_by')
                ->whereIn('report_creator_hospital.hospital_id', $hospitalIds);
        });
    }
}
