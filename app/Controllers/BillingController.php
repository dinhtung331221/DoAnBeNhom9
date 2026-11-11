<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Core\View;
use App\Services\BillingService;
use App\Services\QrService;

class BillingController
{
    public function index()
    {
        Auth::requireRole(array('admin', 'manager', 'cashier'));
        $pdo = Database::connection();
        $items = (new BillingService())->availableItems($pdo);
        $invoices = $pdo->query("SELECT * FROM hoa_don WHERE trang_thai IN ('unpaid','paid')
            ORDER BY trang_thai = 'unpaid' DESC, id DESC LIMIT 100")->fetchAll();
        $revenue = $pdo->query("SELECT DATE(ngay_thanh_toan) AS day, COUNT(*) AS invoices,
            SUM(tong_thanh_toan) AS total FROM hoa_don WHERE trang_thai = 'paid'
            GROUP BY DATE(ngay_thanh_toan) ORDER BY day DESC LIMIT 30")->fetchAll();
        View::render('billing/index', compact('items', 'invoices', 'revenue') + array('title' => 'Thu ngân'));
    }

    public function customer()
    {
        $context = isset($_SESSION['table_context']) ? $_SESSION['table_context'] : null;
        $table = $context ? (new QrService())->validate($context['token']) : null;
        if (!$table) {
            http_response_code(403);
            View::render('menu/invalid', array('title' => 'Mã bàn hết hiệu lực'), 'layouts/customer');
            return;
        }
        $session = isset($_SESSION['billing_session'][$table['ban_id']])
            ? $_SESSION['billing_session'][$table['ban_id']] : 0;
        $statement = Database::connection()->prepare("SELECT h.* FROM hoa_don h JOIN hoa_don_phien_ban l ON l.hoa_don_id = h.id
            WHERE l.phien_ban_id = ? AND h.trang_thai IN ('unpaid','paid')
            AND (SELECT COUNT(*) FROM hoa_don_phien_ban x WHERE x.hoa_don_id = h.id) = 1 ORDER BY h.id DESC");
        $statement->execute(array($session));
        $invoices = $statement->fetchAll();
        View::render('billing/customer', compact('table', 'invoices') + array('title' => 'Thanh toán'), 'layouts/customer');
    }

    public function detail()
    {
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        $pdo = Database::connection();
        $query = $pdo->prepare("SELECT * FROM hoa_don WHERE id = ? AND trang_thai IN ('unpaid','paid')");
        $query->execute(array($id));
        $invoice = $query->fetch();
        $staff = Auth::hasRole(array('admin', 'manager', 'cashier'));
        if (!$staff) {
            $context = isset($_SESSION['table_context']) ? $_SESSION['table_context'] : null;
            $table = $context ? (new QrService())->validate($context['token']) : null;
            $session = $table && isset($_SESSION['billing_session'][$table['ban_id']])
                ? $_SESSION['billing_session'][$table['ban_id']] : 0;
            $query = $pdo->prepare('SELECT COUNT(*) FROM hoa_don_phien_ban WHERE hoa_don_id = ?
                HAVING COUNT(*) = 1 AND MIN(phien_ban_id) = ?');
            $query->execute(array($id, $session));
            if (!$query->fetchColumn()) {
                $invoice = false;
            }
        }
        if (!$invoice) {
            http_response_code(404);
            View::render('errors/404', array('title' => 'Không tìm thấy hóa đơn'), 'layouts/customer');
            return;
        }
        header('Cache-Control: no-store');
        $query = $pdo->prepare('SELECT l.*, c.ten_san_pham_snapshot, c.ten_size_snapshot, b.ten_ban
            FROM chi_tiet_hoa_don l JOIN chi_tiet_don_hang c ON c.id = l.chi_tiet_don_hang_id
            JOIN don_hang d ON d.id = c.don_hang_id JOIN phien_ban p ON p.id = d.phien_ban_id
            JOIN ban_an b ON b.id = p.ban_id WHERE l.hoa_don_id = ? ORDER BY l.id');
        $query->execute(array($id));
        $items = $query->fetchAll();
        $qr = (new BillingService())->qrUrl($invoice);
        View::render('billing/detail', compact('invoice', 'items', 'qr', 'staff') + array('title' => 'Hóa đơn'),
            $staff ? 'layouts/app' : 'layouts/customer');
    }

    public function create(Request $request)
    {
        $this->authorize($request);
        try {
            $id = (new BillingService())->create($request->input('items', array()), trim((string) $request->input('coupon', '')));
            header('Location: ' . url('invoice?id=' . $id), true, 303);
        } catch (\RuntimeException $exception) {
            flash('billing_error', $exception->getMessage());
            header('Location: ' . url('billing'), true, 303);
        }
        exit;
    }

    public function confirm(Request $request)
    {
        $this->authorize($request);
        $id = (int) $request->input('id', 0);
        try {
            (new BillingService())->confirm($id, strtoupper(trim((string) $request->input('reference', ''))),
                trim((string) $request->input('amount', '')));
        } catch (\RuntimeException $exception) {
            flash('billing_error', $exception->getMessage());
        }
        header('Location: ' . url('invoice?id=' . $id), true, 303);
        exit;
    }

    public function cancel(Request $request)
    {
        $this->authorize($request);
        try {
            (new BillingService())->cancel((int) $request->input('id', 0));
        } catch (\RuntimeException $exception) {
            flash('billing_error', $exception->getMessage());
        }
        header('Location: ' . url('billing'), true, 303);
        exit;
    }

    private function authorize(Request $request)
    {
        Auth::requireRole(array('admin', 'manager', 'cashier'));
        if (!$request->validateCsrf()) {
            http_response_code(419);
            exit('Phiên làm việc hết hạn. Vui lòng tải lại trang.');
        }
    }
}
