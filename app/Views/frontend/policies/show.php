<?= $this->extend('frontend/layouts/main') ?>
<?= $this->section('content') ?>
<section class="py-4 py-lg-5">
    <div class="container" style="max-width:1100px">
        <nav aria-label="Breadcrumb" class="small mb-4"><a href="<?= url_to('store.home') ?>">হোম</a> <span class="text-muted mx-2">/</span> <?= esc($page['title']) ?></nav>
        <div class="row g-4 g-lg-5">
            <div class="col-lg-8">
                <article class="bg-white rounded-4 border shadow-sm p-4 p-lg-5">
                    <h1 class="h2 mb-2"><?= esc($page['title']) ?></h1>
                    <p class="text-muted mb-4">Taharat Agro-তে কেনাকাটা ও সেবা সংক্রান্ত তথ্য।</p>
                    <?php foreach (preg_split('/\R\s*\R/u', trim($page['body'])) ?: [] as $block): ?>
                        <?php if (str_starts_with($block, '## ')): ?>
                            <?php [$heading, $rest] = array_pad(explode("\n", substr($block, 3), 2), 2, ''); ?>
                            <h2 class="h5 mt-4 mb-2"><?= esc(trim($heading)) ?></h2>
                            <?php if (trim($rest) !== ''): ?><p class="text-secondary lh-lg mb-3"><?= nl2br(esc(trim($rest))) ?></p><?php endif ?>
                        <?php else: ?>
                            <p class="text-secondary lh-lg mb-3"><?= nl2br(esc(trim($block))) ?></p>
                        <?php endif ?>
                    <?php endforeach ?>
                </article>
            </div>
            <div class="col-lg-4">
                <aside class="bg-white rounded-4 border p-4 mb-3">
                    <h2 class="h6 mb-3">অন্যান্য তথ্য</h2>
                    <?php foreach (\App\Services\PolicyPageService::PAGES as $key => $item): ?>
                        <a class="d-block py-2 text-decoration-none<?= $key === $page['key'] ? ' fw-bold text-success' : '' ?>" href="<?= url_to($item['route']) ?>"><?= esc($item['title']) ?></a>
                    <?php endforeach ?>
                </aside>
                <aside class="bg-success-subtle rounded-4 p-4">
                    <h2 class="h6 mb-2">আরও সাহায্য দরকার?</h2>
                    <p class="small mb-2">অর্ডার নম্বরসহ আমাদের সাথে যোগাযোগ করুন।</p>
                    <?php $mobile = preg_replace('/[^0-9+]/', '', (string) ($settings['support_mobile'] ?? '')); ?>
                    <?php if ($mobile): ?><a class="d-block" href="tel:<?= esc($mobile, 'attr') ?>"><?= esc($settings['support_mobile']) ?></a><?php endif ?>
                    <?php if (!empty($settings['support_email'])): ?><a class="d-block text-break" href="mailto:<?= esc($settings['support_email'], 'attr') ?>"><?= esc($settings['support_email']) ?></a><?php endif ?>
                </aside>
            </div>
        </div>
    </div>
</section>
<?= $this->endSection() ?>
