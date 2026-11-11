<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e(isset($title) ? $title : '') ?> | TableTap</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&family=Newsreader:opsz,wght@6..72,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('assets/css/app.css') ?>">
</head>
<body class="auth-page">
    <div class="auth-visual" aria-hidden="true"><div class="auth-quote"><span>QR TABLE ORDERING</span><strong>Phục vụ liền mạch,<br>từ bàn đến bếp.</strong></div></div>
    <main class="auth-main">
        <a class="brand brand-dark" href="<?= url() ?>"><span class="brand-mark">T</span><span>TableTap</span></a>
        <?= $content ?>
    </main>
</body>
</html>

