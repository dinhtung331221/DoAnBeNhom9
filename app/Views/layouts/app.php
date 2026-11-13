<?php use App\Core\Auth; $currentPath = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/'); ?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <title><?= e(isset($title) ? $title : '') ?> | TableTap</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&family=Newsreader:opsz,wght@6..72,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('assets/css/app.css?v=20261113') ?>">
</head>
<body class="app-shell">
<aside class="sidebar" id="sidebar">
    <a class="brand" href="<?= url('dashboard') ?>"><span class="brand-mark">T</span><span>TableTap</span></a>
    <nav class="sidebar-nav" aria-label="Điều hướng chính">
        <a class="nav-link" href="<?= url('dashboard') ?>" data-nav="dashboard"><i data-lucide="layout-dashboard"></i><span>Tổng quan</span></a>
        <?php if (Auth::hasRole(array('admin', 'manager'))): ?>
            <div class="nav-heading">Vận hành menu</div>
            <a class="nav-link" href="<?= url('admin/categories') ?>"><i data-lucide="tags"></i><span>Danh mục</span></a>
            <a class="nav-link" href="<?= url('admin/products') ?>"><i data-lucide="coffee"></i><span>Sản phẩm</span></a>
            <div class="nav-heading">Không gian</div>
            <a class="nav-link" href="<?= url('admin/tables') ?>"><i data-lucide="armchair"></i><span>Bàn phục vụ</span></a>
            <a class="nav-link" href="<?= url('admin/qr') ?>"><i data-lucide="qr-code"></i><span>Mã QR</span></a>
        <?php endif; ?>
        <?php if (Auth::hasRole(array('admin', 'manager', 'kitchen'))): ?>
            <a class="nav-link" href="<?= url('kitchen') ?>"><i data-lucide="chef-hat"></i><span>Màn hình bếp</span></a>
        <?php endif; ?>
    </nav>
    <div class="sidebar-user">
        <div class="avatar"><?= e(mb_strtoupper(mb_substr(Auth::user()['ho_ten'], 0, 1, 'UTF-8'), 'UTF-8')) ?></div>
        <div><strong><?= e(Auth::user()['ho_ten']) ?></strong><small><?= e(ucfirst(Auth::user()['role'])) ?></small></div>
        <form action="<?= url('logout') ?>" method="post">
            <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
            <button class="icon-button" title="Đăng xuất" aria-label="Đăng xuất"><i data-lucide="log-out"></i></button>
        </form>
    </div>
</aside>
<main class="main-content">
    <header class="topbar">
        <button class="icon-button mobile-menu" data-sidebar-toggle aria-label="Mở menu"><i data-lucide="menu"></i></button>
        <div><span class="eyebrow">TABLETAP / VẬN HÀNH</span><h1><?= e(isset($title) ? $title : '') ?></h1></div>
        <div class="topbar-actions"><span class="live-dot"></span><span>Hệ thống trực tuyến</span></div>
    </header>
    <div class="page-content">
        <?php if ($message = flash('success')): ?><div class="alert alert-success"><?= e($message) ?></div><?php endif; ?>
        <?php if ($message = flash('error')): ?><div class="alert alert-error"><?= e($message) ?></div><?php endif; ?>
        <?= $content ?>
    </div>
</main>
<div class="toast-region" id="toastRegion" aria-live="polite"></div>
<div class="modal-backdrop" id="modalBackdrop" hidden></div>
<script>window.APP_URL = <?= json_encode(app_config('url')) ?>;</script>
<script src="<?= asset('assets/vendor/lucide.min.js') ?>"></script>
<script src="<?= asset('assets/js/app.js') ?>"></script>
</body>
</html>

