<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <title><?= e(isset($title) ? $title : '') ?> | TableTap</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&family=Newsreader:opsz,wght@6..72,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= versioned_asset('assets/css/app.css') ?>">
</head>
<body class="customer-shell" data-table-id="<?= isset($table) ? (int) $table['ban_id'] : 0 ?>">
    <?= $content ?>
    <div class="toast-region" id="toastRegion" aria-live="polite"></div>
    <script>window.APP_URL = <?= json_encode(app_config('url')) ?>;</script>
    <script src="<?= asset('assets/vendor/lucide.min.js') ?>"></script>
    <script src="<?= versioned_asset('assets/js/app.js') ?>"></script>
    <script src="<?= asset('assets/vendor/jsqr.min.js') ?>"></script>
    <script src="<?= versioned_asset('assets/js/customer.js') ?>"></script>
    <script src="<?= versioned_asset('assets/js/menu.js') ?>"></script>
</body>
</html>

