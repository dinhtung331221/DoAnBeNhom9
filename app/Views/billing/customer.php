<main class="billing-customer">
    <a class="text-link" href="<?= e(url('menu?token=' . rawurlencode($_SESSION['table_context']['token']))) ?>">← Về thực đơn</a>
    <section class="content-panel billing-panel">
        <h1>Thanh toán · <?= e($table['ten_ban']) ?></h1>
        <?php if (!$invoices): ?><p>Gửi yêu cầu đến thu ngân. Sau khi thu ngân lập hóa đơn, tải lại trang để mở VietQR.</p><?php endif; ?>
        <?php foreach ($invoices as $invoice): ?>
            <a class="billing-row" href="<?= e(url('invoice?id=' . $invoice['id'])) ?>">
                <strong><?= e($invoice['ma_hoa_don']) ?></strong>
                <span><?= number_format($invoice['tong_thanh_toan'], 0, ',', '.') ?> ₫</span>
                <span><?= $invoice['trang_thai'] === 'paid' ? 'Xem phiếu thanh toán' : 'Mở mã VietQR' ?></span>
            </a>
        <?php endforeach; ?>
        <?php if (!$invoices): ?>
            <button class="button button-primary" type="button" data-table-request="yeu_cau_thanh_toan">Yêu cầu thanh toán</button>
        <?php endif; ?>
        <a class="button button-secondary" href="<?= e(url('table-payment')) ?>">Tải lại</a>
    </section>
</main>
