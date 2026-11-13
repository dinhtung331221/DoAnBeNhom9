<!doctype html><html lang="vi"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width"><title>Không có quyền</title><link rel="stylesheet" href="<?= asset('assets/css/app.css') ?>"></head>
<?php $returnPath = \App\Core\Auth::hasRole('customer') ? 'customer' : (\App\Core\Auth::hasRole('kitchen') ? 'kitchen' : (\App\Core\Auth::hasRole('cashier') ? 'cashier' : 'dashboard')); ?>
<body class="status-page"><div><span class="status-code">403</span><h1>Bạn không có quyền truy cập</h1><p>Tài khoản hiện tại không được phép mở chức năng này.</p><a class="button button-primary" href="<?= url($returnPath) ?>">Quay lại</a></div></body></html>

