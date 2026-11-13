# Tiêu chí nghiệm thu và test cases

| ID | Tình huống | Kết quả mong đợi |
|---|---|---|
| AUTH-01 | Đăng nhập đúng tài khoản demo | Tạo session mới, chuyển đến trang đúng vai trò |
| AUTH-02 | Sai mật khẩu | Không tạo session, hiện thông báo chung |
| AUTH-03 | Kitchen mở `/admin/products` | HTTP 403 |
| AUTH-04 | Đăng ký và cố gửi trường role | Tài khoản vẫn là `customer` |
| CAT-01 | Tạo/sửa danh mục hợp lệ | Database cập nhật, toast thành công |
| CAT-02 | Bỏ trống tên | API 422, không ghi database |
| PROD-01 | Tạo sản phẩm có đủ size/giá | Sản phẩm và `san_pham_size` lưu trong transaction |
| PROD-02 | Giá âm | API 422 |
| PROD-03 | Upload file không phải ảnh hoặc >2 MB | Bị từ chối, không lưu file |
| TABLE-01 | Trùng mã bàn | API 422 |
| TABLE-02 | Ngừng bàn | Bàn `maintenance`, QR active bị thu hồi |
| QR-01 | Tạo QR mới cho bàn | QR cũ bị revoked, chỉ token mới mở menu |
| QR-02 | Token giả/thu hồi/hết hạn | Không hiển thị menu, HTTP 410/403 |
| ORDER-01 | Gửi giỏ hợp lệ | Tạo phiên/đơn/chi tiết, giá snapshot đúng DB |
| ORDER-02 | Client sửa giá trong localStorage/request | Backend bỏ qua giá client và lấy lại từ DB |
| ORDER-03 | Size/sản phẩm đã ẩn | API 422 và rollback toàn bộ đơn |
| KITCHEN-01 | `pending -> preparing -> ready -> served` | Thành công và có lịch sử |
| KITCHEN-02 | `pending -> served` | API 422 |
| CASHIER-01 | Cashier đăng nhập | Chuyển thẳng đến `/cashier`, không dùng dashboard chung |
| CASHIER-02 | Áp mã hợp lệ/không hợp lệ | Backend tính giảm đúng hoặc trả API 422 |
| CASHIER-03 | Thanh toán đủ | Hóa đơn `paid`, phiên `closed`, bàn `available`, yêu cầu hoàn tất |
| CASHIER-04 | Thanh toán một phần | Hóa đơn `partially_paid`, phiên và bàn vẫn mở |
| UI-01 | 360px, 768px, 1440px | Không chồng chữ; menu, bảng và điều hướng dùng được |
| ERROR-01 | Database ngắt kết nối | Thông báo chung, chi tiết chỉ ghi error log |

## Smoke test đã thực hiện trên XAMPP

- PHP lint toàn bộ file: đạt.
- Import `install.sql` trên MariaDB 10.4: đạt sau khi chuẩn hóa cú pháp seed `INSERT ... SELECT`.
- Migration QR/index: đạt.
- `GET /login`: HTTP 200, CSS/JS/ảnh được phục vụ qua Apache.
- Đăng nhập admin: chuyển `/dashboard` và trả HTTP 200.
- Tạo QR -> mở menu -> gửi đơn -> đơn xuất hiện ở bếp: đạt.
- Kitchen chuyển món sang `preparing`: đạt; mở trang sản phẩm: HTTP 403.

