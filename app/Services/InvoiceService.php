<?php

namespace App\Services;

use App\Core\Auth;
use App\Core\Database;
use RuntimeException;

class InvoiceService
{
    public function dashboardData()
    {
        $pdo = Database::connection();
        $sessions = $pdo->query(
            "SELECT p.id AS session_id, p.ma_phien, p.thoi_gian_bat_dau,
                    b.id AS table_id, b.ma_ban, b.ten_ban, b.khu_vuc, b.trang_thai AS table_status,
                    (SELECT COUNT(*) FROM don_hang d WHERE d.phien_ban_id = p.id AND d.trang_thai <> 'cancelled') AS order_count,
                    (SELECT COALESCE(SUM(c.so_luong * c.don_gia), 0)
                       FROM chi_tiet_don_hang c INNER JOIN don_hang d ON d.id = c.don_hang_id
                      WHERE d.phien_ban_id = p.id AND d.trang_thai <> 'cancelled' AND c.trang_thai <> 'cancelled') AS subtotal,
                    (SELECT COUNT(*) FROM yeu_cau_ban y WHERE y.phien_ban_id = p.id AND y.loai_yeu_cau = 'yeu_cau_thanh_toan' AND y.trang_thai IN ('pending','processing')) AS payment_requested
               FROM phien_ban p INNER JOIN ban_an b ON b.id = p.ban_id
              WHERE p.trang_thai = 'open'
                AND EXISTS (SELECT 1 FROM don_hang d2 WHERE d2.phien_ban_id = p.id AND d2.trang_thai <> 'cancelled')
              ORDER BY (b.trang_thai = 'waiting_payment') DESC, p.thoi_gian_bat_dau"
        )->fetchAll();

        $items = $pdo->query(
            "SELECT d.phien_ban_id AS session_id, c.id, c.ten_san_pham_snapshot, c.ten_size_snapshot,
                    c.so_luong, c.don_gia, c.ghi_chu, c.trang_thai, d.ma_don
               FROM chi_tiet_don_hang c INNER JOIN don_hang d ON d.id = c.don_hang_id
               INNER JOIN phien_ban p ON p.id = d.phien_ban_id
              WHERE p.trang_thai = 'open' AND d.trang_thai <> 'cancelled' AND c.trang_thai <> 'cancelled'
              ORDER BY d.ngay_dat, c.id"
        )->fetchAll();
        $itemsBySession = array();
        foreach ($items as $item) {
            $key = (int) $item['session_id'];
            if (!isset($itemsBySession[$key])) {
                $itemsBySession[$key] = array();
            }
            $item['so_luong'] = (int) $item['so_luong'];
            $item['don_gia'] = (float) $item['don_gia'];
            $item['thanh_tien'] = $item['so_luong'] * $item['don_gia'];
            $itemsBySession[$key][] = $item;
        }
        $invoiceRows = $pdo->query(
            "SELECT hp.phien_ban_id, h.id AS invoice_id, h.ma_hoa_don, h.tong_tien_goc, h.tien_giam,
                    h.tong_thanh_toan, h.trang_thai AS invoice_status,
                    COALESCE((SELECT SUM(t.so_tien) FROM thanh_toan t WHERE t.hoa_don_id = h.id AND t.trang_thai = 'da_thanh_toan'), 0) AS paid_amount,
                    (SELECT k.ma_code FROM hoa_don_khuyen_mai hk INNER JOIN khuyen_mai k ON k.id = hk.khuyen_mai_id WHERE hk.hoa_don_id = h.id LIMIT 1) AS promotion_code
               FROM hoa_don_phien_ban hp INNER JOIN hoa_don h ON h.id = hp.hoa_don_id
              WHERE h.trang_thai IN ('unpaid','partially_paid')"
        )->fetchAll();
        $invoiceBySession = array();
        foreach ($invoiceRows as $invoiceRow) {
            $invoiceBySession[(int) $invoiceRow['phien_ban_id']] = $invoiceRow;
        }
        foreach ($sessions as &$session) {
            $session['session_id'] = (int) $session['session_id'];
            $session['table_id'] = (int) $session['table_id'];
            $session['order_count'] = (int) $session['order_count'];
            $session['subtotal'] = (float) $session['subtotal'];
            $session['payment_requested'] = (int) $session['payment_requested'];
            $session['items'] = isset($itemsBySession[$session['session_id']]) ? $itemsBySession[$session['session_id']] : array();
            $invoice = isset($invoiceBySession[$session['session_id']]) ? $invoiceBySession[$session['session_id']] : null;
            $session['invoice_status'] = $invoice ? $invoice['invoice_status'] : 'unpaid';
            $session['invoice_subtotal'] = $invoice ? (float) $invoice['tong_tien_goc'] : $session['subtotal'];
            $session['discount'] = $invoice ? (float) $invoice['tien_giam'] : 0;
            $session['invoice_total'] = $invoice ? (float) $invoice['tong_thanh_toan'] : $session['subtotal'];
            $session['paid_amount'] = $invoice ? (float) $invoice['paid_amount'] : 0;
            $session['remaining'] = max(0, $session['invoice_total'] - $session['paid_amount']);
            $session['promotion_code'] = $invoice ? $invoice['promotion_code'] : null;
        }
        unset($session);

        $stats = array(
            'waiting' => (int) $pdo->query("SELECT COUNT(DISTINCT y.phien_ban_id) FROM yeu_cau_ban y INNER JOIN phien_ban p ON p.id = y.phien_ban_id WHERE p.trang_thai = 'open' AND y.loai_yeu_cau = 'yeu_cau_thanh_toan' AND y.trang_thai IN ('pending','processing')")->fetchColumn(),
            'open_total' => array_sum(array_map(function ($session) { return $session['subtotal']; }, $sessions)),
            'paid_today' => (float) $pdo->query("SELECT COALESCE(SUM(tong_thanh_toan),0) FROM hoa_don WHERE trang_thai = 'paid' AND DATE(ngay_thanh_toan) = CURDATE()")->fetchColumn(),
            'invoice_count' => (int) $pdo->query("SELECT COUNT(*) FROM hoa_don WHERE trang_thai = 'paid' AND DATE(ngay_thanh_toan) = CURDATE()")->fetchColumn(),
        );
        $recent = $pdo->query(
            "SELECT h.ma_hoa_don, h.tong_thanh_toan, h.ngay_thanh_toan, u.ho_ten,
                    GROUP_CONCAT(DISTINCT b.ten_ban ORDER BY b.ten_ban SEPARATOR ', ') AS ten_ban
               FROM hoa_don h
               LEFT JOIN users u ON u.id = h.nhan_vien_thanh_toan
               LEFT JOIN hoa_don_phien_ban hp ON hp.hoa_don_id = h.id
               LEFT JOIN phien_ban p ON p.id = hp.phien_ban_id
               LEFT JOIN ban_an b ON b.id = p.ban_id
              WHERE h.trang_thai = 'paid'
              GROUP BY h.id ORDER BY h.ngay_thanh_toan DESC LIMIT 8"
        )->fetchAll();
        return array('sessions' => $sessions, 'stats' => $stats, 'recent' => $recent);
    }

    public function quote($sessionId, $promotionCode = '')
    {
        $pdo = Database::connection();
        $state = $this->invoiceState($pdo, (int) $sessionId);
        $quote = $this->calculate($pdo, (int) $sessionId, $state && $state['paid_amount'] > 0 ? '' : trim($promotionCode), false);
        if ($state && $state['paid_amount'] > 0) {
            $quote['subtotal'] = (float) $state['tong_tien_goc'];
            $quote['discount'] = (float) $state['tien_giam'];
            $quote['total'] = (float) $state['tong_thanh_toan'];
            $quote['invoice_status'] = $state['invoice_status'];
            $quote['promotion_code'] = $state['promotion_code'];
        } else {
            $quote['invoice_status'] = 'unpaid';
            $quote['promotion_code'] = $quote['promotion'] ? $quote['promotion']['ma_code'] : null;
        }
        $quote['paid'] = $state ? (float) $state['paid_amount'] : 0;
        $quote['remaining'] = max(0, $quote['total'] - $quote['paid']);
        return $quote;
    }

    public function pay($sessionId, $promotionCode, $method, $amount, $note = '')
    {
        $methods = array('tien_mat', 'chuyen_khoan', 'vi_dien_tu', 'the_ngan_hang');
        if (!in_array($method, $methods, true)) {
            throw new RuntimeException('Phương thức thanh toán không hợp lệ.');
        }
        $pdo = Database::connection();
        $pdo->beginTransaction();
        try {
            $existingState = $this->invoiceState($pdo, (int) $sessionId);
            $quote = $this->calculate($pdo, (int) $sessionId, $existingState && $existingState['paid_amount'] > 0 ? '' : trim($promotionCode), true);
            $invoiceStatement = $pdo->prepare(
                "SELECT h.* FROM hoa_don h INNER JOIN hoa_don_phien_ban hp ON hp.hoa_don_id = h.id
                  WHERE hp.phien_ban_id = :session_id AND h.trang_thai IN ('unpaid','partially_paid')
                  ORDER BY h.id DESC LIMIT 1 FOR UPDATE"
            );
            $invoiceStatement->execute(array('session_id' => $sessionId));
            $invoice = $invoiceStatement->fetch();
            if (!$invoice) {
                $code = 'HD' . date('ymdHis') . strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
                $pdo->prepare(
                    "INSERT INTO hoa_don (ma_hoa_don, nhan_vien_thanh_toan, tong_tien_goc, tien_giam, tong_thanh_toan, trang_thai, ghi_chu)
                     VALUES (:code, :user, :subtotal, :discount, :total, 'unpaid', :note)"
                )->execute(array('code' => $code, 'user' => Auth::id(), 'subtotal' => $quote['subtotal'], 'discount' => $quote['discount'], 'total' => $quote['total'], 'note' => trim($note)));
                $invoiceId = (int) $pdo->lastInsertId();
                $pdo->prepare('INSERT INTO hoa_don_phien_ban (hoa_don_id, phien_ban_id) VALUES (:invoice, :session)')
                    ->execute(array('invoice' => $invoiceId, 'session' => $sessionId));
                $invoice = array('id' => $invoiceId, 'ma_hoa_don' => $code, 'tong_thanh_toan' => $quote['total']);
            } else {
                $invoiceId = (int) $invoice['id'];
            }

            $paidStatement = $pdo->prepare("SELECT COALESCE(SUM(so_tien),0) FROM thanh_toan WHERE hoa_don_id = :id AND trang_thai = 'da_thanh_toan'");
            $paidStatement->execute(array('id' => $invoiceId));
            $alreadyPaid = (float) $paidStatement->fetchColumn();
            if ($alreadyPaid <= 0) {
                $pdo->prepare('DELETE FROM chi_tiet_hoa_don WHERE hoa_don_id = :id')->execute(array('id' => $invoiceId));
                $detail = $pdo->prepare(
                    'INSERT INTO chi_tiet_hoa_don (hoa_don_id, chi_tiet_don_hang_id, so_luong, don_gia, thanh_tien) VALUES (:invoice, :item, :quantity, :price, :total)'
                );
                foreach ($quote['items'] as $item) {
                    $detail->execute(array('invoice' => $invoiceId, 'item' => $item['id'], 'quantity' => $item['so_luong'], 'price' => $item['don_gia'], 'total' => $item['thanh_tien']));
                }
                $pdo->prepare('DELETE FROM hoa_don_khuyen_mai WHERE hoa_don_id = :id')->execute(array('id' => $invoiceId));
                if ($quote['promotion']) {
                    $pdo->prepare('INSERT INTO hoa_don_khuyen_mai (hoa_don_id, khuyen_mai_id, so_tien_giam) VALUES (:invoice, :promotion, :discount)')
                        ->execute(array('invoice' => $invoiceId, 'promotion' => $quote['promotion']['id'], 'discount' => $quote['discount']));
                    $pdo->prepare('UPDATE khuyen_mai SET da_su_dung = da_su_dung + 1 WHERE id = :id')->execute(array('id' => $quote['promotion']['id']));
                }
                $pdo->prepare('UPDATE hoa_don SET nhan_vien_thanh_toan = :user, tong_tien_goc = :subtotal, tien_giam = :discount, tong_thanh_toan = :total, ghi_chu = :note WHERE id = :id')
                    ->execute(array('user' => Auth::id(), 'subtotal' => $quote['subtotal'], 'discount' => $quote['discount'], 'total' => $quote['total'], 'note' => trim($note), 'id' => $invoiceId));
                $invoice['tong_thanh_toan'] = $quote['total'];
            }

            $grandTotal = (float) $invoice['tong_thanh_toan'];
            $remaining = max(0, $grandTotal - $alreadyPaid);
            $paymentAmount = $amount === null || $amount === '' ? $remaining : (float) $amount;
            if ($paymentAmount <= 0 || $paymentAmount > $remaining) {
                throw new RuntimeException('Số tiền thanh toán phải lớn hơn 0 và không vượt số tiền còn lại.');
            }
            $transactionCode = 'TT' . date('ymdHis') . strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
            $pdo->prepare(
                "INSERT INTO thanh_toan (hoa_don_id, ma_giao_dich, phuong_thuc, so_tien, trang_thai, nguoi_thu, ghi_chu)
                 VALUES (:invoice, :code, :method, :amount, 'da_thanh_toan', :user, :note)"
            )->execute(array('invoice' => $invoiceId, 'code' => $transactionCode, 'method' => $method, 'amount' => $paymentAmount, 'user' => Auth::id(), 'note' => trim($note)));
            $newPaid = $alreadyPaid + $paymentAmount;
            $status = $newPaid >= $grandTotal ? 'paid' : 'partially_paid';
            $pdo->prepare("UPDATE hoa_don SET trang_thai = :status, ngay_thanh_toan = IF(:paid_status = 'paid', NOW(), ngay_thanh_toan) WHERE id = :id")
                ->execute(array('status' => $status, 'paid_status' => $status, 'id' => $invoiceId));
            $pdo->prepare("INSERT INTO lich_su_hoa_don (hoa_don_id, loai_thao_tac, mo_ta, nguoi_thuc_hien) VALUES (:invoice, 'thanh_toan', :description, :user)")
                ->execute(array('invoice' => $invoiceId, 'description' => 'Ghi nhận ' . number_format($paymentAmount, 0, ',', '.') . 'đ qua ' . $method, 'user' => Auth::id()));

            if ($status === 'paid') {
                $pdo->prepare("UPDATE phien_ban SET trang_thai = 'closed', thoi_gian_ket_thuc = NOW() WHERE id = :id")
                    ->execute(array('id' => $sessionId));
                $pdo->prepare("UPDATE ban_an SET trang_thai = 'available' WHERE id = :id")
                    ->execute(array('id' => $quote['session']['table_id']));
                $pdo->prepare("UPDATE yeu_cau_ban SET trang_thai = 'completed', nhan_vien_xu_ly = :user, ngay_xu_ly = NOW() WHERE phien_ban_id = :session AND loai_yeu_cau = 'yeu_cau_thanh_toan' AND trang_thai IN ('pending','processing')")
                    ->execute(array('user' => Auth::id(), 'session' => $sessionId));
            }
            $pdo->commit();
            return array('invoice_code' => $invoice['ma_hoa_don'], 'transaction_code' => $transactionCode, 'status' => $status, 'paid' => $newPaid, 'remaining' => max(0, $grandTotal - $newPaid));
        } catch (\Exception $exception) {
            $pdo->rollBack();
            throw $exception;
        }
    }

    private function calculate($pdo, $sessionId, $promotionCode, $lock)
    {
        $sql = "SELECT p.id, p.ma_phien, p.trang_thai, b.id AS table_id, b.ma_ban, b.ten_ban, b.khu_vuc
                  FROM phien_ban p INNER JOIN ban_an b ON b.id = p.ban_id
                 WHERE p.id = :id AND p.trang_thai = 'open' LIMIT 1" . ($lock ? ' FOR UPDATE' : '');
        $statement = $pdo->prepare($sql);
        $statement->execute(array('id' => $sessionId));
        $session = $statement->fetch();
        if (!$session) {
            throw new RuntimeException('Phiên bàn không còn mở hoặc không tồn tại.');
        }
        $itemsStatement = $pdo->prepare(
            "SELECT c.id, c.ten_san_pham_snapshot, c.ten_size_snapshot, c.so_luong, c.don_gia,
                    (c.so_luong * c.don_gia) AS thanh_tien
               FROM chi_tiet_don_hang c INNER JOIN don_hang d ON d.id = c.don_hang_id
              WHERE d.phien_ban_id = :session AND d.trang_thai <> 'cancelled' AND c.trang_thai <> 'cancelled' ORDER BY c.id"
        );
        $itemsStatement->execute(array('session' => $sessionId));
        $items = $itemsStatement->fetchAll();
        if (!$items) {
            throw new RuntimeException('Phiên bàn chưa có món hợp lệ để thanh toán.');
        }
        $subtotal = 0;
        foreach ($items as &$item) {
            $item['id'] = (int) $item['id'];
            $item['so_luong'] = (int) $item['so_luong'];
            $item['don_gia'] = (float) $item['don_gia'];
            $item['thanh_tien'] = (float) $item['thanh_tien'];
            $subtotal += $item['thanh_tien'];
        }
        unset($item);
        $promotion = null;
        $discount = 0;
        if ($promotionCode !== '') {
            $promoStatement = $pdo->prepare(
                "SELECT * FROM khuyen_mai WHERE UPPER(ma_code) = UPPER(:code) AND trang_thai = 1
                   AND NOW() BETWEEN ngay_bat_dau AND ngay_ket_thuc
                   AND (so_lan_su_dung IS NULL OR da_su_dung < so_lan_su_dung) LIMIT 1"
            );
            $promoStatement->execute(array('code' => $promotionCode));
            $promotion = $promoStatement->fetch();
            if (!$promotion) {
                throw new RuntimeException('Mã khuyến mãi không tồn tại, hết hạn hoặc hết lượt sử dụng.');
            }
            if ($subtotal < (float) $promotion['don_toi_thieu']) {
                throw new RuntimeException('Hóa đơn chưa đạt giá trị tối thiểu của mã khuyến mãi.');
            }
            if ($promotion['loai_khuyen_mai'] === 'phan_tram') {
                $discount = $subtotal * (float) $promotion['gia_tri'] / 100;
                if ($promotion['gia_tri_toi_da'] !== null) {
                    $discount = min($discount, (float) $promotion['gia_tri_toi_da']);
                }
            } else {
                $discount = (float) $promotion['gia_tri'];
            }
            $discount = min($subtotal, round($discount));
        }
        $session['table_id'] = (int) $session['table_id'];
        return array('session' => $session, 'items' => $items, 'subtotal' => $subtotal, 'discount' => $discount, 'total' => max(0, $subtotal - $discount), 'promotion' => $promotion);
    }

    private function invoiceState($pdo, $sessionId)
    {
        $statement = $pdo->prepare(
            "SELECT h.tong_tien_goc, h.tien_giam, h.tong_thanh_toan, h.trang_thai AS invoice_status,
                    COALESCE((SELECT SUM(t.so_tien) FROM thanh_toan t WHERE t.hoa_don_id = h.id AND t.trang_thai = 'da_thanh_toan'), 0) AS paid_amount,
                    (SELECT k.ma_code FROM hoa_don_khuyen_mai hk INNER JOIN khuyen_mai k ON k.id = hk.khuyen_mai_id WHERE hk.hoa_don_id = h.id LIMIT 1) AS promotion_code
               FROM hoa_don h INNER JOIN hoa_don_phien_ban hp ON hp.hoa_don_id = h.id
              WHERE hp.phien_ban_id = :session AND h.trang_thai IN ('unpaid','partially_paid') ORDER BY h.id DESC LIMIT 1"
        );
        $statement->execute(array('session' => $sessionId));
        $state = $statement->fetch();
        if ($state) {
            $state['paid_amount'] = (float) $state['paid_amount'];
        }
        return $state ?: null;
    }
}
