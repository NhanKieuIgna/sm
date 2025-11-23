<?php
session_start();
include "sm/dp.php"; 
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$driverId = $_SESSION['user_id'];
$sql = "SELECT 
            dh.ID_DonHang AS MaDonHang,
            nd.HoTen AS TenNguoiNhan,
            dh.DiaChiGiaoHang AS DiaChiGiao,
            nd.SoDienThoai AS SoDienThoaiNhan,
            dh.TongGiaTriDonHang AS TongTien,
            dh.TrangThaiDonHang AS TrangThai,
            dh.NgayDatHang AS ThoiGianGiao
        FROM danhsachdonhang dh
        JOIN nguoidung nd ON dh.ID_NguoiMua = nd.ID_NguoiDung
        WHERE dh.ID_NguoiGiaoHang = ?
          AND dh.TrangThaiDonHang = 'DaGiao'
        ORDER BY dh.NgayDatHang DESC";

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
body {
    font-family: Arial, sans-serif;
    background: #f5f6fa;
    margin: 0;
    padding: 20px;
}
.container {
    max-width: 900px;
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
.table th {
    background: #008cff;
    color: #fff;
    padding: 10px;
}
.table td {
    padding: 10px;
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
            <th>SĐT</th>
            <th>Tổng tiền</th>
            <th>Trạng thái</th>
            <th>Ngày giao</th>
        </tr>

        <?php 
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['MaDonHang']}</td>
                        <td>{$row['TenNguoiNhan']}</td>
                        <td>{$row['DiaChiGiao']}</td>
                        <td>{$row['SoDienThoaiNhan']}</td>
                        <td>" . number_format($row['TongTien']) . " đ</td>
                        <td><span class='status status-ok'>Đã giao</span></td>
                        <td>{$row['ThoiGianGiao']}</td>
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
