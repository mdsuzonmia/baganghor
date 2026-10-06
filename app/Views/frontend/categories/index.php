<?= $this->extend('frontend/layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-hero"><div class="container"><?= view('frontend/partials/breadcrumb', ['items' => [['label' => 'ক্যাটাগরি']]]) ?><h1>পণ্যের ক্যাটাগরি</h1><p>আপনার প্রয়োজন অনুযায়ী ক্যাটাগরি বেছে নিন।</p></div></div>
<section class="container section" aria-labelledby="category-list-title">
    <h2 id="category-list-title" class="h4 mb-4">সব ক্যাটাগরি</h2>
    <?php if ($categories): ?>
        <div class="category-grid">
            <?php foreach ($categories as $category): ?>
                <?= view('frontend/partials/category_card', ['category' => $category]) ?>
            <?php endforeach ?>
        </div>
    <?php else: ?>
        <div class="empty-state"><p>বর্তমানে কোনো ক্যাটাগরি পাওয়া যায়নি।</p><a class="btn btn-primary" href="<?= url_to('catalog.products') ?>">সব পণ্য দেখুন</a></div>
    <?php endif ?>
</section>
<?= $this->endSection() ?>
