<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB as FacadesDB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Insert or update the group
        $group = FacadesDB::table('groups')->updateOrInsert(
            ['name' => 'Super Admin'],
            [
                'created_at' => now(),
                'updated_at' => now(),
                'createdBy' => 1
            ]
        );

        // Get the group ID
        $groupId = FacadesDB::table('groups')->where('name', 'Super Admin')->value('id');

        // Define modules
        $modules = [
            ['module_name' => 'Dashboard', 'module_page' => 'dashboard'],
            ['module_name' => 'User Management > Users', 'module_page' => 'users'],
            ['module_name' => 'User Management > Roles', 'module_page' => 'roles'],
            ['module_name' => 'Sales', 'module_page' => 'sales'],
            ['module_name' => 'Godowns', 'module_page' => 'godowns'],
            ['module_name' => 'Area', 'module_page' => 'area'],
            ['module_name' => 'Customers', 'module_page' => 'customers'],
            ['module_name' => 'Purchase', 'module_page' => 'purchase'],
            ['module_name' => 'Suppliers', 'module_page' => 'supplier'],
            ['module_name' => 'Suppliers > Supplier Payments', 'module_page' => 'suppliers_payment'],
            ['module_name' => 'Products', 'module_page' => 'product'],
            ['module_name' => 'Employees', 'module_page' => 'employees'],
            ['module_name' => 'Expenses', 'module_page' => 'expenses'],
            ['module_name' => 'Settings', 'module_page' => 'settings'],
            ['module_name' => 'Reports', 'module_page' => 'reports'],
        ];

        foreach ($modules as $module) {
            FacadesDB::table('group_modules')->updateOrInsert(
                ['module_page' => $module['module_page']],
                array_merge($module, ['created_at' => now(), 'updated_at' => now()])
            );
        }

        // Get all inserted modules
        $insertedModules = FacadesDB::table('group_modules')->get();

        // Insert or update group permissions
        foreach ($insertedModules as $module) {
            FacadesDB::table('group_permissions')->updateOrInsert(
                ['group_id' => $groupId, 'module_id' => $module->id],
                [
                    'module_name' => $module->module_name,
                    'module_page' => $module->module_page,
                    'access' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // Insert or update the Super Admin user
        FacadesDB::table('users')->updateOrInsert(
            ['email' => 'admin@admin.com'],
            [
                'group_id' => $groupId,
                'user_type' => 'superadmin',
                'username' => 'admin',
                'name' => 'Admin',
                'password' => Hash::make('12345678'),
                'code' => '12345678',
                'createdBy' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Insert or update the Super Admin user
        FacadesDB::table('customers')->updateOrInsert(
            ['email' => 'walkin@customer.com'],
            [
                'area_id' => 0,
                'name' => 'Walk-in Customer',
                'name_ur' => 'Walk-in Customer',
                'createdBy' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Define site settings
        $date = now();
        $site_settings = [
            ['key' => 'login_logo', 'value' => 'settings/default-image.png'],
            ['key' => 'invoice_logo', 'value' => 'settings/default-image.png'],
            ['key' => 'default_image', 'value' => 'settings/default-image.png'],
            ['key' => 'site_name', 'value' => 'Company Name'],
            ['key' => 'site_name_ur', 'value' => 'Company Name'],
            ['key' => 'site_address', 'value' => 'lakar mandi mamu kanjan'],
            ['key' => 'site_address_urdu', 'value' => 'lakar mandi mamu kanjan'],
            ['key' => 'mobile_numbers', 'value' => '03366667686,03366667486'],
            ['key' => 'footer_text', 'value' => 'Developed By: Irfan Mirza | Contact: +92336-666768...'],
            ['key' => 'favicon', 'value' => 'settings/default-image.png'],
            ['key' => 'timezone', 'value' => 'Asia/Karachi'],
            ['key' => 'currency', 'value' => 'PKR'],
            ['key' => 'invoice_logo2', 'value' => 'settings/default-image.png'],
            ['key' => 'billing_language', 'value' => 'english'],
        ];

        // Insert or update site settings
        foreach ($site_settings as $setting) {
            FacadesDB::table('site_settings')->updateOrInsert(
                ['key' => $setting['key']],
                array_merge($setting, ['createdBy' => 1, 'created_at' => $date, 'updated_at' => $date])
            );
        }
    }
}
