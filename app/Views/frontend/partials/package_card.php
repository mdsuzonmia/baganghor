<article class="package-card h-100">
    <a class="package-image" href="<?= url_to('catalog.package', $package['slug']) ?>">
        <img src="<?= esc(store_card_image($package['main_image'] ?? null, 'package'), 'attr') ?>" width="640" height="440" loading="lazy" decoding="async" alt="<?= esc($package['name'], 'attr') ?>">
        <?php if (!empty($package['featured'])): ?><span>জনপ্রিয়</span><?php endif ?>
    </a>
    <div class="p-3 p-lg-4">
        <h3><a href="<?= url_to('catalog.package', $package['slug']) ?>"><?= esc($package['name']) ?></a></h3>
        <?php if (!empty($package['short_description'])): ?><p class="text-muted clamp-2"><?= esc($package['short_description']) ?></p><?php endif ?>
        <div class="package-pricing">
            <div><small>নিয়মিত</small><del><?= format_bdt($package['regular_total']) ?></del></div>
            <div><small>প্যাক মূল্য</small><strong><?= format_bdt($package['package_price']) ?></strong></div>
            <?php if ($package['savings'] > 0): ?><div class="save-pill">সাশ্রয় <?= format_bdt($package['savings']) ?></div><?php endif ?>
        </div>
        <div class="d-flex justify-content-between align-items-center mt-3">
            <span class="stock-text <?= $package['available'] > 0 ? 'in' : 'out' ?>"><?= $package['available'] > 0 ? 'স্টক আছে' : 'স্টক শেষ' ?></span>
            <a class="btn btn-primary btn-sm" href="<?= url_to('catalog.package', $package['slug']) ?>">প্যাকটি দেখুন</a>
        </div>
    </div>
</article>
