<header class="customer-header cart-header"><a class="icon-button" href="<?= url('menu?token=' . rawurlencode($_SESSION['table_context']['token'])) ?>" aria-label="Quay lại menu"><i data-lucide="arrow-left"></i></a><div><small><?= e($table['ten_ban']) ?></small><strong>Giỏ hàng của bạn</strong></div><span></span></header>
<main class="cart-main">
    <section class="cart-list" id="cartList"></section>
    <aside class="cart-summary" id="cartSummary">
        <div class="summary-heading"><span class="eyebrow">XÁC NHẬN ĐƠN</span><h1>Thông tin đơn hàng</h1></div>
        <label class="field"><span>Ghi chú chung</span><textarea id="orderNote" rows="3" maxlength="500" placeholder="Ví dụ: Mang tất cả món ra cùng lúc"></textarea></label>
        <div class="price-summary"><div><span>Tạm tính</span><strong data-cart-subtotal>0đ</strong></div><div><span>Phí phục vụ</span><strong>0đ</strong></div><div class="price-total"><span>Tổng cộng</span><strong data-cart-total>0đ</strong></div></div>
        <button class="button button-primary button-block button-large" id="placeOrder"><span>Gửi món đến bếp</span><i data-lucide="arrow-right"></i></button>
        <p class="secure-note"><i data-lucide="shield-check"></i>Giá và tình trạng món sẽ được kiểm tra lại khi gửi đơn.</p>
    </aside>
</main>
<dialog class="modal success-modal" id="orderSuccess"><div class="modal-form"><div class="success-icon"><i data-lucide="check"></i></div><h2>Bếp đã nhận đơn</h2><p>Mã đơn <strong data-order-code></strong>. Bạn có thể tiếp tục gọi thêm món từ menu.</p><a class="button button-primary button-block" href="<?= url('menu?token=' . rawurlencode($_SESSION['table_context']['token'])) ?>">Về menu</a></div></dialog>

