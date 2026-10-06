<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div><h1 class="h3 mb-1">Policy Pages</h1><p class="text-muted mb-0">ফুটারে যুক্ত তিনটি নীতিমালা পেজ এখান থেকে সম্পাদনা করুন।</p></div>
</div>
<div class="alert alert-info">প্রকাশের আগে ডেলিভারি, রিটার্ন ও তথ্য ব্যবহারের শর্ত আপনার বাস্তব ব্যবসায়িক প্রক্রিয়ার সঙ্গে মিলিয়ে নিন।</div>
<form method="post" action="<?= url_to('admin.policies') ?>">
    <?= csrf_field() ?>
    <?php foreach ($pages as $key => $page): ?>
        <section class="card border-0 shadow-sm mb-4">
            <div class="card-body p-3 p-lg-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                    <label class="h5 mb-0" for="policy-<?= esc($key, 'attr') ?>"><?= esc($page['title']) ?></label>
                    <a href="<?= url_to($page['route']) ?>" target="_blank" rel="noopener">পেজ দেখুন ↗</a>
                </div>
                <p class="small text-muted">নতুন শিরোনাম লিখতে লাইনের শুরুতে <code>## </code> দিন। অনুচ্ছেদের মাঝে একটি ফাঁকা লাইন রাখুন। HTML দেখানো হবে না।</p>
                <textarea class="form-control" id="policy-<?= esc($key, 'attr') ?>" name="<?= esc($key, 'attr') ?>" rows="13" minlength="50" maxlength="30000" required><?= esc(old($key, $content[$key] ?? '')) ?></textarea>
            </div>
        </section>
    <?php endforeach ?>
    <button type="submit" class="btn btn-success px-4">সব নীতিমালা সংরক্ষণ করুন</button>
</form>
<?= $this->endSection() ?>
