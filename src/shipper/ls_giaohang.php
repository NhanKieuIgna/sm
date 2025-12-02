<?php
session_start();
require_once __DIR__ . '/../../database/db.php';
// if (!isset($_SESSION['user_id'])) {
//     header("Location: login.php");
//     exit;
// }

$driverId = $_SESSION['user_id'];
// Câu truy vấn SQL giữ nguyên
$sql = "SELECT 
            dh.ID_DonHang AS MaDonHang,
            nd.HoTen AS TenNguoiNhan,
            dh.DiaChiGiaoHang AS DiaChiGiao,
            dh.TongGiaTriDonHang AS TongTien,
            dh.TrangThaiDonHang AS TrangThai,
            dh.ThoiGianHoanThanh AS ThoiGianHoanThanh, 
            GROUP_CONCAT(CONCAT(sp.TenSanPham, ' (x', ctdh.SoLuongMua, ')') SEPARATOR ', ') AS ChiTietSanPham 
        FROM danhsachdonhang dh
        JOIN nguoidung nd ON dh.ID_NguoiMua = nd.ID_NguoiDung
        JOIN chitietdonhang ctdh ON dh.ID_DonHang = ctdh.ID_DonHang
        JOIN sanpham sp ON ctdh.ID_SanPham = sp.ID_SanPham
        WHERE dh.ID_NguoiGiaoHang = ?
          AND dh.TrangThaiDonHang IN ('DaGiao', 'DaHuy')
        GROUP BY dh.ID_DonHang, nd.HoTen, dh.DiaChiGiaoHang, dh.TongGiaTriDonHang, dh.TrangThaiDonHang, dh.ThoiGianHoanThanh
        ORDER BY dh.ThoiGianHoanThanh DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $driverId);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Lịch sử giao hàng</title>
<style>
/* ... (CSS giữ nguyên) ... */
body {
    font-family: Arial, sans-serif;
    background: #f5f6fa;
    margin: 0;
    padding: 20px;
}
.container {
    max-width: 1200px;
    margin: auto;
    background: #fff;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0px 2px 10px rgba(0,0,0,0.1);
}
h2 {
    text-align: center;
    margin-bottom: 20px;
    color: #333;
}
.table {
    width: 100%;
    border-collapse: collapse;
}
.table th, .table td {
    padding: 10px;
    text-align: left;
}
.table th {
    background: #008cff;
    color: #fff;
}
.table td {
    border-bottom: 1px solid #ddd;
}
.table tr:hover {
    background: #f0f6ff;
}
.status {
    padding: 4px 8px;
    border-radius: 6px;
    font-weight: bold;
    color: white;
}
.status-ok {
    background: #28a745;
}
.back-btn {
    display: inline-block;
    padding: 10px 15px;
    background: #006fd1;
    color: #fff;
    text-decoration: none;
    border-radius: 8px;
    margin-bottom: 15px;
}
.back-btn:hover {
    background: #005bb5;
}
</style>
</head>
<body>

<div class="container">
    <a href="delivery_index.php" class="back-btn">⬅ Quay lại</a>

    <h2>📜 Lịch sử giao hàng</h2>

    <table class="table">
        <tr>
            <th>Mã đơn</th>
            <th>Người nhận</th>
            <th>Địa chỉ</th>
            <th>Sản phẩm</th> 
            <th>Tổng tiền</th>
            <th>Trạng thái</th>
            <th>Thời gian hoàn thành</th> 
        </tr>

        <?php 
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                
                // *** BƯỚC MỚI: TÍNH PHÍ GIAO HÀNG BẰNG 5% TỔNG GIÁ TRỊ ***
                $tongTien = (float)$row['TongTien'];
                $phiShipMoi = $tongTien * 0.05;
                // Gán lại vào biến row để hiển thị
                
                // Định dạng lại trường thời gian
                $completionTime = $row['ThoiGianHoanThanh'] ? date("d/m/Y H:i", strtotime($row['ThoiGianHoanThanh'])) : 'Chưa hoàn thành';

                echo "<tr>
                        <td>{$row['MaDonHang']}</td>
                        <td>{$row['TenNguoiNhan']}</td>
                        <td>{$row['DiaChiGiao']}</td>
                        <td>{$row['ChiTietSanPham']}</td> 
                        <td>" . number_format($row['TongTien']) . " đ</td>
                        <td>{$row['TrangThai']}</td>
                        <td>{$completionTime}</td> 
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='7' style='text-align:center;'>Không có đơn hàng nào đã giao.</td></tr>";
        }
        ?>
    </table>
</div>

</body>
</html>