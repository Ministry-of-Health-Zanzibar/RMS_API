<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            if (Schema::hasTable('diagnoses')) {
                DB::statement(
                    'CREATE INDEX IF NOT EXISTS diagnoses_name_prefix_idx '
                    .'ON diagnoses (LOWER(diagnosis_name) text_pattern_ops)'
                );
                DB::statement(
                    'CREATE INDEX IF NOT EXISTS diagnoses_code_prefix_idx '
                    .'ON diagnoses (LOWER(diagnosis_code) text_pattern_ops)'
                );
            }

            if (Schema::hasTable('patients')) {
                DB::statement(
                    'CREATE INDEX IF NOT EXISTS patients_name_prefix_idx '
                    .'ON patients (LOWER(name) text_pattern_ops)'
                );
                DB::statement(
                    'CREATE INDEX IF NOT EXISTS patients_phone_prefix_idx '
                    .'ON patients (LOWER(phone) text_pattern_ops)'
                );
                DB::statement(
                    'CREATE INDEX IF NOT EXISTS patients_matibabu_card_prefix_idx '
                    .'ON patients (LOWER(matibabu_card) text_pattern_ops)'
                );
                DB::statement(
                    'CREATE INDEX IF NOT EXISTS patients_zan_id_prefix_idx '
                    .'ON patients (LOWER(zan_id) text_pattern_ops)'
                );
                DB::statement(
                    'CREATE INDEX IF NOT EXISTS patients_created_at_idx '
                    .'ON patients (created_at DESC)'
                );
            }

            if (Schema::hasTable('users')) {
                DB::statement(
                    'CREATE INDEX IF NOT EXISTS users_first_name_prefix_idx '
                    .'ON users (LOWER(first_name) text_pattern_ops)'
                );
                DB::statement(
                    'CREATE INDEX IF NOT EXISTS users_last_name_prefix_idx '
                    .'ON users (LOWER(last_name) text_pattern_ops)'
                );
                DB::statement(
                    'CREATE INDEX IF NOT EXISTS users_email_prefix_idx '
                    .'ON users (LOWER(email) text_pattern_ops)'
                );
            }

            if (Schema::hasTable('patient_histories')) {
                DB::statement(
                    'CREATE INDEX IF NOT EXISTS patient_histories_patient_created_idx '
                    .'ON patient_histories (patient_id, patient_histories_id DESC)'
                );
                DB::statement(
                    'CREATE INDEX IF NOT EXISTS patient_histories_status_created_idx '
                    .'ON patient_histories (status, patient_histories_id DESC)'
                );
            }

            if (Schema::hasTable('referrals')) {
                DB::statement(
                    'CREATE INDEX IF NOT EXISTS referrals_patient_status_idx '
                    .'ON referrals (patient_id, status)'
                );
                DB::statement(
                    'CREATE INDEX IF NOT EXISTS referrals_hospital_status_idx '
                    .'ON referrals (hospital_id, status)'
                );
                DB::statement(
                    'CREATE INDEX IF NOT EXISTS referrals_parent_idx '
                    .'ON referrals (parent_referral_id)'
                );
                DB::statement(
                    'CREATE INDEX IF NOT EXISTS referrals_created_at_idx '
                    .'ON referrals (created_at DESC)'
                );
                DB::statement(
                    'CREATE INDEX IF NOT EXISTS referrals_number_prefix_idx '
                    .'ON referrals (LOWER(referral_number) text_pattern_ops)'
                );
            }

            if (Schema::hasTable('hospital_letters')) {
                DB::statement(
                    'CREATE INDEX IF NOT EXISTS hospital_letters_outcome_created_idx '
                    .'ON hospital_letters (outcome, letter_id DESC)'
                );
                DB::statement(
                    'CREATE INDEX IF NOT EXISTS hospital_letters_referral_idx '
                    .'ON hospital_letters (referral_id, letter_id DESC)'
                );
            }

            if (Schema::hasTable('followups')) {
                DB::statement(
                    'CREATE INDEX IF NOT EXISTS followups_date_idx '
                    .'ON followups (followup_date, followup_id DESC)'
                );
                DB::statement(
                    'CREATE INDEX IF NOT EXISTS followups_letter_idx '
                    .'ON followups (letter_id, followup_id DESC)'
                );
                DB::statement(
                    'CREATE INDEX IF NOT EXISTS followups_status_date_idx '
                    .'ON followups (followup_status, followup_date DESC, followup_id DESC)'
                );
            }

            if (Schema::hasTable('patient_files')) {
                DB::statement(
                    'CREATE INDEX IF NOT EXISTS patient_files_patient_idx '
                    .'ON patient_files (patient_id, file_id DESC)'
                );
            }

            if (Schema::hasTable('bill_items')) {
                DB::statement(
                    'CREATE INDEX IF NOT EXISTS bill_items_bill_idx '
                    .'ON bill_items (bill_id, bill_item_id DESC)'
                );
            }

            if (Schema::hasTable('bill_payments')) {
                DB::statement(
                    'CREATE INDEX IF NOT EXISTS bill_payments_bill_idx '
                    .'ON bill_payments (bill_id, bill_payment_id DESC)'
                );
            }

            if (Schema::hasTable('history_diagnosis')) {
                DB::statement(
                    'CREATE INDEX IF NOT EXISTS history_diagnosis_diagnosis_idx '
                    .'ON history_diagnosis (diagnosis_id, patient_histories_id)'
                );
            }

            if (Schema::hasTable('diagnosis_referral')) {
                DB::statement(
                    'CREATE INDEX IF NOT EXISTS diagnosis_referral_diagnosis_idx '
                    .'ON diagnosis_referral (diagnosis_id, referral_id)'
                );
            }

            return;
        }

        // The production database is PostgreSQL. Keep a portable fallback for
        // local development and automated tests using MySQL-compatible drivers.
        if (Schema::hasTable('diagnoses')) {
            Schema::table('diagnoses', function ($table): void {
                $table->index('diagnosis_name', 'diagnoses_name_idx');
                $table->index('diagnosis_code', 'diagnoses_code_idx');
            });
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            foreach ([
                'diagnoses_name_prefix_idx',
                'diagnoses_code_prefix_idx',
                'patients_name_prefix_idx',
                'patients_phone_prefix_idx',
                'patients_matibabu_card_prefix_idx',
                'patients_zan_id_prefix_idx',
                'patients_created_at_idx',
                'users_first_name_prefix_idx',
                'users_last_name_prefix_idx',
                'users_email_prefix_idx',
                'patient_histories_patient_created_idx',
                'patient_histories_status_created_idx',
                'referrals_patient_status_idx',
                'referrals_hospital_status_idx',
                'referrals_parent_idx',
                'referrals_created_at_idx',
                'referrals_number_prefix_idx',
                'hospital_letters_outcome_created_idx',
                'hospital_letters_referral_idx',
                'followups_date_idx',
                'followups_letter_idx',
                'followups_status_date_idx',
                'patient_files_patient_idx',
                'bill_items_bill_idx',
                'bill_payments_bill_idx',
                'history_diagnosis_diagnosis_idx',
                'diagnosis_referral_diagnosis_idx',
            ] as $index) {
                DB::statement('DROP INDEX IF EXISTS '.$index);
            }

            return;
        }

        if (Schema::hasTable('diagnoses')) {
            Schema::table('diagnoses', function ($table): void {
                $table->dropIndex('diagnoses_name_idx');
                $table->dropIndex('diagnoses_code_idx');
            });
        }
    }
};
