<section class="auth-card">
    <div class="auth-heading"><span class="eyebrow">CHÀO MỪNG TRỞ LẠI</span><h1>Đăng nhập vận hành</h1><p>Truy cập khu vực dành cho quản lý và nhân viên.</p></div>
    <?php if ($message = flash('success')): ?><div class="alert alert-success"><?= e($message) ?></div><?php endif; ?>
    <?php if ($message = flash('error')): ?><div class="alert alert-error"><?= e($message) ?></div><?php endif; ?>
    <form action="<?= url('login') ?>" method="post" class="form-stack">
        <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
        <label class="field"><span>Tên đăng nhập</span><input name="username" autocomplete="username" required placeholder="admin"></label>
        <label class="field"><span>Mật khẩu</span><input type="password" name="password" autocomplete="current-password" required placeholder="Tối thiểu 8 ký tự"></label>
        <button class="button button-primary button-block" type="submit">Đăng nhập <span aria-hidden="true">→</span></button>
    </form>
    <p class="auth-switch">Chưa có tài khoản? <a href="<?= url('register') ?>">Đăng ký khách hàng</a></p>
    <div class="demo-note"><strong>Tài khoản demo</strong><span>admin / Admin@123</span></div>
</section>

