<?php

namespace App\Services;

use App\Core\Auth;
use App\Core\Database;
use RuntimeException;

class OrderService
{
    public function createOrder($table, $items, $note = '')
    {
        if (!is_array($items) || count($items) < 1 || count($items) > 30) {
            throw new RuntimeException('Giỏ hàng phải có từ 1 đến 30 món.');
        }

        $pdo = Database::connection();
        $pdo->beginTransaction();
        try {
            $session = $this->findOrCreateSession($pdo, (int) $table['ban_id']);
            $orderCode = 'DH' . date('ymdHis') . strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
            $insertOrder = $pdo->prepare(
                "INSERT INTO don_hang (ma_don, phien_ban_id, trang_thai, tong_tien_goc, ghi_chu)
                 VALUES (:ma_don, :phien_ban_id, 'pending', 0, :ghi_chu)"
            );
            $insertOrder->execute(array('ma_don' => $orderCode, 'phien_ban_id' => $session['id'], 'ghi_chu' => trim($note)));
            $orderId = (int) $pdo->lastInsertId();
            $total = 0;

            foreach ($items as $item) {
                $productId = isset($item['product_id']) ? (int) $item['product_id'] : 0;
                $sizeId = isset($item['size_id']) ? (int) $item['size_id'] : 0;
                $quantity = isset($item['quantity']) ? (int) $item['quantity'] : 0;
                if ($productId < 1 || $sizeId < 1 || $quantity < 1 || $quantity > 20) {
                    throw new RuntimeException('Thông tin món trong giỏ hàng không hợp lệ.');
                }

                $productStatement = $pdo->prepare(
                    "SELECT p.id, p.ten_san_pham, s.id AS size_id, s.ten_size, ps.gia_ban
                     FROM san_pham p
                     INNER JOIN san_pham_size ps ON ps.san_pham_id = p.id
                     INNER JOIN size s ON s.id = ps.size_id
                     INNER JOIN danh_muc d ON d.id = p.danh_muc_id
                     WHERE p.id = :product_id AND s.id = :size_id AND p.trang_thai = 1 AND d.trang_thai = 1
                     LIMIT 1"
                );
                $productStatement->execute(array('product_id' => $productId, 'size_id' => $sizeId));
                $product = $productStatement->fetch();
                if (!$product) {
                    throw new RuntimeException('Một món hoặc size đã ngừng bán. Vui lòng tải lại menu.');
                }

                $itemNote = isset($item['note']) ? trim((string) $item['note']) : '';
                if (mb_strlen($itemNote, 'UTF-8') > 300) {
                    throw new RuntimeException('Ghi chú món không được quá 300 ký tự.');
                }
                $insertItem = $pdo->prepare(
                    "INSERT INTO chi_tiet_don_hang
                    (don_hang_id, san_pham_id, size_id, ten_san_pham_snapshot, ten_size_snapshot, so_luong, don_gia, ghi_chu)
                    VALUES (:don_hang_id, :san_pham_id, :size_id, :ten_san_pham, :ten_size, :so_luong, :don_gia, :ghi_chu)"
                );
                $insertItem->execute(array(
                    'don_hang_id' => $orderId,
                    'san_pham_id' => $productId,
                    'size_id' => $sizeId,
                    'ten_san_pham' => $product['ten_san_pham'],
                    'ten_size' => $product['ten_size'],
                    'so_luong' => $quantity,
                    'don_gia' => $product['gia_ban'],
                    'ghi_chu' => $itemNote,
                ));
                $total += ((float) $product['gia_ban']) * $quantity;
            }

            $updateOrder = $pdo->prepare('UPDATE don_hang SET tong_tien_goc = :total WHERE id = :id');
            $updateOrder->execute(array('total' => $total, 'id' => $orderId));
            $updateTable = $pdo->prepare("UPDATE ban_an SET trang_thai = 'occupied' WHERE id = :id AND trang_thai = 'available'");
            $updateTable->execute(array('id' => $table['ban_id']));
            $notify = $pdo->prepare(
                "INSERT INTO thong_bao (user_id, loai_thong_bao, tieu_de, noi_dung, tham_chieu_id)
                 SELECT id, 'don_hang_moi', :title, :content, :order_id FROM users
                 WHERE role IN ('kitchen','manager','admin') AND trang_thai = 1"
            );
            $notify->execute(array('title' => 'Đơn mới ' . $orderCode, 'content' => $table['ten_ban'] . ' vừa gửi món.', 'order_id' => $orderId));
            $pdo->commit();

            return array('id' => $orderId, 'code' => $orderCode, 'total' => $total, 'session_id' => (int) $session['id']);
        } catch (\Exception $exception) {
            $pdo->rollBack();
            throw $exception;
        }
    }

    public function requestService($table, $type, $content = '')
    {
        $allowed = array('goi_nhan_vien', 'yeu_cau_thanh_toan', 'them_dung_cu', 'khac');
        if (!in_array($type, $allowed, true)) {
            throw new RuntimeException('Loại yêu cầu không hợp lệ.');
        }
        $pdo = Database::connection();
        $pdo->beginTransaction();
        try {
            $session = $this->findOrCreateSession($pdo, (int) $table['ban_id']);
            if ($type === 'yeu_cau_thanh_toan') {
                $check = $pdo->prepare("SELECT COUNT(*) FROM chi_tiet_don_hang c JOIN don_hang d ON d.id = c.don_hang_id
                    WHERE d.phien_ban_id = ? AND c.trang_thai <> 'cancelled' AND NOT EXISTS
                    (SELECT 1 FROM chi_tiet_hoa_don l JOIN hoa_don h ON h.id = l.hoa_don_id
                     WHERE l.chi_tiet_don_hang_id = c.id AND h.trang_thai = 'paid')");
                $check->execute(array($session['id']));
                if (!(int) $check->fetchColumn()) {
                    throw new RuntimeException('Bàn chưa có món cần thanh toán.');
                }
                $existing = $pdo->prepare("SELECT id FROM yeu_cau_ban WHERE phien_ban_id = ?
                    AND loai_yeu_cau = 'yeu_cau_thanh_toan' AND trang_thai IN ('pending','processing') LIMIT 1");
                $existing->execute(array($session['id']));
                $requestId = $existing->fetchColumn();
                if ($requestId) {
                    $pdo->commit();
                    return array('id' => (int) $requestId, 'session_id' => (int) $session['id']);
                }
            }
            $statement = $pdo->prepare(
                'INSERT INTO yeu_cau_ban (phien_ban_id, loai_yeu_cau, noi_dung) VALUES (:phien_ban_id, :loai, :noi_dung)'
            );
            $statement->execute(array('phien_ban_id' => $session['id'], 'loai' => $type, 'noi_dung' => trim($content)));
            if ($type === 'yeu_cau_thanh_toan') {
                $pdo->prepare("UPDATE ban_an SET trang_thai = 'waiting_payment' WHERE id = :id")
                    ->execute(array('id' => $table['ban_id']));
            }
            $id = (int) $pdo->lastInsertId();
            $pdo->commit();
            return array('id' => $id, 'session_id' => (int) $session['id']);
        } catch (\Exception $exception) {
            $pdo->rollBack();
            throw $exception;
        }
    }

    public function updateItemStatus($itemId, $newStatus)
    {
        $transitions = array(
            'pending' => array('preparing', 'cancelled'),
            'preparing' => array('ready', 'cancelled'),
            'ready' => array('served'),
            'served' => array(),
            'cancelled' => array(),
        );
        $pdo = Database::connection();
        $pdo->beginTransaction();
        try {
            $statement = $pdo->prepare('SELECT id, don_hang_id, trang_thai FROM chi_tiet_don_hang WHERE id = :id FOR UPDATE');
            $statement->execute(array('id' => $itemId));
            $item = $statement->fetch();
            if (!$item || !isset($transitions[$item['trang_thai']]) || !in_array($newStatus, $transitions[$item['trang_thai']], true)) {
                throw new RuntimeException('Không thể chuyển món sang trạng thái này.');
            }
            $timeSql = $newStatus === 'preparing' ? ', thoi_gian_bat_dau = NOW()' : ($newStatus === 'ready' ? ', thoi_gian_hoan_thanh = NOW()' : '');
            $pdo->prepare('UPDATE chi_tiet_don_hang SET trang_thai = :status' . $timeSql . ' WHERE id = :id')
                ->execute(array('status' => $newStatus, 'id' => $itemId));
            $history = $pdo->prepare(
                'INSERT INTO lich_su_trang_thai_mon (chi_tiet_don_hang_id, nhan_vien_id, trang_thai_cu, trang_thai_moi) VALUES (:item, :user, :old, :new)'
            );
            $history->execute(array('item' => $itemId, 'user' => Auth::id(), 'old' => $item['trang_thai'], 'new' => $newStatus));
            $this->syncOrderStatus($pdo, (int) $item['don_hang_id']);
            $pdo->commit();
            return true;
        } catch (\Exception $exception) {
            $pdo->rollBack();
            throw $exception;
        }
    }

    private function findOrCreateSession($pdo, $tableId)
    {
        $table = $pdo->prepare('SELECT trang_thai FROM ban_an WHERE id = ? FOR UPDATE');
        $table->execute(array($tableId));
        $status = $table->fetchColumn();
        if (!$status || $status === 'maintenance') {
            throw new RuntimeException('Bàn hiện không nhận yêu cầu.');
        }
        $statement = $pdo->prepare("SELECT id, ma_phien FROM phien_ban WHERE ban_id = :ban_id AND trang_thai = 'open' ORDER BY id DESC LIMIT 1 FOR UPDATE");
        $statement->execute(array('ban_id' => $tableId));
        $session = $statement->fetch();
        if ($session) {
            return $session;
        }
        $code = 'PB' . date('ymdHis') . strtoupper(substr(bin2hex(random_bytes(2)), 0, 4));
        $insert = $pdo->prepare("INSERT INTO phien_ban (ban_id, ma_phien, trang_thai) VALUES (:ban_id, :ma_phien, 'open')");
        $insert->execute(array('ban_id' => $tableId, 'ma_phien' => $code));
        return array('id' => (int) $pdo->lastInsertId(), 'ma_phien' => $code);
    }

    private function syncOrderStatus($pdo, $orderId)
    {
        $statement = $pdo->prepare(
            "SELECT COUNT(*) AS total,
                    SUM(trang_thai = 'pending') AS pending_count,
                    SUM(trang_thai = 'preparing') AS preparing_count,
                    SUM(trang_thai = 'ready') AS ready_count,
                    SUM(trang_thai = 'served') AS served_count,
                    SUM(trang_thai = 'cancelled') AS cancelled_count
             FROM chi_tiet_don_hang WHERE don_hang_id = :id"
        );
        $statement->execute(array('id' => $orderId));
        $counts = $statement->fetch();
        $status = 'confirmed';
        if ((int) $counts['preparing_count'] > 0) {
            $status = 'preparing';
        } elseif ((int) $counts['ready_count'] > 0 && (int) $counts['pending_count'] === 0) {
            $status = 'ready';
        } elseif ((int) $counts['served_count'] + (int) $counts['cancelled_count'] === (int) $counts['total']) {
            $status = 'served';
        }
        $pdo->prepare('UPDATE don_hang SET trang_thai = :status WHERE id = :id')->execute(array('status' => $status, 'id' => $orderId));
    }
}

