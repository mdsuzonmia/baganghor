<a class="guide-widget-item" href="<?= url_to('guides.show', $item['slug']) ?>">
    <?php if ($item['featured_image']): ?><img src="<?= esc(store_image($item['featured_image']), 'attr') ?>" alt="" width="64" height="64" loading="lazy" decoding="async"><?php else: ?><span class="guide-widget-fallback" aria-hidden="true">✿</span><?php endif ?>
    <span class="guide-widget-copy"><strong><?= esc($item['title']) ?></strong></span>
</a>
