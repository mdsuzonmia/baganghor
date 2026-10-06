<?= $this->extend('frontend/layouts/main') ?>
<?= $this->section('content') ?>
<?php
$reviewCount = (int)($reviewStats['review_count'] ?? 0);
$average = (float)($reviewStats['average_rating'] ?? 0);
$breakdown = $reviewStats['breakdown'] ?? [5=>0,4=>0,3=>0,2=>0,1=>0];
$recommended = ($breakdown[5] ?? 0) + ($breakdown[4] ?? 0);
$recommendedPercent = $reviewCount ? ($recommended / $reviewCount) * 100 : 0;
?>
<section class="review-form-page">
    <div class="container review-form-shell">
        <a href="<?= url_to('order.track') ?>" class="review-back-link">← অর্ডারে ফিরুন</a>
        <div class="review-form-card">
            <aside class="review-score-panel" aria-label="পণ্যের রেটিং সারাংশ">
                <div class="review-score-heading">
                    <strong><?= number_format($average, 1) ?></strong>
                    <div><span>Average Rating</span><div class="score-stars" aria-label="<?= number_format($average, 1) ?> out of 5"><?php for($star=1;$star<=5;$star++): ?><b class="<?= $average >= $star ? 'filled' : '' ?>">☆</b><?php endfor ?><small>(<?= $reviewCount ?> Reviews)</small></div></div>
                </div>
                <div class="review-recommendation"><strong><?= number_format($recommendedPercent, 2) ?>%</strong><span>Recommended <small>(<?= $recommended ?> of <?= $reviewCount ?>)</small></span></div>
                <div class="review-score-breakdown">
                    <?php for($star=5;$star>=1;$star--): $count=(int)($breakdown[$star]??0);$percent=$reviewCount?($count/$reviewCount*100):0; ?>
                    <div class="score-row"><span class="score-row-stars"><?= str_repeat('★',$star) ?><i><?= str_repeat('★',5-$star) ?></i></span><span class="score-track"><i style="width:<?= round($percent) ?>%"></i></span><small><?= round($percent) ?>%</small></div>
                    <?php endfor ?>
                </div>
            </aside>

            <div class="review-compose-panel">
                <div class="review-title-block"><h1>Submit Your Review</h1><span></span><p class="review-product-name"><?= esc($item['product_name']) ?> · Order <?= esc($item['order_no']) ?></p></div>
                <?php if($existing): ?>
                    <div class="alert alert-info">এই পণ্যের রিভিউ ইতিমধ্যে দেওয়া হয়েছে। বর্তমান অবস্থা: <strong><?= esc($existing['status']) ?></strong></div>
                <?php else: ?>
                    <p class="review-required-note">Your email address will not be published. Required fields are marked *</p>
                    <form class="review-compose-form" method="post" enctype="multipart/form-data" action="<?= url_to('review.store',$token,$item['id']) ?>">
                        <?= csrf_field() ?>
                        <label for="reviewText">Write your opinion about the product</label>
                        <textarea id="reviewText" name="review_text" rows="5" minlength="5" maxlength="3000" placeholder="Write Your Review Here..." required><?= esc(old('review_text')) ?></textarea>

                        <label class="review-upload-label" for="reviewImages">Upload Images (Optional)</label>
                        <label class="review-upload-zone" for="reviewImages" id="reviewUploadZone" tabindex="0">
                            <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M16 16l-4-4-4 4M12 12v8M20.4 17.5A5 5 0 0018 8.2 7 7 0 004.3 10.5 4.5 4.5 0 005 19h3"/></svg>
                            <strong>Drag &amp; Drop Images Here</strong>
                            <span>or click to browse files (3 max)</span>
                            <small id="reviewFileStatus">JPG, PNG or WebP; 5 MB per image</small>
                        </label>
                        <input class="visually-hidden" id="reviewImages" type="file" name="images[]" accept="image/jpeg,image/png,image/webp" multiple>

                        <div class="review-form-actions">
                            <div><label for="reviewRating">Your Rating:</label><select id="reviewRating" name="rating" required><option value="">Select One</option><option value="5" <?= old('rating')==5?'selected':'' ?>>Perfect</option><option value="4" <?= old('rating')==4?'selected':'' ?>>Good</option><option value="3" <?= old('rating')==3?'selected':'' ?>>Average</option><option value="2" <?= old('rating')==2?'selected':'' ?>>Not that bad</option><option value="1" <?= old('rating')==1?'selected':'' ?>>Very poor</option></select></div>
                            <button type="submit">SUBMIT REVIEW</button>
                        </div>
                    </form>
                <?php endif ?>
            </div>
        </div>
    </div>
</section>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded',()=>{
    const input=document.getElementById('reviewImages'),zone=document.getElementById('reviewUploadZone'),status=document.getElementById('reviewFileStatus');
    if(!input||!zone||!status)return;
    const showFiles=()=>{const files=[...input.files];status.textContent=files.length?files.map(file=>file.name).join(', '):'JPG, PNG or WebP; 5 MB per image';zone.classList.toggle('has-files',files.length>0);};
    input.addEventListener('change',()=>{if(input.files.length>3){input.value='';status.textContent='Please select no more than 3 images.';zone.classList.add('has-error');return;}zone.classList.remove('has-error');showFiles();});
    zone.addEventListener('keydown',event=>{if(event.key==='Enter'||event.key===' '){event.preventDefault();input.click();}});
    ['dragenter','dragover'].forEach(type=>zone.addEventListener(type,event=>{event.preventDefault();zone.classList.add('is-dragging');}));
    ['dragleave','drop'].forEach(type=>zone.addEventListener(type,event=>{event.preventDefault();zone.classList.remove('is-dragging');}));
    zone.addEventListener('drop',event=>{const files=[...event.dataTransfer.files].filter(file=>['image/jpeg','image/png','image/webp'].includes(file.type));if(files.length>3){status.textContent='Please select no more than 3 images.';zone.classList.add('has-error');return;}const transfer=new DataTransfer();files.forEach(file=>transfer.items.add(file));input.files=transfer.files;zone.classList.remove('has-error');showFiles();});
});
</script>
<?= $this->endSection() ?>
