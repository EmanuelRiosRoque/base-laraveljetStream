<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $roles = [
            ['rol' => 'Admin'],
            ['rol' => 'User'],
            ['rol' => 'Lector']
        ];

        foreach ($roles as $role) {
            DB::table('roles')->insert($role);
        }
    }
}
