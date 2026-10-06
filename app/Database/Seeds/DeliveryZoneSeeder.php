<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DeliveryZoneSeeder extends Seeder
{
    public function run(): void
    {
        $districtRows = $this->db->table('bd_districts')
            ->select('id, name_en')
            ->whereIn('name_en', ['Dhaka', 'Gazipur', 'Narayanganj', 'Chattogram', 'Cumilla', 'Sylhet', 'Rajshahi', 'Khulna', 'Bogura'])
            ->get()->getResultArray();
        $districts = array_column($districtRows, 'id', 'name_en');

        $zones = [
            ['name' => 'Dhaka District', 'district' => 'Dhaka', 'charge' => 80, 'free' => 1500, 'estimate' => '1–2 working days', 'priority' => 100],
            ['name' => 'Narayanganj', 'district' => 'Narayanganj', 'charge' => 110, 'free' => 1800, 'estimate' => '1–3 working days', 'priority' => 90],
            ['name' => 'Gazipur', 'district' => 'Gazipur', 'charge' => 110, 'free' => 1800, 'estimate' => '1–3 working days', 'priority' => 90],
            ['name' => 'Chattogram', 'district' => 'Chattogram', 'charge' => 130, 'free' => 2000, 'estimate' => '2–4 working days', 'priority' => 70],
            ['name' => 'Cumilla', 'district' => 'Cumilla', 'charge' => 130, 'free' => 2000, 'estimate' => '2–4 working days', 'priority' => 70],
            ['name' => 'Sylhet', 'district' => 'Sylhet', 'charge' => 130, 'free' => 2000, 'estimate' => '2–4 working days', 'priority' => 70],
            ['name' => 'Rajshahi', 'district' => 'Rajshahi', 'charge' => 130, 'free' => 2000, 'estimate' => '2–4 working days', 'priority' => 70],
            ['name' => 'Khulna', 'district' => 'Khulna', 'charge' => 130, 'free' => 2000, 'estimate' => '2–4 working days', 'priority' => 70],
            ['name' => 'Bogura', 'district' => 'Bogura', 'charge' => 130, 'free' => 2000, 'estimate' => '2–4 working days', 'priority' => 70],
        ];

        $this->db->transStart();
        foreach ($zones as $zone) {
            if (! isset($districts[$zone['district']])) continue;
            $data = [
                'name' => $zone['name'], 'zone_type' => 'district',
                'district_id' => (int) $districts[$zone['district']], 'upazila_id' => null,
                'delivery_charge' => $zone['charge'], 'free_delivery_minimum' => $zone['free'],
                'estimated_delivery_text' => $zone['estimate'], 'priority' => $zone['priority'],
                'status' => 'active', 'updated_at' => date('Y-m-d H:i:s'),
            ];
            $existing = $this->db->table('delivery_zones')->where([
                'zone_type' => 'district', 'district_id' => $data['district_id'], 'upazila_id' => null,
            ])->get()->getRowArray();
            if ($existing) $this->db->table('delivery_zones')->where('id', $existing['id'])->update($data);
            else $this->db->table('delivery_zones')->insert($data + ['created_at' => date('Y-m-d H:i:s')]);
        }

        $fallback = [
            'name' => 'Rest of Bangladesh', 'zone_type' => 'all_bangladesh',
            'district_id' => null, 'upazila_id' => null, 'delivery_charge' => 150,
            'free_delivery_minimum' => 2500, 'estimated_delivery_text' => '3–5 working days',
            'priority' => 0, 'status' => 'active', 'updated_at' => date('Y-m-d H:i:s'),
        ];
        $existingFallback = $this->db->table('delivery_zones')->where('zone_type', 'all_bangladesh')->get()->getRowArray();
        if ($existingFallback) $this->db->table('delivery_zones')->where('id', $existingFallback['id'])->update($fallback);
        else $this->db->table('delivery_zones')->insert($fallback + ['created_at' => date('Y-m-d H:i:s')]);
        $this->db->transComplete();
    }
}
