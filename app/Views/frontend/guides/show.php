<?= $this->extend('frontend/layouts/main') ?>
<?= $this->section('head') ?>
<?php
$url = url_to('guides.show', $guide['slug']);
$facebookShareUrl = 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode($url) . '&quote=' . rawurlencode($guide['title']);
$articleSchema = [
    '@context' => 'https://schema.org',
    '@type' => 'Article',
    'headline' => $guide['title'],
    'description' => $guide['seo_description'] ?: $guide['excerpt'],
    'datePublished' => date(DATE_ATOM, strtotime($guide['published_at'])),
    'dateModified' => date(DATE_ATOM, strtotime($guide['updated_at'])),
    'mainEntityOfPage' => $url,
    'publisher' => ['@type' => 'Organization', 'name' => 'Taharat Agro'],
];
if ($guide['featured_image']) $articleSchema['image'] = store_image($guide['featured_image']);
$crumbs = [['@type' => 'ListItem', 'position' => 1, 'name' => 'গাইড', 'item' => url_to('guides.index')]];
if ($category) $crumbs[] = ['@type' => 'ListItem', 'position' => 2, 'name' => $category['name'], 'item' => url_to('guides.category', $category['slug'])];
$crumbs[] = ['@type' => 'ListItem', 'position' => count($crumbs) + 1, 'name' => $guide['title'], 'item' => $url];
$schema = [$articleSchema, ['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => $crumbs]];
if ($faqs) $schema[] = ['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => array_map(static fn ($f) => ['@type' => 'Question', 'name' => $f['question'], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['answer']]], $faqs)];
?>
<link href="<?= base_url('assets/frontend/css/guides.css') ?>?v=<?= filemtime(FCPATH . 'assets/frontend/css/guides.css') ?>" rel="stylesheet">
<meta property="article:published_time" content="<?= esc(date(DATE_ATOM, strtotime($guide['published_at'])), 'attr') ?>">
<meta property="article:modified_time" content="<?= esc(date(DATE_ATOM, strtotime($guide['updated_at'])), 'attr') ?>">
<?php if ($guide['featured_image']): ?><meta property="og:image:alt" content="<?= esc($guide['title'], 'attr') ?>"><?php endif ?>
<script type="application/ld+json"><?= json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
<?= $this->endSection() ?>
<?= $this->section('content') ?>
<div class="guide-page">
    <div class="guide-breadcrumb-wrap">
        <div class="container">
            <nav class="guide-breadcrumb" aria-label="Breadcrumb">
                <a href="<?= url_to('store.home') ?>">হোম</a><span aria-hidden="true">/</span>
                <a href="<?= url_to('guides.index') ?>">গাইড</a>
                <?php if ($category): ?><span aria-hidden="true">/</span><a href="<?= url_to('guides.category', $category['slug']) ?>"><?= esc($category['name']) ?></a><?php endif ?>
                <span aria-hidden="true">/</span><span aria-current="page"><?= esc($guide['title']) ?></span>
            </nav>
        </div>
    </div>

    <header class="guide-hero">
        <div class="container guide-hero-inner">
            <div class="guide-hero-copy">
                <?php if ($category): ?><a class="guide-category-pill" href="<?= url_to('guides.category', $category['slug']) ?>"><?= esc($category['name']) ?></a><?php endif ?>
                <h1><?= esc($guide['title']) ?></h1>
                <?php if ($guide['excerpt']): ?><p class="guide-lead"><?= esc($guide['excerpt']) ?></p><?php endif ?>
                <div class="guide-meta"><span>তাহারাত এগ্রো গাইড</span><span aria-hidden="true">•</span><time datetime="<?= esc(date('Y-m-d', strtotime($guide['published_at'])), 'attr') ?>"><?= date('d/m/Y', strtotime($guide['published_at'])) ?></time></div>
                <div class="guide-share">
                    <span>শেয়ার করুন</span>
                    <a class="guide-share-facebook" href="<?= esc($facebookShareUrl, 'attr') ?>" target="_blank" rel="noopener noreferrer" aria-label="ফেসবুকে এই গাইডটি শেয়ার করুন">
                        <svg width="15" height="15" aria-hidden="true" focusable="false" viewBox="0 0 24 24"><path d="M13.7 21v-8h2.7l.4-3.1h-3.1v-2c0-.9.3-1.5 1.6-1.5H17V3.6c-.8-.1-1.6-.2-2.4-.2-2.4 0-4.1 1.5-4.1 4.2v2.3H7.8V13h2.7v8h3.2Z"/></svg>
                        <span>Facebook</span>
                    </a>
                </div>
            </div>
            <?php if ($guide['featured_image']): ?><div class="guide-hero-image"><img src="<?= esc(store_image($guide['featured_image']), 'attr') ?>" alt="<?= esc($guide['title'], 'attr') ?>" width="640" height="420" decoding="async" fetchpriority="high"></div><?php endif ?>
        </div>
    </header>

    <div class="container guide-main-layout">
        <article class="guide-main">
            <?php if ($toc): ?>
                <nav class="guide-toc" aria-label="সূচিপত্র">
                    <div class="guide-toc-heading"><span class="guide-toc-icon" aria-hidden="true">☷</span><h2>এই গাইডে যা থাকছে</h2></div>
                    <ol><?php foreach ($toc as $item): ?><li class="<?= $item['level'] === 3 ? 'is-sublevel' : '' ?>"><a href="#<?= esc($item['id'], 'attr') ?>"><?= esc($item['title']) ?></a></li><?php endforeach ?></ol>
                </nav>
            <?php endif ?>
            <div class="guide-article-body guide-content"><?= $content ?></div>
            <?php if ($faqs): ?>
                <section class="guide-faq" aria-labelledby="guide-faq-title">
                    <div class="guide-section-label">জানতে চান?</div><h2 id="guide-faq-title">সাধারণ প্রশ্ন ও উত্তর</h2>
                    <?php foreach ($faqs as $faq): ?><details class="guide-faq-item"><summary><?= esc($faq['question']) ?><span aria-hidden="true">+</span></summary><div><?= nl2br(esc($faq['answer'])) ?></div></details><?php endforeach ?>
                </section>
            <?php endif ?>
            <div class="guide-endnote"><strong>আরও জানতে চান?</strong><span>বাগান পরিচর্যার নতুন গাইডগুলো এক জায়গায় পড়ুন।</span><a href="<?= url_to('guides.index') ?>">সব গাইড দেখুন <span aria-hidden="true">→</span></a></div>
        </article>

        <aside class="guide-sidebar" aria-label="আরও গাইড ও ক্যাটাগরি">
            <div class="guide-sidebar-inner">
                <section class="guide-sidebar-widget guide-sidebar-search"><h2>গাইড খুঁজুন</h2><form method="get" action="<?= url_to('guides.search') ?>"><label class="visually-hidden" for="sidebar-guide-search">গাইড খুঁজুন</label><input id="sidebar-guide-search" name="q" type="search" placeholder="কী জানতে চান?" required><button type="submit" aria-label="খুঁজুন">⌕</button></form></section>
                <?php if ($latest): ?><section class="guide-sidebar-widget"><div class="guide-widget-heading"><h2>নতুন গাইড</h2><a href="<?= url_to('guides.index') ?>">সব দেখুন</a></div><div class="guide-widget-list"><?php foreach ($latest as $item): ?><?= view('frontend/guides/sidebar_item', ['item' => $item]) ?><?php endforeach ?></div></section><?php endif ?>
                <?php if ($popular): ?><section class="guide-sidebar-widget"><div class="guide-widget-heading"><h2>জনপ্রিয় গাইড</h2></div><div class="guide-widget-list"><?php foreach ($popular as $item): ?><?= view('frontend/guides/sidebar_item', ['item' => $item]) ?><?php endforeach ?></div></section><?php endif ?>
                <?php if ($guideCategories): ?><section class="guide-sidebar-widget"><div class="guide-widget-heading"><h2>গাইড ক্যাটাগরি</h2></div><div class="guide-category-list"><?php foreach ($guideCategories as $item): if (empty($categoryCounts[$item['id']])) continue; ?><a href="<?= url_to('guides.category', $item['slug']) ?>" <?= $category && $category['id'] == $item['id'] ? 'aria-current="page"' : '' ?>><span><?= esc($item['name']) ?></span><small><?= (int) $categoryCounts[$item['id']] ?></small></a><?php endforeach ?></div></section><?php endif ?>
            </div>
        </aside>
    </div>

    <?php if ($products || $packages): ?><section class="guide-related-shop"><div class="container"><div class="guide-related-heading"><div><span>এই গাইডে সহায়ক</span><h2>সম্পর্কিত পণ্য ও প্যাকেজ</h2></div></div><?php if ($products): ?><div class="product-grid"><?php foreach ($products as $product): ?><?= view('frontend/partials/product_card', ['product' => $product]) ?><?php endforeach ?></div><?php endif ?><?php if ($packages): ?><div class="package-grid <?= $products ? 'mt-4' : '' ?>"><?php foreach ($packages as $package): ?><?= view('frontend/partials/package_card', ['package' => $package]) ?><?php endforeach ?></div><?php endif ?></div></section><?php endif ?>
    <?php if ($related): ?><section class="guide-more"><div class="container"><div class="guide-related-heading"><div><span>পড়তে থাকুন</span><h2>আরও গাইড</h2></div><a href="<?= url_to('guides.index') ?>">সব গাইড <span aria-hidden="true">→</span></a></div><div class="row g-4"><?php foreach ($related as $item): ?><div class="col-md-4"><?= view('frontend/guides/card', ['guide' => $item]) ?></div><?php endforeach ?></div></div></section><?php endif ?>
</div>
<?= $this->endSection() ?>
