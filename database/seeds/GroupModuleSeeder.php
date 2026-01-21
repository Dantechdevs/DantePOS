<?php

use Illuminate\Database\Seeder;
use App\GroupModule;

class GroupModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $groupModule = [
        	[
        		'id' =>1,
        		'module_name' => 'Dashboard',
        		'module_page' => 'dashboard'
        	],
        	[
        		'id' =>2,
        		'module_name' => 'Users',
        		'module_page' => 'users'
        	],
        	[
        		'id' =>3,
        		'module_name' => 'Roles',
        		'module_page' => 'roles'
        	],
            [
                'id' =>4,
                'module_name' => 'Sales',
                'module_page' => 'sales'
            ],
            [
                'id' =>5,
                'module_name' => 'Customers',
                'module_page' => 'customers'
            ],
            [
                'id' =>6,
                'module_name' => 'Customers Payment',
                'module_page' => 'customers_payment'
            ],
            [
                'id' =>7,
                'module_name' => 'Purchase',
                'module_page' => 'purchase'
            ],
            [
                'id' =>8,
                'module_name' => 'Supplier',
                'module_page' => 'supplier'
            ],
            [
                'id' =>9,
                'module_name' => 'Suppliers Payment',
                'module_page' => 'suppliers_payment'
            ],
            [
                'id' =>10,
                'module_name' => 'Products',
                'module_page' => 'product'
            ],
            [
                'id' =>11,
                'module_name' => 'Employees',
                'module_page' => 'employees'
            ],
            [
                'id' =>12,
                'module_name' => 'Expenses',
                'module_page' => 'expenses'
            ],
            [
                'id' =>13,
                'module_name' => 'Settings',
                'module_page' => 'settings'
            ],
            [
                'id' =>14,
                'module_name' => 'Reports',
                'module_page' => 'reports'
            ],
            
        ];
        GroupModule::insert($groupModule);
    }
}
