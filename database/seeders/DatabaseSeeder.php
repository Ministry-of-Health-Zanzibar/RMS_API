<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            nationality_info::class,
            permission_info::class,
            user_info::class,
            admin_info::class,
            national_role::class,
            batch_year::class,
            ReasonSeeder::class,
            HospitalSeeder::class,
        ]);
    }
}