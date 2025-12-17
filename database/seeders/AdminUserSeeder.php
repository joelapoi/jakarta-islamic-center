<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        // Create Super Admin
        $adminId = DB::table('users')->insertGetId([
            'name' => 'Super Admin',
            'email' => 'admin@islamiccenter.com',
            'password' => Hash::make('password'),
            'nip' => '199001012020011001',
            'phone' => '081234567890',
            'is_active' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // Assign super_admin role
        $superAdminRoleId = DB::table('roles')->where('name', 'super_admin')->first()->id;
        
        DB::table('user_roles')->insert([
            'user_id' => $adminId,
            'role_id' => $superAdminRoleId,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}