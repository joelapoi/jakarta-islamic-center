<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = [
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@jic.test',
                'password' => Hash::make('password123'),
                'nip' => '001',
                'phone' => '081234567890',
                'is_active' => true,
                'role' => 'super_admin'
            ],
            [
                'name' => 'Admin User',
                'email' => 'admin@jic.test',
                'password' => Hash::make('password123'),
                'nip' => '002',
                'phone' => '081234567891',
                'is_active' => true,
                'role' => 'admin'
            ],
            [
                'name' => 'Kepala JIC',
                'email' => 'kepala@jic.test',
                'password' => Hash::make('password123'),
                'nip' => '003',
                'phone' => '081234567892',
                'is_active' => true,
                'role' => 'kepala_jic'
            ],
            [
                'name' => 'Kadiv Umum',
                'email' => 'kadiv@jic.test',
                'password' => Hash::make('password123'),
                'nip' => '004',
                'phone' => '081234567893',
                'is_active' => true,
                'role' => 'kadiv_umum'
            ],
            [
                'name' => 'User Biasa 1',
                'email' => 'user1@jic.test',
                'password' => Hash::make('password123'),
                'nip' => '005',
                'phone' => '081234567894',
                'is_active' => true,
                'role' => 'staff'
            ],
            [
                'name' => 'User Biasa 2',
                'email' => 'user2@jic.test',
                'password' => Hash::make('password123'),
                'nip' => '006',
                'phone' => '081234567895',
                'is_active' => true,
                'role' => 'staff'
            ],
        ];

        foreach ($users as $userData) {
            $roleData = $userData['role'];
            unset($userData['role']);

            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                $userData
            );

            // Attach role
            $role = Role::where('name', $roleData)->first();
            if ($role) {
                $user->roles()->syncWithoutDetaching([$role->id]);
            }
        }

        $this->command->info('User seeder completed!');
    }
}
