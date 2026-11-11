<?php if ($error = flash('billing_error')): ?><p class="billing-alert" role="alert"><?= e($error) ?></p><?php endif; ?>
<section class="content-panel billing-panel">
    <h2>Lập hóa đơn</h2>
    <p>Chọn các dòng món đã sẵn sàng hoặc đã phục vụ. Chọn món từ nhiều bàn để gộp, hoặc chọn từng nhóm món để tách hóa đơn.</p>
    <?php if (!$items): ?>
        <p>Hiện chưa có món đủ điều kiện lập hóa đơn.</p>
    <?php else: ?>
    <form method="post" action="<?= e(url('billing/create')) ?>">
        <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
        <div class="table-wrap"><table class="data-table">
            <thead><tr><th>Chọn</th><th>Bàn</th><th>Món</th><th>Số lượng</th><th>Thành tiền</th></tr></thead>
            <tbody>
            <?php foreach ($items as $item): ?>
            <tr>
                <td><input type="checkbox" name="items[]" value="<?= (int) $item['id'] ?>"
                    aria-label="<?= e($item['ten_ban'] . ' ' . $item['ten_san_pham_snapshot'] . ' ' . $item['ma_don']) ?>"></td>
                <td><?= e($item['ten_ban']) ?><br><small><?= e($item['ma_don']) ?></small></td>
                <td><?= e($item['ten_san_pham_snapshot'] . ' · ' . $item['ten_size_snapshot']) ?></td>
                <td><?= (int) $item['so_luong'] ?></td>
                <td><?= number_format($item['so_luong'] * $item['don_gia'], 0, ',', '.') ?> ₫</td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table></div>
        <div class="billing-actions">
            <label class="field"><span>Mã giảm giá (nếu có)</span><input name="coupon" maxlength="50"></label>
            <button class="button button-primary">Lập hóa đơn</button>
        </div>
    </form>
    <?php endif; ?>
</section>
<section class="content-panel billing-panel">
    <h2>Hóa đơn gần đây</h2>
    <?php if (!$invoices): ?><p>Chưa có hóa đơn.</p><?php endif; ?>
    <?php foreach ($invoices as $invoice): ?>
        <a class="billing-row" href="<?= e(url('invoice?id=' . $invoice['id'])) ?>">
            <strong><?= e($invoice['ma_hoa_don']) ?></strong>
            <span><?= number_format($invoice['tong_thanh_toan'], 0, ',', '.') ?> ₫</span>
            <span><?= $invoice['trang_thai'] === 'paid' ? 'Đã thanh toán' : 'Chờ chuyển khoản' ?></span>
        </a>
    <?php endforeach; ?>
</section>
<section class="content-panel billing-panel">
    <h2>Doanh thu theo ngày</h2>
    <?php if (!$revenue): ?><p>Chưa ghi nhận doanh thu.</p><?php endif; ?>
    <?php foreach ($revenue as $day): ?>
        <div class="billing-row"><strong><?= e(date('d/m/Y', strtotime($day['day']))) ?></strong>
            <span><?= (int) $day['invoices'] ?> hóa đơn</span>
            <span><?= number_format($day['total'], 0, ',', '.') ?> ₫</span></div>
    <?php endforeach; ?>
</section>
