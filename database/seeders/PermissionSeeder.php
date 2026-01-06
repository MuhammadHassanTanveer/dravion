<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create screen-based permissions
        $screenPermissions = [
            'access dashboard',
        ];

        // Create user management permissions
        $userManagementPermissions = [
            'view users',
            'add user',
            'edit user',
            'delete user',
        ];

        // Create roles management permissions
        $rolesManagementPermissions = [
            'view roles',
            'add role',
            'edit role',
            'delete role',
            'manage permissions',
        ];

            // Create pages management permissions
            $pagesManagementPermissions = [
                'view pages',
                'add page',
                'edit page',
                'delete page',
            ];

            // Create deposit form management permissions
            $depositFormManagementPermissions = [
                'view deposit forms',
                'add deposit form',
                'edit deposit form',
                'delete deposit form',
            ];

            // Create redeem form management permissions
            $redeemFormManagementPermissions = [
                'view redeem forms',
                'add redeem form',
                'edit redeem form',
                'delete redeem form',
            ];

            // Create redeem process management permissions
            $redeemProcessManagementPermissions = [
                'view redeem process',
            ];

            // Create payment method management permissions
            $paymentMethodManagementPermissions = [
                'view payment methods',
                'add payment method',
                'edit payment method',
                'delete payment method',
            ];

            // Create company management permissions (only for super admin)
            $companyManagementPermissions = [
                'view companies',
                'add company',
                'edit company',
                'delete company',
            ];

        $allPermissions = [];
        
        // Create screen permissions
        foreach ($screenPermissions as $permissionName) {
            $allPermissions[] = Permission::firstOrCreate(
                ['name' => $permissionName, 'guard_name' => 'web']
            );
        }
        
        // Create user management permissions
        foreach ($userManagementPermissions as $permissionName) {
            $allPermissions[] = Permission::firstOrCreate(
                ['name' => $permissionName, 'guard_name' => 'web']
            );
        }
        
        // Create roles management permissions
        foreach ($rolesManagementPermissions as $permissionName) {
            $allPermissions[] = Permission::firstOrCreate(
                ['name' => $permissionName, 'guard_name' => 'web']
            );
        }
        
            // Create pages management permissions
            foreach ($pagesManagementPermissions as $permissionName) {
                $allPermissions[] = Permission::firstOrCreate(
                    ['name' => $permissionName, 'guard_name' => 'web']
                );
            }

            // Create deposit form management permissions
            foreach ($depositFormManagementPermissions as $permissionName) {
                $allPermissions[] = Permission::firstOrCreate(
                    ['name' => $permissionName, 'guard_name' => 'web']
                );
            }

            // Create redeem form management permissions
            foreach ($redeemFormManagementPermissions as $permissionName) {
                $allPermissions[] = Permission::firstOrCreate(
                    ['name' => $permissionName, 'guard_name' => 'web']
                );
            }

            // Create redeem process management permissions
            foreach ($redeemProcessManagementPermissions as $permissionName) {
                $allPermissions[] = Permission::firstOrCreate(
                    ['name' => $permissionName, 'guard_name' => 'web']
                );
            }

            // Create payment method management permissions
            foreach ($paymentMethodManagementPermissions as $permissionName) {
                $allPermissions[] = Permission::firstOrCreate(
                    ['name' => $permissionName, 'guard_name' => 'web']
                );
            }

            // Create company management permissions
            foreach ($companyManagementPermissions as $permissionName) {
                $allPermissions[] = Permission::firstOrCreate(
                    ['name' => $permissionName, 'guard_name' => 'web']
                );
            }

        // Create super admin role if it doesn't exist
        $superAdminRole = Role::firstOrCreate(['name' => 'super admin', 'guard_name' => 'web']);
        
        // Assign all permissions to super admin role (including company management)
        $superAdminRole->syncPermissions(collect($allPermissions)->pluck('id'));
        
        $this->command->info('Permissions created successfully!');
        $this->command->info('Screen Permissions:');
        foreach ($screenPermissions as $perm) {
            $this->command->info("  - {$perm}");
        }
        $this->command->info('User Management Permissions:');
        foreach ($userManagementPermissions as $perm) {
            $this->command->info("  - {$perm}");
        }
        $this->command->info('Roles Management Permissions:');
        foreach ($rolesManagementPermissions as $perm) {
            $this->command->info("  - {$perm}");
        }
            $this->command->info('Pages Management Permissions:');
            foreach ($pagesManagementPermissions as $perm) {
                $this->command->info("  - {$perm}");
            }
            $this->command->info('Deposit Form Management Permissions:');
            foreach ($depositFormManagementPermissions as $perm) {
                $this->command->info("  - {$perm}");
            }
            $this->command->info('Redeem Form Management Permissions:');
            foreach ($redeemFormManagementPermissions as $perm) {
                $this->command->info("  - {$perm}");
            }
            $this->command->info('Redeem Process Management Permissions:');
            foreach ($redeemProcessManagementPermissions as $perm) {
                $this->command->info("  - {$perm}");
            }
            $this->command->info('Payment Method Management Permissions:');
            foreach ($paymentMethodManagementPermissions as $perm) {
                $this->command->info("  - {$perm}");
            }
            $this->command->info('Company Management Permissions:');
            foreach ($companyManagementPermissions as $perm) {
                $this->command->info("  - {$perm}");
            }
            $this->command->info('Super Admin role created and assigned all permissions (including company management).');
            $this->command->info('Note: Admin role must be created manually through the Roles & Permissions screen.');
    }
}

