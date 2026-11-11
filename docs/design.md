# Phân tích và thiết kế

## Use case tổng thể

```mermaid
flowchart LR
  C[Khách hàng] --> QR[Quét và xác thực QR]
  C --> M[Xem menu và gửi món]
  C --> R[Gọi phục vụ / thanh toán]
  W[Phục vụ] --> T[Theo dõi bàn và yêu cầu]
  K[Nhân viên bếp] --> KS[Xử lý trạng thái món]
  CA[Thu ngân] --> I[Hóa đơn và thanh toán]
  A[Admin / Manager] --> AU[Tài khoản và phân quyền]
  A --> CM[Danh mục / sản phẩm]
  A --> TM[Bàn / QR]
```

## Phân quyền

| Route/Module | Admin | Manager | Cashier | Kitchen | Waiter | Customer |
|---|---:|---:|---:|---:|---:|---:|
| Dashboard | Có | Có | Có | Không | Có | Không |
| Danh mục/Sản phẩm/Bàn/QR | Có | Có | Không | Không | Không | Không |
| Màn hình bếp | Có | Có | Không | Có | Không | Không |
| Menu/giỏ hàng qua QR | Có token | Có token | Có token | Có token | Có token | Có token |
| API đặt món/yêu cầu | Có QR hợp lệ | Có QR hợp lệ | Có QR hợp lệ | Có QR hợp lệ | Có QR hợp lệ | Có QR hợp lệ |

## Sequence quét QR và gửi món

```mermaid
sequenceDiagram
  actor Customer
  participant Web
  participant QR as QrService
  participant Order as OrderService
  participant DB as MariaDB
  Customer->>Web: Mở /menu?token=random
  Web->>QR: validate(token)
  QR->>DB: SELECT token_hash, active, expiry, table
  DB-->>QR: Bàn hợp lệ
  Web-->>Customer: Menu từ database
  Customer->>Web: POST /api/orders + CSRF + items
  Web->>QR: Xác thực lại token
  Web->>Order: createOrder(table, items)
  Order->>DB: BEGIN; kiểm tra lại món/size/giá
  Order->>DB: Tạo phiên/đơn/chi tiết snapshot/thông báo
  Order->>DB: COMMIT
  Web-->>Customer: Mã đơn và tổng tiền
```

## DFD mức 1

```mermaid
flowchart TB
  Customer[Khách hàng] -->|QR token| P1((1. Xác thực bàn))
  P1 <--> QR[(qr_ban / ban_an)]
  Customer -->|Lựa chọn món| P2((2. Đặt món))
  Menu[(danh_muc / san_pham / size)] --> P2
  P2 --> Orders[(phien_ban / don_hang / chi_tiet)]
  Orders --> P3((3. Chế biến))
  Kitchen[Nhân viên bếp] <--> P3
  P3 --> History[(lịch sử trạng thái)]
  Customer -->|Yêu cầu bàn| P4((4. Phục vụ))
  P4 --> Requests[(yeu_cau_ban)]
  Waiter[Phục vụ / Thu ngân] <-->|xử lý| Requests
```

## ERD cốt lõi

```mermaid
erDiagram
  BAN_AN ||--o{ QR_BAN : owns
  BAN_AN ||--o{ PHIEN_BAN : has
  PHIEN_BAN ||--o{ DON_HANG : creates
  DON_HANG ||--|{ CHI_TIET_DON_HANG : includes
  SAN_PHAM ||--o{ CHI_TIET_DON_HANG : snapshot_from
  SAN_PHAM }o--|| DANH_MUC : belongs_to
  SAN_PHAM ||--o{ SAN_PHAM_SIZE : prices
  SIZE ||--o{ SAN_PHAM_SIZE : defines
  CHI_TIET_DON_HANG ||--o{ LICH_SU_TRANG_THAI_MON : tracks
  HOA_DON }o--o{ PHIEN_BAN : groups
  HOA_DON ||--o{ CHI_TIET_HOA_DON : splits
  CHI_TIET_DON_HANG ||--o{ CHI_TIET_HOA_DON : allocated_to
  HOA_DON ||--o{ THANH_TOAN : receives
  HOA_DON }o--o{ KHUYEN_MAI : applies
  PHIEN_BAN ||--o{ YEU_CAU_BAN : requests
  USERS ||--o{ LICH_SU_TRANG_THAI_MON : updates
```

## Wireframe khách hàng (mobile)

```text
+--------------------------------+
| TableTap         Bàn 01   [bag] |
+--------------------------------+
| Thực đơn hôm nay               |
| Chọn món bạn thích.            |
| [Tất cả] [Cà phê] [Trà] ...    |
+---------------+----------------+
| [ảnh món]     | [ảnh món]      |
| Tên món       | Tên món        |
| từ 29.000  [+]| từ 39.000  [+] |
+---------------+----------------+
| Menu       Giỏ hàng    Thanh toán|
+--------------------------------+
```

## Wireframe vận hành

```text
+------------+-------------------------------------------+
| TableTap   | Tiêu đề / trạng thái hệ thống             |
| Tổng quan  +-------------------------------------------+
| Danh mục   | KPI  | KPI  | KPI  | KPI                  |
| Sản phẩm   +-------------------------------------------+
| Bàn / QR   | Sơ đồ bàn             | Yêu cầu khách     |
| Bếp        +-------------------------------------------+
| Tài khoản  | Danh sách đơn / bảng dữ liệu / thao tác   |
+------------+-------------------------------------------+
```

## Kiến trúc code

```text
public/index.php
  -> Controllers (route/web/API)
     -> Services (QR, order, upload)
        -> Core/Database (PDO)
           -> MariaDB
  -> Views/layouts + assets
```

