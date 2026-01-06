<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Find or create super admin role
        $superAdminRole = Role::where('name', 'super admin')->first();
        
        if (!$superAdminRole) {
            $this->command->error('Super admin role not found. Please run PermissionSeeder first.');
            return;
        }

        // Create or update super admin user
        $superAdmin = User::updateOrCreate(
            ['user_id' => 'SuperAdmin'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('12345678'),
                'role' => 'super admin',
                'company_id' => null, // Super admin doesn't belong to any company
            ]
        );

        // Assign super admin role
        if (!$superAdmin->hasRole('super admin')) {
            $superAdmin->assignRole($superAdminRole);
        }

        $this->command->info('Super Admin user created/updated successfully!');
        $this->command->info('User ID: SuperAdmin');
        $this->command->info('Password: 12345678');
    }
}
