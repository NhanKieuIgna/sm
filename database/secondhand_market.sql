-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1:3306
-- Thời gian đã tạo: Th10 15, 2025 lúc 10:52 AM
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
-- Cơ sở dữ liệu: `secondhand_market`
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
) ;

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
  `NhanXet` text COLLATE utf8mb4_vietnamese_ci,
  `NgayDanhGia` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID_DanhGia`),
  UNIQUE KEY `ID_DonHang` (`ID_DonHang`),
  KEY `ID_NguoiDanhGia` (`ID_NguoiDanhGia`),
  KEY `ID_NguoiDuocDanhGia` (`ID_NguoiDuocDanhGia`)
) ;

-- --------------------------------------------------------

--
-- Table structure for table `danhmuc`
--

DROP TABLE IF EXISTS `danhmuc`;
CREATE TABLE IF NOT EXISTS `danhmuc` (
  `ID_DanhMuc` int NOT NULL AUTO_INCREMENT,
  `TenDanhMuc` varchar(100) COLLATE utf8mb4_vietnamese_ci NOT NULL,
  PRIMARY KEY (`ID_DanhMuc`),
  UNIQUE KEY `TenDanhMuc` (`TenDanhMuc`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

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
  `TrangThaiDonHang` enum('ChoXacNhan','ChoGiaoHang','DangXuLy','DangVanChuyen','DaGiao','HoanThanh','DaHuy','KhieuNai') COLLATE utf8mb4_vietnamese_ci NOT NULL,
  `DiaChiGiaoHang` text COLLATE utf8mb4_vietnamese_ci NOT NULL,
  `PhuongThucVanChuyen` varchar(50) COLLATE utf8mb4_vietnamese_ci DEFAULT NULL,
  `PhuongThucThanhToan` varchar(50) COLLATE utf8mb4_vietnamese_ci DEFAULT NULL,
  `TongGiaTriDonHang` decimal(10,2) NOT NULL,
  `LyDoHuy` text COLLATE utf8mb4_vietnamese_ci,
  `SoTienCanThu_COD` decimal(10,2) DEFAULT '0.00',
  PRIMARY KEY (`ID_DonHang`),
  KEY `ID_NguoiMua` (`ID_NguoiMua`),
  KEY `ID_NguoiBan` (`ID_NguoiBan`),
  KEY `ID_NguoiGiaoHang` (`ID_NguoiGiaoHang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hinhanhsanpham`
--

DROP TABLE IF EXISTS `hinhanhsanpham`;
CREATE TABLE IF NOT EXISTS `hinhanhsanpham` (
  `ID_HinhAnh` int NOT NULL AUTO_INCREMENT,
  `ID_SanPham` int NOT NULL,
  `URL_HinhAnh` varchar(255) COLLATE utf8mb4_vietnamese_ci NOT NULL,
  PRIMARY KEY (`ID_HinhAnh`),
  KEY `ID_SanPham` (`ID_SanPham`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hosonguoigiaohang`
--

DROP TABLE IF EXISTS `hosonguoigiaohang`;
CREATE TABLE IF NOT EXISTS `hosonguoigiaohang` (
  `ID_NguoiDung` int NOT NULL,
  `GioiTinh` enum('Nam','Nu','Khac') COLLATE utf8mb4_vietnamese_ci DEFAULT NULL,
  `NamSinh` int DEFAULT NULL,
  `Anh_CCCD_Truoc` varchar(255) COLLATE utf8mb4_vietnamese_ci DEFAULT NULL,
  `Anh_CCCD_Sau` varchar(255) COLLATE utf8mb4_vietnamese_ci DEFAULT NULL,
  `Anh_BangLaiXe` varchar(255) COLLATE utf8mb4_vietnamese_ci DEFAULT NULL,
  `BienSoXe` varchar(20) COLLATE utf8mb4_vietnamese_ci DEFAULT NULL,
  `KhuVucHoatDong` varchar(100) COLLATE utf8mb4_vietnamese_ci DEFAULT NULL,
  `DiemDanhGiaTrungBinh` decimal(2,1) DEFAULT '5.0',
  PRIMARY KEY (`ID_NguoiDung`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Dumping data for table `hosonguoigiaohang`
--

INSERT INTO `hosonguoigiaohang` (`ID_NguoiDung`, `GioiTinh`, `NamSinh`, `Anh_CCCD_Truoc`, `Anh_CCCD_Sau`, `Anh_BangLaiXe`, `BienSoXe`, `KhuVucHoatDong`, `DiemDanhGiaTrungBinh`, `loaixe`) VALUES
(1, 'Nam', 2000, '6921333ded82b.jfif', '6921333ded9f0.jpg', '6921333dedb50.jpg', '77A-12345', 'Bình Định', 5.0, ''),
(4, 'Nam', 2004, '692133afdf8d8.jpg', '692133afdfa2e.jpg', '692133afdfb60.jfif', '77A-12342', 'Bình Định', 5.0, ''),
(5, 'Nam', 2003, '69213636209e0.jfif', '6921363620ba6.jpg', '6921363620d68.jfif', '77A-12344', 'Bình Định', 5.0, ''),
(7, 'Nam', 2001, '6922939dc1e30.jpg', '6922939dc200a.jpg', '6922939dc22db.jpg', '77A-63628', 'Tuy Hòa', 5.0, ''),
(8, 'Nam', 2001, '69229ffe44478.jpg', '69229ffe46fcc.jpg', '69229ffe4715f.jfif', '77A-123773', 'Đà Nẵng', 5.0, ''),
(9, 'Nam', 1990, '6922a50d71835.webp', '6922a50d719bc.webp', '6922a50d77fec.jfif', '77A-13345', 'Tp Hồ Chí Minh', 5.0, ''),
(10, '', 2005, '6922a7e868ea4.webp', '6922a7e86908a.jpg', '6922a7e86927c.jpg', '77A-12042', 'Bình Định', 5.0, ''),
(11, 'Nam', 1998, '6922a9ab7b470.webp', '6922a9ab7b604.webp', '6922a9ab7b8cd.jpg', '77A-13349', 'Bình Định', 5.0, '');

-- --------------------------------------------------------

--
-- Table structure for table `khieunai`
--

DROP TABLE IF EXISTS `khieunai`;
CREATE TABLE IF NOT EXISTS `khieunai` (
  `ID_KhieuNai` int NOT NULL AUTO_INCREMENT,
  `ID_DonHang` int NOT NULL,
  `ID_NguoiBaoCao` int NOT NULL,
  `LoaiSuCo` varchar(100) COLLATE utf8mb4_vietnamese_ci NOT NULL,
  `MoTaChiTiet` text COLLATE utf8mb4_vietnamese_ci,
  `PhanHoi_NguoiBan` text COLLATE utf8mb4_vietnamese_ci,
  `TrangThaiXuLy` enum('HoanTien','DoiSanPham','TuChoi','DangXuLy') COLLATE utf8mb4_vietnamese_ci DEFAULT 'DangXuLy',
  `NgayKhieuNai` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID_KhieuNai`),
  KEY `ID_DonHang` (`ID_DonHang`),
  KEY `ID_NguoiBaoCao` (`ID_NguoiBaoCao`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lichsutrangthai`
--

DROP TABLE IF EXISTS `lichsutrangthai`;
CREATE TABLE IF NOT EXISTS `lichsutrangthai` (
  `ID_LichSu` int NOT NULL AUTO_INCREMENT,
  `ID_DonHang` int NOT NULL,
  `TrangThaiMoi` varchar(50) COLLATE utf8mb4_vietnamese_ci NOT NULL,
  `ThoiGianCapNhat` datetime DEFAULT CURRENT_TIMESTAMP,
  `GhiChu` text COLLATE utf8mb4_vietnamese_ci,
  PRIMARY KEY (`ID_LichSu`),
  KEY `ID_DonHang` (`ID_DonHang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nguoidung`
--

DROP TABLE IF EXISTS `nguoidung`;
CREATE TABLE IF NOT EXISTS `nguoidung` (
  `ID_NguoiDung` int NOT NULL AUTO_INCREMENT,
  `HoTen` varchar(100) COLLATE utf8mb4_vietnamese_ci NOT NULL,
  `Email` varchar(100) COLLATE utf8mb4_vietnamese_ci NOT NULL,
  `SoDienThoai` varchar(15) COLLATE utf8mb4_vietnamese_ci NOT NULL,
  `MatKhau_Hash` varchar(255) COLLATE utf8mb4_vietnamese_ci NOT NULL,
  `VaiTro` enum('NguoiMua','NguoiBan','NguoiGiaoHang','QuanTriVien') COLLATE utf8mb4_vietnamese_ci NOT NULL,
  `TrangThaiHoatDong` tinyint(1) DEFAULT '1',
  `DiaChiGiaoHangMacDinh` text COLLATE utf8mb4_vietnamese_ci,
  `NgayTao` datetime DEFAULT CURRENT_TIMESTAMP,
  `TenDangNhap` varchar(50) COLLATE utf8mb4_vietnamese_ci DEFAULT NULL,
  PRIMARY KEY (`ID_NguoiDung`),
  UNIQUE KEY `Email` (`Email`),
  UNIQUE KEY `SoDienThoai` (`SoDienThoai`),
  UNIQUE KEY `TenDangNhap` (`TenDangNhap`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Dumping data for table `nguoidung`
--

INSERT INTO `nguoidung` (`ID_NguoiDung`, `HoTen`, `Email`, `SoDienThoai`, `AnhDaiDien`, `MatKhau_Hash`, `VaiTro`, `TrangThaiHoatDong`, `DiaChiGiaoHangMacDinh`, `NgayTao`, `TenDangNhap`) VALUES
(1, 'Nguyễn Văn 1', 'Van1@gmail.com', '02837772727', 'default_avatar.png', '$2y$10$nfWODBm5WYttj0yVqYaA3eCYfXEEunIlcDnLUB0joOKUNusiKIKCy', 'NguoiGiaoHang', 1, NULL, '2025-11-22 10:51:25', NULL),
(4, 'Nguyễn Văn 2', 'Van2@gmail.com', '028377723292', 'default_avatar.png', '$2y$10$DFrcqoVLTH/VXoNsWFm.h.AnyxkGmPfa5Y2WDufmko3w05Bq1WMDa', 'NguoiGiaoHang', 1, NULL, '2025-11-22 10:53:19', NULL),
(5, 'Nguyễn Văn 3', 'Van3@gmail.com', '09876543210', '5_avatar_1763789594.webp', '$2y$10$R2JLX1btF1CBsR59G9kGOu/rAoFMYs0jxsfr3ht.mfscJJ3foMkB2', 'NguoiGiaoHang', 1, '', '2025-11-22 11:04:06', NULL),
(7, 'Nguyễn văn Bảy', 'VanBay@gmail.com', '0955872712', '7_avatar_1764163742.jpg', '$2y$10$8Wo5Ub1Vlkq47Rda2rbS7.swbtMeDO7X3U0Z/inqVtJW8XqbHwLmK', 'NguoiGiaoHang', 1, '', '2025-11-23 11:54:53', NULL),
(8, 'Nguyen Văn Test', 'Test001@gmail.com', '1234567892', '8_avatar_1763877971.jpg', '$2y$10$8Me9EpIVJwqvarLel8UQ6.CVuyeo0pWN7YjQ6.cEZeSn5le6.m9HO', 'NguoiGiaoHang', 1, '', '2025-11-23 12:47:42', NULL),
(9, 'Nguyen Văn Test2', 'Van222@gmail.com', '1234567893', '9_avatar_1763878637.jpg', '$2y$10$YrohM93oM.LV9ri2sLJaWOmFW7CIZbkyh9q7H4wPr2UDPv.S.Ohnm', 'NguoiGiaoHang', 1, '', '2025-11-23 13:09:17', NULL),
(10, 'Nguyen Văn Test3', 'Test003@gmail.com', '1234567894', '6922a7e868d27.webp', '$2y$10$ZN9Vv3C2raxsDvmYgG2ABOQUHuOeQ68t/QimZyMKclSXVUaG.bAU6', 'NguoiGiaoHang', 1, NULL, '2025-11-23 13:21:28', NULL),
(11, 'Nguyễn Văn 2.0', 'Van2.0@gmail.com', '0987654322', '6922a9ab7b262.webp', '$2y$10$r0xeLVNBcJBUE2OPLTdHiOmTavJOPCn0L/UG/sEpVrcVwKOcsnmm6', 'NguoiGiaoHang', 1, NULL, '2025-11-23 13:28:59', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sanpham`
--

DROP TABLE IF EXISTS `sanpham`;
CREATE TABLE IF NOT EXISTS `sanpham` (
  `ID_SanPham` int NOT NULL AUTO_INCREMENT,
  `ID_NguoiBan` int NOT NULL,
  `TenSanPham` varchar(255) COLLATE utf8mb4_vietnamese_ci NOT NULL,
  `MoTa` text COLLATE utf8mb4_vietnamese_ci,
  `Gia` decimal(10,2) NOT NULL,
  `TinhTrang` enum('Moi','NhuMoi','Tot','TrungBinh','Kem') COLLATE utf8mb4_vietnamese_ci NOT NULL,
  `SoLuong` int DEFAULT '1',
  `DiaChiLayHang` text COLLATE utf8mb4_vietnamese_ci,
  `KichThuoc` varchar(50) COLLATE utf8mb4_vietnamese_ci DEFAULT NULL,
  `ID_DanhMuc` int DEFAULT NULL,
  `TrangThaiDangBan` enum('ChoDuyet','DangBan','DaBan','BiGoBo') COLLATE utf8mb4_vietnamese_ci DEFAULT 'ChoDuyet',
  `NgayTao` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID_SanPham`),
  KEY `ID_NguoiBan` (`ID_NguoiBan`),
  KEY `ID_DanhMuc` (`ID_DanhMuc`)
) ;

-- --------------------------------------------------------

--
-- Table structure for table `thongkedoanhthu`
--

DROP TABLE IF EXISTS `thongkedoanhthu`;
CREATE TABLE IF NOT EXISTS `thongkedoanhthu` (
  `ID_ThongKe` int NOT NULL AUTO_INCREMENT,
  `ID_NguoiBan` int NOT NULL,
  `LoaiThoiGian` enum('Ngay','Thang','Nam') COLLATE utf8mb4_vietnamese_ci NOT NULL,
  `GiaTriThoiGian` varchar(10) COLLATE utf8mb4_vietnamese_ci NOT NULL,
  `TongDoanhThu` decimal(10,2) DEFAULT '0.00',
  `TienThucNhan` decimal(10,2) DEFAULT '0.00',
  `GiaTriMat_HuyHoan` decimal(10,2) DEFAULT '0.00',
  PRIMARY KEY (`ID_ThongKe`),
  KEY `ID_NguoiBan` (`ID_NguoiBan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `chitietdonhang`
--
ALTER TABLE `chitietdonhang`
  ADD PRIMARY KEY (`ID_ChiTiet`),
  ADD KEY `ID_DonHang` (`ID_DonHang`),
  ADD KEY `ID_SanPham` (`ID_SanPham`);

--
-- Indexes for table `danhgia_nhanxet`
--
ALTER TABLE `danhgia_nhanxet`
  ADD PRIMARY KEY (`ID_DanhGia`),
  ADD UNIQUE KEY `ID_DonHang` (`ID_DonHang`),
  ADD KEY `ID_NguoiDanhGia` (`ID_NguoiDanhGia`),
  ADD KEY `ID_NguoiDuocDanhGia` (`ID_NguoiDuocDanhGia`);

--
-- Indexes for table `danhmuc`
--
ALTER TABLE `danhmuc`
  ADD PRIMARY KEY (`ID_DanhMuc`),
  ADD UNIQUE KEY `TenDanhMuc` (`TenDanhMuc`);

--
-- Indexes for table `danhsachdonhang`
--
ALTER TABLE `danhsachdonhang`
  ADD PRIMARY KEY (`ID_DonHang`),
  ADD KEY `ID_NguoiMua` (`ID_NguoiMua`),
  ADD KEY `ID_NguoiBan` (`ID_NguoiBan`),
  ADD KEY `ID_NguoiGiaoHang` (`ID_NguoiGiaoHang`);

--
-- Indexes for table `hinhanhsanpham`
--
ALTER TABLE `hinhanhsanpham`
  ADD PRIMARY KEY (`ID_HinhAnh`),
  ADD KEY `ID_SanPham` (`ID_SanPham`);

--
-- Indexes for table `hosonguoigiaohang`
--
ALTER TABLE `hosonguoigiaohang`
  ADD PRIMARY KEY (`ID_NguoiDung`);

--
-- Indexes for table `khieunai`
--
ALTER TABLE `khieunai`
  ADD PRIMARY KEY (`ID_KhieuNai`),
  ADD KEY `ID_DonHang` (`ID_DonHang`),
  ADD KEY `ID_NguoiBaoCao` (`ID_NguoiBaoCao`);

--
-- Indexes for table `lichsutrangthai`
--
ALTER TABLE `lichsutrangthai`
  ADD PRIMARY KEY (`ID_LichSu`),
  ADD KEY `ID_DonHang` (`ID_DonHang`);

--
-- Indexes for table `nguoidung`
--
ALTER TABLE `nguoidung`
  ADD PRIMARY KEY (`ID_NguoiDung`),
  ADD UNIQUE KEY `Email` (`Email`),
  ADD UNIQUE KEY `SoDienThoai` (`SoDienThoai`),
  ADD UNIQUE KEY `TenDangNhap` (`TenDangNhap`);

--
-- Indexes for table `sanpham`
--
ALTER TABLE `sanpham`
  ADD PRIMARY KEY (`ID_SanPham`),
  ADD KEY `ID_NguoiBan` (`ID_NguoiBan`),
  ADD KEY `ID_DanhMuc` (`ID_DanhMuc`);

--
-- Indexes for table `thongkedoanhthu`
--
ALTER TABLE `thongkedoanhthu`
  ADD PRIMARY KEY (`ID_ThongKe`),
  ADD KEY `ID_NguoiBan` (`ID_NguoiBan`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `chitietdonhang`
--
ALTER TABLE `chitietdonhang`
  MODIFY `ID_ChiTiet` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `danhgia_nhanxet`
--
ALTER TABLE `danhgia_nhanxet`
  MODIFY `ID_DanhGia` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `danhmuc`
--
ALTER TABLE `danhmuc`
  MODIFY `ID_DanhMuc` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `danhsachdonhang`
--
ALTER TABLE `danhsachdonhang`
  MODIFY `ID_DonHang` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hinhanhsanpham`
--
ALTER TABLE `hinhanhsanpham`
  MODIFY `ID_HinhAnh` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `khieunai`
--
ALTER TABLE `khieunai`
  MODIFY `ID_KhieuNai` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lichsutrangthai`
--
ALTER TABLE `lichsutrangthai`
  MODIFY `ID_LichSu` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `nguoidung`
--
ALTER TABLE `nguoidung`
  MODIFY `ID_NguoiDung` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `sanpham`
--
ALTER TABLE `sanpham`
  MODIFY `ID_SanPham` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `thongkedoanhthu`
--
ALTER TABLE `thongkedoanhthu`
  MODIFY `ID_ThongKe` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `chitietdonhang`
--
ALTER TABLE `chitietdonhang`
  ADD CONSTRAINT `chitietdonhang_ibfk_1` FOREIGN KEY (`ID_DonHang`) REFERENCES `danhsachdonhang` (`ID_DonHang`) ON DELETE CASCADE,
  ADD CONSTRAINT `chitietdonhang_ibfk_2` FOREIGN KEY (`ID_SanPham`) REFERENCES `sanpham` (`ID_SanPham`);

--
-- Constraints for table `danhgia_nhanxet`
--
ALTER TABLE `danhgia_nhanxet`
  ADD CONSTRAINT `danhgia_nhanxet_ibfk_1` FOREIGN KEY (`ID_DonHang`) REFERENCES `danhsachdonhang` (`ID_DonHang`) ON DELETE CASCADE,
  ADD CONSTRAINT `danhgia_nhanxet_ibfk_2` FOREIGN KEY (`ID_NguoiDanhGia`) REFERENCES `nguoidung` (`ID_NguoiDung`),
  ADD CONSTRAINT `danhgia_nhanxet_ibfk_3` FOREIGN KEY (`ID_NguoiDuocDanhGia`) REFERENCES `nguoidung` (`ID_NguoiDung`);

--
-- Constraints for table `danhsachdonhang`
--
ALTER TABLE `danhsachdonhang`
  ADD CONSTRAINT `danhsachdonhang_ibfk_1` FOREIGN KEY (`ID_NguoiMua`) REFERENCES `nguoidung` (`ID_NguoiDung`),
  ADD CONSTRAINT `danhsachdonhang_ibfk_2` FOREIGN KEY (`ID_NguoiBan`) REFERENCES `nguoidung` (`ID_NguoiDung`),
  ADD CONSTRAINT `danhsachdonhang_ibfk_3` FOREIGN KEY (`ID_NguoiGiaoHang`) REFERENCES `hosonguoigiaohang` (`ID_NguoiDung`) ON DELETE SET NULL;

--
-- Constraints for table `hinhanhsanpham`
--
ALTER TABLE `hinhanhsanpham`
  ADD CONSTRAINT `hinhanhsanpham_ibfk_1` FOREIGN KEY (`ID_SanPham`) REFERENCES `sanpham` (`ID_SanPham`) ON DELETE CASCADE;

--
-- Constraints for table `hosonguoigiaohang`
--
ALTER TABLE `hosonguoigiaohang`
  ADD CONSTRAINT `hosonguoigiaohang_ibfk_1` FOREIGN KEY (`ID_NguoiDung`) REFERENCES `nguoidung` (`ID_NguoiDung`) ON DELETE CASCADE;

--
-- Constraints for table `khieunai`
--
ALTER TABLE `khieunai`
  ADD CONSTRAINT `khieunai_ibfk_1` FOREIGN KEY (`ID_DonHang`) REFERENCES `danhsachdonhang` (`ID_DonHang`) ON DELETE CASCADE,
  ADD CONSTRAINT `khieunai_ibfk_2` FOREIGN KEY (`ID_NguoiBaoCao`) REFERENCES `nguoidung` (`ID_NguoiDung`);

--
-- Constraints for table `lichsutrangthai`
--
ALTER TABLE `lichsutrangthai`
  ADD CONSTRAINT `lichsutrangthai_ibfk_1` FOREIGN KEY (`ID_DonHang`) REFERENCES `danhsachdonhang` (`ID_DonHang`) ON DELETE CASCADE;

--
-- Constraints for table `sanpham`
--
ALTER TABLE `sanpham`
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
