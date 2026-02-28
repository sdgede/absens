<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenants = \App\Models\Tenant::with('branches')->get();

        foreach ($tenants as $tenant) {
            $branch = $tenant->branches->first();
            $rolePrefix = $tenant->type === 'company' ? 'employee' : 'student';

            // Create admin
            $admin = \App\Models\User::create([
                'tenant_id' => $tenant->id,
                'branch_id' => $branch->id,
                'name' => 'Admin ' . $tenant->name,
                'email' => 'admin@' . ($tenant->domain ?? 'example.com'),
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
            ]);
            $admin->assignRole('admin');

            // Create normal users
            for ($i = 1; $i <= 3; $i++) {
                $user = \App\Models\User::create([
                    'tenant_id' => $tenant->id,
                    'branch_id' => $branch->id,
                    'name' => ucfirst($rolePrefix) . ' ' . $i . ' ' . $tenant->name,
                    'email' => $rolePrefix . $i . '@' . ($tenant->domain ?? 'example.com'),
                    'password' => \Illuminate\Support\Facades\Hash::make('password'),
                ]);
                $user->assignRole($rolePrefix);
            }
        }
    }
}
