<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::updateOrCreate(
            [
                'email' => env(
                    'SUPER_ADMIN_EMAIL',
                    'labtech.borrowing@ucc-caloocan.edu.ph'
                ),
            ],
            [
                'user_code' => 'UCC-USR-000001',

                'id_number' => env(
                    'SUPER_ADMIN_ID_NUMBER',
                    'UCC-ADMIN-001'
                ),

                'first_name' => env(
                    'SUPER_ADMIN_FIRST_NAME',
                    'System'
                ),

                'middle_name' => null,

                'last_name' => env(
                    'SUPER_ADMIN_LAST_NAME',
                    'Administrator'
                ),

                'suffix' => null,

                'password' => Hash::make(
                    env(
                        'SUPER_ADMIN_PASSWORD',
                        'Admin@1234'
                    )
                ),

                'account_status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        $user->syncRoles(['super_admin']);
    }
}