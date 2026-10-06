<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class GuideImageSeeder extends Seeder
{
    public function run(): void
    {
        $slugs = [
            'cow-dung-and-mustard-cake-guide',
            'rooftop-garden-beginner-checklist',
            'rooftop-garden-rain-and-heat-care',
            'tomato-growing-in-container-bangladesh',
            'chili-growing-in-pots',
            'fruit-trees-in-rooftop-containers',
            'fruit-tree-flower-drop-causes',
            'flower-plant-potting-care',
            'ornamental-plant-yellow-leaves',
            'potting-mix-for-rooftop-vegetables',
            'old-potting-soil-reuse-guide',
            'seedling-tray-bangla-guide',
            'healthy-seedling-selection',
            'aphids-on-garden-plants-control',
            'root-rot-container-prevention',
            'watering-potted-plants-correctly',
            'pruning-and-cleaning-garden-plants',
            'first-home-garden-common-mistakes',
            'small-balcony-garden-plan',
            'taharat-vermicompost-how-to-use',
            'taharat-tricho-compost-how-to-use',
            'winter-vegetables-home-garden-bangladesh',
            'monsoon-container-gardening-bangladesh',
        ];

        foreach ($slugs as $slug) {
            $path = 'uploads/guides/generated/' . $slug . '.webp';
            if (!is_file(FCPATH . $path)) throw new \RuntimeException("Missing guide image: {$path}");
            $this->db->table('guides')->where('slug', $slug)->update([
                'featured_image' => $path,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }
}
