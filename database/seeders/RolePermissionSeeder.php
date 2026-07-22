<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]
            ->forgetCachedPermissions();

        $permissions = [
            // Dashboard
            'view dashboard',

            // Profile
            'view own profile',
            'edit own profile',

            // Users
            'view users',
            'create users',
            'edit users',
            'activate users',
            'suspend users',
            'archive users',
            'restore users',

            // Categories
            'view categories',
            'create categories',
            'edit categories',
            'archive categories',
            'restore categories',

            // Items
            'view items',
            'create items',
            'edit items',
            'archive items',
            'restore items',
            'print item barcodes',

            // Borrowings
            'create borrowing requests',
            'view own borrowings',
            'view all borrowings',
            'approve borrowings',
            'reject borrowings',
            'release borrowings',
            'receive returns',
            'cancel borrowings',
            'extend borrowing due dates',
            'view reservation calendar',
            'reschedule borrowings',
            'export reservation calendar',

            // Maintenance
            'view maintenance',
            'create maintenance',
            'manage maintenance',

            // Walk-in transactions
            'process walk-in borrowings',
            'scan university ids',
            'scan item barcodes',

            // Tickets
            'create tickets',
            'view own tickets',
            'view all tickets',
            'assign tickets',
            'reply to tickets',
            'close tickets',

            // Announcements
            'view announcements',
            'manage announcements',

            // Reports
            'view reports',
            'export reports',

            // Administration
            'view activity logs',
            'export activity logs',
            'manage roles',
            'manage permissions',
            'manage settings',
        ];

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);
        }

        $student = Role::firstOrCreate([
            'name' => 'student',
            'guard_name' => 'web',
        ]);

        $professor = Role::firstOrCreate([
            'name' => 'professor',
            'guard_name' => 'web',
        ]);

        $faculty = Role::firstOrCreate([
            'name' => 'faculty',
            'guard_name' => 'web',
        ]);

        $admin = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);

        $superAdmin = Role::firstOrCreate([
            'name' => 'super_admin',
            'guard_name' => 'web',
        ]);

        $borrowerPermissions = [
            'view dashboard',
            'view own profile',
            'edit own profile',
            'view items',
            'create borrowing requests',
            'view own borrowings',
            'create tickets',
            'view own tickets',
            'reply to tickets',
            'view announcements',
            'view reservation calendar',
            'export reservation calendar',
        ];

        $student->syncPermissions($borrowerPermissions);
        $professor->syncPermissions($borrowerPermissions);
        $faculty->syncPermissions($borrowerPermissions);

        $admin->syncPermissions([
            'view dashboard',
            'view own profile',
            'edit own profile',

            'view users',
            'create users',
            'edit users',
            'activate users',
            'suspend users',
            'archive users',
            'restore users',

            'view categories',
            'create categories',
            'edit categories',
            'archive categories',
            'restore categories',

            'view items',
            'create items',
            'edit items',
            'archive items',
            'restore items',
            'print item barcodes',

            'create borrowing requests',
            'view own borrowings',
            'view all borrowings',
            'approve borrowings',
            'reject borrowings',
            'release borrowings',
            'receive returns',
            'cancel borrowings',
            'extend borrowing due dates',
            'view reservation calendar',
            'reschedule borrowings',
            'export reservation calendar',

            'view maintenance',
            'create maintenance',
            'manage maintenance',

            'process walk-in borrowings',
            'scan university ids',
            'scan item barcodes',

            'create tickets',
            'view own tickets',
            'view all tickets',
            'assign tickets',
            'reply to tickets',
            'close tickets',

            'view announcements',
            'manage announcements',

            'view reports',
            'export reports',
            'view activity logs',
            'export activity logs',
            'manage settings',
        ]);

        $superAdmin->syncPermissions(
            Permission::all()
        );

        app()[\Spatie\Permission\PermissionRegistrar::class]
            ->forgetCachedPermissions();
    }
}