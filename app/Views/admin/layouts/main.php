<?php
$uri = trim(service('uri')->getPath(), '/');
$basePath = trim((string) parse_url(base_url(), PHP_URL_PATH), '/');
if ($basePath !== '' && ($uri === $basePath || str_starts_with($uri, $basePath . '/'))) {
    $uri = ltrim(substr($uri, strlen($basePath)), '/');
}
$isActive = static fn (string $path): bool => $uri === $path || str_starts_with($uri, $path . '/');
$role = (string) session('admin_role');
$permissions = $role === 'super_admin' ? [] : array_column(db_connect()->table('admin_role_permissions rp')
        ->join('admin_permissions p', 'p.id = rp.permission_id')
        ->join('admin_users u', 'u.role_id = rp.role_id')
        ->select('p.slug')->where('u.id', session('admin_id'))->get()->getResultArray(), 'slug');
$can = static fn (string $permission): bool => $role === 'super_admin' || in_array($permission, $permissions, true);
$groups = [
    'Orders' => [
        ['Orders', 'admin.orders', 'admin/orders', 'orders.view'],
        ['Reviews', 'admin.reviews', 'admin/reviews', 'reviews.view'],
        ['Shipments', 'admin.shipments', 'admin/shipments', 'shipments.view'],
        ['Order SMS', 'admin.sms', 'admin/sms', 'sms.manage'],
    ],
    'Catalog' => [
        ['Products', 'admin.products', 'admin/products', 'products.view'],
        ['Categories', 'admin.categories', 'admin/categories', 'categories.view'],
        ['Packages', 'admin.packages', 'admin/packages', 'packages.view'],
        ['Inventory', 'admin.inventory', 'admin/inventory', 'inventory.view'],
    ],
    'Guides & Content' => [
        ['Guides', 'admin.guides', 'admin/guides', 'guides.view'],
        ['Guide Categories', 'admin.guide.categories', 'admin/guide-categories', 'guide_categories.view'],
        ['Videos', 'admin.videos', 'admin/videos', 'videos.view'],
        ['Video Categories', 'admin.video.categories', 'admin/video-categories', 'video_categories.view'],
        ['Homepage Content', 'admin.homepage', 'admin/homepage', null],
        ['Policy Pages', 'admin.policies', 'admin/policies', null],
    ],
    'Management' => [
        ['Customers', 'admin.customers', 'admin/customers', 'all'],
        ['Delivery Zones', 'admin.delivery', 'admin/delivery-zones', 'delivery.manage'],
        ['Payment Methods', 'admin.payment.methods', 'admin/payment-methods', 'payments.verify'],
        ['Settings', 'admin.settings', 'admin/settings', null],
        ['Admin Users', 'admin.users', 'admin/admin-users', 'super_admin'],
    ],
];
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? 'Admin') ?> | Taharat Agro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --sidebar-width: 270px; --green: #21683b; --dark-green: #173d27; }
        body { margin: 0; background: #f4f7f3; overflow-x: hidden; }
        .admin-shell { min-height: 100vh; }
        .sidebar { position: fixed; inset: 0 auto 0 0; z-index: 1045; width: var(--sidebar-width); overflow-y: auto; background: var(--dark-green); transition: transform .2s ease; }
        .sidebar-brand { color: #fff; text-decoration: none; }
        .sidebar-nav { display: grid; gap: .2rem; }
        .sidebar-link { display: flex; align-items: center; justify-content: space-between; gap: .75rem; width: 100%; padding: .7rem 1rem; border: 0; border-radius: .5rem; color: #dce9df; background: transparent; text-align: left; text-decoration: none; transition: color .18s ease, background-color .18s ease, transform .18s ease; }
        .sidebar-link:hover, .sidebar-link.active, .sidebar-group[open] > summary { color: #fff; background: #2a6942; }
        .sidebar-link:hover { transform: translateX(2px); }
        .sidebar-group summary { cursor: pointer; list-style: none; user-select: none; }
        .sidebar-group summary::-webkit-details-marker { display: none; }
        .nav-chevron { width: 1rem; height: 1rem; flex: 0 0 auto; margin-left: auto; fill: none; stroke: currentColor; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; transition: transform .28s cubic-bezier(.4, 0, .2, 1); }
        .sidebar-group[open] .nav-chevron { transform: rotate(180deg); }
        .sidebar-subnav { display: grid; gap: .1rem; height: 0; margin: 0 0 0 .75rem; padding-left: .65rem; overflow: hidden; border-left: 1px solid transparent; opacity: 0; transition: height .28s cubic-bezier(.4, 0, .2, 1), opacity .2s ease, margin .28s ease, border-color .2s ease; }
        .sidebar-group[open] .sidebar-subnav { margin-top: .25rem; margin-bottom: .4rem; border-left-color: #63816c; opacity: 1; }
        .sidebar-group:not(.is-animating)[open] .sidebar-subnav { height: auto; }
        .sidebar-subnav .sidebar-link { padding: .55rem .8rem; font-size: .92rem; }
        .sidebar-subnav .sidebar-link.active { background: #347a4d; font-weight: 600; }
        .sidebar-link:focus-visible, .sidebar-group summary:focus-visible { outline: 2px solid #fff; outline-offset: 2px; }
        .admin-page { width: calc(100% - var(--sidebar-width)); min-width: 0; margin-left: var(--sidebar-width); }
        .topbar { position: sticky; top: 0; z-index: 1030; min-height: 64px; background: #fff; }
        .topbar-actions { margin-left: auto; }
        .sidebar-backdrop { display: none; position: fixed; inset: 0; z-index: 1040; background: rgba(13, 30, 19, .55); }
        .stat { border: 0; border-left: 4px solid var(--green); box-shadow: 0 .2rem 1rem #173d2710; }
        @media (max-width: 991.98px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .sidebar-backdrop.show { display: block; }
            .admin-page { width: 100%; margin-left: 0; }
        }
    </style>
</head>
<body>
<div class="admin-shell">
    <aside class="sidebar p-3" id="adminSidebar" aria-label="Admin navigation">
        <div class="d-flex align-items-center justify-content-between px-2 mb-4">
            <a class="sidebar-brand fs-4 fw-bold" href="<?= url_to('admin.dashboard') ?>">Taharat Agro</a>
            <button class="btn btn-sm btn-outline-light d-lg-none" type="button" data-sidebar-close aria-label="Close navigation">&times;</button>
        </div>
        <nav class="sidebar-nav">
            <a class="sidebar-link<?= $uri === 'admin' || $isActive('admin/dashboard') ? ' active' : '' ?>" href="<?= url_to('admin.dashboard') ?>" <?= $uri === 'admin' || $isActive('admin/dashboard') ? 'aria-current="page"' : '' ?>>Dashboard</a>
            <?php foreach ($groups as $groupName => $items): ?>
                <?php
                $visible = array_values(array_filter($items, static function (array $item) use ($can, $role): bool {
                    if ($item[3] === 'super_admin') return $role === 'super_admin';
                    if ($item[3] === 'all') return true;
                    if ($item[3] === null) return in_array($role, ['super_admin', 'admin'], true);
                    return $can($item[3]);
                }));
                if (!$visible) continue;
                $groupActive = false;
                foreach ($visible as $item) if ($isActive($item[2])) $groupActive = true;
                ?>
                <details class="sidebar-group" <?= $groupActive ? 'open' : '' ?>>
                    <summary class="sidebar-link nav-dropdown-toggle<?= $groupActive ? ' active' : '' ?>">
                        <span><?= esc($groupName) ?></span>
                        <svg class="nav-chevron" aria-hidden="true" viewBox="0 0 20 20"><path d="m5 7.5 5 5 5-5" /></svg>
                    </summary>
                    <div class="sidebar-subnav">
                        <?php foreach ($visible as $item): $active = $isActive($item[2]); ?>
                            <a class="sidebar-link<?= $active ? ' active' : '' ?>" href="<?= url_to($item[1]) ?>" <?= $active ? 'aria-current="page"' : '' ?>><?= esc($item[0]) ?></a>
                        <?php endforeach ?>
                    </div>
                </details>
            <?php endforeach ?>
            <a class="sidebar-link" href="<?= site_url('/') ?>" target="_blank" rel="noopener">Website <span aria-hidden="true">&#8599;</span></a>
        </nav>
    </aside>
    <div class="sidebar-backdrop" id="sidebarBackdrop" data-sidebar-close></div>
    <div class="admin-page d-flex flex-column">
        <header class="topbar border-bottom px-3 px-lg-4 d-flex align-items-center">
            <button class="btn btn-outline-success d-lg-none" type="button" id="sidebarToggle" aria-controls="adminSidebar" aria-expanded="false" aria-label="Open navigation">&#9776;</button>
            <div class="topbar-actions d-flex align-items-center gap-2 gap-md-3">
                <a href="<?= url_to('admin.profile') ?>" class="btn btn-sm btn-light text-dark text-decoration-none"><?= esc(session('admin_name')) ?></a>
                <form class="m-0" method="post" action="<?= url_to('admin.logout') ?>">
                    <?= csrf_field() ?>
                    <button class="btn btn-sm btn-outline-danger" type="submit">Logout</button>
                </form>
            </div>
        </header>
        <main class="p-3 p-lg-4 flex-grow-1">
            <?php include APPPATH . 'Views/partials/alerts.php' ?>
            <?= $this->renderSection('content') ?>
        </main>
        <footer class="px-4 py-3 text-muted small">&copy; <?= date('Y') ?> Taharat Agro</footer>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script>
(() => {
    const sidebar = document.getElementById('adminSidebar');
    const backdrop = document.getElementById('sidebarBackdrop');
    const toggle = document.getElementById('sidebarToggle');
    const setOpen = (open) => {
        sidebar.classList.toggle('show', open);
        backdrop.classList.toggle('show', open);
        toggle?.setAttribute('aria-expanded', String(open));
    };
    toggle?.addEventListener('click', () => setOpen(!sidebar.classList.contains('show')));
    document.querySelectorAll('[data-sidebar-close]').forEach((element) => element.addEventListener('click', () => setOpen(false)));
    window.addEventListener('resize', () => { if (window.innerWidth >= 992) setOpen(false); });

    document.querySelectorAll('.sidebar-group').forEach((group) => {
        const trigger = group.querySelector('summary');
        const panel = group.querySelector('.sidebar-subnav');
        let animationEnd;

        trigger.addEventListener('click', (event) => {
            event.preventDefault();
            window.clearTimeout(animationEnd);
            const isOpen = group.hasAttribute('open');
            const startHeight = panel.getBoundingClientRect().height;

            group.classList.add('is-animating');
            if (!isOpen) group.setAttribute('open', '');
            panel.style.height = `${startHeight}px`;

            requestAnimationFrame(() => {
                panel.style.height = isOpen ? '0px' : `${panel.scrollHeight}px`;
            });

            animationEnd = window.setTimeout(() => {
                if (isOpen) group.removeAttribute('open');
                panel.style.height = '';
                group.classList.remove('is-animating');
            }, 300);
        });
    });
})();
</script>
</body>
</html>
