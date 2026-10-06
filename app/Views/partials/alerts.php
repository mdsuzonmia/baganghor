<?php foreach (['success','danger','warning','info'] as $type): ?>
    <?php if ($message = session()->getFlashdata($type)): ?><div class="alert alert-<?= esc($type) ?> alert-dismissible fade show" role="alert"><?= esc($message) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif ?>
<?php endforeach ?>
<?php if ($errors = session()->getFlashdata('errors')): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $error): ?><li><?= esc($error) ?></li><?php endforeach ?></ul></div><?php endif ?>
