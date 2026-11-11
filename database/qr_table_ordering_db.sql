-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th10 11, 2026 lúc 03:31 PM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `qr_table_ordering_db`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `ban_an`
--

CREATE TABLE `ban_an` (
  `id` int(11) NOT NULL,
  `ma_ban` varchar(30) NOT NULL,
  `ten_ban` varchar(100) NOT NULL,
  `khu_vuc` varchar(100) DEFAULT NULL,
  `suc_chua` int(11) DEFAULT 4,
  `trang_thai` enum('available','occupied','waiting_payment','maintenance') DEFAULT 'available',
  `ghi_chu` text DEFAULT NULL,
  `ngay_tao` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `ban_an`
--

INSERT INTO `ban_an` (`id`, `ma_ban`, `ten_ban`, `khu_vuc`, `suc_chua`, `trang_thai`, `ghi_chu`, `ngay_tao`) VALUES
(1, 'B01', 'Bàn 01', 'Tầng trệt', 2, 'occupied', NULL, '2026-11-06 14:14:17'),
(2, 'B02', 'Bàn 02', 'Tầng trệt', 4, 'available', NULL, '2026-11-06 14:14:17'),
(3, 'B03', 'Bàn 03', 'Tầng trệt', 4, 'available', NULL, '2026-11-06 14:14:17'),
(4, 'B04', 'Bàn 04', 'Tầng trệt', 6, 'available', NULL, '2026-11-06 14:14:17'),
(5, 'B05', 'Bàn 05', 'Lầu 1', 2, 'available', NULL, '2026-11-06 14:14:17'),
(6, 'B06', 'Bàn 06', 'Lầu 1', 4, 'available', NULL, '2026-11-06 14:14:17'),
(7, 'B07', 'Bàn 07', 'Lầu 1', 4, 'available', NULL, '2026-11-06 14:14:17'),
(8, 'B08', 'Bàn 08', 'Lầu 1', 6, 'available', NULL, '2026-11-06 14:14:17'),
(9, 'B09', 'Ban 09', 'Tang 2', 4, 'available', NULL, '2026-11-06 14:14:17'),
(10, 'B10', 'Ban 10', 'Tang 2', 8, 'available', NULL, '2026-11-06 14:14:17');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chi_tiet_don_hang`
--

CREATE TABLE `chi_tiet_don_hang` (
  `id` int(11) NOT NULL,
  `don_hang_id` int(11) NOT NULL,
  `san_pham_id` int(11) DEFAULT NULL,
  `size_id` int(11) DEFAULT NULL,
  `ten_san_pham_snapshot` varchar(200) NOT NULL,
  `ten_size_snapshot` varchar(20) DEFAULT NULL,
  `so_luong` int(11) NOT NULL DEFAULT 1,
  `don_gia` decimal(12,0) NOT NULL,
  `ghi_chu` text DEFAULT NULL,
  `trang_thai` enum('pending','preparing','ready','served','cancelled') DEFAULT 'pending',
  `thoi_gian_bat_dau` datetime DEFAULT NULL,
  `thoi_gian_hoan_thanh` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `chi_tiet_don_hang`
--

INSERT INTO `chi_tiet_don_hang` (`id`, `don_hang_id`, `san_pham_id`, `size_id`, `ten_san_pham_snapshot`, `ten_size_snapshot`, `so_luong`, `don_gia`, `ghi_chu`, `trang_thai`, `thoi_gian_bat_dau`, `thoi_gian_hoan_thanh`) VALUES
(1, 1, 1, 1, 'Cà phê sữa đá', 'S', 1, 29000, 'Kiem thu luong', 'preparing', '2026-11-06 16:09:34', NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chi_tiet_hoa_don`
--

CREATE TABLE `chi_tiet_hoa_don` (
  `id` int(11) NOT NULL,
  `hoa_don_id` int(11) NOT NULL,
  `chi_tiet_don_hang_id` int(11) NOT NULL,
  `so_luong` int(11) NOT NULL,
  `don_gia` decimal(12,0) NOT NULL,
  `thanh_tien` decimal(12,0) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `danh_muc`
--

CREATE TABLE `danh_muc` (
  `id` int(11) NOT NULL,
  `ten_danh_muc` varchar(100) NOT NULL,
  `mo_ta` text DEFAULT NULL,
  `hinh_anh` varchar(500) DEFAULT NULL,
  `trang_thai` tinyint(1) DEFAULT 1,
  `ngay_tao` datetime DEFAULT current_timestamp(),
  `cap_nhat` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `danh_muc`
--

INSERT INTO `danh_muc` (`id`, `ten_danh_muc`, `mo_ta`, `hinh_anh`, `trang_thai`, `ngay_tao`, `cap_nhat`) VALUES
(1, 'Cà phê', 'Cà phê rang xay và espresso', NULL, 1, '2026-11-06 14:14:16', '2026-11-06 16:04:52'),
(2, 'Trà', 'Trà trái cây và trà sữa', NULL, 1, '2026-11-06 14:14:16', '2026-11-06 16:04:52'),
(3, 'Đá xay', 'Đồ uống mát lạnh, xay mịn', NULL, 1, '2026-11-06 14:14:16', '2026-11-06 16:04:52'),
(4, 'Nước ép', 'Nước ép trái cây tươi', NULL, 1, '2026-11-06 14:14:16', '2026-11-06 16:04:52'),
(5, 'dfvsế', 'făcf', NULL, 1, '2026-11-06 16:12:58', '2026-11-06 16:13:06'),
(6, 'nước ngọt', 'áddsas', NULL, 1, '2026-11-06 16:14:39', '2026-11-06 16:14:39'),
(7, 'sinh tố', 'ádsa', NULL, 1, '2026-11-06 16:17:51', '2026-11-06 16:17:51');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `don_hang`
--

CREATE TABLE `don_hang` (
  `id` int(11) NOT NULL,
  `ma_don` varchar(50) NOT NULL,
  `phien_ban_id` int(11) NOT NULL,
  `khach_hang_id` int(11) DEFAULT NULL,
  `nhan_vien_xac_nhan` int(11) DEFAULT NULL,
  `trang_thai` enum('pending','confirmed','preparing','ready','served','cancelled') DEFAULT 'pending',
  `tong_tien_goc` decimal(12,0) DEFAULT 0,
  `ghi_chu` text DEFAULT NULL,
  `ngay_dat` datetime DEFAULT current_timestamp(),
  `cap_nhat` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `don_hang`
--

INSERT INTO `don_hang` (`id`, `ma_don`, `phien_ban_id`, `khach_hang_id`, `nhan_vien_xac_nhan`, `trang_thai`, `tong_tien_goc`, `ghi_chu`, `ngay_dat`, `cap_nhat`) VALUES
(1, 'DH26110616084162897', 1, NULL, NULL, 'preparing', 29000, 'Don kiem thu tich hop', '2026-11-06 16:08:41', '2026-11-06 16:09:34');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `hoa_don`
--

CREATE TABLE `hoa_don` (
  `id` int(11) NOT NULL,
  `ma_hoa_don` varchar(50) NOT NULL,
  `nhan_vien_thanh_toan` int(11) DEFAULT NULL,
  `tong_tien_goc` decimal(12,0) DEFAULT 0,
  `tien_giam` decimal(12,0) DEFAULT 0,
  `tong_thanh_toan` decimal(12,0) DEFAULT 0,
  `trang_thai` enum('unpaid','partially_paid','paid','cancelled') DEFAULT 'unpaid',
  `ghi_chu` text DEFAULT NULL,
  `ngay_tao` datetime DEFAULT current_timestamp(),
  `ngay_thanh_toan` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `hoa_don_khuyen_mai`
--

CREATE TABLE `hoa_don_khuyen_mai` (
  `id` int(11) NOT NULL,
  `hoa_don_id` int(11) NOT NULL,
  `khuyen_mai_id` int(11) NOT NULL,
  `so_tien_giam` decimal(12,0) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `hoa_don_phien_ban`
--

CREATE TABLE `hoa_don_phien_ban` (
  `id` int(11) NOT NULL,
  `hoa_don_id` int(11) NOT NULL,
  `phien_ban_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `khach_hang`
--

CREATE TABLE `khach_hang` (
  `id` int(11) NOT NULL,
  `ten_khach_hang` varchar(150) DEFAULT NULL,
  `so_dien_thoai` varchar(20) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `diem_tich_luy` int(11) DEFAULT 0,
  `tong_chi_tieu` decimal(12,0) DEFAULT 0,
  `ngay_tao` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `khuyen_mai`
--

CREATE TABLE `khuyen_mai` (
  `id` int(11) NOT NULL,
  `ma_code` varchar(50) NOT NULL,
  `ten_khuyen_mai` varchar(150) NOT NULL,
  `loai_khuyen_mai` enum('phan_tram','so_tien') NOT NULL,
  `gia_tri` decimal(12,2) NOT NULL,
  `gia_tri_toi_da` decimal(12,0) DEFAULT NULL,
  `don_toi_thieu` decimal(12,0) DEFAULT 0,
  `so_lan_su_dung` int(11) DEFAULT NULL,
  `da_su_dung` int(11) DEFAULT 0,
  `ngay_bat_dau` datetime NOT NULL,
  `ngay_ket_thuc` datetime NOT NULL,
  `trang_thai` tinyint(1) DEFAULT 1,
  `ngay_tao` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `khuyen_mai`
--

INSERT INTO `khuyen_mai` (`id`, `ma_code`, `ten_khuyen_mai`, `loai_khuyen_mai`, `gia_tri`, `gia_tri_toi_da`, `don_toi_thieu`, `so_lan_su_dung`, `da_su_dung`, `ngay_bat_dau`, `ngay_ket_thuc`, `trang_thai`, `ngay_tao`) VALUES
(1, 'WELCOME10', 'Giam 10 phan tram', 'phan_tram', 10.00, 50000, 50000, 100, 0, '2026-01-01 00:00:00', '2027-12-31 23:59:59', 1, '2026-11-06 14:14:17'),
(2, 'GIAM20000', 'Giam 20.000 dong', 'so_tien', 20000.00, NULL, 100000, 100, 0, '2026-01-01 00:00:00', '2027-12-31 23:59:59', 1, '2026-11-06 14:14:17'),
(3, 'TABLE20', 'Giảm trực tiếp 20.000đ', 'so_tien', 20000.00, NULL, 150000, NULL, 0, '2026-01-01 00:00:00', '2030-12-31 23:59:59', 1, '2026-11-06 16:05:57');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `lich_su_ban`
--

CREATE TABLE `lich_su_ban` (
  `id` int(11) NOT NULL,
  `ban_id` int(11) NOT NULL,
  `trang_thai_cu` varchar(30) DEFAULT NULL,
  `trang_thai_moi` varchar(30) NOT NULL,
  `nguoi_thay_doi` int(11) DEFAULT NULL,
  `ghi_chu` text DEFAULT NULL,
  `thoi_gian` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `lich_su_hoa_don`
--

CREATE TABLE `lich_su_hoa_don` (
  `id` int(11) NOT NULL,
  `hoa_don_id` int(11) NOT NULL,
  `loai_thao_tac` enum('tao','gop','tach','cap_nhat','huy','thanh_toan') NOT NULL,
  `mo_ta` text DEFAULT NULL,
  `nguoi_thuc_hien` int(11) DEFAULT NULL,
  `thoi_gian` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `lich_su_trang_thai_mon`
--

CREATE TABLE `lich_su_trang_thai_mon` (
  `id` int(11) NOT NULL,
  `chi_tiet_don_hang_id` int(11) NOT NULL,
  `nhan_vien_id` int(11) DEFAULT NULL,
  `trang_thai_cu` varchar(30) DEFAULT NULL,
  `trang_thai_moi` varchar(30) NOT NULL,
  `ghi_chu` text DEFAULT NULL,
  `thoi_gian` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `lich_su_trang_thai_mon`
--

INSERT INTO `lich_su_trang_thai_mon` (`id`, `chi_tiet_don_hang_id`, `nhan_vien_id`, `trang_thai_cu`, `trang_thai_moi`, `ghi_chu`, `thoi_gian`) VALUES
(1, 1, 5, 'pending', 'preparing', NULL, '2026-11-06 16:09:34');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `phien_ban`
--

CREATE TABLE `phien_ban` (
  `id` int(11) NOT NULL,
  `ban_id` int(11) NOT NULL,
  `ma_phien` varchar(50) NOT NULL,
  `so_khach` int(11) DEFAULT 1,
  `trang_thai` enum('open','closed') DEFAULT 'open',
  `thoi_gian_bat_dau` datetime DEFAULT current_timestamp(),
  `thoi_gian_ket_thuc` datetime DEFAULT NULL,
  `ghi_chu` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `phien_ban`
--

INSERT INTO `phien_ban` (`id`, `ban_id`, `ma_phien`, `so_khach`, `trang_thai`, `thoi_gian_bat_dau`, `thoi_gian_ket_thuc`, `ghi_chu`) VALUES
(1, 1, 'PB2611061608411A2E', 1, 'open', '2026-11-06 16:08:41', NULL, NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `qr_ban`
--

CREATE TABLE `qr_ban` (
  `id` int(11) NOT NULL,
  `ban_id` int(11) NOT NULL,
  `token_hash` varchar(255) NOT NULL,
  `qr_image` varchar(500) DEFAULT NULL,
  `trang_thai` enum('active','revoked') DEFAULT 'active',
  `ngay_tao` datetime DEFAULT current_timestamp(),
  `ngay_het_han` datetime DEFAULT NULL,
  `ngay_thu_hoi` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `qr_ban`
--

INSERT INTO `qr_ban` (`id`, `ban_id`, `token_hash`, `qr_image`, `trang_thai`, `ngay_tao`, `ngay_het_han`, `ngay_thu_hoi`) VALUES
(1, 1, '129cbb7ac9e1f73a6674c18ace71716eb82d44203c8e32970b695593926bbb80', 'http://localhost/DoAnBeNhom9/DoAnBeNhom9/public/menu?token=3f6a3fe420570b36691794084ab8e2db818aeefbe57be580257cede22f6e80a4', 'revoked', '2026-11-06 16:07:58', '2027-11-06 16:07:58', '2026-11-06 16:08:40'),
(2, 1, '960d09528a328edeb213ad0c54352ba98957a3f5f8dd81d891bfabc9d38d9aa9', 'http://localhost/DoAnBeNhom9/DoAnBeNhom9/public/menu?token=b3d50c52753a64d2b72f491edc0a754ec2a405fe962f5ba52158b30d1bb9a202', 'active', '2026-11-06 16:08:40', '2027-11-06 16:08:40', NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `san_pham`
--

CREATE TABLE `san_pham` (
  `id` int(11) NOT NULL,
  `ten_san_pham` varchar(150) NOT NULL,
  `danh_muc_id` int(11) NOT NULL,
  `mo_ta` text DEFAULT NULL,
  `hinh_anh` varchar(500) DEFAULT NULL,
  `gia_ban_chinh` decimal(12,0) NOT NULL DEFAULT 0,
  `trang_thai` tinyint(1) DEFAULT 1,
  `ngay_tao` datetime DEFAULT current_timestamp(),
  `cap_nhat` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `san_pham`
--

INSERT INTO `san_pham` (`id`, `ten_san_pham`, `danh_muc_id`, `mo_ta`, `hinh_anh`, `gia_ban_chinh`, `trang_thai`, `ngay_tao`, `cap_nhat`) VALUES
(1, 'Cà phê sữa đá', 1, 'Cà phê đậm vị cùng sữa đặc, phục vụ với đá.', 'assets/images/coffee-milk.jpg', 29000, 1, '2026-11-06 14:14:17', '2026-11-06 16:04:52'),
(2, 'Bạc xỉu', 1, 'Sữa ngọt dịu hòa với một lớp cà phê thơm.', 'assets/images/bac-xiu.jpg', 32000, 1, '2026-11-06 14:14:17', '2026-11-06 16:04:52'),
(3, 'Americano', 1, 'Espresso pha cùng nước nóng, hương vị thanh sạch.', 'assets/images/americano.jpg', 35000, 1, '2026-11-06 14:14:17', '2026-11-06 16:04:52'),
(4, 'Latte', 1, 'Espresso và sữa tươi đánh nóng mịn màng.', 'assets/images/latte.jpg', 42000, 1, '2026-11-06 14:14:17', '2026-11-06 16:04:52'),
(5, 'Cold brew cam', 1, 'Cà phê ủ lạnh kết hợp nước cam tươi.', 'assets/images/cold-brew.jpg', 45000, 1, '2026-11-06 14:14:17', '2026-11-06 16:04:52'),
(6, 'Trà đào cam sả', 2, 'Trà thơm, đào miếng, cam vàng và sả tươi.', 'assets/images/peach-tea.jpg', 42000, 1, '2026-11-06 14:14:17', '2026-11-06 16:04:52'),
(7, 'Trà vải', 2, 'Trà thanh mát cùng quả vải mọng nước.', 'assets/images/lychee-tea.jpg', 39000, 1, '2026-11-06 14:14:17', '2026-11-06 16:04:52'),
(8, 'Trà sen vàng', 2, 'Trà ô long, hạt sen và kem sữa dịu nhẹ.', 'assets/images/lotus-tea.jpg', 45000, 1, '2026-11-06 14:14:17', '2026-11-06 16:04:52'),
(9, 'Trà sữa ô long', 2, 'Ô long rang thơm hòa cùng sữa tươi.', 'assets/images/milk-tea.jpg', 42000, 1, '2026-11-06 14:14:17', '2026-11-06 16:04:52'),
(10, 'Matcha đá xay', 3, 'Matcha Nhật Bản xay cùng sữa và đá.', 'assets/images/matcha.jpg', 49000, 1, '2026-11-06 14:14:17', '2026-11-06 16:04:52'),
(11, 'Cookie đá xay', 3, 'Bánh quy cacao, sữa tươi và kem mềm.', 'assets/images/cookie.jpg', 52000, 1, '2026-11-06 14:14:17', '2026-11-06 16:04:52'),
(12, 'Dâu đá xay', 3, 'Dâu chua ngọt xay mịn cùng sữa chua.', 'assets/images/strawberry.jpg', 49000, 1, '2026-11-06 14:14:17', '2026-11-06 16:04:52'),
(13, 'Nước ép cam', 4, 'Cam tươi ép nguyên chất theo ngày.', 'assets/images/orange-juice.jpg', 39000, 1, '2026-11-06 14:14:17', '2026-11-06 16:04:52'),
(14, 'Nước ép dưa hấu', 4, 'Dưa hấu tươi, vị ngọt tự nhiên.', 'assets/images/watermelon.jpg', 35000, 1, '2026-11-06 14:14:17', '2026-11-06 16:04:52'),
(15, 'Nước ép ổi', 4, 'Ổi tươi ép cùng một chút tắc.', 'assets/images/guava.jpg', 37000, 1, '2026-11-06 14:14:17', '2026-11-06 16:04:52'),
(16, 'Chanh Day Tuyet', 3, 'Chanh day tuyet', NULL, 38000, 1, '2026-11-06 14:14:17', '2026-11-06 14:14:17'),
(17, 'Cookie Cream Da Xay', 3, 'Cookie cream', NULL, 48000, 1, '2026-11-06 14:14:17', '2026-11-06 14:14:17'),
(18, 'Xoai Smoothie', 3, 'Xoai smoothie', NULL, 45000, 1, '2026-11-06 14:14:17', '2026-11-06 14:14:17'),
(19, 'Dau Tay Smoothie', 3, 'Dau tay smoothie', NULL, 48000, 1, '2026-11-06 14:14:17', '2026-11-06 14:14:17'),
(20, 'Nuoc Ep Cam Tuoi', 4, 'Nuoc ep cam tuoi', NULL, 35000, 1, '2026-11-06 14:14:17', '2026-11-06 14:14:17'),
(21, 'Nuoc Ep Dua Hau', 4, 'Nuoc ep dua hau', NULL, 32000, 1, '2026-11-06 14:14:17', '2026-11-06 14:14:17'),
(22, 'baitap', 3, '123', NULL, 22000, 1, '2026-11-06 16:11:45', '2026-11-06 16:11:45'),
(23, 'ádsá', 6, 'asâsa', NULL, 20000, 1, '2026-11-06 16:16:25', '2026-11-06 16:16:44');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `san_pham_size`
--

CREATE TABLE `san_pham_size` (
  `id` int(11) NOT NULL,
  `san_pham_id` int(11) NOT NULL,
  `size_id` int(11) NOT NULL,
  `gia_ban` decimal(12,0) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `san_pham_size`
--

INSERT INTO `san_pham_size` (`id`, `san_pham_id`, `size_id`, `gia_ban`) VALUES
(1, 1, 3, 43000),
(2, 1, 2, 36000),
(3, 1, 1, 29000),
(4, 2, 3, 46000),
(5, 2, 2, 39000),
(6, 2, 1, 32000),
(7, 3, 3, 49000),
(8, 3, 2, 42000),
(9, 3, 1, 35000),
(10, 4, 3, 56000),
(11, 4, 2, 49000),
(12, 4, 1, 42000),
(13, 5, 3, 59000),
(14, 5, 2, 52000),
(15, 5, 1, 45000),
(16, 6, 3, 56000),
(17, 6, 2, 49000),
(18, 6, 1, 42000),
(19, 7, 3, 53000),
(20, 7, 2, 46000),
(21, 7, 1, 39000),
(22, 8, 3, 59000),
(23, 8, 2, 52000),
(24, 8, 1, 45000),
(25, 9, 3, 56000),
(26, 9, 2, 49000),
(27, 9, 1, 42000),
(28, 10, 3, 63000),
(29, 10, 2, 56000),
(30, 10, 1, 49000),
(31, 11, 3, 66000),
(32, 11, 2, 59000),
(33, 11, 1, 52000),
(34, 12, 3, 63000),
(35, 12, 2, 56000),
(36, 12, 1, 49000),
(37, 13, 3, 53000),
(38, 13, 2, 46000),
(39, 13, 1, 39000),
(40, 14, 3, 49000),
(41, 14, 2, 42000),
(42, 14, 1, 35000),
(43, 15, 3, 51000),
(44, 15, 2, 44000),
(45, 15, 1, 37000),
(46, 16, 3, 52000),
(47, 16, 2, 45000),
(48, 16, 1, 38000),
(49, 17, 3, 62000),
(50, 17, 2, 55000),
(51, 17, 1, 48000),
(52, 18, 3, 59000),
(53, 18, 2, 52000),
(54, 18, 1, 45000),
(55, 19, 3, 62000),
(56, 19, 2, 55000),
(57, 19, 1, 48000),
(58, 20, 3, 49000),
(59, 20, 2, 42000),
(60, 20, 1, 35000),
(61, 21, 3, 46000),
(62, 21, 2, 39000),
(63, 21, 1, 32000),
(65, 22, 1, 1001),
(66, 22, 2, 1001),
(67, 22, 3, 2001),
(68, 23, 1, 1001),
(69, 23, 2, 35001),
(70, 23, 3, 50001);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `size`
--

CREATE TABLE `size` (
  `id` int(11) NOT NULL,
  `ten_size` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `size`
--

INSERT INTO `size` (`id`, `ten_size`) VALUES
(3, 'L'),
(2, 'M'),
(1, 'S');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `thanh_toan`
--

CREATE TABLE `thanh_toan` (
  `id` int(11) NOT NULL,
  `hoa_don_id` int(11) NOT NULL,
  `ma_giao_dich` varchar(100) DEFAULT NULL,
  `phuong_thuc` enum('tien_mat','chuyen_khoan','vi_dien_tu','the_ngan_hang') NOT NULL,
  `so_tien` decimal(12,0) NOT NULL,
  `trang_thai` enum('cho_thanh_toan','da_thanh_toan','hoan_tien','that_bai') DEFAULT 'cho_thanh_toan',
  `nguoi_thu` int(11) DEFAULT NULL,
  `ghi_chu` text DEFAULT NULL,
  `ngay_thanh_toan` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `thong_bao`
--

CREATE TABLE `thong_bao` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `loai_thong_bao` enum('don_hang_moi','mon_dang_che_bien','mon_san_sang','yeu_cau_thanh_toan','thanh_toan_thanh_cong','he_thong') NOT NULL,
  `tieu_de` varchar(200) NOT NULL,
  `noi_dung` text DEFAULT NULL,
  `tham_chieu_id` int(11) DEFAULT NULL,
  `da_doc` tinyint(1) DEFAULT 0,
  `ngay_tao` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `thong_bao`
--

INSERT INTO `thong_bao` (`id`, `user_id`, `loai_thong_bao`, `tieu_de`, `noi_dung`, `tham_chieu_id`, `da_doc`, `ngay_tao`) VALUES
(1, 2, 'don_hang_moi', 'Đơn mới DH26110616084162897', 'Bàn 01 vừa gửi món.', 1, 0, '2026-11-06 16:08:41'),
(2, 3, 'don_hang_moi', 'Đơn mới DH26110616084162897', 'Bàn 01 vừa gửi món.', 1, 0, '2026-11-06 16:08:41'),
(3, 5, 'don_hang_moi', 'Đơn mới DH26110616084162897', 'Bàn 01 vừa gửi món.', 1, 0, '2026-11-06 16:08:41');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `ho_ten` varchar(150) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `so_dien_thoai` varchar(20) DEFAULT NULL,
  `role` enum('admin','manager','cashier','kitchen','waiter','customer') NOT NULL DEFAULT 'customer',
  `trang_thai` tinyint(1) NOT NULL DEFAULT 1,
  `ngay_tao` datetime DEFAULT current_timestamp(),
  `cap_nhat` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `username`, `password_hash`, `ho_ten`, `email`, `so_dien_thoai`, `role`, `trang_thai`, `ngay_tao`, `cap_nhat`) VALUES
(2, 'admin', '$2y$10$lYt.rPYxdWKin/T1LZppYO.0KASro2dMmiiTYQ4u01a4CVY/blLcm', 'Quản trị viên', 'admin@tabletap.local', NULL, 'admin', 1, '2026-11-06 16:04:52', '2026-11-06 16:04:52'),
(3, 'manager', '$2y$10$rcaytynibtqSTTB9iUBpweQz8Gvjy.caHsUEM2jZYWuz/5Nucy0i2', 'Quản lý ca', 'manager@tabletap.local', NULL, 'manager', 1, '2026-11-06 16:04:52', '2026-11-06 16:04:52'),
(4, 'cashier', '$2y$10$JBCVnQkPPFuPOHheIVLvj.SffKlaYNrwpnJgOvRzvhv/pAHeoEHzC', 'Thu ngân', 'cashier@tabletap.local', NULL, 'cashier', 1, '2026-11-06 16:04:52', '2026-11-06 16:04:52'),
(5, 'kitchen', '$2y$10$tz66766nNoQm3BkTqpyeEOSiUzgB8biGzPDrzmZyxJVOVw0ZQ2t2y', 'Nhân viên bếp', 'kitchen@tabletap.local', NULL, 'kitchen', 1, '2026-11-06 16:04:52', '2026-11-06 16:04:52'),
(6, 'waiter', '$2y$10$71IAjqSbeiHAsj3J8H7Ktezbmrdjg/fm3bIpORzWUHV98OkuAY18m', 'Nhân viên phục vụ', 'waiter@tabletap.local', NULL, 'waiter', 1, '2026-11-06 16:04:52', '2026-11-06 16:04:52');

-- --------------------------------------------------------

--
-- Cấu trúc đóng vai cho view `v_doanh_thu_ngay`
-- (See below for the actual view)
--
CREATE TABLE `v_doanh_thu_ngay` (
`ngay` date
,`so_hoa_don` bigint(21)
,`tong_tien_goc` decimal(34,0)
,`tong_giam_gia` decimal(34,0)
,`doanh_thu` decimal(34,0)
);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `yeu_cau_ban`
--

CREATE TABLE `yeu_cau_ban` (
  `id` int(11) NOT NULL,
  `phien_ban_id` int(11) NOT NULL,
  `loai_yeu_cau` enum('goi_nhan_vien','yeu_cau_thanh_toan','them_dung_cu','khac') NOT NULL,
  `noi_dung` text DEFAULT NULL,
  `trang_thai` enum('pending','processing','completed','cancelled') DEFAULT 'pending',
  `nhan_vien_xu_ly` int(11) DEFAULT NULL,
  `ngay_tao` datetime DEFAULT current_timestamp(),
  `ngay_xu_ly` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc cho view `v_doanh_thu_ngay`
--
DROP TABLE IF EXISTS `v_doanh_thu_ngay`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_doanh_thu_ngay`  AS SELECT cast(`hoa_don`.`ngay_thanh_toan` as date) AS `ngay`, count(0) AS `so_hoa_don`, sum(`hoa_don`.`tong_tien_goc`) AS `tong_tien_goc`, sum(`hoa_don`.`tien_giam`) AS `tong_giam_gia`, sum(`hoa_don`.`tong_thanh_toan`) AS `doanh_thu` FROM `hoa_don` WHERE `hoa_don`.`trang_thai` = 'paid' AND `hoa_don`.`ngay_thanh_toan` is not null GROUP BY cast(`hoa_don`.`ngay_thanh_toan` as date) ;

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `ban_an`
--
ALTER TABLE `ban_an`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ma_ban` (`ma_ban`),
  ADD KEY `idx_ban_status` (`trang_thai`);

--
-- Chỉ mục cho bảng `chi_tiet_don_hang`
--
ALTER TABLE `chi_tiet_don_hang`
  ADD PRIMARY KEY (`id`),
  ADD KEY `san_pham_id` (`san_pham_id`),
  ADD KEY `size_id` (`size_id`),
  ADD KEY `idx_ctdh_donhang` (`don_hang_id`),
  ADD KEY `idx_ctdh_trangthai` (`trang_thai`);

--
-- Chỉ mục cho bảng `chi_tiet_hoa_don`
--
ALTER TABLE `chi_tiet_hoa_don`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_cthoadon_hoadon` (`hoa_don_id`),
  ADD KEY `idx_cthoadon_mon` (`chi_tiet_don_hang_id`);

--
-- Chỉ mục cho bảng `danh_muc`
--
ALTER TABLE `danh_muc`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_danhmuc_status` (`trang_thai`);

--
-- Chỉ mục cho bảng `don_hang`
--
ALTER TABLE `don_hang`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ma_don` (`ma_don`),
  ADD KEY `khach_hang_id` (`khach_hang_id`),
  ADD KEY `nhan_vien_xac_nhan` (`nhan_vien_xac_nhan`),
  ADD KEY `idx_don_trangthai` (`trang_thai`),
  ADD KEY `idx_don_ngaydat` (`ngay_dat`),
  ADD KEY `idx_don_phienban` (`phien_ban_id`);

--
-- Chỉ mục cho bảng `hoa_don`
--
ALTER TABLE `hoa_don`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ma_hoa_don` (`ma_hoa_don`),
  ADD KEY `nhan_vien_thanh_toan` (`nhan_vien_thanh_toan`),
  ADD KEY `idx_hoadon_trangthai` (`trang_thai`),
  ADD KEY `idx_hoadon_ngaytao` (`ngay_tao`);

--
-- Chỉ mục cho bảng `hoa_don_khuyen_mai`
--
ALTER TABLE `hoa_don_khuyen_mai`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hoa_don_id` (`hoa_don_id`),
  ADD KEY `khuyen_mai_id` (`khuyen_mai_id`);

--
-- Chỉ mục cho bảng `hoa_don_phien_ban`
--
ALTER TABLE `hoa_don_phien_ban`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_hoadon_phienban` (`hoa_don_id`,`phien_ban_id`),
  ADD KEY `phien_ban_id` (`phien_ban_id`);

--
-- Chỉ mục cho bảng `khach_hang`
--
ALTER TABLE `khach_hang`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `so_dien_thoai` (`so_dien_thoai`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Chỉ mục cho bảng `khuyen_mai`
--
ALTER TABLE `khuyen_mai`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ma_code` (`ma_code`);

--
-- Chỉ mục cho bảng `lich_su_ban`
--
ALTER TABLE `lich_su_ban`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ban_id` (`ban_id`),
  ADD KEY `nguoi_thay_doi` (`nguoi_thay_doi`);

--
-- Chỉ mục cho bảng `lich_su_hoa_don`
--
ALTER TABLE `lich_su_hoa_don`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hoa_don_id` (`hoa_don_id`),
  ADD KEY `nguoi_thuc_hien` (`nguoi_thuc_hien`);

--
-- Chỉ mục cho bảng `lich_su_trang_thai_mon`
--
ALTER TABLE `lich_su_trang_thai_mon`
  ADD PRIMARY KEY (`id`),
  ADD KEY `chi_tiet_don_hang_id` (`chi_tiet_don_hang_id`),
  ADD KEY `nhan_vien_id` (`nhan_vien_id`);

--
-- Chỉ mục cho bảng `phien_ban`
--
ALTER TABLE `phien_ban`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ma_phien` (`ma_phien`),
  ADD KEY `idx_phien_ban` (`ban_id`,`trang_thai`);

--
-- Chỉ mục cho bảng `qr_ban`
--
ALTER TABLE `qr_ban`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token_hash` (`token_hash`),
  ADD KEY `idx_qr_ban` (`ban_id`,`trang_thai`);

--
-- Chỉ mục cho bảng `san_pham`
--
ALTER TABLE `san_pham`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sanpham_danhmuc` (`danh_muc_id`),
  ADD KEY `idx_sanpham_trangthai` (`trang_thai`);

--
-- Chỉ mục cho bảng `san_pham_size`
--
ALTER TABLE `san_pham_size`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_sanpham_size` (`san_pham_id`,`size_id`),
  ADD KEY `size_id` (`size_id`);

--
-- Chỉ mục cho bảng `size`
--
ALTER TABLE `size`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ten_size` (`ten_size`);

--
-- Chỉ mục cho bảng `thanh_toan`
--
ALTER TABLE `thanh_toan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ma_giao_dich` (`ma_giao_dich`),
  ADD KEY `nguoi_thu` (`nguoi_thu`),
  ADD KEY `idx_thanhtoan_hoadon` (`hoa_don_id`),
  ADD KEY `idx_thanhtoan_trangthai` (`trang_thai`);

--
-- Chỉ mục cho bảng `thong_bao`
--
ALTER TABLE `thong_bao`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_thongbao_user` (`user_id`,`da_doc`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_users_role_status` (`role`,`trang_thai`);

--
-- Chỉ mục cho bảng `yeu_cau_ban`
--
ALTER TABLE `yeu_cau_ban`
  ADD PRIMARY KEY (`id`),
  ADD KEY `phien_ban_id` (`phien_ban_id`),
  ADD KEY `nhan_vien_xu_ly` (`nhan_vien_xu_ly`),
  ADD KEY `idx_yeucau_trangthai` (`trang_thai`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `ban_an`
--
ALTER TABLE `ban_an`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT cho bảng `chi_tiet_don_hang`
--
ALTER TABLE `chi_tiet_don_hang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `chi_tiet_hoa_don`
--
ALTER TABLE `chi_tiet_hoa_don`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `danh_muc`
--
ALTER TABLE `danh_muc`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT cho bảng `don_hang`
--
ALTER TABLE `don_hang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `hoa_don`
--
ALTER TABLE `hoa_don`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `hoa_don_khuyen_mai`
--
ALTER TABLE `hoa_don_khuyen_mai`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `hoa_don_phien_ban`
--
ALTER TABLE `hoa_don_phien_ban`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `khach_hang`
--
ALTER TABLE `khach_hang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `khuyen_mai`
--
ALTER TABLE `khuyen_mai`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `lich_su_ban`
--
ALTER TABLE `lich_su_ban`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `lich_su_hoa_don`
--
ALTER TABLE `lich_su_hoa_don`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `lich_su_trang_thai_mon`
--
ALTER TABLE `lich_su_trang_thai_mon`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `phien_ban`
--
ALTER TABLE `phien_ban`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `qr_ban`
--
ALTER TABLE `qr_ban`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `san_pham`
--
ALTER TABLE `san_pham`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT cho bảng `san_pham_size`
--
ALTER TABLE `san_pham_size`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- AUTO_INCREMENT cho bảng `size`
--
ALTER TABLE `size`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `thanh_toan`
--
ALTER TABLE `thanh_toan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `thong_bao`
--
ALTER TABLE `thong_bao`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT cho bảng `yeu_cau_ban`
--
ALTER TABLE `yeu_cau_ban`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `chi_tiet_don_hang`
--
ALTER TABLE `chi_tiet_don_hang`
  ADD CONSTRAINT `chi_tiet_don_hang_ibfk_1` FOREIGN KEY (`don_hang_id`) REFERENCES `don_hang` (`id`),
  ADD CONSTRAINT `chi_tiet_don_hang_ibfk_2` FOREIGN KEY (`san_pham_id`) REFERENCES `san_pham` (`id`),
  ADD CONSTRAINT `chi_tiet_don_hang_ibfk_3` FOREIGN KEY (`size_id`) REFERENCES `size` (`id`);

--
-- Các ràng buộc cho bảng `chi_tiet_hoa_don`
--
ALTER TABLE `chi_tiet_hoa_don`
  ADD CONSTRAINT `chi_tiet_hoa_don_ibfk_1` FOREIGN KEY (`hoa_don_id`) REFERENCES `hoa_don` (`id`),
  ADD CONSTRAINT `chi_tiet_hoa_don_ibfk_2` FOREIGN KEY (`chi_tiet_don_hang_id`) REFERENCES `chi_tiet_don_hang` (`id`);

--
-- Các ràng buộc cho bảng `don_hang`
--
ALTER TABLE `don_hang`
  ADD CONSTRAINT `don_hang_ibfk_1` FOREIGN KEY (`phien_ban_id`) REFERENCES `phien_ban` (`id`),
  ADD CONSTRAINT `don_hang_ibfk_2` FOREIGN KEY (`khach_hang_id`) REFERENCES `khach_hang` (`id`),
  ADD CONSTRAINT `don_hang_ibfk_3` FOREIGN KEY (`nhan_vien_xac_nhan`) REFERENCES `users` (`id`);

--
-- Các ràng buộc cho bảng `hoa_don`
--
ALTER TABLE `hoa_don`
  ADD CONSTRAINT `hoa_don_ibfk_1` FOREIGN KEY (`nhan_vien_thanh_toan`) REFERENCES `users` (`id`);

--
-- Các ràng buộc cho bảng `hoa_don_khuyen_mai`
--
ALTER TABLE `hoa_don_khuyen_mai`
  ADD CONSTRAINT `hoa_don_khuyen_mai_ibfk_1` FOREIGN KEY (`hoa_don_id`) REFERENCES `hoa_don` (`id`),
  ADD CONSTRAINT `hoa_don_khuyen_mai_ibfk_2` FOREIGN KEY (`khuyen_mai_id`) REFERENCES `khuyen_mai` (`id`);

--
-- Các ràng buộc cho bảng `hoa_don_phien_ban`
--
ALTER TABLE `hoa_don_phien_ban`
  ADD CONSTRAINT `hoa_don_phien_ban_ibfk_1` FOREIGN KEY (`hoa_don_id`) REFERENCES `hoa_don` (`id`),
  ADD CONSTRAINT `hoa_don_phien_ban_ibfk_2` FOREIGN KEY (`phien_ban_id`) REFERENCES `phien_ban` (`id`);

--
-- Các ràng buộc cho bảng `lich_su_ban`
--
ALTER TABLE `lich_su_ban`
  ADD CONSTRAINT `lich_su_ban_ibfk_1` FOREIGN KEY (`ban_id`) REFERENCES `ban_an` (`id`),
  ADD CONSTRAINT `lich_su_ban_ibfk_2` FOREIGN KEY (`nguoi_thay_doi`) REFERENCES `users` (`id`);

--
-- Các ràng buộc cho bảng `lich_su_hoa_don`
--
ALTER TABLE `lich_su_hoa_don`
  ADD CONSTRAINT `lich_su_hoa_don_ibfk_1` FOREIGN KEY (`hoa_don_id`) REFERENCES `hoa_don` (`id`),
  ADD CONSTRAINT `lich_su_hoa_don_ibfk_2` FOREIGN KEY (`nguoi_thuc_hien`) REFERENCES `users` (`id`);

--
-- Các ràng buộc cho bảng `lich_su_trang_thai_mon`
--
ALTER TABLE `lich_su_trang_thai_mon`
  ADD CONSTRAINT `lich_su_trang_thai_mon_ibfk_1` FOREIGN KEY (`chi_tiet_don_hang_id`) REFERENCES `chi_tiet_don_hang` (`id`),
  ADD CONSTRAINT `lich_su_trang_thai_mon_ibfk_2` FOREIGN KEY (`nhan_vien_id`) REFERENCES `users` (`id`);

--
-- Các ràng buộc cho bảng `phien_ban`
--
ALTER TABLE `phien_ban`
  ADD CONSTRAINT `phien_ban_ibfk_1` FOREIGN KEY (`ban_id`) REFERENCES `ban_an` (`id`);

--
-- Các ràng buộc cho bảng `qr_ban`
--
ALTER TABLE `qr_ban`
  ADD CONSTRAINT `qr_ban_ibfk_1` FOREIGN KEY (`ban_id`) REFERENCES `ban_an` (`id`);

--
-- Các ràng buộc cho bảng `san_pham`
--
ALTER TABLE `san_pham`
  ADD CONSTRAINT `san_pham_ibfk_1` FOREIGN KEY (`danh_muc_id`) REFERENCES `danh_muc` (`id`);

--
-- Các ràng buộc cho bảng `san_pham_size`
--
ALTER TABLE `san_pham_size`
  ADD CONSTRAINT `san_pham_size_ibfk_1` FOREIGN KEY (`san_pham_id`) REFERENCES `san_pham` (`id`),
  ADD CONSTRAINT `san_pham_size_ibfk_2` FOREIGN KEY (`size_id`) REFERENCES `size` (`id`);

--
-- Các ràng buộc cho bảng `thanh_toan`
--
ALTER TABLE `thanh_toan`
  ADD CONSTRAINT `thanh_toan_ibfk_1` FOREIGN KEY (`hoa_don_id`) REFERENCES `hoa_don` (`id`),
  ADD CONSTRAINT `thanh_toan_ibfk_2` FOREIGN KEY (`nguoi_thu`) REFERENCES `users` (`id`);

--
-- Các ràng buộc cho bảng `thong_bao`
--
ALTER TABLE `thong_bao`
  ADD CONSTRAINT `thong_bao_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Các ràng buộc cho bảng `yeu_cau_ban`
--
ALTER TABLE `yeu_cau_ban`
  ADD CONSTRAINT `yeu_cau_ban_ibfk_1` FOREIGN KEY (`phien_ban_id`) REFERENCES `phien_ban` (`id`),
  ADD CONSTRAINT `yeu_cau_ban_ibfk_2` FOREIGN KEY (`nhan_vien_xu_ly`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
