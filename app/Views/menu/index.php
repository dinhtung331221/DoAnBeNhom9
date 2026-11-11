<header class="customer-header">
    <a class="brand brand-dark" href="<?= url('menu?token=' . rawurlencode($_SESSION['table_context']['token'])) ?>"><span class="brand-mark">T</span><span>TableTap</span></a>
    <div class="table-chip"><span class="live-dot"></span><div><small>Bạn đang ngồi tại</small><strong><?= e($table['ten_ban']) ?></strong></div></div>
    <a class="cart-button" href="<?= url('cart') ?>" aria-label="Mở giỏ hàng"><i data-lucide="shopping-bag"></i><span data-cart-count>0</span></a>
</header>
<main class="customer-main">
    <section class="menu-intro"><div><span class="eyebrow">THỰC ĐƠN HÔM NAY</span><h1>Chọn món bạn thích.</h1><p>Món được pha chế sau khi bếp xác nhận đơn.</p></div><button class="button button-secondary" data-table-request="goi_nhan_vien"><i data-lucide="bell"></i>Gọi nhân viên</button></section>
    <div class="category-tabs" role="tablist"><button class="category-tab is-active" data-category="all">Tất cả</button><?php foreach ($categories as $category): ?><button class="category-tab" data-category="<?= (int) $category['id'] ?>"><?= e($category['ten_danh_muc']) ?></button><?php endforeach; ?></div>
    <section class="menu-grid" id="menuGrid">
    <?php foreach ($products as $product): ?>
        <?php
        $sizes = array();
        foreach (explode('|', $product['sizes']) as $sizeString) {
            $parts = explode(':', $sizeString);
            if (count($parts) === 3) { $sizes[] = array('id' => (int) $parts[0], 'name' => $parts[1], 'price' => (float) $parts[2]); }
        }
        $imageUrl = $product['hinh_anh'] ? (strpos($product['hinh_anh'], 'http') === 0 ? $product['hinh_anh'] : asset($product['hinh_anh'])) : asset('assets/images/placeholder-food.svg');
        ?>
        <article class="menu-item" data-category-id="<?= (int) $product['danh_muc_id'] ?>">
            <div class="menu-item-image"><img src="<?= e($imageUrl) ?>" alt="<?= e($product['ten_san_pham']) ?>" loading="lazy"><span><?= e($product['ten_danh_muc']) ?></span></div>
            <div class="menu-item-body"><div><h2><?= e($product['ten_san_pham']) ?></h2><p><?= e($product['mo_ta']) ?></p></div><div class="menu-item-footer"><div><small>Từ</small><strong><?= number_format($sizes[0]['price'], 0, ',', '.') ?>đ</strong></div><button class="icon-button add-button" data-add-product='<?= e(json_encode(array('id' => (int) $product['id'], 'name' => $product['ten_san_pham'], 'image' => $imageUrl, 'sizes' => $sizes), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?>' aria-label="Thêm <?= e($product['ten_san_pham']) ?>"><i data-lucide="plus"></i></button></div></div>
        </article>
    <?php endforeach; ?>
    </section>
</main>
<nav class="customer-bottom-nav"><a class="is-active" href="#"><i data-lucide="utensils"></i><span>Menu</span></a><a href="<?= url('cart') ?>"><i data-lucide="shopping-bag"></i><span>Giỏ hàng</span><b data-cart-count>0</b></a><a href="<?= e(url('table-payment')) ?>"><i data-lucide="credit-card"></i><span>Thanh toán</span></a></nav>
<dialog class="modal product-picker" id="productPicker"><form method="dialog" class="modal-form" id="productPickerForm">
    <div class="modal-heading"><div><span class="eyebrow">CHỌN TÙY CHỌN</span><h2 data-picker-name></h2></div><button class="icon-button" value="cancel" aria-label="Đóng"><i data-lucide="x"></i></button></div>
    <div class="picker-sizes" data-picker-sizes></div>
    <label class="field"><span>Ghi chú cho món</span><textarea name="note" rows="3" maxlength="300" placeholder="Ít đá, ít ngọt..."></textarea></label>
    <div class="quantity-control"><span>Số lượng</span><div><button type="button" class="icon-button" data-qty-minus><i data-lucide="minus"></i></button><strong data-qty>1</strong><button type="button" class="icon-button" data-qty-plus><i data-lucide="plus"></i></button></div></div>
    <button type="submit" class="button button-primary button-block">Thêm vào giỏ</button>
</form></dialog>

