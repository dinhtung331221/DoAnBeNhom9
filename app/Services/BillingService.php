<?php

namespace App\Services;

use App\Core\Auth;
use App\Core\Database;
use RuntimeException;

class BillingService
{
    const BANK_BIN = '970423';
    const ACCOUNT = '79206033999';
    const ACCOUNT_NAME = 'TRAN KHAI';

    public function availableItems($pdo)
    {
        return $pdo->query("SELECT c.id, c.ten_san_pham_snapshot, c.ten_size_snapshot, c.so_luong,
            c.don_gia, b.ten_ban, p.id AS session_id, d.ma_don
            FROM chi_tiet_don_hang c
            JOIN don_hang d ON d.id = c.don_hang_id
            JOIN phien_ban p ON p.id = d.phien_ban_id
            JOIN ban_an b ON b.id = p.ban_id
            WHERE p.trang_thai = 'open' AND c.trang_thai IN ('ready','served')
            AND NOT EXISTS (SELECT 1 FROM chi_tiet_hoa_don l JOIN hoa_don h ON h.id = l.hoa_don_id
                WHERE l.chi_tiet_don_hang_id = c.id AND h.trang_thai <> 'cancelled')
            ORDER BY b.ma_ban, c.id")->fetchAll();
    }

    public function create($selection, $coupon)
    {
        if (!is_array($selection) || !$selection || count($selection) > 300) {
            throw new RuntimeException('Chọn từ 1 đến 300 dòng món để lập hóa đơn.');
        }
        $ids = array_unique(array_map('intval', $selection));
        $pdo = Database::connection();
        $pdo->beginTransaction();
        try {
            $pdo->query('SELECT id FROM ban_an ORDER BY id FOR UPDATE')->fetchAll();
            $items = array_filter($this->availableItems($pdo), function ($item) use ($ids) {
                return in_array((int) $item['id'], $ids, true);
            });
            if (count($items) !== count($ids)) {
                throw new RuntimeException('Có món đã được lập hóa đơn hoặc chưa sẵn sàng. Vui lòng tải lại.');
            }
            $total = 0;
            foreach ($items as $item) {
                $total += (int) $item['don_gia'] * (int) $item['so_luong'];
            }

            $discount = 0;
            $promotion = null;
            if ($coupon !== '') {
                $query = $pdo->prepare("SELECT * FROM khuyen_mai WHERE ma_code = ? AND trang_thai = 1
                    AND NOW() BETWEEN ngay_bat_dau AND ngay_ket_thuc
                    AND (so_lan_su_dung IS NULL OR da_su_dung < so_lan_su_dung) FOR UPDATE");
                $query->execute(array($coupon));
                $promotion = $query->fetch();
                if (!$promotion || $total < (int) $promotion['don_toi_thieu']) {
                    throw new RuntimeException('Mã giảm giá không hợp lệ hoặc chưa đạt giá trị đơn tối thiểu.');
                }
                $discount = $promotion['loai_khuyen_mai'] === 'phan_tram'
                    ? (int) round($total * (float) $promotion['gia_tri'] / 100)
                    : (int) round($promotion['gia_tri']);
                $discount = max(0, min($total, $discount,
                    $promotion['gia_tri_toi_da'] === null ? $total : (int) $promotion['gia_tri_toi_da']));
            }

            $code = 'HD' . strtoupper(bin2hex(random_bytes(6)));
            $pdo->prepare('INSERT INTO hoa_don (ma_hoa_don, tong_tien_goc, tien_giam, tong_thanh_toan)
                VALUES (?, ?, ?, ?)')->execute(array($code, $total, $discount, $total - $discount));
            $id = (int) $pdo->lastInsertId();
            $line = $pdo->prepare('INSERT INTO chi_tiet_hoa_don
                (hoa_don_id, chi_tiet_don_hang_id, so_luong, don_gia, thanh_tien) VALUES (?, ?, ?, ?, ?)');
            $sessions = array();
            foreach ($items as $item) {
                $line->execute(array($id, $item['id'], $item['so_luong'], $item['don_gia'],
                    (int) $item['so_luong'] * (int) $item['don_gia']));
                $sessions[$item['session_id']] = true;
            }
            $link = $pdo->prepare('INSERT INTO hoa_don_phien_ban (hoa_don_id, phien_ban_id) VALUES (?, ?)');
            foreach (array_keys($sessions) as $sessionId) {
                $link->execute(array($id, $sessionId));
            }
            if ($promotion) {
                $pdo->prepare('INSERT INTO hoa_don_khuyen_mai (hoa_don_id, khuyen_mai_id, so_tien_giam)
                    VALUES (?, ?, ?)')->execute(array($id, $promotion['id'], $discount));
                $pdo->prepare('UPDATE khuyen_mai SET da_su_dung = da_su_dung + 1 WHERE id = ?')
                    ->execute(array($promotion['id']));
            }
            $this->history($pdo, $id, count($sessions) > 1 ? 'gop' : 'tao', 'Lập hóa đơn theo các món đã chọn.');
            $pdo->commit();
            return $id;
        } catch (\Throwable $exception) {
            $pdo->rollBack();
            throw $exception;
        }
    }

    public function confirm($id, $reference, $amount)
    {
        $pdo = Database::connection();
        $pdo->beginTransaction();
        try {
            $pdo->query('SELECT id FROM ban_an ORDER BY id FOR UPDATE')->fetchAll();
            $query = $pdo->prepare('SELECT * FROM hoa_don WHERE id = ? FOR UPDATE');
            $query->execute(array($id));
            $invoice = $query->fetch();
            if (!$invoice || $invoice['trang_thai'] !== 'unpaid') {
                throw new RuntimeException('Hóa đơn không còn chờ xác nhận.');
            }
            $due = (int) $invoice['tong_thanh_toan'];
            if ($amount !== (string) $due || ($due > 0 && !preg_match('/^[A-Z0-9._-]{3,100}$/D', $reference))) {
                throw new RuntimeException('Nhập đúng số tiền thực nhận và mã giao dịch ngân hàng.');
            }
            if ($due > 0) {
                $query = $pdo->prepare("SELECT id FROM thanh_toan WHERE ma_giao_dich = ?");
                $query->execute(array($reference));
                if ($query->fetchColumn()) {
                    throw new RuntimeException('Mã giao dịch đã được dùng cho một hóa đơn.');
                }
                $pdo->prepare("INSERT INTO thanh_toan (hoa_don_id, ma_giao_dich, phuong_thuc, so_tien, trang_thai, nguoi_thu)
                    VALUES (?, ?, 'chuyen_khoan', ?, 'da_thanh_toan', ?)")
                    ->execute(array($id, $reference, $due, Auth::id()));
            }
            $pdo->prepare("UPDATE hoa_don SET trang_thai = 'paid', ngay_thanh_toan = NOW(), nhan_vien_thanh_toan = ? WHERE id = ?")
                ->execute(array(Auth::id(), $id));
            $this->history($pdo, $id, 'thanh_toan', $due > 0 ? 'Thu ngân đã đối chiếu giao dịch ' . $reference : 'Hóa đơn 0 đồng.');

            $sessions = $pdo->prepare('SELECT p.id, p.ban_id FROM phien_ban p JOIN hoa_don_phien_ban l
                ON l.phien_ban_id = p.id WHERE l.hoa_don_id = ?');
            $sessions->execute(array($id));
            $remaining = $pdo->prepare("SELECT COUNT(*) FROM chi_tiet_don_hang c JOIN don_hang d ON d.id = c.don_hang_id
                WHERE d.phien_ban_id = ? AND c.trang_thai <> 'cancelled' AND NOT EXISTS
                (SELECT 1 FROM chi_tiet_hoa_don l JOIN hoa_don h ON h.id = l.hoa_don_id
                 WHERE l.chi_tiet_don_hang_id = c.id AND h.trang_thai = 'paid')");
            foreach ($sessions->fetchAll() as $session) {
                $remaining->execute(array($session['id']));
                if ((int) $remaining->fetchColumn() !== 0) {
                    continue;
                }
                $pdo->prepare("UPDATE phien_ban SET trang_thai = 'closed', thoi_gian_ket_thuc = NOW() WHERE id = ?")
                    ->execute(array($session['id']));
                $pdo->prepare("UPDATE ban_an SET trang_thai = 'available' WHERE id = ? AND trang_thai <> 'maintenance'")
                    ->execute(array($session['ban_id']));
                $pdo->prepare("UPDATE yeu_cau_ban SET trang_thai = 'completed', ngay_xu_ly = NOW(), nhan_vien_xu_ly = ?
                    WHERE phien_ban_id = ? AND loai_yeu_cau = 'yeu_cau_thanh_toan' AND trang_thai IN ('pending','processing')")
                    ->execute(array(Auth::id(), $session['id']));
            }
            $pdo->commit();
        } catch (\Throwable $exception) {
            $pdo->rollBack();
            throw $exception;
        }
    }

    public function cancel($id)
    {
        $pdo = Database::connection();
        $pdo->beginTransaction();
        try {
            $pdo->query('SELECT id FROM ban_an ORDER BY id FOR UPDATE')->fetchAll();
            $change = $pdo->prepare("UPDATE hoa_don SET trang_thai = 'cancelled' WHERE id = ? AND trang_thai = 'unpaid'");
            $change->execute(array($id));
            if ($change->rowCount() !== 1) {
                throw new RuntimeException('Chỉ có thể hủy hóa đơn chưa thanh toán.');
            }
            $pdo->prepare('UPDATE khuyen_mai k JOIN hoa_don_khuyen_mai h ON h.khuyen_mai_id = k.id
                SET k.da_su_dung = GREATEST(0, k.da_su_dung - 1) WHERE h.hoa_don_id = ?')->execute(array($id));
            $this->history($pdo, $id, 'huy', 'Hủy hóa đơn chưa thanh toán để lập lại.');
            $pdo->commit();
        } catch (\Throwable $exception) {
            $pdo->rollBack();
            throw $exception;
        }
    }

    public function qrUrl($invoice)
    {
        return 'https://img.vietqr.io/image/' . self::BANK_BIN . '-' . self::ACCOUNT . '-compact2.png?'
            . http_build_query(array(
                'amount' => (int) $invoice['tong_thanh_toan'],
                'addInfo' => $invoice['ma_hoa_don'],
                'accountName' => self::ACCOUNT_NAME,
            ), '', '&', PHP_QUERY_RFC3986);
    }

    private function history($pdo, $id, $action, $description)
    {
        $pdo->prepare('INSERT INTO lich_su_hoa_don (hoa_don_id, loai_thao_tac, mo_ta, nguoi_thuc_hien)
            VALUES (?, ?, ?, ?)')->execute(array($id, $action, $description, Auth::id()));
    }
}
