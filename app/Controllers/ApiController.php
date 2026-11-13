<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;
use App\Services\OrderService;
use App\Services\QrService;
use App\Services\UploadService;
use App\Services\InvoiceService;

class ApiController
{
    public function dispatch($path, Request $request)
    {
        if ($request->method() !== 'GET' && !$request->validateCsrf()) {
            Response::error('Phiên làm việc không hợp lệ. Hãy tải lại trang.', 419);
        }

        $routes = array(
            'categories/save' => 'saveCategory',
            'categories/toggle' => 'toggleCategory',
            'products/save' => 'saveProduct',
            'products/toggle' => 'toggleProduct',
            'tables/save' => 'saveTable',
            'tables/deactivate' => 'deactivateTable',
            'qr/generate' => 'generateQr',
            'qr/revoke' => 'revokeQr',
            'orders' => 'createOrder',
            'table-requests' => 'createTableRequest',
            'kitchen/status' => 'updateKitchenStatus',
            'cashier/quote' => 'cashierQuote',
            'cashier/pay' => 'cashierPay',
        );
        if (!isset($routes[$path])) {
            Response::error('API không tồn tại.', 404);
        }
        $method = $routes[$path];
        $this->$method($request);
    }

    private function saveCategory(Request $request)
    {
        $this->requireRole(array('admin', 'manager'));
        $data = $request->all();
        $errors = Validator::required($data, array('ten_danh_muc' => 'Tên danh mục'));
        if (mb_strlen(trim(isset($data['ten_danh_muc']) ? $data['ten_danh_muc'] : ''), 'UTF-8') > 100) {
            $errors['ten_danh_muc'] = 'Tên danh mục không được quá 100 ký tự.';
        }
        if ($errors) {
            Response::error('Dữ liệu chưa hợp lệ.', 422, $errors);
        }
        $parameters = array(
            'name' => trim($data['ten_danh_muc']),
            'description' => trim(isset($data['mo_ta']) ? $data['mo_ta'] : ''),
            'status' => isset($data['trang_thai']) ? (int) !!$data['trang_thai'] : 1,
        );
        $id = isset($data['id']) ? (int) $data['id'] : 0;
        if ($id > 0) {
            $parameters['id'] = $id;
            Database::connection()->prepare(
                'UPDATE danh_muc SET ten_danh_muc = :name, mo_ta = :description, trang_thai = :status WHERE id = :id'
            )->execute($parameters);
        } else {
            Database::connection()->prepare(
                'INSERT INTO danh_muc (ten_danh_muc, mo_ta, trang_thai) VALUES (:name, :description, :status)'
            )->execute($parameters);
            $id = (int) Database::connection()->lastInsertId();
        }
        Response::success(array('id' => $id), 'Đã lưu danh mục.');
    }

    private function toggleCategory(Request $request)
    {
        $this->requireRole(array('admin', 'manager'));
        $id = (int) $request->input('id', 0);
        $statement = Database::connection()->prepare('UPDATE danh_muc SET trang_thai = 1 - trang_thai WHERE id = :id');
        $statement->execute(array('id' => $id));
        Response::success(array(), 'Đã cập nhật trạng thái danh mục.');
    }

    private function saveProduct(Request $request)
    {
        $this->requireRole(array('admin', 'manager'));
        $data = $request->all();
        $errors = Validator::required($data, array(
            'ten_san_pham' => 'Tên sản phẩm',
            'danh_muc_id' => 'Danh mục',
            'gia_ban_chinh' => 'Giá cơ bản',
        ));
        $priceError = Validator::positiveNumber(isset($data['gia_ban_chinh']) ? $data['gia_ban_chinh'] : null, 'Giá cơ bản');
        if ($priceError) {
            $errors['gia_ban_chinh'] = $priceError;
        }
        $prices = isset($data['sizes']) && is_array($data['sizes']) ? $data['sizes'] : array();
        foreach ($prices as $sizeId => $price) {
            $error = Validator::positiveNumber($price, 'Giá size', false);
            if ($error) {
                $errors['sizes'] = $error;
                break;
            }
        }
        if (!$prices) {
            $errors['sizes'] = 'Cần nhập ít nhất một mức giá size.';
        }
        if ($errors) {
            Response::error('Dữ liệu chưa hợp lệ.', 422, $errors);
        }

        $id = isset($data['id']) ? (int) $data['id'] : 0;
        $image = (new UploadService())->productImage(isset($_FILES['hinh_anh']) ? $_FILES['hinh_anh'] : array());
        $pdo = Database::connection();
        $pdo->beginTransaction();
        try {
            $parameters = array(
                'name' => trim($data['ten_san_pham']),
                'category' => (int) $data['danh_muc_id'],
                'description' => trim(isset($data['mo_ta']) ? $data['mo_ta'] : ''),
                'price' => (float) $data['gia_ban_chinh'],
                'status' => isset($data['trang_thai']) ? (int) !!$data['trang_thai'] : 1,
            );
            if ($id > 0) {
                $parameters['id'] = $id;
                $imageSql = '';
                if ($image !== null) {
                    $parameters['image'] = $image;
                    $imageSql = ', hinh_anh = :image';
                }
                $pdo->prepare(
                    'UPDATE san_pham SET ten_san_pham = :name, danh_muc_id = :category, mo_ta = :description, gia_ban_chinh = :price, trang_thai = :status' . $imageSql . ' WHERE id = :id'
                )->execute($parameters);
            } else {
                $parameters['image'] = $image;
                $pdo->prepare(
                    'INSERT INTO san_pham (ten_san_pham, danh_muc_id, mo_ta, hinh_anh, gia_ban_chinh, trang_thai) VALUES (:name, :category, :description, :image, :price, :status)'
                )->execute($parameters);
                $id = (int) $pdo->lastInsertId();
            }
            $pdo->prepare('DELETE FROM san_pham_size WHERE san_pham_id = :id')->execute(array('id' => $id));
            $sizeStatement = $pdo->prepare('INSERT INTO san_pham_size (san_pham_id, size_id, gia_ban) VALUES (:product, :size, :price)');
            foreach ($prices as $sizeId => $price) {
                $sizeStatement->execute(array('product' => $id, 'size' => (int) $sizeId, 'price' => (float) $price));
            }
            $pdo->commit();
        } catch (\Exception $exception) {
            $pdo->rollBack();
            throw $exception;
        }
        Response::success(array('id' => $id), 'Đã lưu sản phẩm.');
    }

    private function toggleProduct(Request $request)
    {
        $this->requireRole(array('admin', 'manager'));
        $statement = Database::connection()->prepare('UPDATE san_pham SET trang_thai = 1 - trang_thai WHERE id = :id');
        $statement->execute(array('id' => (int) $request->input('id', 0)));
        Response::success(array(), 'Đã cập nhật trạng thái sản phẩm.');
    }

    private function saveTable(Request $request)
    {
        $this->requireRole(array('admin', 'manager'));
        $data = $request->all();
        $errors = Validator::required($data, array('ma_ban' => 'Mã bàn', 'ten_ban' => 'Tên bàn', 'suc_chua' => 'Sức chứa'));
        if (!preg_match('/^[A-Z0-9_-]{1,30}$/i', isset($data['ma_ban']) ? $data['ma_ban'] : '')) {
            $errors['ma_ban'] = 'Mã bàn chỉ gồm chữ, số, gạch ngang hoặc gạch dưới.';
        }
        if ((int) (isset($data['suc_chua']) ? $data['suc_chua'] : 0) < 1 || (int) $data['suc_chua'] > 50) {
            $errors['suc_chua'] = 'Sức chứa phải từ 1 đến 50.';
        }
        $statuses = array('available', 'occupied', 'waiting_payment', 'maintenance');
        if (!in_array(isset($data['trang_thai']) ? $data['trang_thai'] : '', $statuses, true)) {
            $errors['trang_thai'] = 'Trạng thái bàn không hợp lệ.';
        }
        if ($errors) {
            Response::error('Dữ liệu chưa hợp lệ.', 422, $errors);
        }
        $parameters = array(
            'code' => strtoupper(trim($data['ma_ban'])),
            'name' => trim($data['ten_ban']),
            'area' => trim(isset($data['khu_vuc']) ? $data['khu_vuc'] : ''),
            'capacity' => (int) $data['suc_chua'],
            'status' => $data['trang_thai'],
            'note' => trim(isset($data['ghi_chu']) ? $data['ghi_chu'] : ''),
        );
        $id = isset($data['id']) ? (int) $data['id'] : 0;
        try {
            if ($id > 0) {
                $parameters['id'] = $id;
                Database::connection()->prepare(
                    'UPDATE ban_an SET ma_ban = :code, ten_ban = :name, khu_vuc = :area, suc_chua = :capacity, trang_thai = :status, ghi_chu = :note WHERE id = :id'
                )->execute($parameters);
            } else {
                Database::connection()->prepare(
                    'INSERT INTO ban_an (ma_ban, ten_ban, khu_vuc, suc_chua, trang_thai, ghi_chu) VALUES (:code, :name, :area, :capacity, :status, :note)'
                )->execute($parameters);
                $id = (int) Database::connection()->lastInsertId();
            }
        } catch (\PDOException $exception) {
            Response::error('Mã bàn đã tồn tại hoặc dữ liệu không hợp lệ.', 422);
        }
        Response::success(array('id' => $id), 'Đã lưu thông tin bàn.');
    }

    private function deactivateTable(Request $request)
    {
        $this->requireRole(array('admin', 'manager'));
        $id = (int) $request->input('id', 0);
        $pdo = Database::connection();
        $pdo->beginTransaction();
        try {
            $pdo->prepare("UPDATE ban_an SET trang_thai = 'maintenance' WHERE id = :id")->execute(array('id' => $id));
            $pdo->prepare("UPDATE qr_ban SET trang_thai = 'revoked', ngay_thu_hoi = NOW() WHERE ban_id = :id AND trang_thai = 'active'")->execute(array('id' => $id));
            $pdo->commit();
        } catch (\Exception $exception) {
            $pdo->rollBack();
            throw $exception;
        }
        Response::success(array(), 'Đã ngừng sử dụng bàn và thu hồi QR.');
    }

    private function generateQr(Request $request)
    {
        $this->requireRole(array('admin', 'manager'));
        $expiresAt = trim((string) $request->input('expires_at', ''));
        if ($expiresAt !== '' && strtotime($expiresAt) <= time()) {
            Response::error('Ngày hết hạn phải ở tương lai.', 422);
        }
        $result = (new QrService())->generate((int) $request->input('table_id', 0), $expiresAt !== '' ? date('Y-m-d H:i:s', strtotime($expiresAt)) : null);
        Response::success($result, 'Đã tạo QR mới. QR cũ của bàn đã được thu hồi.', 201);
    }

    private function revokeQr(Request $request)
    {
        $this->requireRole(array('admin', 'manager'));
        (new QrService())->revoke((int) $request->input('id', 0));
        Response::success(array(), 'Đã thu hồi mã QR.');
    }

    private function createOrder(Request $request)
    {
        $context = isset($_SESSION['table_context']) ? $_SESSION['table_context'] : null;
        $table = $context ? (new QrService())->validate($context['token']) : null;
        if (!$table) {
            Response::error('Mã QR không còn hiệu lực. Vui lòng quét lại.', 403);
        }
        try {
            $order = (new OrderService())->createOrder($table, $request->input('items', array()), $request->input('note', ''));
            Response::success($order, 'Bếp đã nhận được đơn của bạn.', 201);
        } catch (\RuntimeException $exception) {
            Response::error($exception->getMessage(), 422);
        }
    }

    private function createTableRequest(Request $request)
    {
        $context = isset($_SESSION['table_context']) ? $_SESSION['table_context'] : null;
        $table = $context ? (new QrService())->validate($context['token']) : null;
        if (!$table) {
            Response::error('Mã QR không còn hiệu lực.', 403);
        }
        try {
            $id = (new OrderService())->requestService($table, $request->input('type', ''), $request->input('content', ''));
            Response::success(array('id' => $id), 'Yêu cầu đã được gửi.', 201);
        } catch (\RuntimeException $exception) {
            Response::error($exception->getMessage(), 422);
        }
    }

    private function updateKitchenStatus(Request $request)
    {
        $this->requireRole(array('admin', 'manager', 'kitchen'));
        try {
            (new OrderService())->updateItemStatus((int) $request->input('item_id', 0), (string) $request->input('status', ''));
            Response::success(array(), 'Đã cập nhật trạng thái món.');
        } catch (\RuntimeException $exception) {
            Response::error($exception->getMessage(), 422);
        }
    }

    private function cashierQuote(Request $request)
    {
        $this->requireRole(array('admin', 'manager', 'cashier'));
        try {
            $quote = (new InvoiceService())->quote((int) $request->input('session_id', 0), (string) $request->input('promotion_code', ''));
            Response::success($quote, $quote['promotion'] ? 'Đã áp dụng mã khuyến mãi.' : 'Đã cập nhật hóa đơn.');
        } catch (\RuntimeException $exception) {
            Response::error($exception->getMessage(), 422);
        }
    }

    private function cashierPay(Request $request)
    {
        $this->requireRole(array('admin', 'manager', 'cashier'));
        try {
            $result = (new InvoiceService())->pay(
                (int) $request->input('session_id', 0),
                (string) $request->input('promotion_code', ''),
                (string) $request->input('method', ''),
                $request->input('amount', null),
                (string) $request->input('note', '')
            );
            Response::success($result, $result['status'] === 'paid' ? 'Thanh toán hoàn tất. Bàn đã được giải phóng.' : 'Đã ghi nhận thanh toán một phần.');
        } catch (\RuntimeException $exception) {
            Response::error($exception->getMessage(), 422);
        }
    }

    private function requireRole($roles)
    {
        if (!Auth::check()) {
            Response::error('Bạn chưa đăng nhập.', 401);
        }
        if (!Auth::hasRole($roles)) {
            Response::error('Bạn không có quyền thực hiện thao tác này.', 403);
        }
    }
}
