# TableTap · Gọi món tại bàn bằng QR

Ứng dụng PHP và MariaDB dành cho quán cà phê hoặc nhà hàng. Khách quét QR bàn để xem menu, chọn size, ghi chú, gửi món và yêu cầu thanh toán. Màn hình bếp tự làm mới danh sách mỗi 5 giây khi đang mở. Thu ngân lập hóa đơn, áp mã giảm giá, đối chiếu VietQR và in phiếu.

## Chạy trên XAMPP

1. Bật **MySQL** trong XAMPP. Trong phpMyAdmin, tạo database `qr_table_ordering_db` với collation `utf8mb4_unicode_ci`, chọn database đó và import `database/qr_table_ordering_db.sql`. Nếu database đang chạy đã có các bảng hóa đơn, tiếp tục dùng database đó.
2. Mở PowerShell trong thư mục dự án. Sao chép `.env.example` thành `.env` và đổi `APP_URL` thành `http://localhost:8080`. Kiểm tra thông tin `DB_*` khớp MySQL. Dùng PHP 7.1 trở lên với `pdo_mysql`, `fileinfo` và `mbstring`.
3. Chạy lệnh dưới đây và mở `http://localhost:8080/login`.

```powershell
C:\xampp\php\php.exe -S 0.0.0.0:8080 -t public router.php
```

Để quét QR bàn bằng điện thoại, cho điện thoại và máy tính dùng cùng Wi-Fi, đổi `APP_URL` thành địa chỉ LAN của máy tính kèm cổng 8080, mở ứng dụng bằng URL đó và tạo lại QR bàn. `localhost` trên điện thoại trỏ về chính điện thoại. Khi đưa lên hosting, đặt thư mục public làm document root, bật rewrite và cấu hình APP_URL HTTPS, DB và QR_TOKEN_SECRET.

## Tài khoản demo

| Vai trò | Tên đăng nhập | Mật khẩu |
|---|---|---|
| Quản trị | admin | Admin@123 |
| Quản lý | manager | Manager@123 |
| Thu ngân | cashier | Cashier@123 |
| Bếp | kitchen | Kitchen@123 |
| Phục vụ | waiter | Waiter@123 |

Khách có thể đăng ký tài khoản khách hoặc gọi món trực tiếp sau khi quét QR bàn.

## Quy trình thanh toán

1. Quản trị vào **Mã QR** để tạo mã cho bàn. Khách quét mã, gửi món và mở **Thanh toán → Yêu cầu thanh toán**.
2. Bếp chuyển món sang **Sẵn sàng phục vụ**. Trong **Thu ngân**, chọn dòng món và nhập mã giảm giá nếu có. Chọn món từ nhiều bàn để gộp. Lập từng hóa đơn cho từng nhóm dòng món để tách. Hóa đơn chưa nhận tiền có thể hủy để chọn lại món.
3. Sau khi lập, trang hóa đơn hiện VietQR của **TPBank · 79206033999 · TRAN KHAI**, kèm số tiền sau giảm và mã hóa đơn. Khách xem hóa đơn của phiên bàn trên màn hình Thanh toán. Với hóa đơn gộp nhiều bàn, thu ngân mở mã để khách quét.
4. Thu ngân đối chiếu trên sao kê hoặc ứng dụng ngân hàng rồi nhập **mã giao dịch** và **số tiền thực nhận**. Hệ thống từ chối số tiền sai, mã giao dịch đã dùng và xác nhận lặp. Sau khi xác nhận, phiếu có nút **In / Lưu PDF**. Trong hộp thoại in, chọn **Save as PDF** để lưu file.
5. Khi mọi món còn hiệu lực trong phiên đã được thanh toán, hệ thống đóng phiên và trả bàn về trạng thái sẵn sàng. Chỉ hóa đơn đã thanh toán được cộng vào doanh thu ngày.

VietQR dùng Quick Link và cần Internet để tải ảnh. Xác nhận nhận tiền do thu ngân thực hiện. Mã QR bàn mở thực đơn và mã VietQR chuyển khoản là hai mã riêng. Phiếu in là phiếu thanh toán nội bộ.

## Kiểm thử nhanh

- Khách thử mở trang Thu ngân và xác nhận hóa đơn. Hệ thống phải từ chối.
- Gọi hai dòng món, chuyển cả hai sang Sẵn sàng, tạo hóa đơn cho một dòng. Dòng còn lại phải xuất hiện để lập hóa đơn thứ hai.
- Lập hóa đơn cho các món của hai bàn để kiểm tra gộp. Thử mã giảm giá còn hiệu lực và so sánh số tiền trên hóa đơn với số tiền trong app ngân hàng trước khi xác nhận.
- Thử nhập số tiền sai, thanh toán hai lần và dùng lại mã giao dịch. Doanh thu chỉ tăng một lần sau khi thu ngân xác nhận thành công.
- Mở phiếu đã thanh toán và dùng bản xem trước in để kiểm tra PDF.

