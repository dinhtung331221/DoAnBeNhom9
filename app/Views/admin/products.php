<section class="toolbar">
    <div class="search-box"><i data-lucide="search"></i><input type="search" placeholder="Tìm sản phẩm hoặc danh mục..." data-table-search="productTable"></div>
    <button class="button button-primary" data-open-modal="productModal"><i data-lucide="plus"></i>Thêm sản phẩm</button>
</section>
<section class="content-panel">
    <div class="panel-heading"><div><span class="eyebrow">THỰC ĐƠN</span><h2><?= count($products) ?> sản phẩm</h2></div><p class="panel-note">Giá size là giá chính thức được dùng khi đặt món.</p></div>
    <div class="table-wrap"><table class="data-table" id="productTable"><thead><tr><th>Sản phẩm</th><th>Danh mục</th><th>Mức giá</th><th>Trạng thái</th><th class="actions-column">Thao tác</th></tr></thead><tbody>
    <?php foreach ($products as $product): ?>
        <?php $imageUrl = $product['hinh_anh'] ? (strpos($product['hinh_anh'], 'http') === 0 ? $product['hinh_anh'] : asset($product['hinh_anh'])) : asset('assets/images/placeholder-food.svg'); ?>
        <tr><td><div class="product-cell"><img src="<?= e($imageUrl) ?>" alt=""><div><strong><?= e($product['ten_san_pham']) ?></strong><small><?= e(mb_strimwidth($product['mo_ta'], 0, 64, '...', 'UTF-8')) ?></small></div></div></td><td><?= e($product['ten_danh_muc']) ?></td><td><strong><?= number_format($product['gia_ban_chinh'], 0, ',', '.') ?>đ</strong><small class="block-muted"><?= e(str_replace('|', ' · ', preg_replace('/\d+:/', '', $product['sizes']))) ?></small></td><td><span class="pill <?= $product['trang_thai'] ? 'pill-active' : 'pill-inactive' ?>"><?= $product['trang_thai'] ? 'Đang bán' : 'Tạm ẩn' ?></span></td><td class="row-actions">
            <button class="icon-button" title="Chỉnh sửa" data-edit-product='<?= e(json_encode($product, JSON_UNESCAPED_UNICODE)) ?>'><i data-lucide="pencil"></i></button>
            <button class="icon-button" title="Đổi trạng thái" data-api-action="products/toggle" data-id="<?= (int) $product['id'] ?>" data-confirm="Cập nhật trạng thái sản phẩm này?"><i data-lucide="<?= $product['trang_thai'] ? 'eye-off' : 'eye' ?>"></i></button>
        </td></tr>
    <?php endforeach; ?>
    </tbody></table></div>
</section>
<dialog class="modal modal-wide" id="productModal"><form class="modal-form" data-api-form="products/save" enctype="multipart/form-data">
    <div class="modal-heading"><div><span class="eyebrow">SẢN PHẨM</span><h2 data-modal-title>Thêm sản phẩm</h2></div><button type="button" class="icon-button" data-close-modal aria-label="Đóng"><i data-lucide="x"></i></button></div>
    <input type="hidden" name="id">
    <div class="form-grid"><label class="field"><span>Tên sản phẩm *</span><input name="ten_san_pham" required maxlength="150"></label><label class="field"><span>Danh mục *</span><select name="danh_muc_id" required><option value="">Chọn danh mục</option><?php foreach ($categories as $category): ?><option value="<?= (int) $category['id'] ?>"><?= e($category['ten_danh_muc']) ?></option><?php endforeach; ?></select></label></div>
    <label class="field"><span>Mô tả</span><textarea name="mo_ta" rows="3" maxlength="1000"></textarea></label>
    <div class="form-grid"><label class="field"><span>Giá cơ bản *</span><div class="input-suffix"><input name="gia_ban_chinh" type="number" required min="0" step="1000"><span>đ</span></div></label><label class="field"><span>Hình ảnh (JPG, PNG, WEBP)</span><input name="hinh_anh" type="file" accept="image/jpeg,image/png,image/webp"></label></div>
    <fieldset class="price-fieldset"><legend>Giá theo size</legend><div class="size-price-grid"><?php foreach ($sizes as $size): ?><label class="field"><span>Size <?= e($size['ten_size']) ?></span><div class="input-suffix"><input name="sizes[<?= (int) $size['id'] ?>]" type="number" required min="1" step="1000"><span>đ</span></div></label><?php endforeach; ?></div></fieldset>
    <label class="switch-row"><span><strong>Đang bán</strong><small>Hiển thị sản phẩm trên menu khách hàng.</small></span><input type="checkbox" name="trang_thai" value="1" checked></label>
    <div class="modal-actions"><button type="button" class="button button-secondary" data-close-modal>Hủy</button><button class="button button-primary" type="submit">Lưu sản phẩm</button></div>
</form></dialog>

