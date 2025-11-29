-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1:3306
-- Thời gian đã tạo: Th10 29, 2025 lúc 02:22 PM
-- Phiên bản máy phục vụ: 9.1.0
-- Phiên bản PHP: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `secondhand_market2`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chitietdonhang`
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `danhgia_nhanxet`
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `danhmuc`
--

DROP TABLE IF EXISTS `danhmuc`;
CREATE TABLE IF NOT EXISTS `danhmuc` (
  `ID_DanhMuc` int NOT NULL AUTO_INCREMENT,
  `TenDanhMuc` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_vietnamese_ci NOT NULL,
  PRIMARY KEY (`ID_DanhMuc`),
  UNIQUE KEY `TenDanhMuc` (`TenDanhMuc`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `danhsachdonhang`
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
  PRIMARY KEY (`ID_DonHang`),
  KEY `ID_NguoiMua` (`ID_NguoiMua`),
  KEY `ID_NguoiBan` (`ID_NguoiBan`),
  KEY `ID_NguoiGiaoHang` (`ID_NguoiGiaoHang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `hinhanhsanpham`
--

DROP TABLE IF EXISTS `hinhanhsanpham`;
CREATE TABLE IF NOT EXISTS `hinhanhsanpham` (
  `ID_HinhAnh` int NOT NULL AUTO_INCREMENT,
  `ID_SanPham` int NOT NULL,
  `URL_HinhAnh` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_vietnamese_ci NOT NULL,
  PRIMARY KEY (`ID_HinhAnh`),
  KEY `ID_SanPham` (`ID_SanPham`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `hosonguoigiaohang`
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
  PRIMARY KEY (`ID_NguoiDung`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `khieunai`
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `lichsutrangthai`
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `nguoidung`
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
  PRIMARY KEY (`ID_NguoiDung`),
  UNIQUE KEY `Email` (`Email`),
  UNIQUE KEY `SoDienThoai` (`SoDienThoai`),
  UNIQUE KEY `TenDangNhap` (`TenDangNhap`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `sanpham`
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
  `MauSac` text CHARACTER SET utf8mb4 COLLATE utf8mb4_vietnamese_ci NOT NULL,
  `ThuongHieu` text CHARACTER SET utf8mb4 COLLATE utf8mb4_vietnamese_ci NOT NULL,
  `ID_DanhMuc` int DEFAULT NULL,
  `TrangThaiDangBan` enum('ChoDuyet','DangBan','DaBan','BiGoBo') CHARACTER SET utf8mb4 COLLATE utf8mb4_vietnamese_ci DEFAULT 'ChoDuyet',
  `NgayTao` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID_SanPham`),
  KEY `ID_NguoiBan` (`ID_NguoiBan`),
  KEY `ID_DanhMuc` (`ID_DanhMuc`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `thongkedoanhthu`
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `chitietdonhang`
--
ALTER TABLE `chitietdonhang`
  ADD CONSTRAINT `chitietdonhang_ibfk_1` FOREIGN KEY (`ID_DonHang`) REFERENCES `danhsachdonhang` (`ID_DonHang`) ON DELETE CASCADE,
  ADD CONSTRAINT `chitietdonhang_ibfk_2` FOREIGN KEY (`ID_SanPham`) REFERENCES `sanpham` (`ID_SanPham`) ON DELETE RESTRICT;

--
-- Các ràng buộc cho bảng `danhgia_nhanxet`
--
ALTER TABLE `danhgia_nhanxet`
  ADD CONSTRAINT `danhgia_nhanxet_ibfk_1` FOREIGN KEY (`ID_DonHang`) REFERENCES `danhsachdonhang` (`ID_DonHang`) ON DELETE CASCADE,
  ADD CONSTRAINT `danhgia_nhanxet_ibfk_2` FOREIGN KEY (`ID_NguoiDanhGia`) REFERENCES `nguoidung` (`ID_NguoiDung`) ON DELETE RESTRICT,
  ADD CONSTRAINT `danhgia_nhanxet_ibfk_3` FOREIGN KEY (`ID_NguoiDuocDanhGia`) REFERENCES `nguoidung` (`ID_NguoiDung`) ON DELETE RESTRICT;

--
-- Các ràng buộc cho bảng `danhsachdonhang`
--
ALTER TABLE `danhsachdonhang`
  ADD CONSTRAINT `danhsachdonhang_ibfk_1` FOREIGN KEY (`ID_NguoiMua`) REFERENCES `nguoidung` (`ID_NguoiDung`) ON DELETE RESTRICT,
  ADD CONSTRAINT `danhsachdonhang_ibfk_2` FOREIGN KEY (`ID_NguoiBan`) REFERENCES `nguoidung` (`ID_NguoiDung`) ON DELETE RESTRICT,
  ADD CONSTRAINT `danhsachdonhang_ibfk_3` FOREIGN KEY (`ID_NguoiGiaoHang`) REFERENCES `hosonguoigiaohang` (`ID_NguoiDung`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `hinhanhsanpham`
--
ALTER TABLE `hinhanhsanpham`
  ADD CONSTRAINT `hinhanhsanpham_ibfk_1` FOREIGN KEY (`ID_SanPham`) REFERENCES `sanpham` (`ID_SanPham`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `hosonguoigiaohang`
--
ALTER TABLE `hosonguoigiaohang`
  ADD CONSTRAINT `hosonguoigiaohang_ibfk_1` FOREIGN KEY (`ID_NguoiDung`) REFERENCES `nguoidung` (`ID_NguoiDung`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `khieunai`
--
ALTER TABLE `khieunai`
  ADD CONSTRAINT `khieunai_ibfk_1` FOREIGN KEY (`ID_DonHang`) REFERENCES `danhsachdonhang` (`ID_DonHang`) ON DELETE CASCADE,
  ADD CONSTRAINT `khieunai_ibfk_2` FOREIGN KEY (`ID_NguoiBaoCao`) REFERENCES `nguoidung` (`ID_NguoiDung`) ON DELETE RESTRICT;

--
-- Các ràng buộc cho bảng `lichsutrangthai`
--
ALTER TABLE `lichsutrangthai`
  ADD CONSTRAINT `lichsutrangthai_ibfk_1` FOREIGN KEY (`ID_DonHang`) REFERENCES `danhsachdonhang` (`ID_DonHang`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `sanpham`
--
ALTER TABLE `sanpham`
  ADD CONSTRAINT `sanpham_ibfk_1` FOREIGN KEY (`ID_NguoiBan`) REFERENCES `nguoidung` (`ID_NguoiDung`) ON DELETE CASCADE,
  ADD CONSTRAINT `sanpham_ibfk_2` FOREIGN KEY (`ID_DanhMuc`) REFERENCES `danhmuc` (`ID_DanhMuc`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `thongkedoanhthu`
--
ALTER TABLE `thongkedoanhthu`
  ADD CONSTRAINT `thongkedoanhthu_ibfk_1` FOREIGN KEY (`ID_NguoiBan`) REFERENCES `nguoidung` (`ID_NguoiDung`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
