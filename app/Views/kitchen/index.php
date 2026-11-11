<section class="kitchen-toolbar"><div class="kitchen-legend"><span><i class="dot dot-pending"></i>Chờ làm</span><span><i class="dot dot-preparing"></i>Đang làm</span><span><i class="dot dot-ready"></i>Sẵn sàng</span></div><button class="button button-secondary" onclick="location.reload()"><i data-lucide="refresh-cw"></i>Làm mới</button></section>
<section class="kitchen-board">
<?php foreach (array('pending' => 'Chờ chế biến', 'preparing' => 'Đang chế biến', 'ready' => 'Sẵn sàng phục vụ') as $status => $label): ?>
    <?php $statusItems = array_values(array_filter($items, function ($item) use ($status) { return $item['trang_thai'] === $status; })); ?>
    <div class="kitchen-column column-<?= e($status) ?>"><div class="kitchen-column-heading"><div><i class="dot dot-<?= e($status) ?>"></i><h2><?= e($label) ?></h2></div><span><?= count($statusItems) ?></span></div><div class="kitchen-items">
    <?php if (!$statusItems): ?><div class="empty-state compact"><i data-lucide="check-circle-2"></i><p>Không có món ở trạng thái này.</p></div><?php endif; ?>
    <?php foreach ($statusItems as $item): ?>
        <article class="kitchen-ticket <?= (int) $item['so_phut_cho'] >= 15 ? 'is-late' : '' ?>">
            <div class="ticket-heading"><div><strong><?= e($item['ten_san_pham_snapshot']) ?></strong><span><?= e($item['ten_size_snapshot']) ?> · x<?= (int) $item['so_luong'] ?></span></div><b><?= e($item['ten_ban']) ?></b></div>
            <?php if ($item['ghi_chu']): ?><p class="ticket-note"><i data-lucide="message-square"></i><?= e($item['ghi_chu']) ?></p><?php endif; ?>
            <div class="ticket-meta"><span><?= e($item['ma_don']) ?></span><span class="<?= (int) $item['so_phut_cho'] >= 15 ? 'late-time' : '' ?>"><i data-lucide="clock-3"></i><?= (int) $item['so_phut_cho'] ?> phút</span></div>
            <?php if ($status === 'pending'): ?><button class="button button-amber button-block" data-kitchen-status="preparing" data-item-id="<?= (int) $item['id'] ?>">Bắt đầu làm <i data-lucide="flame"></i></button><?php endif; ?>
            <?php if ($status === 'preparing'): ?><button class="button button-teal button-block" data-kitchen-status="ready" data-item-id="<?= (int) $item['id'] ?>">Hoàn thành <i data-lucide="check"></i></button><?php endif; ?>
            <?php if ($status === 'ready'): ?><button class="button button-primary button-block" data-kitchen-status="served" data-item-id="<?= (int) $item['id'] ?>">Đã phục vụ <i data-lucide="send"></i></button><?php endif; ?>
        </article>
    <?php endforeach; ?></div></div>
<?php endforeach; ?>
</section>

