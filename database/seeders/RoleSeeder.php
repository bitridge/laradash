<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
        ]);
        $admin->assignRole('admin');

        // Create a demo SEO provider
        $seoProvider = User::create([
            'name' => 'SEO Provider',
            'email' => 'seo@example.com',
            'password' => Hash::make('password'),
        ]);
        $seoProvider->assignRole('seo provider');

        // Create a demo customer
        $customer = User::create([
            'name' => 'Customer User',
            'email' => 'customer@example.com',
            'password' => Hash::make('password'),
        ]);
        $customer->assignRole('customer');
    }
} 