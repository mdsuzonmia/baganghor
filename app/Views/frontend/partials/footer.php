<?php $support = preg_replace('/[^0-9+]/', '', (string) ($settings['support_mobile'] ?? '')); ?>
<footer class="store-footer">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4">
                <h2 class="h4 text-white"><?= esc($settings['site_name'] ?? 'Taharat Agro') ?></h2>
                <p>Taharat Agro বাংলাদেশের কৃষি ও বাগানপ্রেমীদের জন্য একটি অনলাইন শপ। গাছের যত্ন, বাগান ও কৃষিকাজের প্রয়োজনীয় পণ্য সহজে খুঁজে পেতে আমরা কাজ করছি।</p>
                <?php if ($support): ?><a href="tel:<?= esc($support, 'attr') ?>">☎ <?= esc($settings['support_mobile']) ?></a><?php endif ?>
            </div>
            <div class="col-6 col-lg-2">
                <h3>কেনাকাটা</h3>
                <a href="<?= url_to('catalog.products') ?>">সকল পণ্য</a>
                <a href="<?= url_to('catalog.categories') ?>">ক্যাটাগরি</a>
                <a href="<?= url_to('catalog.packages') ?>">গার্ডেনিং প্যাক</a>
            </div>
            <div class="col-6 col-lg-3">
                <h3>তথ্য</h3>
                <a href="<?= url_to('policy.delivery') ?>">ডেলিভারি নীতি</a>
                <a href="<?= url_to('policy.returns') ?>">রিটার্ন নীতি</a>
                <a href="<?= url_to('policy.privacy') ?>">গোপনীয়তা ও শর্তাবলি</a>
            </div>
            <div class="col-lg-3">
                <h3>আমাদের সাথে থাকুন</h3>
                <div class="footer-social-links">
                    <a class="footer-social-link" href="<?= esc(($settings['facebook_url'] ?? '') ?: 'https://www.facebook.com/taharatagro', 'attr') ?>" target="_blank" rel="noopener noreferrer">
                        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M22 12a10 10 0 1 0-11.56 9.88v-6.99H7.9V12h2.54V9.8c0-2.5 1.49-3.89 3.77-3.89 1.09 0 2.23.2 2.23.2v2.46h-1.26c-1.24 0-1.63.77-1.63 1.56V12h2.77l-.44 2.89h-2.33v6.99A10 10 0 0 0 22 12Z"/></svg>
                        <span>Facebook</span>
                    </a>
                    <a class="footer-social-link" href="<?= esc(($settings['youtube_url'] ?? '') ?: 'https://www.youtube.com/@taharat-agro', 'attr') ?>" target="_blank" rel="noopener noreferrer">
                        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M23.5 6.2a2.96 2.96 0 0 0-2.08-2.1C19.58 3.6 12 3.6 12 3.6s-7.58 0-9.42.5A2.96 2.96 0 0 0 .5 6.2 31.4 31.4 0 0 0 0 12a31.4 31.4 0 0 0 .5 5.8 2.96 2.96 0 0 0 2.08 2.1c1.84.5 9.42.5 9.42.5s7.58 0 9.42-.5a2.96 2.96 0 0 0 2.08-2.1A31.4 31.4 0 0 0 24 12a31.4 31.4 0 0 0-.5-5.8ZM9.6 15.6V8.4L15.9 12l-6.3 3.6Z"/></svg>
                        <span>YouTube</span>
                    </a>
                </div>
            </div>
        </div>
        <hr>
        <p class="small mb-0">© <?= date('Y') ?> <?= esc($settings['site_name'] ?? 'Taharat Agro') ?>। সর্বস্বত্ব সংরক্ষিত।</p>
    </div>
</footer>
