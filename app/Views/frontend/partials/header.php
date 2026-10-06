<?php
$siteName = $settings['site_name'] ?? 'Taharat Agro';
$support = preg_replace('/[^0-9+]/', '', (string) ($settings['support_mobile'] ?? ''));
$whatsAppNumber = str_starts_with($support, '0') ? '880' . substr($support, 1) : ltrim($support, '+');
$whatsAppUrl = $whatsAppNumber ? 'https://wa.me/' . $whatsAppNumber . '?text=' . rawurlencode('আসসালামু আলাইকুম, তাহারাত এগ্রোর পণ্য সম্পর্কে জানতে চাই।') : '';
$cartCount = (new \App\Services\CartService())->count();
$navPath = parse_url(current_url(), PHP_URL_PATH) ?: '/';
$navActive = static fn (string $route): bool => $navPath === parse_url(url_to($route), PHP_URL_PATH);
$productCategories = (new \App\Models\ProductCategoryModel())->where('status', 'active')->orderBy('sort_order')->orderBy('name')->findAll();
$categoriesActive = $navActive('catalog.categories') || str_contains($navPath, '/category/');
$videoCategories = (new \App\Models\VideoCategoryModel())->where('status', 'active')->orderBy('sort_order')->orderBy('name')->findAll();
$videosActive = str_contains($navPath, '/videos');
?>
<a class="skip-link" href="#main-content">মূল কনটেন্টে যান</a>
<?php if ($support): ?>
<div class="utility-bar d-none d-lg-block">
    <div class="container utility-bar-inner">
        <div class="utility-message"><svg aria-hidden="true" viewBox="0 0 24 24"><path d="M3 6.75h11v9.5H3zM14 10h3.2l2.8 3v3.25h-6zM7 19a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm10 0a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z"/></svg><span>সারাদেশে দ্রুত ও নিরাপদ ডেলিভারি</span></div>
        <div class="utility-contact"><span>সহায়তা প্রয়োজন?</span><a href="tel:<?= esc($support, 'attr') ?>"><svg aria-hidden="true" viewBox="0 0 24 24"><path d="M7.2 3.2 10 7.7 8.3 9.4a14.3 14.3 0 0 0 6.3 6.3l1.7-1.7 4.5 2.8-.6 3c-.2.8-.9 1.4-1.8 1.4A15.6 15.6 0 0 1 2.8 5.6c0-.9.6-1.6 1.4-1.8l3-.6Z"/></svg><?= esc($settings['support_mobile']) ?></a></div>
    </div>
</div>
<?php endif ?>
<header class="store-header sticky-top bg-white">
    <div class="header-main"><div class="container"><div class="header-grid">
        <button class="btn menu-button d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileMenu" aria-label="মেনু খুলুন">☰</button>
        <a class="store-brand" href="<?= url_to('store.home') ?>"><?php if (!empty($settings['site_logo'])): ?><img src="<?= store_image($settings['site_logo']) ?>" width="170" height="52" alt="<?= esc($siteName, 'attr') ?>"><?php else: ?><span><?= esc($siteName) ?></span><?php endif ?></a>
        <form class="header-search" action="<?= url_to('store.search') ?>" method="get" role="search"><label class="visually-hidden" for="header-search">পণ্য খুঁজুন</label><svg aria-hidden="true" viewBox="0 0 24 24"><path d="m21 21-4.35-4.35m2.35-5.65a8 8 0 1 1-16 0 8 8 0 0 1 16 0Z"/></svg><input id="header-search" name="q" type="search" value="<?= esc((string) service('request')->getGet('q')) ?>" placeholder="পণ্য, বীজ বা সরঞ্জাম খুঁজুন..." autocomplete="off"><button type="submit"><span>খুঁজুন</span><svg aria-hidden="true" viewBox="0 0 24 24"><path d="m9 18 6-6-6-6"/></svg></button></form>
        <div class="header-actions d-none d-lg-flex">
            <a class="header-action" href="<?= url_to('customer.account') ?>"><span class="action-icon"><svg aria-hidden="true" viewBox="0 0 24 24"><path d="M20 21a8 8 0 0 0-16 0M12 13a5 5 0 1 0 0-10 5 5 0 0 0 0 10Z"/></svg></span><span><small>স্বাগতম</small><?= session('customer_logged_in') ? esc(explode(' ', trim((string) session('customer_name')))[0]) : lang('Store.account', [], 'bn') ?></span></a>
            <a class="header-action header-cart" href="<?= url_to('cart.index') ?>"><span class="action-icon"><svg aria-hidden="true" viewBox="0 0 24 24"><path d="M3 4h2l2.2 10.2a2 2 0 0 0 2 1.6h7.7a2 2 0 0 0 2-1.6L20 7H6M10 21a1.5 1.5 0 1 1-3 0m12 0a1.5 1.5 0 1 1-3 0"/></svg><b><?= $cartCount ?></b></span><span><small>আপনার</small><?= lang('Store.cart', [], 'bn') ?></span></a>
        </div>
    </div></div></div>
    <nav class="desktop-nav d-none d-lg-block" aria-label="প্রধান নেভিগেশন"><div class="container desktop-nav-inner">
        <a class="<?= $navActive('store.home') ? 'active' : '' ?>" href="<?= url_to('store.home') ?>">হোম</a>
        <a class="<?= $navActive('catalog.products') ? 'active' : '' ?>" href="<?= url_to('catalog.products') ?>">সকল পণ্য</a>
        <div class="nav-dropdown"><a class="nav-dropdown-toggle <?= $categoriesActive ? 'active' : '' ?>" href="<?= url_to('catalog.categories') ?>">ক্যাটাগরি <svg class="nav-chevron" aria-hidden="true" viewBox="0 0 12 8"><path d="m1 1.25 5 5 5-5"/></svg></a><?php if ($productCategories): ?><div class="nav-dropdown-menu category-dropdown-menu"><a class="all-items-link" href="<?= url_to('catalog.categories') ?>">সব ক্যাটাগরি <span aria-hidden="true">→</span></a><?php foreach ($productCategories as $item): ?><a href="<?= url_to('catalog.category', $item['slug']) ?>"><?= esc($item['name']) ?></a><?php endforeach ?></div><?php endif ?></div>
        <a class="<?= $navActive('catalog.packages') ? 'active' : '' ?>" href="<?= url_to('catalog.packages') ?>">গার্ডেনিং প্যাক</a>
        <a href="<?= url_to('guides.index') ?>">গাইড</a>
        <div class="nav-dropdown"><a class="nav-dropdown-toggle <?= $videosActive ? 'active' : '' ?>" href="<?= url_to('videos.index') ?>">ভিডিও <svg class="nav-chevron" aria-hidden="true" viewBox="0 0 12 8"><path d="m1 1.25 5 5 5-5"/></svg></a><?php if ($videoCategories): ?><div class="nav-dropdown-menu"><a href="<?= url_to('videos.index') ?>">সব ভিডিও</a><?php foreach ($videoCategories as $item): ?><a href="<?= site_url('videos?category=' . rawurlencode($item['slug'])) ?>"><?= esc($item['name']) ?></a><?php endforeach ?></div><?php endif ?></div>
        <a class="<?= str_contains($navPath, '/guides/category/hydroponics') ? 'active' : '' ?>" href="<?= url_to('guides.category', 'hydroponics') ?>">হাইড্রোপনিক্স</a>
        <?php if ($whatsAppUrl): ?><a class="whatsapp-nav-link ms-auto" href="<?= esc($whatsAppUrl, 'attr') ?>" target="_blank" rel="noopener noreferrer"><svg class="whatsapp-icon" aria-hidden="true" viewBox="0 0 24 24"><path d="M20.5 3.5A11.8 11.8 0 0 0 1.9 17.7L.2 24l6.4-1.7A11.8 11.8 0 0 0 24 11.9c0-3.2-1.2-6.1-3.5-8.4Z"/><path d="M17.9 14.8c-.3-.2-1.9-.9-2.2-1-.3-.1-.5-.2-.7.2-.2.3-.8 1-.9 1.2-.2.2-.3.2-.7.1-1.8-.9-3-1.6-4.2-3.6-.3-.6.3-.6.9-1.8.1-.2 0-.4 0-.6L9 6.8c-.2-.6-.5-.5-.7-.5H7.7c-.3 0-.7.1-1 .5-.3.4-1.3 1.3-1.3 3.2s1.4 3.7 1.6 4c.2.3 2.7 4.1 6.5 5.7 2.4 1 3.3 1.1 4.5.9.7-.1 2.1-.9 2.4-1.7.3-.8.3-1.5.2-1.7-.1-.1-.4-.2-.7-.4Z"/></svg><span>হোয়াটসঅ্যাপে অর্ডার ও পরামর্শ</span><strong><?= esc($settings['support_mobile']) ?></strong></a><?php endif ?>
    </div></nav>
</header>
<div class="offcanvas offcanvas-start" tabindex="-1" id="mobileMenu" aria-labelledby="mobileMenuTitle"><div class="offcanvas-header"><h2 class="offcanvas-title h5" id="mobileMenuTitle"><?= esc($siteName) ?></h2><button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="বন্ধ করুন"></button></div><div class="offcanvas-body mobile-menu">
    <a href="<?= url_to('store.home') ?>">হোম</a><a href="<?= url_to('catalog.products') ?>">সকল পণ্য</a>
    <details class="mobile-submenu" <?= $categoriesActive ? 'open' : '' ?>><summary>ক্যাটাগরি</summary><div><a href="<?= url_to('catalog.categories') ?>">সব ক্যাটাগরি</a><?php foreach ($productCategories as $item): ?><a href="<?= url_to('catalog.category', $item['slug']) ?>"><?= esc($item['name']) ?></a><?php endforeach ?></div></details>
    <a href="<?= url_to('catalog.packages') ?>">গার্ডেনিং প্যাক</a><a href="<?= url_to('guides.index') ?>">গাইড</a>
    <details class="mobile-submenu" <?= $videosActive ? 'open' : '' ?>><summary>ভিডিও</summary><div><a href="<?= url_to('videos.index') ?>">সব ভিডিও</a><?php foreach ($videoCategories as $item): ?><a href="<?= site_url('videos?category=' . rawurlencode($item['slug'])) ?>"><?= esc($item['name']) ?></a><?php endforeach ?></div></details>
    <a href="<?= url_to('guides.category', 'hydroponics') ?>">হাইড্রোপনিক্স</a>
    <?php if ($whatsAppUrl): ?><a class="mobile-whatsapp-link" href="<?= esc($whatsAppUrl, 'attr') ?>" target="_blank" rel="noopener noreferrer"><span>হোয়াটসঅ্যাপে অর্ডার ও পরামর্শ</span><strong><?= esc($settings['support_mobile']) ?></strong></a><?php endif ?>
    <a href="<?= url_to('customer.account') ?>">আমার অ্যাকাউন্ট</a><?php if (session('customer_logged_in')): ?><form method="post" action="<?= url_to('customer.logout') ?>"><?= csrf_field() ?><button type="submit">লগআউট</button></form><?php endif ?>
</div></div>
