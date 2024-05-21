<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\model_has_roles;

class admin_info extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('model_has_roles')->delete();

        $admin_role = [
            [
                'role_id' => 1, 'model_type' => 'App\Models\User', 'model_id' => 1
            ]
        ];

        DB::table('model_has_roles')->insert($admin_role);
    
    }
}
