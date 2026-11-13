<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\View;
use App\Services\QrService;

class PageController
{
    public function home()
    {
        if (Auth::check()) {
            $destination = Auth::hasRole('customer') ? 'customer' : (Auth::hasRole('kitchen') ? 'kitchen' : 'dashboard');
            header('Location: ' . url($destination));
        } else {
            header('Location: ' . url('login'));
        }
        exit;
    }

    public function customer()
    {
        Auth::requireRole(array('customer'));
        $context = isset($_SESSION['table_context']) ? $_SESSION['table_context'] : null;
        $table = $context ? (new QrService())->validate($context['token']) : null;
        if ($table) {
            header('Location: ' . url('menu?token=' . rawurlencode($context['token'])));
            exit;
        }
        View::render('menu/customer-home', array('title' => 'Khách hàng'), 'layouts/customer');
    }

    public function dashboard()
    {
        Auth::requireRole(array('admin', 'manager', 'cashier', 'waiter'));
        $pdo = Database::connection();
        $stats = array(
            'orders_today' => (int) $pdo->query("SELECT COUNT(*) FROM don_hang WHERE DATE(ngay_dat) = CURDATE()")->fetchColumn(),
            'pending_items' => (int) $pdo->query("SELECT COUNT(*) FROM chi_tiet_don_hang WHERE trang_thai IN ('pending','preparing')")->fetchColumn(),
            'occupied_tables' => (int) $pdo->query("SELECT COUNT(*) FROM ban_an WHERE trang_thai IN ('occupied','waiting_payment')")->fetchColumn(),
            'revenue_today' => (float) $pdo->query("SELECT COALESCE(SUM(tong_thanh_toan),0) FROM hoa_don WHERE trang_thai = 'paid' AND DATE(ngay_thanh_toan) = CURDATE()")->fetchColumn(),
        );
        $tables = $pdo->query('SELECT * FROM ban_an ORDER BY khu_vuc, ma_ban')->fetchAll();
        $orders = $pdo->query(
            "SELECT d.id, d.ma_don, d.trang_thai, d.tong_tien_goc, d.ngay_dat, b.ten_ban,
                    COUNT(c.id) AS so_mon
             FROM don_hang d
             INNER JOIN phien_ban p ON p.id = d.phien_ban_id
             INNER JOIN ban_an b ON b.id = p.ban_id
             LEFT JOIN chi_tiet_don_hang c ON c.don_hang_id = d.id
             GROUP BY d.id ORDER BY d.id DESC LIMIT 8"
        )->fetchAll();
        $requests = $pdo->query(
            "SELECT y.*, b.ten_ban FROM yeu_cau_ban y
             INNER JOIN phien_ban p ON p.id = y.phien_ban_id
             INNER JOIN ban_an b ON b.id = p.ban_id
             WHERE y.trang_thai IN ('pending','processing') ORDER BY y.ngay_tao DESC LIMIT 8"
        )->fetchAll();
        View::render('dashboard/index', compact('stats', 'tables', 'orders', 'requests') + array('title' => 'Tổng quan'));
    }

    public function categories()
    {
        Auth::requireRole(array('admin', 'manager'));
        $categories = Database::connection()->query(
            'SELECT d.*, COUNT(p.id) AS so_san_pham FROM danh_muc d LEFT JOIN san_pham p ON p.danh_muc_id = d.id GROUP BY d.id ORDER BY d.id DESC'
        )->fetchAll();
        View::render('admin/categories', array('title' => 'Danh mục', 'categories' => $categories));
    }

    public function products()
    {
        Auth::requireRole(array('admin', 'manager'));
        $pdo = Database::connection();
        $products = $pdo->query(
            "SELECT p.*, d.ten_danh_muc,
                    GROUP_CONCAT(CONCAT(s.id, ':', s.ten_size, ':', ps.gia_ban) ORDER BY s.id SEPARATOR '|') AS sizes
             FROM san_pham p INNER JOIN danh_muc d ON d.id = p.danh_muc_id
             LEFT JOIN san_pham_size ps ON ps.san_pham_id = p.id
             LEFT JOIN size s ON s.id = ps.size_id
             GROUP BY p.id ORDER BY p.id DESC"
        )->fetchAll();
        $categories = $pdo->query('SELECT id, ten_danh_muc FROM danh_muc WHERE trang_thai = 1 ORDER BY ten_danh_muc')->fetchAll();
        $sizes = $pdo->query('SELECT * FROM size ORDER BY id')->fetchAll();
        View::render('admin/products', compact('products', 'categories', 'sizes') + array('title' => 'Sản phẩm'));
    }

    public function tables()
    {
        Auth::requireRole(array('admin', 'manager'));
        $tables = Database::connection()->query('SELECT * FROM ban_an ORDER BY khu_vuc, ma_ban')->fetchAll();
        View::render('admin/tables', array('title' => 'Bàn phục vụ', 'tables' => $tables));
    }

    public function qrCodes()
    {
        Auth::requireRole(array('admin', 'manager'));
        $qrCodes = Database::connection()->query(
            "SELECT q.*, b.ma_ban, b.ten_ban, b.khu_vuc FROM qr_ban q
             INNER JOIN ban_an b ON b.id = q.ban_id ORDER BY q.id DESC"
        )->fetchAll();
        $tables = Database::connection()->query("SELECT id, ma_ban, ten_ban FROM ban_an WHERE trang_thai <> 'maintenance' ORDER BY ma_ban")->fetchAll();
        View::render('admin/qr', array('title' => 'Mã QR', 'qrCodes' => $qrCodes, 'tables' => $tables));
    }

    public function menu()
    {
        $token = isset($_GET['token']) ? (string) $_GET['token'] : '';
        $table = (new QrService())->validate($token);
        if (!$table) {
            http_response_code(410);
            View::render('menu/invalid', array('title' => 'Mã QR không hợp lệ'), 'layouts/customer');
            return;
        }
        $_SESSION['table_context'] = array('token' => $token, 'table' => $table);
        $pdo = Database::connection();
        $categories = $pdo->query('SELECT * FROM danh_muc WHERE trang_thai = 1 ORDER BY id')->fetchAll();
        $products = $pdo->query(
            "SELECT p.*, d.ten_danh_muc,
                    GROUP_CONCAT(CONCAT(s.id, ':', s.ten_size, ':', ps.gia_ban) ORDER BY s.id SEPARATOR '|') AS sizes
             FROM san_pham p INNER JOIN danh_muc d ON d.id = p.danh_muc_id AND d.trang_thai = 1
             INNER JOIN san_pham_size ps ON ps.san_pham_id = p.id
             INNER JOIN size s ON s.id = ps.size_id
             WHERE p.trang_thai = 1 GROUP BY p.id ORDER BY p.id"
        )->fetchAll();
        View::render('menu/index', compact('table', 'categories', 'products') + array('title' => 'Menu'), 'layouts/customer');
    }

    public function cart()
    {
        $context = isset($_SESSION['table_context']) ? $_SESSION['table_context'] : null;
        $table = $context ? (new QrService())->validate($context['token']) : null;
        if (!$table) {
            header('Location: ' . url('menu'));
            exit;
        }
        View::render('menu/cart', array('title' => 'Giỏ hàng', 'table' => $table), 'layouts/customer');
    }

    public function kitchen()
    {
        Auth::requireRole(array('admin', 'manager', 'kitchen'));
        $items = Database::connection()->query(
            "SELECT c.*, d.ma_don, d.ngay_dat, b.ten_ban,
                    TIMESTAMPDIFF(MINUTE, d.ngay_dat, NOW()) AS so_phut_cho
             FROM chi_tiet_don_hang c
             INNER JOIN don_hang d ON d.id = c.don_hang_id
             INNER JOIN phien_ban p ON p.id = d.phien_ban_id
             INNER JOIN ban_an b ON b.id = p.ban_id
             WHERE c.trang_thai IN ('pending','preparing','ready')
             ORDER BY FIELD(c.trang_thai, 'preparing','pending','ready'), d.ngay_dat"
        )->fetchAll();
        View::render('kitchen/index', array('title' => 'Màn hình bếp', 'items' => $items));
    }
}
