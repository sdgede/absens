<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LeaveTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenants = \App\Models\Tenant::all();

        foreach ($tenants as $tenant) {
            \App\Models\LeaveType::insert([
                [
                    'tenant_id' => $tenant->id,
                    'name' => 'Cuti Tahunan',
                    'max_days_per_year' => 12,
                    'is_paid' => true,
                    'requires_attachment' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'tenant_id' => $tenant->id,
                    'name' => 'Izin',
                    'max_days_per_year' => null,
                    'is_paid' => false,
                    'requires_attachment' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'tenant_id' => $tenant->id,
                    'name' => 'Sakit',
                    'max_days_per_year' => null,
                    'is_paid' => true,
                    'requires_attachment' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            ]);
        }
    }
}
