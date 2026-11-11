<main class="billing-customer">
    <section class="content-panel billing-panel">
        <h1>Chào <?= e(\App\Core\Auth::user()['ho_ten']) ?></h1>
        <p>Quét mã QR tại bàn bằng camera điện thoại để mở thực đơn và gọi món.</p>
        <form method="post" action="<?= e(url('logout')) ?>">
            <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
            <button class="button button-secondary">Đăng xuất</button>
        </form>
    </section>
</main>
