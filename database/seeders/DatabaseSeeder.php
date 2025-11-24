<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Role;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed roles
        $roles = ['Admin', 'Kasir', 'Customer'];
        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }

        // Seed categories
        $categories = ['Coffee', 'Tea', 'Dessert', 'Snack', 'Beverage'];
        foreach ($categories as $categoryName) {
            \App\Models\Category::firstOrCreate(['name' => $categoryName]);
        }

        // Seed users with specific roles
        $adminRole = Role::where('name', 'Admin')->first();
        $kasirRole = Role::where('name', 'Kasir')->first();
        $customerRole = Role::where('name', 'Customer')->first();

        \App\Models\User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@cafein.com',
            'role_id' => $adminRole->id,
        ]);

        \App\Models\User::factory()->create([
            'name' => 'Kasir User',
            'email' => 'kasir@cafein.com',
            'role_id' => $kasirRole->id,
        ]);

        \App\Models\User::factory()->create([
            'name' => 'Customer User',
            'email' => 'customer@cafein.com',
            'role_id' => $customerRole->id,
        ]);

        // Seed additional random users
        \App\Models\User::factory(10)->create();

        // Seed menus
        \App\Models\Menu::factory(20)->create();

        // Seed orders and payments
        \App\Models\Order::factory(15)->create()->each(function ($order) {
            // Create payment for some orders
            if (rand(0, 1)) {
                \App\Models\Payment::factory()->create([
                    'order_id' => $order->id,
                    'amount' => $order->total_price,
                ]);
            }
        });
    }
}
