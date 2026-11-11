<section class="stats-grid">
    <article class="stat-block"><div class="stat-icon coral"><i data-lucide="receipt-text"></i></div><div><span>Đơn hôm nay</span><strong><?= number_format($stats['orders_today']) ?></strong></div><small>Cập nhật trực tiếp từ hệ thống</small></article>
    <article class="stat-block"><div class="stat-icon amber"><i data-lucide="cooking-pot"></i></div><div><span>Món đang chờ</span><strong><?= number_format($stats['pending_items']) ?></strong></div><small>Đang chờ hoặc chế biến</small></article>
    <article class="stat-block"><div class="stat-icon teal"><i data-lucide="armchair"></i></div><div><span>Bàn đang dùng</span><strong><?= number_format($stats['occupied_tables']) ?></strong></div><small>Gồm bàn chờ thanh toán</small></article>
    <article class="stat-block"><div class="stat-icon ink"><i data-lucide="banknote"></i></div><div><span>Doanh thu hôm nay</span><strong><?= number_format($stats['revenue_today'], 0, ',', '.') ?>đ</strong></div><small>Hóa đơn đã thanh toán</small></article>
</section>

<section class="split-layout">
    <div class="content-panel wide-panel">
        <div class="panel-heading"><div><span class="eyebrow">SƠ ĐỒ PHỤC VỤ</span><h2>Trạng thái bàn</h2></div><a class="text-link" href="<?= url('admin/tables') ?>">Quản lý bàn <span>→</span></a></div>
        <div class="table-map">
            <?php foreach ($tables as $table): ?>
                <div class="table-tile status-<?= e($table['trang_thai']) ?>">
                    <div><strong><?= e($table['ma_ban']) ?></strong><span><?= e($table['ten_ban']) ?></span></div>
                    <small><?= e($table['khu_vuc']) ?> · <?= (int) $table['suc_chua'] ?> chỗ</small>
                    <span class="status-label"><?= e(array('available' => 'Sẵn sàng', 'occupied' => 'Đang dùng', 'waiting_payment' => 'Chờ thanh toán', 'maintenance' => 'Tạm ngưng')[$table['trang_thai']]) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <aside class="content-panel request-panel">
        <div class="panel-heading"><div><span class="eyebrow">YÊU CẦU MỚI</span><h2>Từ khách hàng</h2></div><span class="count-badge"><?= count($requests) ?></span></div>
        <div class="request-list">
            <?php if (!$requests): ?><div class="empty-state compact"><i data-lucide="bell-off"></i><p>Chưa có yêu cầu đang chờ.</p></div><?php endif; ?>
            <?php foreach ($requests as $request): ?>
                <div class="request-item"><div class="request-icon"><i data-lucide="bell-ring"></i></div><div><strong><?= e($request['ten_ban']) ?></strong><span><?= e(str_replace('_', ' ', $request['loai_yeu_cau'])) ?></span><small><?= e(date('H:i', strtotime($request['ngay_tao']))) ?></small></div></div>
            <?php endforeach; ?>
        </div>
    </aside>
</section>

<section class="content-panel">
    <div class="panel-heading"><div><span class="eyebrow">HOẠT ĐỘNG GẦN ĐÂY</span><h2>Đơn hàng mới nhất</h2></div><a class="button button-secondary button-small" href="<?= url('kitchen') ?>"><i data-lucide="chef-hat"></i>Xem bếp</a></div>
    <div class="table-wrap"><table class="data-table"><thead><tr><th>Mã đơn</th><th>Bàn</th><th>Số món</th><th>Thời gian</th><th>Tổng tiền</th><th>Trạng thái</th></tr></thead><tbody>
    <?php if (!$orders): ?><tr><td colspan="6"><div class="empty-state compact"><p>Chưa có đơn hàng nào.</p></div></td></tr><?php endif; ?>
    <?php foreach ($orders as $order): ?><tr><td><strong><?= e($order['ma_don']) ?></strong></td><td><?= e($order['ten_ban']) ?></td><td><?= (int) $order['so_mon'] ?></td><td><?= e(date('H:i · d/m', strtotime($order['ngay_dat']))) ?></td><td><?= number_format($order['tong_tien_goc'], 0, ',', '.') ?>đ</td><td><span class="pill pill-<?= e($order['trang_thai']) ?>"><?= e($order['trang_thai']) ?></span></td></tr><?php endforeach; ?>
    </tbody></table></div>
</section>

