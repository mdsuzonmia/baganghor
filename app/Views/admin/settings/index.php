<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>
<style>
.image-drop-zone{min-height:190px;border:2px dashed #adb5bd;border-radius:1rem;background:#f8f9fa;cursor:pointer;transition:border-color .2s,background-color .2s,box-shadow .2s}
.image-drop-zone:hover,.image-drop-zone:focus-visible,.image-drop-zone.is-dragging{border-color:#198754;background:#f0faf4;box-shadow:0 0 0 .2rem rgba(25,135,84,.12);outline:0}
.image-drop-zone.has-error{border-color:#dc3545;background:#fff5f5}
.image-preview{width:96px;height:96px;object-fit:contain;background:#fff;border:1px solid #dee2e6;border-radius:.75rem}
.image-preview.favicon-preview{width:64px;height:64px}
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div><h1 class="h3 mb-1">Settings</h1><p class="text-muted mb-0">Manage your store identity and contact details.</p></div>
</div>

<form method="post" enctype="multipart/form-data" action="<?= site_url('admin/settings') ?>">
    <?= csrf_field() ?>
    <div class="card border-0 p-4 mb-3">
        <h2 class="h5 mb-3">General</h2>
        <div class="row g-3 mb-4">
            <div class="col-md-6"><label class="form-label" for="site_name">Site Name</label><input class="form-control" id="site_name" name="site_name" value="<?= esc(old('site_name',$settings['site_name']??'')) ?>" required></div>
            <div class="col-md-6"><label class="form-label" for="site_tagline">Tagline</label><input class="form-control" id="site_tagline" name="site_tagline" value="<?= esc(old('site_tagline',$settings['site_tagline']??'')) ?>"></div>
        </div>
        <div class="row g-4">
            <?php foreach(['site_logo'=>['Logo','Recommended: transparent PNG or WebP','logo-preview',''],'favicon'=>['Favicon','Recommended: square PNG or WebP','favicon-preview','favicon-preview']] as $key=>$image): ?>
            <?php $path=$settings[$key]??''; ?>
            <div class="col-lg-6">
                <label class="form-label fw-semibold" for="<?= $key ?>"><?= $image[0] ?></label>
                <div class="image-drop-zone d-flex flex-column align-items-center justify-content-center text-center p-4" tabindex="0" role="button" data-input="<?= $key ?>" aria-controls="<?= $key ?>" aria-label="Upload <?= strtolower($image[0]) ?>">
                    <img id="<?= $image[2] ?>" class="image-preview <?= $image[3] ?> mb-3<?= $path?'':' d-none' ?>" src="<?= $path?base_url($path):'' ?>" alt="<?= esc($image[0]) ?> preview">
                    <div class="upload-prompt"><strong>Drop your <?= strtolower($image[0]) ?> here</strong><div class="small text-muted mt-1">or click to browse</div></div>
                    <div class="small text-muted mt-2"><?= $image[1] ?> · JPG, PNG, WebP · Max 5 MB</div>
                    <div class="selected-file small text-success mt-2" aria-live="polite"></div>
                </div>
                <input class="visually-hidden image-file-input" type="file" id="<?= $key ?>" name="<?= $key ?>" accept="image/jpeg,image/png,image/webp" data-preview="<?= $image[2] ?>">
                <div class="upload-error small text-danger mt-2" role="alert"></div>
                <?php if($path): ?><div class="form-check mt-2"><input class="form-check-input" type="checkbox" id="remove_<?= $key ?>" name="remove_<?= $key ?>" value="1"><label class="form-check-label text-danger" for="remove_<?= $key ?>">Remove current <?= strtolower($image[0]) ?></label></div><?php endif ?>
            </div>
            <?php endforeach ?>
        </div>
    </div>

    <?php $sections=['Contact'=>['support_mobile'=>'Support Mobile','support_email'=>'Support Email'],'Social'=>['facebook_url'=>'Facebook URL','youtube_url'=>'YouTube URL'],'Localization'=>['currency'=>'Currency','currency_symbol'=>'Currency Symbol','timezone'=>'Timezone','default_language'=>'Default Language']]; ?>
    <?php foreach($sections as $section=>$fields): ?><div class="card border-0 p-4 mb-3"><h2 class="h5 mb-3"><?= esc($section) ?></h2><div class="row g-3"><?php foreach($fields as $key=>$label): ?><div class="col-md-6"><label class="form-label" for="<?= esc($key) ?>"><?= esc($label) ?></label><input class="form-control" id="<?= esc($key) ?>" name="<?= esc($key) ?>" value="<?= esc(old($key,$settings[$key]??'')) ?>"></div><?php endforeach ?></div></div><?php endforeach ?>
    <div class="d-flex justify-content-end"><button class="btn btn-success px-4" type="submit">Save Settings</button></div>
</form>

<script>
document.querySelectorAll('.image-drop-zone').forEach(zone=>{
    const input=document.getElementById(zone.dataset.input);
    const preview=document.getElementById(input.dataset.preview);
    const status=zone.querySelector('.selected-file');
    const error=zone.parentElement.querySelector('.upload-error');
    const choose=()=>input.click();
    const showFile=file=>{
        error.textContent=''; zone.classList.remove('has-error');
        if(!file)return;
        if(!['image/jpeg','image/png','image/webp'].includes(file.type)){input.value='';error.textContent='Please choose a JPG, PNG, or WebP image.';zone.classList.add('has-error');return;}
        if(file.size>5*1024*1024){input.value='';error.textContent='The image must be no larger than 5 MB.';zone.classList.add('has-error');return;}
        preview.src=URL.createObjectURL(file); preview.classList.remove('d-none'); status.textContent=file.name;
        const remove=document.getElementById('remove_'+input.id); if(remove)remove.checked=false;
    };
    zone.addEventListener('click',choose);
    zone.addEventListener('keydown',e=>{if(e.key==='Enter'||e.key===' '){e.preventDefault();choose();}});
    input.addEventListener('change',()=>showFile(input.files[0]));
    ['dragenter','dragover'].forEach(type=>zone.addEventListener(type,e=>{e.preventDefault();zone.classList.add('is-dragging');}));
    ['dragleave','drop'].forEach(type=>zone.addEventListener(type,e=>{e.preventDefault();zone.classList.remove('is-dragging');}));
    zone.addEventListener('drop',e=>{const file=e.dataTransfer.files[0];if(!file)return;const transfer=new DataTransfer();transfer.items.add(file);input.files=transfer.files;showFile(file);});
});
</script>
<?= $this->endSection() ?>
