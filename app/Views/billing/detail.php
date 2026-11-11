<?php
use App\Services\BillingService;
$paid = $invoice['trang_thai'] === 'paid';
$due = (int) $invoice['tong_thanh_toan'];
$money = function ($value) { return number_format($value, 0, ',', '.') . ' ₫'; };
?>
<div class="billing-customer">
    <div class="billing-actions no-print">
        <a class="text-link" href="<?= e(url($staff ? 'billing' : 'table-payment')) ?>">← Danh sách hóa đơn</a>
        <?php if ($paid): ?>
        <button class="button button-primary" type="button" onclick="window.print()">In / Lưu PDF</button>
        <?php else: ?>
        <a class="button button-secondary" href="<?= e(url('invoice?id=' . $invoice['id'])) ?>">Kiểm tra trạng thái</a>
        <?php endif; ?>
    </div>
    <?php if ($error = flash('billing_error')): ?><p class="billing-alert no-print" role="alert"><?= e($error) ?></p><?php endif; ?>
    <article class="content-panel billing-panel"<?= $paid ? ' id="receipt"' : '' ?>>
        <h1><?= $paid ? 'Phiếu thanh toán' : 'Thanh toán VietQR' ?></h1>
        <p><strong><?= e($invoice['ma_hoa_don']) ?></strong> · <?= $paid ? 'Đã thanh toán' : 'Chờ xác nhận' ?></p>
        <?php if ($paid): ?><p><?= e(date('H:i d/m/Y', strtotime($invoice['ngay_thanh_toan']))) ?></p><?php endif; ?>
        <div class="table-wrap"><table class="data-table">
            <thead><tr><th>Món</th><th>SL</th><th>Thành tiền</th></tr></thead>
            <tbody><?php foreach ($items as $item): ?>
                <tr><td><?= e($item['ten_san_pham_snapshot'] . ' · ' . $item['ten_size_snapshot']) ?><br><small><?= e($item['ten_ban']) ?></small></td>
                    <td><?= (int) $item['so_luong'] ?></td><td><?= e($money($item['thanh_tien'])) ?></td></tr>
            <?php endforeach; ?></tbody>
        </table></div>
        <dl class="billing-totals">
            <div><dt>Tạm tính</dt><dd><?= e($money($invoice['tong_tien_goc'])) ?></dd></div>
            <div><dt>Giảm giá</dt><dd><?= e($money($invoice['tien_giam'])) ?></dd></div>
            <div><dt><?= $paid ? 'Đã thanh toán' : 'Cần thanh toán' ?></dt><dd><strong><?= e($money($due)) ?></strong></dd></div>
        </dl>
        <?php if (!$paid && $due > 0): ?>
        <section class="billing-qr">
            <img src="<?= e($qr) ?>" alt="VietQR chuyển khoản <?= e($money($due)) ?> tới TPBank" referrerpolicy="no-referrer">
            <div><h2>TPBank · <?= e(BillingService::ACCOUNT_NAME) ?></h2>
                <p>Số tài khoản <strong><?= e(BillingService::ACCOUNT) ?></strong></p>
                <p>Nội dung <strong><?= e($invoice['ma_hoa_don']) ?></strong></p>
                <p>Mở ứng dụng ngân hàng để quét mã. Thu ngân sẽ đối chiếu giao dịch thực nhận.</p>
            </div>
        </section>
        <?php endif; ?>
        <?php if (!$paid && $staff): ?>
        <form class="billing-confirm no-print" method="post" action="<?= e(url('billing/confirm')) ?>">
            <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="id" value="<?= (int) $invoice['id'] ?>">
            <?php if ($due > 0): ?>
            <h2>Đối chiếu giao dịch</h2>
            <label class="field"><span>Mã giao dịch ngân hàng</span><input name="reference" required maxlength="100" autocomplete="off"></label>
            <label class="field"><span>Số tiền thực nhận (₫)</span><input name="amount" inputmode="numeric" pattern="[0-9]+" required></label>
            <?php else: ?><input type="hidden" name="amount" value="0"><?php endif; ?>
            <button class="button button-primary"><?= $due > 0 ? 'Xác nhận đã nhận tiền' : 'Hoàn tất hóa đơn 0 ₫' ?></button>
        </form>
        <form class="billing-actions no-print" method="post" action="<?= e(url('billing/cancel')) ?>"
            onsubmit="return confirm('Chỉ hủy sau khi đã kiểm tra khách chưa chuyển khoản. Hủy hóa đơn này?')">
            <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="id" value="<?= (int) $invoice['id'] ?>">
            <button class="button button-secondary">Hủy hóa đơn để lập lại</button>
        </form>
        <?php endif; ?>
    </article>
</div>
