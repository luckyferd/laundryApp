<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Seed the default laundry price list.
     *
     * @return void
     */
    public function run()
    {
        $services = [
            ['name' => 'Cuci Kering Setrika', 'unit' => 'kg', 'price' => 7000, 'duration_days' => 2],
            ['name' => 'Cuci Kering', 'unit' => 'kg', 'price' => 5000, 'duration_days' => 2],
            ['name' => 'Setrika Saja', 'unit' => 'kg', 'price' => 4000, 'duration_days' => 1],
            ['name' => 'Express (1 Hari)', 'unit' => 'kg', 'price' => 12000, 'duration_days' => 1],
            ['name' => 'Bed Cover', 'unit' => 'pcs', 'price' => 25000, 'duration_days' => 3],
            ['name' => 'Selimut', 'unit' => 'pcs', 'price' => 20000, 'duration_days' => 3],
            ['name' => 'Jas / Blazer', 'unit' => 'pcs', 'price' => 30000, 'duration_days' => 3],
            ['name' => 'Karpet', 'unit' => 'meter', 'price' => 15000, 'duration_days' => 4],
        ];

        foreach ($services as $service) {
            Service::updateOrCreate(['name' => $service['name']], $service + ['is_active' => true]);
        }
    }
}
