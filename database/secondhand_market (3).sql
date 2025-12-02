-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Dec 02, 2025 at 12:35 PM
-- Server version: 9.1.0
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `secondhand_market`
--

-- --------------------------------------------------------

--
-- Table structure for table `chitietdonhang`
--

DROP TABLE IF EXISTS `chitietdonhang`;
CREATE TABLE IF NOT EXISTS `chitietdonhang` (
  `ID_ChiTiet` int NOT NULL AUTO_INCREMENT,
  `ID_DonHang` int NOT NULL,
  `ID_SanPham` int NOT NULL,
  `SoLuongMua` int NOT NULL,
  `GiaTaiThoiDiemDat` decimal(10,2) NOT NULL,
  PRIMARY KEY (`ID_ChiTiet`),
  KEY `ID_DonHang` (`ID_DonHang`),
  KEY `ID_SanPham` (`ID_SanPham`)
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `chitietdonhang`
--

INSERT INTO `chitietdonhang` (`ID_ChiTiet`, `ID_DonHang`, `ID_SanPham`, `SoLuongMua`, `GiaTaiThoiDiemDat`) VALUES
(1, 1, 1, 1, 3500000.00),
(2, 1, 2, 1, 450000.00),
(3, 2, 2, 2, 450000.00),
(4, 3, 3, 1, 600000.00),
(5, 4, 5, 1, 800000.00),
(6, 5, 4, 1, 1200000.00),
(7, 5, 7, 1, 350000.00),
(8, 6, 6, 1, 300000.00),
(11, 9, 3, 1, 600000.00),
(12, 10, 9, 1, 299999.00),
(13, 11, 9, 1, 299999.00),
(14, 12, 4, 1, 1200000.00),
(15, 13, 9, 1, 299999.00),
(16, 14, 4, 1, 1200000.00),
(17, 14, 5, 1, 800000.00),
(18, 15, 5, 1, 800000.00),
(19, 16, 9, 1, 299999.00),
(20, 17, 9, 1, 299999.00),
(21, 17, 3, 1, 600000.00),
(22, 18, 3, 1, 600000.00),
(23, 18, 5, 1, 800000.00),
(24, 19, 4, 1, 1200000.00),
(25, 19, 5, 1, 800000.00),
(26, 20, 4, 1, 1200000.00),
(27, 20, 3, 1, 600000.00),
(28, 21, 9, 1, 299999.00),
(29, 22, 9, 1, 299999.00),
(30, 23, 9, 1, 299999.00),
(31, 24, 4, 1, 1200000.00),
(32, 25, 9, 1, 299999.00),
(33, 25, 3, 1, 600000.00),
(34, 26, 5, 1, 800000.00),
(35, 27, 3, 1, 600000.00),
(36, 27, 4, 1, 1200000.00),
(37, 28, 7, 1, 350000.00),
(38, 29, 7, 1, 350000.00),
(39, 30, 4, 1, 1200000.00),
(40, 31, 9, 1, 299999.00),
(45, 36, 9, 1, 299999.00),
(47, 38, 9, 1, 299999.00),
(48, 39, 9, 1, 299999.00);

-- --------------------------------------------------------

--
-- Table structure for table `danhgia_nhanxet`
--

DROP TABLE IF EXISTS `danhgia_nhanxet`;
CREATE TABLE IF NOT EXISTS `danhgia_nhanxet` (
  `ID_DanhGia` int NOT NULL AUTO_INCREMENT,
  `ID_DonHang` int NOT NULL,
  `ID_NguoiDanhGia` int NOT NULL,
  `ID_NguoiDuocDanhGia` int NOT NULL,
  `SoSao` int NOT NULL,
  `NhanXet` text CHARACTER SET utf8mb4 COLLATE utf8mb4_vietnamese_ci,
  `NgayDanhGia` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID_DanhGia`),
  UNIQUE KEY `ID_DonHang` (`ID_DonHang`),
  KEY `ID_NguoiDanhGia` (`ID_NguoiDanhGia`),
  KEY `ID_NguoiDuocDanhGia` (`ID_NguoiDuocDanhGia`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `danhgia_nhanxet`
--

INSERT INTO `danhgia_nhanxet` (`ID_DanhGia`, `ID_DonHang`, `ID_NguoiDanhGia`, `ID_NguoiDuocDanhGia`, `SoSao`, `NhanXet`, `NgayDanhGia`) VALUES
(1, 1, 1, 2, 5, 'Giao hàng nhanh, sản phẩm đúng mô tả', '2025-11-25 08:01:07'),
(2, 5, 1, 6, 5, 'Bàn làm việc đẹp, chất lượng tốt', '2025-11-26 15:41:21'),
(3, 3, 5, 6, 4, 'Máy xay dùng tốt, đóng gói ổn', '2025-11-26 15:41:21'),
(4, 9, 1, 13, 5, 'Sản phẩm tốt', '2025-12-01 09:26:20');

-- --------------------------------------------------------

--
-- Table structure for table `danhmuc`
--

DROP TABLE IF EXISTS `danhmuc`;
CREATE TABLE IF NOT EXISTS `danhmuc` (
  `ID_DanhMuc` int NOT NULL AUTO_INCREMENT,
  `TenDanhMuc` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_vietnamese_ci NOT NULL,
  PRIMARY KEY (`ID_DanhMuc`),
  UNIQUE KEY `TenDanhMuc` (`TenDanhMuc`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Dumping data for table `danhmuc`
--

INSERT INTO `danhmuc` (`ID_DanhMuc`, `TenDanhMuc`) VALUES
(1, 'Điện tử'),
(3, 'Đồ gia dụng'),
(5, 'Nội thất'),
(4, 'Sách'),
(6, 'Thể thao'),
(2, 'Thời trang');

-- --------------------------------------------------------

--
-- Table structure for table `danhsachdonhang`
--

DROP TABLE IF EXISTS `danhsachdonhang`;
CREATE TABLE IF NOT EXISTS `danhsachdonhang` (
  `ID_DonHang` int NOT NULL AUTO_INCREMENT,
  `ID_NguoiMua` int NOT NULL,
  `ID_NguoiBan` int NOT NULL,
  `ID_NguoiGiaoHang` int DEFAULT NULL,
  `NgayDatHang` datetime DEFAULT CURRENT_TIMESTAMP,
  `TrangThaiDonHang` enum('ChoXacNhan','ChoGiaoHang','DangXuLy','DangVanChuyen','DaGiao','HoanThanh','DaHuy','KhieuNai') CHARACTER SET utf8mb4 COLLATE utf8mb4_vietnamese_ci NOT NULL,
  `DiaChiGiaoHang` text CHARACTER SET utf8mb4 COLLATE utf8mb4_vietnamese_ci NOT NULL,
  `PhuongThucVanChuyen` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_vietnamese_ci DEFAULT NULL,
  `PhuongThucThanhToan` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_vietnamese_ci DEFAULT NULL,
  `TongGiaTriDonHang` decimal(10,2) NOT NULL,
  `LyDoHuy` text CHARACTER SET utf8mb4 COLLATE utf8mb4_vietnamese_ci,
  `SoTienCanThu_COD` decimal(10,2) DEFAULT '0.00',
  `GhiChu` text COLLATE utf8mb4_vietnamese_ci NOT NULL,
  `AnhXacNhanGiaoHang` text COLLATE utf8mb4_vietnamese_ci NOT NULL,
  `ThoiGianHoanThanh` text COLLATE utf8mb4_vietnamese_ci NOT NULL,
  `PhiGiaoHang` decimal(10,0) NOT NULL,
  PRIMARY KEY (`ID_DonHang`),
  KEY `ID_NguoiMua` (`ID_NguoiMua`),
  KEY `ID_NguoiBan` (`ID_NguoiBan`),
  KEY `ID_NguoiGiaoHang` (`ID_NguoiGiaoHang`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Dumping data for table `danhsachdonhang`
--

INSERT INTO `danhsachdonhang` (`ID_DonHang`, `ID_NguoiMua`, `ID_NguoiBan`, `ID_NguoiGiaoHang`, `NgayDatHang`, `TrangThaiDonHang`, `DiaChiGiaoHang`, `PhuongThucVanChuyen`, `PhuongThucThanhToan`, `TongGiaTriDonHang`, `LyDoHuy`, `SoTienCanThu_COD`, `GhiChu`, `AnhXacNhanGiaoHang`, `ThoiGianHoanThanh`, `PhiGiaoHang`) VALUES
(1, 1, 2, 3, '2025-11-25 08:01:06', 'DaGiao', '123 Đ. A, Q.1, TP.HCM', 'Giao nhanh', 'COD', 3950000.00, NULL, 3950000.00, '', '', '', 15000),
(2, 1, 2, 9, '2025-11-23 08:01:06', 'DaGiao', '123 Đ. A, Q.1, TP.HCM', 'Giao tiết kiệm', 'Chuyển khoản', 900000.00, NULL, 0.00, '', 'sm/uploads/1764484317_692be4dd1cf67_sport-bike.jpg', '2025-11-30 13:31:57', 0),
(3, 5, 6, 9, '2025-11-26 15:41:21', '', '22 Đ. E, Q.10, TP.HCM', 'Giao nhanh', 'COD', 600000.00, NULL, 600000.00, '', '', '', 0),
(4, 8, 7, NULL, '2025-11-25 15:41:21', 'DaHuy', '12 Đ. H, Q.8, TP.HCM', 'Giao tiết kiệm', 'Chuyển khoản', 800000.00, 'Người mua tự hủy', 0.00, '', '', '', 0),
(5, 1, 6, 3, '2025-11-21 15:41:21', 'DaGiao', '123 Đ. A, Q.1, TP.HCM', 'Giao nhanh', 'COD', 1550000.00, NULL, 1550000.00, '', '', '', 0),
(6, 5, 7, 9, '2025-11-26 15:41:21', 'DaGiao', '22 Đ. E, Q.10, TP.HCM', 'Giao nhanh', 'COD', 300000.00, NULL, 300000.00, '', '../uploads/shipper_documents/1764677141_692ed6156d087_sport-bike.jpg', '2025-12-02 19:05:41', 0),
(9, 13, 6, NULL, '2025-11-30 13:07:30', 'ChoXacNhan', '29 Xuân Diệu, Đà Nẵng', NULL, NULL, 600000.00, NULL, 600000.00, '', '', '', 0),
(10, 1, 13, NULL, '2025-12-01 01:53:28', 'HoanThanh', '29 Xuân Diệu, Quy Nhơn', NULL, NULL, 299999.00, NULL, 299999.00, '', '', '', 0),
(11, 1, 13, NULL, '2025-12-01 04:41:52', 'ChoXacNhan', '29 Tố Hữu, Quy Nhơn', NULL, NULL, 299999.00, NULL, 299999.00, '', '', '', 0),
(12, 1, 6, NULL, '2025-12-01 05:17:39', 'ChoXacNhan', '29 Xuân Diệu, Quy Nhơn', NULL, NULL, 1200000.00, NULL, 1200000.00, '', '', '', 0),
(13, 1, 13, NULL, '2025-12-01 05:26:59', 'ChoXacNhan', '29 Xuân Diệu, Quy Nhơn', NULL, NULL, 299999.00, NULL, 299999.00, '', '', '', 0),
(14, 1, 6, NULL, '2025-12-01 05:57:06', 'ChoXacNhan', '29 Xuân Diệu, Quy Nhơn', NULL, NULL, 2000000.00, NULL, 2000000.00, '', '', '', 0),
(15, 1, 7, NULL, '2025-12-01 06:14:58', 'ChoXacNhan', '29 Xuân Diệu, Quy Nhơn', NULL, NULL, 800000.00, NULL, 800000.00, '', '', '', 0),
(16, 1, 13, NULL, '2025-12-01 06:16:25', 'ChoXacNhan', '29 Tây Sơn, Quy Nhơn', NULL, NULL, 299999.00, NULL, 299999.00, '', '', '', 0),
(17, 1, 13, NULL, '2025-12-01 06:19:42', 'DaHuy', '29 Xuân Diệu, Quy Nhơn', NULL, NULL, 899999.00, NULL, 899999.00, '', '', '', 0),
(18, 1, 6, NULL, '2025-12-01 06:23:35', 'ChoXacNhan', '29 Xuân Diệu, Quy Nhơn', NULL, NULL, 1400000.00, NULL, 1400000.00, '', '', '', 0),
(19, 1, 6, NULL, '2025-12-01 06:36:52', 'ChoXacNhan', '29 Xuân Diệu, Quy Nhơn', NULL, NULL, 59998.00, NULL, 2059998.00, '', '', '', 0),
(20, 1, 6, NULL, '2025-12-01 06:40:06', 'DaHuy', '29 Xuân Diệu, Quy Nhơn', NULL, NULL, 59998.00, NULL, 1859998.00, '', '', '', 0),
(21, 1, 13, NULL, '2025-12-01 06:40:57', 'ChoXacNhan', '29 Xuân Diệu, Quy Nhơn', NULL, NULL, 29999.00, NULL, 329998.00, '', '', '', 0),
(22, 1, 13, NULL, '2025-12-01 06:41:11', 'DaHuy', '29 Xuân Diệu, Quy Nhơn', NULL, NULL, 29999.00, NULL, 329998.00, '', '', '', 0),
(23, 1, 13, NULL, '2025-12-01 06:43:36', 'DaHuy', '29 Xuân Diệu, Quy Nhơn', NULL, NULL, 29999.00, NULL, 329998.00, '', '', '', 0),
(24, 1, 6, NULL, '2025-12-01 06:45:36', 'ChoXacNhan', '29 Xuân Diệu, Quy Nhơn', NULL, NULL, 1229999.00, NULL, 1200000.00, '', '', '', 0),
(25, 1, 13, NULL, '2025-12-01 06:46:09', 'ChoXacNhan', '29 Xuân Diệu, Quy Nhơn', NULL, NULL, 929998.00, NULL, 899999.00, '', '', '', 0),
(26, 1, 7, NULL, '2025-12-01 06:47:24', 'ChoXacNhan', '29 Xuân Diệu, Quy Nhơn', NULL, NULL, 829999.00, NULL, 800000.00, '', '', '', 0),
(27, 1, 6, NULL, '2025-12-01 07:32:23', 'ChoXacNhan', '29 Tố Hữu, Quy Nhơn', NULL, NULL, 1829999.00, NULL, 1800000.00, 'SM', '', '', 0),
(28, 1, 6, NULL, '2025-12-01 07:35:57', 'ChoXacNhan', '29 Xuan Dieu', NULL, NULL, 409998.00, NULL, 379999.00, '', '', '', 0),
(29, 1, 6, NULL, '2025-12-01 07:37:29', 'ChoXacNhan', '29 Xuan Dieu', NULL, NULL, 379999.00, NULL, 350000.00, '', '', '', 0),
(30, 1, 6, NULL, '2025-12-01 07:47:50', 'ChoXacNhan', 'TPHCM', NULL, NULL, 1259998.00, NULL, 1229999.00, '', '', '', 0),
(31, 1, 13, NULL, '2025-12-01 07:49:35', 'DaHuy', 'TPHCM', NULL, NULL, 359997.00, NULL, 329998.00, '', '', '', 0),
(36, 1, 13, NULL, '2025-12-01 08:11:49', 'DaHuy', 'TPHCM', NULL, NULL, 359997.00, NULL, 329998.00, '', '', '', 0),
(37, 1, 13, NULL, '2025-12-02 12:23:17', 'ChoXacNhan', '29 Xuân Diệu, Quy Nhơn', NULL, NULL, 299999.00, NULL, 299999.00, '', '', '', 0),
(38, 1, 13, NULL, '2025-12-02 12:28:07', 'ChoXacNhan', '29 Xuân Diệu, Quy Nhơn', NULL, NULL, 314998.95, NULL, 314998.95, '', '', '', 15000),
(39, 1, 13, NULL, '2025-12-02 12:28:49', 'ChoXacNhan', '29 Xuân Diệu, Quy Nhơn', NULL, NULL, 314998.95, NULL, 299999.00, '', '', '', 0);

-- --------------------------------------------------------

--
-- Table structure for table `hinhanhsanpham`
--

DROP TABLE IF EXISTS `hinhanhsanpham`;
CREATE TABLE IF NOT EXISTS `hinhanhsanpham` (
  `ID_HinhAnh` int NOT NULL AUTO_INCREMENT,
  `ID_SanPham` int NOT NULL,
  `URL_HinhAnh` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_vietnamese_ci NOT NULL,
  PRIMARY KEY (`ID_HinhAnh`),
  KEY `ID_SanPham` (`ID_SanPham`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Dumping data for table `hinhanhsanpham`
--

INSERT INTO `hinhanhsanpham` (`ID_HinhAnh`, `ID_SanPham`, `URL_HinhAnh`) VALUES
(1, 1, 'img/iphone_x_1.jpg'),
(2, 1, 'img/iphone_x_2.jpg'),
(3, 2, 'img/jean_jacket_1.jpg'),
(4, 3, 'img/mayxay_1.jpg'),
(5, 4, 'img/ban_go_1.jpg'),
(6, 5, 'img/nike_shoes_1.jpg'),
(7, 6, 'img/balo_1.jpg'),
(8, 7, 'img/noicom_1.jpg'),
(9, 8, 'img/sach_php_1.jpg'),
(10, 9, 'img/1764483024_0_sport-bike.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `hosonguoigiaohang`
--

DROP TABLE IF EXISTS `hosonguoigiaohang`;
CREATE TABLE IF NOT EXISTS `hosonguoigiaohang` (
  `ID_NguoiDung` int NOT NULL,
  `GioiTinh` enum('Nam','Nu','Khac') CHARACTER SET utf8mb4 COLLATE utf8mb4_vietnamese_ci DEFAULT NULL,
  `NamSinh` int DEFAULT NULL,
  `Anh_CCCD_Truoc` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_vietnamese_ci DEFAULT NULL,
  `Anh_CCCD_Sau` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_vietnamese_ci DEFAULT NULL,
  `Anh_BangLaiXe` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_vietnamese_ci DEFAULT NULL,
  `BienSoXe` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_vietnamese_ci DEFAULT NULL,
  `KhuVucHoatDong` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_vietnamese_ci DEFAULT NULL,
  `DiemDanhGiaTrungBinh` decimal(2,1) DEFAULT '5.0',
  `AnhDaiDien` varchar(255) COLLATE utf8mb4_vietnamese_ci NOT NULL,
  `LoaiXe` text COLLATE utf8mb4_vietnamese_ci NOT NULL,
  PRIMARY KEY (`ID_NguoiDung`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Dumping data for table `hosonguoigiaohang`
--

INSERT INTO `hosonguoigiaohang` (`ID_NguoiDung`, `GioiTinh`, `NamSinh`, `Anh_CCCD_Truoc`, `Anh_CCCD_Sau`, `Anh_BangLaiXe`, `BienSoXe`, `KhuVucHoatDong`, `DiemDanhGiaTrungBinh`, `AnhDaiDien`, `LoaiXe`) VALUES
(3, 'Nam', 1990, 'img/cccd_3_front.jpg', 'img/cccd_3_back.jpg', 'img/license_3.jpg', '51A-12345', 'TP.HCM', 4.8, '', ''),
(9, 'Nam', 1995, 'img/cccd_9_front.jpg', 'img/cccd_9_back.jpg', 'img/license_9.jpg', '59X-67890', 'TP.HCM', 4.6, '', '');

-- --------------------------------------------------------

--
-- Table structure for table `khieunai`
--

DROP TABLE IF EXISTS `khieunai`;
CREATE TABLE IF NOT EXISTS `khieunai` (
  `ID_KhieuNai` int NOT NULL AUTO_INCREMENT,
  `ID_DonHang` int NOT NULL,
  `ID_NguoiBaoCao` int NOT NULL,
  `LoaiSuCo` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_vietnamese_ci NOT NULL,
  `MoTaChiTiet` text CHARACTER SET utf8mb4 COLLATE utf8mb4_vietnamese_ci,
  `PhanHoi_NguoiBan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_vietnamese_ci,
  `TrangThaiXuLy` enum('HoanTien','DoiSanPham','TuChoi','DangXuLy') CHARACTER SET utf8mb4 COLLATE utf8mb4_vietnamese_ci DEFAULT 'DangXuLy',
  `NgayKhieuNai` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID_KhieuNai`),
  KEY `ID_DonHang` (`ID_DonHang`),
  KEY `ID_NguoiBaoCao` (`ID_NguoiBaoCao`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Dumping data for table `khieunai`
--

INSERT INTO `khieunai` (`ID_KhieuNai`, `ID_DonHang`, `ID_NguoiBaoCao`, `LoaiSuCo`, `MoTaChiTiet`, `PhanHoi_NguoiBan`, `TrangThaiXuLy`, `NgayKhieuNai`) VALUES
(1, 2, 1, 'Sản phẩm thiếu', 'Người bán báo thiếu hàng khi chuẩn bị gửi', NULL, 'DangXuLy', '2025-11-25 08:01:07'),
(2, 4, 8, 'Hủy đơn', 'Hệ thống báo giao chậm nên tôi hủy', 'Đang kiểm tra', 'DangXuLy', '2025-11-26 15:41:21');

-- --------------------------------------------------------

--
-- Table structure for table `lichsutrangthai`
--

DROP TABLE IF EXISTS `lichsutrangthai`;
CREATE TABLE IF NOT EXISTS `lichsutrangthai` (
  `ID_LichSu` int NOT NULL AUTO_INCREMENT,
  `ID_DonHang` int NOT NULL,
  `TrangThaiMoi` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_vietnamese_ci NOT NULL,
  `ThoiGianCapNhat` datetime DEFAULT CURRENT_TIMESTAMP,
  `GhiChu` text CHARACTER SET utf8mb4 COLLATE utf8mb4_vietnamese_ci,
  PRIMARY KEY (`ID_LichSu`),
  KEY `ID_DonHang` (`ID_DonHang`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Dumping data for table `lichsutrangthai`
--

INSERT INTO `lichsutrangthai` (`ID_LichSu`, `ID_DonHang`, `TrangThaiMoi`, `ThoiGianCapNhat`, `GhiChu`) VALUES
(1, 1, 'ChoXacNhan', '2025-11-22 08:01:07', 'Đơn vừa tạo'),
(2, 1, 'ChoGiaoHang', '2025-11-23 08:01:07', 'Đã xác nhận, chờ giao'),
(3, 1, 'DaGiao', '2025-11-25 08:01:07', 'Đã giao thành công'),
(4, 2, 'ChoXacNhan', '2025-11-23 08:01:07', 'Chờ người bán xác nhận'),
(5, 3, 'ChoGiaoHang', '2025-11-25 15:41:21', 'Đã xác nhận'),
(6, 3, 'DangGiao', '2025-11-26 15:41:21', 'Đang giao bởi shipper'),
(7, 4, 'ChoXacNhan', '2025-11-24 15:41:21', 'Đơn mới'),
(8, 4, 'DaHuy', '2025-11-25 15:41:21', 'Khách tự hủy'),
(9, 5, 'ChoGiaoHang', '2025-11-22 15:41:21', 'Đã xác nhận'),
(10, 5, 'DangGiao', '2025-11-23 15:41:21', 'Đang vận chuyển'),
(11, 5, 'DaGiao', '2025-11-24 15:41:21', 'Giao thành công'),
(12, 6, 'ChoGiaoHang', '2025-11-26 15:41:21', 'Người bán đã xác nhận đơn'),
(13, 2, 'DangVanChuyen', '2025-11-30 13:18:28', 'Tài xế ID 9 đã nhận đơn và bắt đầu vận chuyển.');

-- --------------------------------------------------------

--
-- Table structure for table `nguoidung`
--

DROP TABLE IF EXISTS `nguoidung`;
CREATE TABLE IF NOT EXISTS `nguoidung` (
  `ID_NguoiDung` int NOT NULL AUTO_INCREMENT,
  `HoTen` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_vietnamese_ci NOT NULL,
  `Email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_vietnamese_ci NOT NULL,
  `SoDienThoai` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_vietnamese_ci NOT NULL,
  `MatKhau_Hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_vietnamese_ci NOT NULL,
  `VaiTro` enum('NguoiMua','NguoiBan','NguoiGiaoHang','QuanTriVien') CHARACTER SET utf8mb4 COLLATE utf8mb4_vietnamese_ci NOT NULL,
  `TrangThaiHoatDong` tinyint(1) DEFAULT '1',
  `DiaChiGiaoHangMacDinh` text CHARACTER SET utf8mb4 COLLATE utf8mb4_vietnamese_ci,
  `NgayTao` datetime DEFAULT CURRENT_TIMESTAMP,
  `TenDangNhap` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_vietnamese_ci DEFAULT NULL,
  `AnhDaiDien` text COLLATE utf8mb4_vietnamese_ci NOT NULL,
  PRIMARY KEY (`ID_NguoiDung`),
  UNIQUE KEY `Email` (`Email`),
  UNIQUE KEY `SoDienThoai` (`SoDienThoai`),
  UNIQUE KEY `TenDangNhap` (`TenDangNhap`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Dumping data for table `nguoidung`
--

INSERT INTO `nguoidung` (`ID_NguoiDung`, `HoTen`, `Email`, `SoDienThoai`, `MatKhau_Hash`, `VaiTro`, `TrangThaiHoatDong`, `DiaChiGiaoHangMacDinh`, `NgayTao`, `TenDangNhap`, `AnhDaiDien`) VALUES
(1, 'Kiều Quang Nhân', 'kieuquangnhan2k5@gmail.com', '0337706409', '11223344', 'NguoiMua', 1, '', '2025-11-24 14:34:28', 'user12', ''),
(2, 'Trần Thị B', 'ttb@gmail.com', '098765721', '!456tb', 'NguoiBan', 1, '456 Đ. B, Q.3, TP.HCM', '2025-11-25 08:01:06', 'ttb', ''),
(3, 'Le Van C', 'lvc@example.com', '0901234567', '$789vc', 'NguoiGiaoHang', 1, '789 Đ. C, Q.5, TP.HCM', '2025-11-25 08:01:06', 'lvc', ''),
(4, 'Pham Thi D', 'ptd@example.com', '0933222111', '#012pd', 'QuanTriVien', 1, NULL, '2025-11-25 08:01:06', 'admin', ''),
(5, 'Nguyen Van A', 'nva@example.com', '0912345678', '@123na', 'NguoiMua', 1, '123 Đ. A, Q.1, TP.HCM', '2025-11-25 08:01:06', 'nva', ''),
(6, 'Vương Tổng', 'tranminhvuongquinhon@gmail.com', '0985236666', '$2y$10$0QJtixraFkh/nv60ALa4NuXKP9OpoTzgYkaHSFVREMsnl/pSGAVLC', 'NguoiMua', 1, NULL, '2025-11-26 15:36:50', 'tongdai', ''),
(7, 'Dang Van G', 'dvg@example.com', '0977888999', '$2y$10$hash7', 'NguoiBan', 1, '301 Đ. G, Q.7, TP.HCM', '2025-11-26 15:41:21', 'dvg', ''),
(8, 'Le Thi H', 'lth@example.com', '0909090909', '$2y$10$hash8', 'NguoiMua', 1, '12 Đ. H, Q.8, TP.HCM', '2025-11-26 15:41:21', 'lth', ''),
(9, 'Pham Van I', 'pvi@example.com', '0966666666', '$2y$10$hash9', 'NguoiGiaoHang', 1, '999 Đ. I, Q.2, TP.HCM', '2025-11-26 15:41:21', 'pvi', ''),
(10, 'Nguyen Tuan K', 'ntk@example.com', '0988111222', '$2y$10$hash10', 'QuanTriVien', 1, NULL, '2025-11-26 15:41:21', 'ntkadmin', ''),
(11, 'Hoang Minh E', 'hme@example.com', '0911222333', '$2y$10$hash5', 'NguoiMua', 1, '22 Đ. E, Q.10, TP.HCM', '2025-11-26 15:41:21', 'hme', ''),
(12, 'Vo Thi F', 'vtf@example.com', '0933444555', '$2y$10$hash6', 'NguoiBan', 1, '18 Đ. F, Q.6, TP.HCM', '2025-11-26 15:41:21', 'vtf', ''),
(13, 'Nguyễn Văn Linh', 'nguyenvanlinh99@gmail.com', '0989878789', 'nguyenvanlinh99', 'NguoiBan', 1, '29 Xuân Diệu, Đà Nẵng', '2025-11-28 15:51:33', 'nguyenvanlinh99', '');

-- --------------------------------------------------------

--
-- Table structure for table `sanpham`
--

DROP TABLE IF EXISTS `sanpham`;
CREATE TABLE IF NOT EXISTS `sanpham` (
  `ID_SanPham` int NOT NULL AUTO_INCREMENT,
  `ID_NguoiBan` int NOT NULL,
  `TenSanPham` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_vietnamese_ci NOT NULL,
  `MoTa` text CHARACTER SET utf8mb4 COLLATE utf8mb4_vietnamese_ci,
  `Gia` decimal(10,2) NOT NULL,
  `TinhTrang` enum('Moi','NhuMoi','Tot','TrungBinh','Kem') CHARACTER SET utf8mb4 COLLATE utf8mb4_vietnamese_ci NOT NULL,
  `SoLuong` int DEFAULT '1',
  `DiaChiLayHang` text CHARACTER SET utf8mb4 COLLATE utf8mb4_vietnamese_ci,
  `KichThuoc` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_vietnamese_ci DEFAULT NULL,
  `ID_DanhMuc` int DEFAULT NULL,
  `TrangThaiDangBan` enum('ChoDuyet','DangBan','DaBan','BiGoBo') CHARACTER SET utf8mb4 COLLATE utf8mb4_vietnamese_ci DEFAULT 'ChoDuyet',
  `NgayTao` datetime DEFAULT CURRENT_TIMESTAMP,
  `MauSac` text NOT NULL,
  `ThuongHieu` text NOT NULL,
  PRIMARY KEY (`ID_SanPham`),
  KEY `ID_NguoiBan` (`ID_NguoiBan`),
  KEY `ID_DanhMuc` (`ID_DanhMuc`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `sanpham`
--

INSERT INTO `sanpham` (`ID_SanPham`, `ID_NguoiBan`, `TenSanPham`, `MoTa`, `Gia`, `TinhTrang`, `SoLuong`, `DiaChiLayHang`, `KichThuoc`, `ID_DanhMuc`, `TrangThaiDangBan`, `NgayTao`, `MauSac`, `ThuongHieu`) VALUES
(1, 2, 'Điện thoại cũ iPhone X', 'Máy còn tốt, pin ~80%', 3500000.00, 'NhuMoi', 1, '456 Đ. B, Q.3, TP.HCM', '5.8 inch', 1, 'DangBan', '2025-11-25 08:01:06', '', ''),
(2, 2, 'Áo khoác jean nam', 'Size M, ít sử dụng', 450000.00, 'Tot', 2, '456 Đ. B, Q.3, TP.HCM', 'M', 2, 'DangBan', '2025-11-25 08:01:06', '', ''),
(3, 6, 'Máy xay sinh tố', 'Dùng 3 tháng, hoạt động tốt', 600000.00, 'Tot', 1, '18 Đ. F, Q.6, TP.HCM', 'Medium', 3, 'DangBan', '2025-11-26 15:41:21', '', ''),
(4, 6, 'Bàn làm việc gỗ', 'Còn mới 90%', 1200000.00, 'NhuMoi', 1, '18 Đ. F, Q.6, TP.HCM', '120x60cm', 5, 'DangBan', '2025-11-26 15:41:21', '', ''),
(5, 7, 'Giày chạy bộ Nike', 'Size 42, còn mới', 800000.00, 'NhuMoi', 2, '301 Đ. G, Q.7, TP.HCM', '42', 6, 'DangBan', '2025-11-26 15:41:21', '', ''),
(6, 7, 'Balo laptop', '15 inch, chống nước', 300000.00, 'Tot', 5, '301 Đ. G, Q.7, TP.HCM', '15 inch', 2, 'DangBan', '2025-11-26 15:41:21', '', ''),
(7, 6, 'Nồi cơm điện', 'Cũ 2 năm, còn sử dụng tốt', 350000.00, 'TrungBinh', 1, '18 Đ. F, Q.6, TP.HCM', '3L', 3, 'DangBan', '2025-11-26 15:41:21', '', ''),
(8, 7, 'Sách Lập trình PHP', 'Sách cũ, không rách', 120000.00, 'Tot', 3, '301 Đ. G, Q.7, TP.HCM', 'A5', 4, 'DangBan', '2025-11-26 15:41:21', '', ''),
(9, 13, 'Xe đạp thể thao', 'Xe đạp thể thao phong cách mới cho giới trẻ', 299999.00, 'Moi', 1, '29 xuân diệu, quy nhơn', '2.8 m', 6, 'DangBan', '2025-11-30 13:10:24', 'Vàng', '');

-- --------------------------------------------------------

--
-- Table structure for table `thongkedoanhthu`
--

DROP TABLE IF EXISTS `thongkedoanhthu`;
CREATE TABLE IF NOT EXISTS `thongkedoanhthu` (
  `ID_ThongKe` int NOT NULL AUTO_INCREMENT,
  `ID_NguoiBan` int NOT NULL,
  `LoaiThoiGian` enum('Ngay','Thang','Nam') CHARACTER SET utf8mb4 COLLATE utf8mb4_vietnamese_ci NOT NULL,
  `GiaTriThoiGian` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_vietnamese_ci NOT NULL,
  `TongDoanhThu` decimal(10,2) DEFAULT '0.00',
  `TienThucNhan` decimal(10,2) DEFAULT '0.00',
  `GiaTriMat_HuyHoan` decimal(10,2) DEFAULT '0.00',
  PRIMARY KEY (`ID_ThongKe`),
  KEY `ID_NguoiBan` (`ID_NguoiBan`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Dumping data for table `thongkedoanhthu`
--

INSERT INTO `thongkedoanhthu` (`ID_ThongKe`, `ID_NguoiBan`, `LoaiThoiGian`, `GiaTriThoiGian`, `TongDoanhThu`, `TienThucNhan`, `GiaTriMat_HuyHoan`) VALUES
(1, 2, 'Ngay', '2025-11-25', 3950000.00, 3800000.00, 0.00),
(2, 6, 'Ngay', '2025-11-26', 1800000.00, 1700000.00, 0.00),
(3, 7, 'Ngay', '2025-11-26', 300000.00, 280000.00, 0.00);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `chitietdonhang`
--
ALTER TABLE `chitietdonhang`
  ADD CONSTRAINT `chitietdonhang_ibfk_1` FOREIGN KEY (`ID_DonHang`) REFERENCES `danhsachdonhang` (`ID_DonHang`) ON DELETE CASCADE,
  ADD CONSTRAINT `chitietdonhang_ibfk_2` FOREIGN KEY (`ID_SanPham`) REFERENCES `sanpham` (`ID_SanPham`),
  ADD CONSTRAINT `fk_ctdh_donhang` FOREIGN KEY (`ID_DonHang`) REFERENCES `danhsachdonhang` (`ID_DonHang`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ctdh_sanpham` FOREIGN KEY (`ID_SanPham`) REFERENCES `sanpham` (`ID_SanPham`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `danhgia_nhanxet`
--
ALTER TABLE `danhgia_nhanxet`
  ADD CONSTRAINT `danhgia_nhanxet_ibfk_1` FOREIGN KEY (`ID_DonHang`) REFERENCES `danhsachdonhang` (`ID_DonHang`) ON DELETE CASCADE,
  ADD CONSTRAINT `danhgia_nhanxet_ibfk_2` FOREIGN KEY (`ID_NguoiDanhGia`) REFERENCES `nguoidung` (`ID_NguoiDung`),
  ADD CONSTRAINT `danhgia_nhanxet_ibfk_3` FOREIGN KEY (`ID_NguoiDuocDanhGia`) REFERENCES `nguoidung` (`ID_NguoiDung`),
  ADD CONSTRAINT `fk_dg_donhang` FOREIGN KEY (`ID_DonHang`) REFERENCES `danhsachdonhang` (`ID_DonHang`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_dg_ngdanhgia` FOREIGN KEY (`ID_NguoiDanhGia`) REFERENCES `nguoidung` (`ID_NguoiDung`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_dg_ngduocdanhgia` FOREIGN KEY (`ID_NguoiDuocDanhGia`) REFERENCES `nguoidung` (`ID_NguoiDung`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `danhsachdonhang`
--
ALTER TABLE `danhsachdonhang`
  ADD CONSTRAINT `danhsachdonhang_ibfk_1` FOREIGN KEY (`ID_NguoiMua`) REFERENCES `nguoidung` (`ID_NguoiDung`) ON DELETE RESTRICT,
  ADD CONSTRAINT `danhsachdonhang_ibfk_2` FOREIGN KEY (`ID_NguoiBan`) REFERENCES `nguoidung` (`ID_NguoiDung`) ON DELETE RESTRICT,
  ADD CONSTRAINT `danhsachdonhang_ibfk_3` FOREIGN KEY (`ID_NguoiGiaoHang`) REFERENCES `hosonguoigiaohang` (`ID_NguoiDung`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_dh_nguoiban` FOREIGN KEY (`ID_NguoiBan`) REFERENCES `nguoidung` (`ID_NguoiDung`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_dh_nguoigiaohang` FOREIGN KEY (`ID_NguoiGiaoHang`) REFERENCES `nguoidung` (`ID_NguoiDung`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_dh_nguoimua` FOREIGN KEY (`ID_NguoiMua`) REFERENCES `nguoidung` (`ID_NguoiDung`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_donhang_nguoiban` FOREIGN KEY (`ID_NguoiBan`) REFERENCES `nguoidung` (`ID_NguoiDung`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_donhang_nguoigiaohang` FOREIGN KEY (`ID_NguoiGiaoHang`) REFERENCES `nguoidung` (`ID_NguoiDung`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_donhang_nguoimua` FOREIGN KEY (`ID_NguoiMua`) REFERENCES `nguoidung` (`ID_NguoiDung`) ON DELETE CASCADE;

--
-- Constraints for table `hinhanhsanpham`
--
ALTER TABLE `hinhanhsanpham`
  ADD CONSTRAINT `fk_hasp_sanpham` FOREIGN KEY (`ID_SanPham`) REFERENCES `sanpham` (`ID_SanPham`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_hinhanh_sanpham` FOREIGN KEY (`ID_SanPham`) REFERENCES `sanpham` (`ID_SanPham`) ON DELETE CASCADE,
  ADD CONSTRAINT `hinhanhsanpham_ibfk_1` FOREIGN KEY (`ID_SanPham`) REFERENCES `sanpham` (`ID_SanPham`) ON DELETE CASCADE;

--
-- Constraints for table `hosonguoigiaohang`
--
ALTER TABLE `hosonguoigiaohang`
  ADD CONSTRAINT `fk_hsg_ngdung` FOREIGN KEY (`ID_NguoiDung`) REFERENCES `nguoidung` (`ID_NguoiDung`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `hosonguoigiaohang_ibfk_1` FOREIGN KEY (`ID_NguoiDung`) REFERENCES `nguoidung` (`ID_NguoiDung`) ON DELETE CASCADE;

--
-- Constraints for table `khieunai`
--
ALTER TABLE `khieunai`
  ADD CONSTRAINT `fk_kn_donhang` FOREIGN KEY (`ID_DonHang`) REFERENCES `danhsachdonhang` (`ID_DonHang`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_kn_ngbao` FOREIGN KEY (`ID_NguoiBaoCao`) REFERENCES `nguoidung` (`ID_NguoiDung`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `khieunai_ibfk_1` FOREIGN KEY (`ID_DonHang`) REFERENCES `danhsachdonhang` (`ID_DonHang`) ON DELETE CASCADE,
  ADD CONSTRAINT `khieunai_ibfk_2` FOREIGN KEY (`ID_NguoiBaoCao`) REFERENCES `nguoidung` (`ID_NguoiDung`);

--
-- Constraints for table `lichsutrangthai`
--
ALTER TABLE `lichsutrangthai`
  ADD CONSTRAINT `fk_ls_donhang` FOREIGN KEY (`ID_DonHang`) REFERENCES `danhsachdonhang` (`ID_DonHang`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `lichsutrangthai_ibfk_1` FOREIGN KEY (`ID_DonHang`) REFERENCES `danhsachdonhang` (`ID_DonHang`) ON DELETE CASCADE;

--
-- Constraints for table `sanpham`
--
ALTER TABLE `sanpham`
  ADD CONSTRAINT `fk_sanpham_danhmuc` FOREIGN KEY (`ID_DanhMuc`) REFERENCES `danhmuc` (`ID_DanhMuc`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_sanpham_nguoidung` FOREIGN KEY (`ID_NguoiBan`) REFERENCES `nguoidung` (`ID_NguoiDung`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_sp_danhmuc` FOREIGN KEY (`ID_DanhMuc`) REFERENCES `danhmuc` (`ID_DanhMuc`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_sp_nguoiban` FOREIGN KEY (`ID_NguoiBan`) REFERENCES `nguoidung` (`ID_NguoiDung`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `sanpham_ibfk_1` FOREIGN KEY (`ID_NguoiBan`) REFERENCES `nguoidung` (`ID_NguoiDung`) ON DELETE CASCADE,
  ADD CONSTRAINT `sanpham_ibfk_2` FOREIGN KEY (`ID_DanhMuc`) REFERENCES `danhmuc` (`ID_DanhMuc`) ON DELETE SET NULL;

--
-- Constraints for table `thongkedoanhthu`
--
ALTER TABLE `thongkedoanhthu`
  ADD CONSTRAINT `thongkedoanhthu_ibfk_1` FOREIGN KEY (`ID_NguoiBan`) REFERENCES `nguoidung` (`ID_NguoiDung`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
