# Đặc tả yêu cầu tuần 1-4

## Phạm vi

Hệ thống phục vụ một nhà hàng/quán cà phê, mỗi QR ánh xạ đến đúng một bàn. Phạm vi hiện tại hoàn thiện nền tảng, xác thực, quản trị menu/bàn/QR, menu khách, giỏ hàng và màn hình bếp. Thanh toán cổng thật, WebSocket, báo cáo nâng cao và thao tác hóa đơn gộp/tách hoàn chỉnh nằm ngoài giai đoạn.

## Vai trò và chức năng

| Vai trò | Chức năng |
|---|---|
| Customer | Quét QR, xem menu, chọn size/số lượng, ghi chú, gửi đơn, gọi nhân viên, yêu cầu thanh toán |
| Waiter | Xem tổng quan bàn, đơn và yêu cầu phục vụ |
| Kitchen | Xem món cần chế biến, chuyển trạng thái món theo thứ tự |
| Cashier | Xem tổng quan bàn/đơn; schema đã chuẩn bị cho hóa đơn và nhiều lần thanh toán |
| Manager | Toàn bộ vận hành và CRUD menu/bàn/QR |
| Admin | Toàn bộ quyền quản trị |

## Luồng nghiệp vụ chính

1. Quản lý tạo QR; QR cũ đang hoạt động của cùng bàn bị thu hồi.
2. Khách mở URL từ QR; backend hash token và đối chiếu QR hoạt động/chưa hết hạn, không tin mã bàn từ client.
3. Khách chọn món, size, số lượng, ghi chú. Giỏ hàng chỉ là trạng thái tạm tại trình duyệt.
4. Khi gửi đơn, backend kiểm tra lại sản phẩm/size/giá, tạo hoặc dùng phiên bàn đang mở và lưu snapshot tên/size/giá.
5. Bếp nhận món `pending`, chuyển tuần tự sang `preparing`, `ready`, `served`; mỗi thay đổi được ghi lịch sử.
6. Khách gửi yêu cầu thanh toán; bàn chuyển `waiting_payment` để thu ngân nhận biết.

## Quy tắc hóa đơn, gộp/tách và khuyến mãi

- Một bàn có nhiều `phien_ban` theo thời gian; tại một thời điểm chỉ dùng phiên `open` mới nhất.
- Một phiên bàn có nhiều đơn hàng; một hóa đơn có thể liên kết nhiều phiên qua `hoa_don_phien_ban`.
- Khi tách hóa đơn, tổng `chi_tiet_hoa_don.so_luong` của một chi tiết món không được vượt `chi_tiet_don_hang.so_luong`.
- Hóa đơn có thể nhận nhiều dòng `thanh_toan`; trạng thái là `partially_paid` đến khi tổng tiền thành công đủ `tong_thanh_toan`.
- Khuyến mãi chỉ hợp lệ trong thời gian, còn lượt, đạt đơn tối thiểu và trạng thái hoạt động.
- Giảm phần trăm bị giới hạn bởi `gia_tri_toi_da`; tổng giảm không vượt tổng tiền gốc.
- Mọi thao tác tạo/gộp/tách/hủy/thanh toán hóa đơn phải ghi `lich_su_hoa_don`.

## Yêu cầu phi chức năng

- Responsive từ 360px đến desktop; thao tác chính dùng được bằng bàn phím.
- Prepared statement cho dữ liệu động; lỗi SQL không lộ ra ở môi trường production.
- Session dùng cookie `HttpOnly`, regenerate ID khi đăng nhập/đăng xuất; request thay đổi dữ liệu phải có CSRF token.
- Upload tối đa mặc định 2 MB; chỉ JPG/PNG/WEBP và MIME thực phải hợp lệ.
- Token QR ngẫu nhiên 256-bit, database chỉ lưu SHA-256; QR thu hồi/hết hạn bị trả `410/403` theo ngữ cảnh.
- Tạo đơn và cập nhật trạng thái chạy trong transaction; giá âm, số lượng ngoài giới hạn và trường bắt buộc bị từ chối.
- Mã nguồn tương thích cú pháp PHP 7.0, database utf8mb4.

## Quy ước Git

- Nhánh tính năng: `feature/<ten-ngan>`; sửa lỗi: `fix/<ten-ngan>`.
- Commit nhỏ, một mục đích; dạng `feat:`, `fix:`, `docs:`, `test:`.
- Không commit `.env`, log, session hoặc ảnh upload runtime.
- Trước merge: lint PHP, import SQL trên database sạch, chạy smoke test login/RBAC/CRUD/QR/order/kitchen.

