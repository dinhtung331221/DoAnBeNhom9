<section class="toolbar">
    <div class="search-box"><i data-lucide="search"></i><input type="search" placeholder="Tìm tên danh mục..." data-table-search="categoryTable"></div>
    <button class="button button-primary" data-open-modal="categoryModal"><i data-lucide="plus"></i>Thêm danh mục</button>
</section>
<section class="content-panel">
    <div class="panel-heading"><div><span class="eyebrow">MENU</span><h2><?= count($categories) ?> danh mục</h2></div><p class="panel-note">Danh mục ẩn sẽ không xuất hiện trên menu khách hàng.</p></div>
    <div class="table-wrap"><table class="data-table" id="categoryTable"><thead><tr><th>Tên danh mục</th><th>Mô tả</th><th>Sản phẩm</th><th>Trạng thái</th><th class="actions-column">Thao tác</th></tr></thead><tbody>
    <?php foreach ($categories as $category): ?>
        <tr><td><strong><?= e($category['ten_danh_muc']) ?></strong></td><td class="muted-cell"><?= e($category['mo_ta']) ?></td><td><?= (int) $category['so_san_pham'] ?></td><td><span class="pill <?= $category['trang_thai'] ? 'pill-active' : 'pill-inactive' ?>"><?= $category['trang_thai'] ? 'Đang hiện' : 'Đã ẩn' ?></span></td><td class="row-actions">
            <button class="icon-button" title="Chỉnh sửa" data-edit-category='<?= e(json_encode($category, JSON_UNESCAPED_UNICODE)) ?>'><i data-lucide="pencil"></i></button>
            <button class="icon-button" title="<?= $category['trang_thai'] ? 'Ẩn danh mục' : 'Hiện danh mục' ?>" data-api-action="categories/toggle" data-id="<?= (int) $category['id'] ?>" data-confirm="Cập nhật trạng thái danh mục này?"><i data-lucide="<?= $category['trang_thai'] ? 'eye-off' : 'eye' ?>"></i></button>
        </td></tr>
    <?php endforeach; ?>
    </tbody></table></div>
</section>
<dialog class="modal" id="categoryModal"><form class="modal-form" data-api-form="categories/save">
    <div class="modal-heading"><div><span class="eyebrow">DANH MỤC MENU</span><h2 data-modal-title>Thêm danh mục</h2></div><button type="button" class="icon-button" data-close-modal aria-label="Đóng"><i data-lucide="x"></i></button></div>
    <input type="hidden" name="id">
    <label class="field"><span>Tên danh mục *</span><input name="ten_danh_muc" required maxlength="100"></label>
    <label class="field"><span>Mô tả</span><textarea name="mo_ta" rows="4" maxlength="500"></textarea></label>
    <label class="switch-row"><span><strong>Hiển thị trên menu</strong><small>Khách có thể xem các sản phẩm thuộc danh mục.</small></span><input type="checkbox" name="trang_thai" value="1" checked></label>
    <div class="modal-actions"><button type="button" class="button button-secondary" data-close-modal>Hủy</button><button class="button button-primary" type="submit">Lưu danh mục</button></div>
</form></dialog>

