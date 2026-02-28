<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $company = \App\Models\Tenant::create([
            'name' => 'PT Sample Company',
            'type' => 'company',
            'domain' => 'samplecompany.com',
        ]);

        $company->branches()->create([
            'name' => 'Kantor Pusat',
            'lat' => -6.200000,
            'lng' => 106.816666,
            'radius_meter' => 100,
            'timezone' => 'Asia/Jakarta',
        ]);

        $school = \App\Models\Tenant::create([
            'name' => 'SMA Sample School',
            'type' => 'school',
            'domain' => 'sampleschool.sch.id',
        ]);

        $school->branches()->create([
            'name' => 'Gedung Utama',
            'lat' => -6.210000,
            'lng' => 106.826666,
            'radius_meter' => 150,
            'timezone' => 'Asia/Jakarta',
        ]);
    }
}
