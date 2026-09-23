<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const STATUSES = [
        'Pending',
        'Confirmed',
        'Death',
        'Cancelled',
        'Transferred',
        'Expired',
        'Closed',
        'Requested',
        'BoardedOut',
    ];

    private const STATUSES_WITHOUT_BOARDED_OUT = [
        'Pending',
        'Confirmed',
        'Death',
        'Cancelled',
        'Transferred',
        'Expired',
        'Closed',
        'Requested',
    ];

    public function up(): void
    {
        $this->updateStatusDefinition(self::STATUSES);
    }

    public function down(): void
    {
        $this->updateStatusDefinition(self::STATUSES_WITHOUT_BOARDED_OUT);
    }

    private function updateStatusDefinition(array $statuses): void
    {
        $driver = DB::getDriverName();
        $quotedStatuses = implode(', ', array_map(
            static fn (string $status): string => "'" . str_replace("'", "''", $status) . "'",
            $statuses,
        ));

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE referrals DROP CONSTRAINT IF EXISTS referrals_status_check');
            DB::statement(
                "ALTER TABLE referrals ADD CONSTRAINT referrals_status_check CHECK (status IN ({$quotedStatuses}))",
            );

            return;
        }

        if ($driver === 'mysql') {
            DB::statement(
                "ALTER TABLE referrals MODIFY status ENUM({$quotedStatuses}) NOT NULL",
            );
        }
    }
};
