<section class="auth-card">
    <div class="auth-heading"><span class="eyebrow">TÀI KHOẢN KHÁCH HÀNG</span><h1>Tạo tài khoản</h1><p>Tài khoản đăng ký công khai luôn được giới hạn ở vai trò khách hàng.</p></div>
    <?php if ($message = flash('error')): ?><div class="alert alert-error"><?= e($message) ?></div><?php endif; ?>
    <form action="<?= url('register') ?>" method="post" class="form-stack">
        <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
        <label class="field"><span>Họ và tên</span><input name="name" required maxlength="150" autocomplete="name" placeholder="Nguyễn Minh Anh"></label>
        <div class="form-grid">
            <label class="field"><span>Tên đăng nhập</span><input name="username" required minlength="4" maxlength="50" autocomplete="username" placeholder="minhanh"></label>
            <label class="field"><span>Email</span><input type="email" name="email" autocomplete="email" placeholder="anh@example.com"></label>
        </div>
        <div class="form-grid">
            <label class="field"><span>Mật khẩu</span><input type="password" name="password" required minlength="8" autocomplete="new-password"></label>
            <label class="field"><span>Xác nhận mật khẩu</span><input type="password" name="password_confirmation" required minlength="8" autocomplete="new-password"></label>
        </div>
        <button class="button button-primary button-block" type="submit">Tạo tài khoản <span aria-hidden="true">→</span></button>
    </form>
    <p class="auth-switch">Đã có tài khoản? <a href="<?= url('login') ?>">Đăng nhập</a></p>
</section>

