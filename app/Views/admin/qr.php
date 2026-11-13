<section class="toolbar"><div class="toolbar-summary"><span class="summary-dot"></span>Mỗi bàn chỉ có một QR đang hoạt động</div><div class="toolbar-actions"><button class="button button-secondary" type="button" data-download-all-qr><i data-lucide="download"></i>Tải QR hoạt động</button><button class="button button-primary" data-open-modal="qrModal"><i data-lucide="qr-code"></i>Tạo QR mới</button></div></section>
<section class="qr-grid">
<?php if (!$qrCodes): ?><div class="empty-state content-panel"><i data-lucide="qr-code"></i><h2>Chưa có mã QR</h2><p>Tạo mã đầu tiên để khách có thể mở menu tại bàn.</p></div><?php endif; ?>
<?php foreach ($qrCodes as $qr): ?>
    <article class="qr-card <?= $qr['trang_thai'] !== 'active' ? 'is-revoked' : '' ?>">
        <div class="qr-canvas" data-qr-value="<?= e($qr['qr_image']) ?>" data-qr-id="<?= (int) $qr['id'] ?>"></div>
        <div class="qr-details"><div><span class="eyebrow"><?= e($qr['khu_vuc']) ?></span><h2><?= e($qr['ma_ban']) ?> · <?= e($qr['ten_ban']) ?></h2></div><span class="pill <?= $qr['trang_thai'] === 'active' ? 'pill-active' : 'pill-inactive' ?>"><?= $qr['trang_thai'] === 'active' ? 'Hoạt động' : 'Đã thu hồi' ?></span></div>
        <div class="qr-meta"><span>Tạo <?= e(date('d/m/Y H:i', strtotime($qr['ngay_tao']))) ?></span><span>Hết hạn <?= $qr['ngay_het_han'] ? e(date('d/m/Y', strtotime($qr['ngay_het_han']))) : 'Không giới hạn' ?></span></div>
        <div class="card-actions"><button class="button button-primary button-small" data-download-qr="<?= (int) $qr['id'] ?>" data-qr-name="<?= e($qr['ma_ban']) ?>" <?= $qr['trang_thai'] !== 'active' ? 'disabled' : '' ?>><i data-lucide="download"></i>Tải mã QR</button><?php if ($qr['trang_thai'] === 'active'): ?><button class="icon-button danger" title="Thu hồi QR" data-api-action="qr/revoke" data-id="<?= (int) $qr['id'] ?>" data-confirm="Thu hồi QR này? Khách sẽ không thể tiếp tục sử dụng."><i data-lucide="ban"></i></button><?php endif; ?></div>
    </article>
<?php endforeach; ?>
</section>
<dialog class="modal" id="qrModal"><form class="modal-form" data-api-form="qr/generate">
    <div class="modal-heading"><div><span class="eyebrow">MÃ TRUY CẬP BÀN</span><h2>Tạo QR mới</h2></div><button type="button" class="icon-button" data-close-modal><i data-lucide="x"></i></button></div>
    <div class="alert alert-warning">QR đang hoạt động của bàn đã chọn sẽ bị thu hồi ngay khi tạo mã mới.</div>
    <label class="field"><span>Bàn *</span><select name="table_id" required><option value="">Chọn bàn</option><?php foreach ($tables as $table): ?><option value="<?= (int) $table['id'] ?>"><?= e($table['ma_ban'] . ' · ' . $table['ten_ban']) ?></option><?php endforeach; ?></select></label>
    <label class="field"><span>Ngày hết hạn</span><input type="datetime-local" name="expires_at"><small>Để trống để dùng thời hạn mặc định 1 năm.</small></label>
    <div class="modal-actions"><button type="button" class="button button-secondary" data-close-modal>Hủy</button><button class="button button-primary" type="submit">Tạo mã QR</button></div>
</form></dialog>
<script src="<?= asset('assets/vendor/qrcode.min.js') ?>"></script>
