<header class="customer-header customer-header-landing">
    <a class="brand brand-dark" href="<?= url('customer') ?>"><span class="brand-mark">T</span><span>TableTap</span></a>
    <div class="landing-header-copy"><span>QR TABLE ORDERING</span><strong>Đặt món tại bàn</strong></div>
    <form action="<?= url('logout') ?>" method="post" class="landing-logout">
        <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
        <button class="icon-button" title="Đăng xuất" aria-label="Đăng xuất"><i data-lucide="log-out"></i></button>
    </form>
</header>
<main class="customer-landing">
    <section class="landing-copy">
        <span class="eyebrow">TRẢI NGHIỆM NHANH HƠN</span>
        <h1>Quét một lần,<br><em>thưởng thức ngay.</em></h1>
        <p class="landing-lead">Mở menu của bàn, chọn món yêu thích và gửi thẳng đến bếp. Không cần chờ nhân viên, không lo gọi nhầm bàn.</p>
        <div class="landing-actions">
            <label class="button button-primary upload-qr-button" for="qrUploadInput"><i data-lucide="image-up"></i><span>Tải ảnh QR lên</span><input id="qrUploadInput" type="file" accept="image/*" hidden></label>
            <button class="button button-secondary" type="button" data-show-qr-help><i data-lucide="help-circle"></i>Cách sử dụng</button>
        </div>
        <p class="landing-hint"><i data-lucide="shield-check"></i>QR được xác thực với đúng bàn trước khi mở menu.</p>
    </section>
    <section class="qr-landing-card" aria-label="Hướng dẫn quét QR">
        <div class="qr-illustration">
            <div class="fake-qr" aria-hidden="true"><span class="finder finder-a"></span><span class="finder finder-b"></span><span class="finder finder-c"></span><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i></div>
            <span class="scan-line"></span>
        </div>
        <div class="qr-card-label"><span class="live-dot"></span><span>Sẵn sàng quét</span></div>
        <div class="landing-steps"><div><b>01</b><span>Quét QR trên bàn</span></div><div><b>02</b><span>Chọn món & ghi chú</span></div><div><b>03</b><span>Gửi đơn đến bếp</span></div></div>
    </section>
</main>
<dialog class="modal qr-help-modal" id="qrHelpModal"><div class="modal-form"><div class="modal-heading"><div><span class="eyebrow">BẮT ĐẦU TRONG 3 BƯỚC</span><h2>Cách đặt món tại bàn</h2></div><button type="button" class="icon-button" data-close-modal aria-label="Đóng"><i data-lucide="x"></i></button></div><ol class="help-steps"><li><b>01</b><span>Quét mã QR được đặt trên bàn bằng camera điện thoại.</span></li><li><b>02</b><span>Nếu có ảnh QR, chọn “Tải ảnh QR lên” để mở ảnh từ thiết bị.</span></li><li><b>03</b><span>Chọn món, size, ghi chú rồi gửi đơn đến bếp.</span></li></ol></div></dialog>
