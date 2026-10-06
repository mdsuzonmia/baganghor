<?php
namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class BanglaGuideSeeder extends Seeder
{
    public function run(): void
    {
        $articles = array_merge(
            require __DIR__ . '/data/guide_articles.php',
            require __DIR__ . '/data/guide_articles_more.php'
        );
        $sources = [
            'rooftop-garden-beginner-checklist' => ['কৃষি তথ্য সার্ভিস: ছাদ কৃষি', 'https://ais.gov.bd/pages/krishi-kotha/%E0%A6%9B%E0%A6%BE%E0%A6%A6-%E0%A6%95%E0%A7%83%E0%A6%B7%E0%A6%BF-bd0d47-6922d93bdbfbab28ce049e11'],
            'rooftop-garden-rain-and-heat-care' => ['কৃষি তথ্য সার্ভিস: ছাদ কৃষি', 'https://ais.gov.bd/pages/krishi-kotha/%E0%A6%9B%E0%A6%BE%E0%A6%A6-%E0%A6%95%E0%A7%83%E0%A6%B7%E0%A6%BF-bd0d47-6922d93bdbfbab28ce049e11'],
            'tomato-growing-in-container-bangladesh' => ['কৃষি তথ্য সার্ভিস: গ্রীষ্মকালীন সবজি', 'https://ais.gov.bd/pages/krishi-kotha/%E0%A6%97%E0%A7%8D%E0%A6%B0%E0%A7%80%E0%A6%B7%E0%A7%8D%E0%A6%AE%E0%A6%95%E0%A6%BE%E0%A6%B2%E0%A7%80%E0%A6%A8-%E0%A6%B8%E0%A6%AC%E0%A6%9C%E0%A6%BF%E0%A6%B0-%E0%A6%97%E0%A7%81%E0%A6%B0%E0%A7%81%E0%A6%A4%E0%A7%8D%E0%A6%AC-%E0%A6%93-%E0%A6%9A%E0%A6%BE%E0%A6%B7%E0%A6%BE%E0%A6%AC%E0%A6%BE%E0%A6%A6-%E0%A6%95%E0%A7%8C%E0%A6%B6%E0%A6%B2-f5423e-6922d951dbfbab28ce04b3c5'],
            'fruit-trees-in-rooftop-containers' => ['কৃষি তথ্য সার্ভিস: ছাদ বাগানে ফল চাষ', 'https://ais.gov.bd/pages/krishi-kotha/%E0%A6%9B%E0%A6%BE%E0%A6%A6-%E0%A6%AC%E0%A6%BE%E0%A6%97%E0%A6%BE%E0%A6%A8%E0%A7%87-%E0%A6%AB%E0%A6%B2-%E0%A6%9A%E0%A6%BE%E0%A6%B7-021080-6922d958dbfbab28ce04b8e4'],
            'seedling-tray-bangla-guide' => ['University of Minnesota Extension: চারা পচা প্রতিরোধ', 'https://extension.umn.edu/garden-and-home/yard-and-garden/gardening-in-minnesota/yard-and-garden-problems/how-to-prevent-seedling-damping-off'],
            'aphids-on-garden-plants-control' => ['University of Minnesota Extension: জাবপোকা ব্যবস্থাপনা', 'https://extension.umn.edu/garden-and-home/yard-and-garden/yard-and-garden-insects/aphids'],
            'watering-potted-plants-correctly' => ['University of Minnesota Extension: টবের গাছে পানি ও সার', 'https://extension.umn.edu/garden-and-home/yard-and-garden/gardening-in-minnesota/fertilizing-and-watering-container-plants'],
            'monsoon-container-gardening-bangladesh' => ['কৃষি তথ্য সার্ভিস: ছাদ কৃষি', 'https://ais.gov.bd/pages/krishi-kotha/%E0%A6%9B%E0%A6%BE%E0%A6%A6-%E0%A6%95%E0%A7%83%E0%A6%B7%E0%A6%BF-bd0d47-6922d93bdbfbab28ce049e11'],
        ];
        $now = date('Y-m-d H:i:s');
        foreach ($articles as $categorySlug => $categoryArticles) {
            $category = $this->db->table('guide_categories')->where('slug', $categorySlug)->get()->getRowArray();
            if (!$category) continue;
            $categorySeo = [];
            if (!$category['seo_title'] || $category['seo_title'] === $category['name'] . ' গাইড | Taharat Agro') $categorySeo['seo_title'] = $category['name'] . ' গাইড';
            if (!$category['seo_description']) $categorySeo['seo_description'] = mb_substr($category['description'] ?: $category['name'] . ' বিষয়ে সহজ বাংলা গাইড পড়ুন।', 0, 160);
            if ($categorySeo) $this->db->table('guide_categories')->where('id', $category['id'])->update($categorySeo);

            foreach ($categoryArticles as $article) {
                $content = '<p>' . esc($article['intro']) . '</p>';
                foreach ($article['sections'] as [$heading, $body]) {
                    $content .= '<h2>' . esc($heading) . '</h2><p>' . esc($body) . '</p>';
                }
                $content .= '<h2>মনে রাখুন</h2><p>গাছের জাত, পাত্র, মাটি ও স্থানীয় আবহাওয়া অনুযায়ী পরিচর্যা বদলাতে পারে। পণ্যের লেবেলের নির্দেশনা অনুসরণ করুন এবং সমস্যা গুরুতর হলে স্থানীয় কৃষি সম্প্রসারণ অফিস বা অভিজ্ঞ বিশেষজ্ঞের পরামর্শ নিন।</p>';
                if (isset($sources[$article['slug']])) {
                    [$label, $url] = $sources[$article['slug']];
                    $content .= '<p>আরও পড়ুন: <a href="' . esc($url, 'attr') . '">' . esc($label) . '</a>।</p>';
                }
                $description = mb_substr($article['intro'], 0, 158);
                $existing = $this->db->table('guides')->where('slug', $article['slug'])->get()->getRowArray();
                if ($existing) {
                    if ($existing['title'] === $article['title'] && $existing['created_at'] === $existing['updated_at'] && $existing['content'] !== $content) {
                        $this->db->table('guides')->where('id', $existing['id'])->update(['content' => $content]);
                    }
                    continue;
                }
                $this->db->table('guides')->insert([
                    'category_id' => $category['id'],
                    'title' => $article['title'],
                    'slug' => $article['slug'],
                    'excerpt' => $description,
                    'content' => $content,
                    'status' => 'published',
                    'featured' => 0,
                    'published_at' => $now,
                    'views' => 0,
                    'reading_time' => max(1, (int) ceil(count(preg_split('/\s+/u', trim(strip_tags($content)))) / 200)),
                    'seo_title' => $article['title'],
                    'seo_description' => $description,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $guideId = (int) $this->db->insertID();
                $this->db->table('guide_faqs')->insert([
                    'guide_id' => $guideId,
                    'question' => $article['faq'][0],
                    'answer' => $article['faq'][1],
                    'sort_order' => 0,
                ]);
                foreach ($article['products'] as $productSlug) {
                    $product = $this->db->table('products')->where('slug', $productSlug)->where('status', 'active')->where('deleted_at', null)->get()->getRowArray();
                    if ($product) $this->db->table('guide_products')->insert(['guide_id' => $guideId, 'product_id' => $product['id']]);
                }
            }
        }
    }
}
