<section class="toolbar"><div class="toolbar-summary"><span class="summary-dot"></span><?= count($tables) ?> bàn trong hệ thống</div><button class="button button-primary" data-open-modal="tableModal"><i data-lucide="plus"></i>Thêm bàn</button></section>
<section class="table-admin-grid">
<?php foreach ($tables as $table): ?>
    <article class="table-admin-card status-<?= e($table['trang_thai']) ?>">
        <div class="table-card-top"><div class="table-code"><?= e($table['ma_ban']) ?></div><span class="status-label"><?= e(array('available' => 'Sẵn sàng', 'occupied' => 'Đang dùng', 'waiting_payment' => 'Chờ thanh toán', 'maintenance' => 'Tạm ngưng')[$table['trang_thai']]) ?></span></div>
        <h2><?= e($table['ten_ban']) ?></h2><p><i data-lucide="map-pin"></i><?= e($table['khu_vuc'] ?: 'Chưa phân khu') ?></p><p><i data-lucide="users"></i><?= (int) $table['suc_chua'] ?> chỗ ngồi</p>
        <div class="card-actions"><button class="button button-secondary button-small" data-edit-table='<?= e(json_encode($table, JSON_UNESCAPED_UNICODE)) ?>'><i data-lucide="pencil"></i>Sửa</button><button class="icon-button danger" title="Ngừng sử dụng" data-api-action="tables/deactivate" data-id="<?= (int) $table['id'] ?>" data-confirm="Ngừng sử dụng bàn và thu hồi tất cả QR đang hoạt động?"><i data-lucide="archive"></i></button></div>
    </article>
<?php endforeach; ?>
</section>
<dialog class="modal" id="tableModal"><form class="modal-form" data-api-form="tables/save">
    <div class="modal-heading"><div><span class="eyebrow">BÀN PHỤC VỤ</span><h2 data-modal-title>Thêm bàn</h2></div><button type="button" class="icon-button" data-close-modal><i data-lucide="x"></i></button></div><input type="hidden" name="id">
    <div class="form-grid"><label class="field"><span>Mã bàn *</span><input name="ma_ban" required maxlength="30" placeholder="T01"></label><label class="field"><span>Tên bàn *</span><input name="ten_ban" required maxlength="100" placeholder="Bàn 01"></label></div>
    <div class="form-grid"><label class="field"><span>Khu vực</span><input name="khu_vuc" maxlength="100" placeholder="Tầng trệt"></label><label class="field"><span>Sức chứa *</span><input name="suc_chua" type="number" min="1" max="50" required value="4"></label></div>
    <label class="field"><span>Trạng thái</span><select name="trang_thai"><option value="available">Sẵn sàng</option><option value="occupied">Đang dùng</option><option value="waiting_payment">Chờ thanh toán</option><option value="maintenance">Tạm ngưng</option></select></label>
    <label class="field"><span>Ghi chú</span><textarea name="ghi_chu" rows="3"></textarea></label>
    <div class="modal-actions"><button type="button" class="button button-secondary" data-close-modal>Hủy</button><button class="button button-primary" type="submit">Lưu bàn</button></div>
</form></dialog>

