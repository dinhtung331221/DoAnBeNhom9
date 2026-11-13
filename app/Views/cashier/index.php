<section class="stats-grid cashier-stats">
    <article class="stat-block"><div class="stat-icon coral"><i data-lucide="bell-ring"></i></div><div><span>Chờ thanh toán</span><strong><?= number_format($stats['waiting']) ?></strong></div><small>Bàn đã gửi yêu cầu thanh toán</small></article>
    <article class="stat-block"><div class="stat-icon amber"><i data-lucide="receipt-text"></i></div><div><span>Đang phục vụ</span><strong><?= count($sessions) ?></strong></div><small>Phiên bàn có đơn chưa đóng</small></article>
    <article class="stat-block"><div class="stat-icon teal"><i data-lucide="wallet-cards"></i></div><div><span>Giá trị đang mở</span><strong><?= number_format($stats['open_total'], 0, ',', '.') ?>đ</strong></div><small>Tổng món chưa thanh toán</small></article>
    <article class="stat-block"><div class="stat-icon ink"><i data-lucide="badge-dollar-sign"></i></div><div><span>Doanh thu hôm nay</span><strong><?= number_format($stats['paid_today'], 0, ',', '.') ?>đ</strong></div><small><?= (int) $stats['invoice_count'] ?> hóa đơn đã hoàn tất</small></article>
</section>

<section class="cashier-workspace">
    <aside class="cashier-queue">
        <div class="cashier-section-heading"><div><span class="eyebrow">HÀNG ĐỢI</span><h2>Phiên bàn</h2></div><span class="count-badge"><?= count($sessions) ?></span></div>
        <div class="search-box cashier-search"><i data-lucide="search"></i><input type="search" placeholder="Tìm bàn, khu vực..." data-cashier-search></div>
        <div class="cashier-session-list" data-cashier-session-list>
            <?php if (!$sessions): ?><div class="empty-state compact"><i data-lucide="circle-check-big"></i><p>Không có phiên bàn cần thanh toán.</p></div><?php endif; ?>
            <?php foreach ($sessions as $index => $session): ?>
                <button class="cashier-session <?= $index === 0 ? 'is-active' : '' ?>" type="button" data-session-id="<?= (int) $session['session_id'] ?>">
                    <span class="cashier-table-code"><?= e($session['ma_ban']) ?></span>
                    <span class="cashier-session-copy"><strong><?= e($session['ten_ban']) ?></strong><small><?= e($session['khu_vuc'] ?: 'Chưa phân khu') ?> · <?= (int) $session['order_count'] ?> đơn</small></span>
                    <span class="cashier-session-total"><strong><?= number_format($session['subtotal'], 0, ',', '.') ?>đ</strong><?php if ($session['payment_requested']): ?><small>Đang chờ</small><?php else: ?><small>Đang dùng</small><?php endif; ?></span>
                </button>
            <?php endforeach; ?>
        </div>
    </aside>

    <div class="cashier-bill" data-cashier-bill>
        <div class="cashier-empty <?= $sessions ? 'is-hidden' : '' ?>" data-cashier-empty><i data-lucide="receipt-text"></i><h2>Chọn một phiên bàn</h2><p>Chi tiết món và thanh toán sẽ hiển thị tại đây.</p></div>
        <div class="cashier-bill-content <?= !$sessions ? 'is-hidden' : '' ?>" data-cashier-content>
            <header class="bill-heading"><div><span class="eyebrow" data-bill-session></span><h2 data-bill-table></h2><p data-bill-meta></p></div><span class="pill pill-pending" data-bill-status>Chưa thanh toán</span></header>
            <div class="bill-items-wrap"><table class="bill-items"><thead><tr><th>Món</th><th>SL</th><th>Đơn giá</th><th>Thành tiền</th></tr></thead><tbody data-bill-items></tbody></table></div>
            <div class="cashier-controls">
                <div class="cashier-promo-block">
                    <label class="field"><span>Mã khuyến mãi</span><div class="cashier-inline-input"><input type="text" maxlength="50" placeholder="WELCOME10" data-promotion-code><button class="button button-secondary" type="button" data-apply-promotion>Áp dụng</button></div></label>
                    <p class="promotion-result" data-promotion-result>Nhập mã nếu khách có khuyến mãi.</p>
                </div>
                <div class="bill-totals">
                    <div><span>Tạm tính</span><strong data-bill-subtotal>0đ</strong></div>
                    <div class="discount-row"><span>Khuyến mãi</span><strong data-bill-discount>-0đ</strong></div>
                    <div><span>Đã thanh toán</span><strong data-bill-paid>0đ</strong></div>
                    <div class="bill-grand-total"><span>Còn phải trả</span><strong data-bill-total>0đ</strong></div>
                </div>
            </div>
            <div class="payment-panel">
                <div class="payment-methods" role="radiogroup" aria-label="Phương thức thanh toán">
                    <label><input type="radio" name="payment_method" value="tien_mat" checked><span><i data-lucide="banknote"></i>Tiền mặt</span></label>
                    <label><input type="radio" name="payment_method" value="chuyen_khoan"><span><i data-lucide="landmark"></i>Chuyển khoản</span></label>
                    <label><input type="radio" name="payment_method" value="the_ngan_hang"><span><i data-lucide="credit-card"></i>Thẻ</span></label>
                    <label><input type="radio" name="payment_method" value="vi_dien_tu"><span><i data-lucide="smartphone"></i>Ví điện tử</span></label>
                </div>
                <div class="payment-entry">
                    <label class="field"><span>Số tiền thanh toán</span><div class="input-suffix"><input type="number" min="1" step="1000" data-payment-amount><span>đ</span></div></label>
                    <label class="field"><span>Ghi chú</span><input type="text" maxlength="300" placeholder="Tùy chọn" data-payment-note></label>
                </div>
                <button class="button button-primary button-large button-block" type="button" data-confirm-payment><i data-lucide="circle-check-big"></i>Xác nhận thanh toán</button>
                <p class="secure-note"><i data-lucide="shield-check"></i>Thanh toán được ghi lịch sử. Khi trả đủ, phiên đóng và bàn tự chuyển về sẵn sàng.</p>
            </div>
        </div>
    </div>
</section>

<section class="content-panel cashier-history">
    <div class="panel-heading"><div><span class="eyebrow">GẦN ĐÂY</span><h2>Hóa đơn đã thanh toán</h2></div><span class="panel-note">Tối đa 8 giao dịch mới nhất</span></div>
    <div class="table-wrap"><table class="data-table"><thead><tr><th>Mã hóa đơn</th><th>Bàn</th><th>Thu ngân</th><th>Thời gian</th><th>Tổng tiền</th></tr></thead><tbody>
    <?php if (!$recent): ?><tr><td colspan="5"><div class="empty-state compact"><p>Chưa có hóa đơn hoàn tất.</p></div></td></tr><?php endif; ?>
    <?php foreach ($recent as $invoice): ?><tr><td><strong><?= e($invoice['ma_hoa_don']) ?></strong></td><td><?= e($invoice['ten_ban'] ?: '-') ?></td><td><?= e($invoice['ho_ten'] ?: '-') ?></td><td><?= e(date('H:i · d/m/Y', strtotime($invoice['ngay_thanh_toan']))) ?></td><td><strong><?= number_format($invoice['tong_thanh_toan'], 0, ',', '.') ?>đ</strong></td></tr><?php endforeach; ?>
    </tbody></table></div>
</section>

<script>window.CASHIER_SESSIONS = <?= json_encode($sessions, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;</script>
<script src="<?= versioned_asset('assets/js/cashier.js') ?>"></script>
