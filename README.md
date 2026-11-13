# TableTap - QR Table Ordering System

Ứng dụng PHP thuần cho phép khách quét QR tại bàn, xem menu, gửi món và yêu cầu thanh toán; nhân viên quản lý menu, bàn, QR và xử lý món trên màn hình bếp.

## Yêu cầu môi trường

- PHP 7.0 trở lên với `pdo_mysql`, `fileinfo`, `mbstring`.
- MariaDB/MySQL (đã kiểm thử MariaDB 10.4.32).
- Apache bật `mod_rewrite` và `AllowOverride All` (XAMPP mặc định đáp ứng).

## Cài đặt trên XAMPP

1. Đặt repository tại `D:\xampp\htdocs\DoAnBeNhom9\DoAnBeNhom9`.
2. Import `database/install.sql` bằng phpMyAdmin hoặc MySQL CLI.
3. Chạy `database/migrations/20261106_week4.sql` nếu nâng cấp database cũ.
4. Sao chép `.env.example` thành `.env` khi cần đổi URL hoặc tài khoản database. Không commit `.env`.
5. Bật Apache và MySQL trong XAMPP.
6. Mở `http://localhost/DoAnBeNhom9/DoAnBeNhom9/public/login`.

Nếu thay đổi vị trí thư mục, cập nhật `APP_URL` trong `.env`.

## Tài khoản demo

| Vai trò | Tên đăng nhập | Mật khẩu |
|---|---|---|
| Admin | `admin` | `Admin@123` |
| Manager | `manager` | `Manager@123` |
| Cashier | `cashier` | `Cashier@123` |
| Kitchen | `kitchen` | `Kitchen@123` |
| Waiter | `waiter` | `Waiter@123` |
| Customer | `customer` | `Customer@123` |

Đăng ký công khai chỉ tạo tài khoản `customer`; client không thể tự gán vai trò nhân viên.

## Điểm vào ứng dụng

- Web front controller: `public/index.php`.
- API front controller: cũng qua `public/index.php`, prefix `/api/`.
- PHP development server: `D:\xampp\php\php.exe -S localhost:8080 router.php`.
- Schema đầy đủ: `database/install.sql`.

## Chức năng đã triển khai

- Đăng ký, đăng nhập, đăng xuất, hash mật khẩu và session/CSRF.
- Phân quyền backend cho Admin, Manager, Cashier, Kitchen, Waiter, Customer.
- CRUD/ẩn danh mục, sản phẩm, size/giá; upload ảnh có kiểm tra MIME và dung lượng.
- Quản lý bàn, khu vực, sức chứa, trạng thái và ngừng sử dụng.
- Tạo/thu hồi QR theo từng bàn; token chỉ lưu dưới dạng SHA-256, có ngày hết hạn.
- Menu responsive, chọn size, ghi chú, giỏ hàng, gửi món và gọi nhân viên/thanh toán.
- Giá/tên món được lấy lại từ database và lưu snapshot trong transaction khi đặt món.
- Màn hình bếp với luồng `pending -> preparing -> ready -> served` và lịch sử trạng thái.
- Màn hình thu ngân riêng: hàng đợi bàn, chi tiết hóa đơn, áp mã khuyến mãi, thanh toán một phần hoặc toàn bộ và tự giải phóng bàn.
- Schema sẵn cho phiên bàn, hóa đơn gộp/tách, nhiều lần thanh toán, khuyến mãi và thông báo.

## Kiểm thử nhanh

```powershell
Get-ChildItem -Recurse -Filter *.php | ForEach-Object { D:\xampp\php\php.exe -l $_.FullName }
D:\xampp\mysql\bin\mysql.exe -u root qr_table_ordering_db < database\migrations\20261106_week4.sql
```

Chi tiết thiết kế và tiêu chí nghiệm thu nằm trong `docs/requirements.md`, `docs/design.md` và `docs/test-cases.md`.

