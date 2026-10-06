<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class GardeningPackageSeeder extends Seeder
{
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');

        $products = [
            'geo-grow-bag' => ['Geo Grow Bag (12×12 ইঞ্চি)', 'TA-GB-1212', 180, 'টেকসই, পুনর্ব্যবহারযোগ্য জিও ফেব্রিক গ্রো ব্যাগ।'],
            'ready-soil-mix' => ['Ready Soil Mix (10 KG)', 'TA-SOIL-10KG', 350, 'ছাদ ও বারান্দার টবের জন্য প্রস্তুত ঝুরঝুরে মাটির মিশ্রণ।'],
            'vegetable-seed-pack' => ['সবজি বীজ প্যাক', 'TA-SEED-VEG', 120, 'মৌসুমি সবজির মানসম্মত বীজের প্যাক।'],
            'tomato-seedling' => ['টমেটো বীজ/চারা', 'TA-SEED-TOM', 80, 'বাসায় চাষের উপযোগী টমেটো বীজ বা সুস্থ চারা।'],
            'gardening-guide' => ['ব্যবহার নির্দেশিকা', 'TA-GUIDE-01', 50, 'নতুন বাগানির জন্য ধাপে ধাপে বাংলা নির্দেশিকা।'],
            'organic-pest-control' => ['Organic Pest Control (500 ML)', 'TA-PEST-500', 220, 'বাড়ির বাগানের জন্য জৈব বালাই নিয়ন্ত্রণ দ্রবণ।'],
            'coco-peat-block' => ['Coco Peat Block (5 KG)', 'TA-CP-5KG', 450, 'পানি ধারণক্ষম পরিবেশবান্ধব কোকো পিট ব্লক।'],
            'basic-gardening-tools' => ['Basic Gardening Tools Set', 'TA-TOOLS-SET', 650, 'হ্যান্ড ট্রাওয়েল, ফর্ক, প্রুনার ও ছোট ওয়াটারিং ক্যানের সেট।'],
        ];

        foreach ($products as $slug => [$name, $sku, $price, $description]) {
            $existing = $this->db->table('products')->where('slug', $slug)->get()->getRowArray();
            $data = [
                'name' => $name, 'slug' => $slug, 'short_description' => $description,
                'description' => $description, 'regular_price' => $price, 'sale_price' => null,
                'purchase_cost' => round($price * .62, 2), 'sku' => $sku, 'stock_type' => 'simple',
                'stock_quantity' => 100, 'low_stock_threshold' => 10, 'manage_stock' => 1,
                'allow_backorder' => 0, 'unit' => 'টি', 'featured' => 0, 'status' => 'active',
                'updated_at' => $now,
            ];
            if ($existing) {
                $this->db->table('products')->where('id', $existing['id'])->update($data);
            } else {
                $data['product_code'] = 'TAH-PROD-' . strtoupper(substr(md5($slug), 0, 8));
                $data['created_at'] = $now;
                $this->db->table('products')->insert($data);
            }
        }

        $packages = [
            'chad-bagan-starter-pack' => [
                'ছাদ বাগান স্টার্টার প্যাক', 'TA-PACK-START', 899, 'নতুন বাগানি',
                'ছাদ বা বারান্দায় প্রথম বাগান শুরু করার সহজ সমাধান। প্রয়োজনীয় উপকরণ ও বাংলা নির্দেশিকা একসাথে।',
                'uploads/packages/gardening/starter-pack.png',
                [['geo-grow-bag', null, 2], ['ready-soil-mix', null, 1], ['vermicompost', '2 KG', 1], ['vegetable-seed-pack', null, 1], ['gardening-guide', null, 1]],
            ],
            'tomato-grow-pack' => [
                'টমেটো গ্রো প্যাক', 'TA-PACK-TOMATO', 799, 'টমেটো চাষ',
                'বাড়িতে স্বাস্থ্যকর টমেটো ফলানোর জন্য মাটি, পুষ্টি ও বীজ/চারা সহ সম্পূর্ণ প্যাক।',
                'uploads/packages/gardening/tomato-pack.png',
                [['geo-grow-bag', null, 1], ['ready-soil-mix', null, 1], ['tomato-seedling', null, 1], ['vermicompost', '2 KG', 1], ['bone-meal-organic-fertilizer', '500G', 1], ['mustard-oil-cake', '500GM', 1]],
            ],
            'vegetable-grow-pack' => [
                'সবজি গ্রো প্যাক', 'TA-PACK-VEG', 1099, 'বাসার সবজি',
                'ছোট জায়গায় তিন ধরনের মৌসুমি সবজি চাষের জন্য দুইটি গ্রো ব্যাগসহ প্রস্তুত সমাধান।',
                'uploads/packages/gardening/vegetable-pack.png',
                [['geo-grow-bag', null, 2], ['ready-soil-mix', null, 1], ['vegetable-seed-pack', null, 3], ['vermicompost', '2 KG', 1], ['mustard-oil-cake', '500GM', 1]],
            ],
            'organic-garden-care-pack' => [
                'অর্গানিক গার্ডেন কেয়ার প্যাক', 'TA-PACK-CARE', 449, 'আগে থেকেই বাগান আছে',
                'চলমান বাগানের নিয়মিত পুষ্টি, মাটির স্বাস্থ্য ও জৈব বালাই ব্যবস্থাপনার সম্পূর্ণ কেয়ার প্যাক।',
                'uploads/packages/gardening/organic-care-pack.png',
                [['vermicompost', '1 KG', 1], ['trichoderma-compost', '1 KG', 1], ['mustard-oil-cake', '500GM', 1], ['bone-meal-organic-fertilizer', '500G', 1], ['organic-pest-control', null, 1]],
            ],
            'complete-rooftop-garden-pack' => [
                'কমপ্লিট ছাদ বাগান প্যাক', 'TA-PACK-COMPLETE', 2399, 'Complete solution',
                'পরিকল্পনা থেকে পরিচর্যা—চারটি গ্রো ব্যাগ, মাটি, সার, বীজ ও প্রয়োজনীয় টুলসসহ পূর্ণাঙ্গ ছাদ বাগান সমাধান।',
                'uploads/packages/gardening/complete-pack.png',
                [['geo-grow-bag', null, 4], ['ready-soil-mix', null, 1], ['coco-peat-block', null, 1], ['vermicompost', '2 KG', 1], ['trichoderma-compost', '2 KG', 1], ['bone-meal-organic-fertilizer', '500G', 1], ['mustard-oil-cake', '500GM', 1], ['vegetable-seed-pack', null, 1], ['basic-gardening-tools', null, 1]],
            ],
        ];

        // Remove the development verification card from the public package listing.
        $this->db->table('product_packages')->like('slug', 'package-verification-', 'after')->update(['status' => 'draft', 'updated_at' => $now]);

        $sort = 0;
        foreach ($packages as $slug => $definition) {
            $sort++;
            [$name, $code, $price, $target, $description, $image, $items] = $definition;
            $resolved = [];
            $regularTotal = 0;
            foreach ($items as [$productSlug, $variantName, $quantity]) {
                $product = $this->db->table('products')->where('slug', $productSlug)->get()->getRowArray();
                if (!$product) throw new \RuntimeException("Missing package product: {$productSlug}");
                $variant = $variantName ? $this->db->table('product_variants')->where(['product_id' => $product['id'], 'variant_name' => $variantName])->get()->getRowArray() : null;
                if ($variantName && !$variant) throw new \RuntimeException("Missing variant: {$productSlug} / {$variantName}");
                $regularTotal += (float) ($variant['regular_price'] ?? $product['regular_price']) * $quantity;
                $resolved[] = ['product_id' => $product['id'], 'variant_id' => $variant['id'] ?? null, 'quantity' => $quantity];
            }
            $package = $this->db->table('product_packages')->where('slug', $slug)->get()->getRowArray();
            $data = [
                'name' => $name, 'slug' => $slug, 'package_code' => $code,
                'short_description' => "কার জন্য: {$target} — {$description}", 'description' => $description,
                'regular_total' => $regularTotal, 'package_price' => $price, 'main_image' => $image,
                'featured' => $sort <= 4 ? 1 : 0, 'status' => 'active', 'seo_title' => $name . ' | Taharat Agro',
                'seo_description' => $description, 'sort_order' => $sort, 'updated_at' => $now,
            ];
            if ($package) {
                $this->db->table('product_packages')->where('id', $package['id'])->update($data);
                $packageId = (int) $package['id'];
            } else {
                $data['created_at'] = $now;
                $this->db->table('product_packages')->insert($data);
                $packageId = (int) $this->db->insertID();
            }
            $this->db->table('product_package_items')->where('package_id', $packageId)->delete();
            foreach ($resolved as $itemSort => $item) {
                $this->db->table('product_package_items')->insert($item + ['package_id' => $packageId, 'sort_order' => $itemSort, 'created_at' => $now]);
            }
        }
    }
}
