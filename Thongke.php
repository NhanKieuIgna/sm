<?php

session_start(); 
require "sm/dp.php"; 


$nguoi_ban_id = 1; 

$filter = $_GET['filter'] ?? 'day'; 
$sql = "";
if ($filter === 'day') {
    $sql = "SELECT DATE(NgayDatHang) AS time, SUM(TongGiaTriDonHang) AS revenue, COUNT(*) AS orders
            FROM danhsachdonhang
            WHERE ID_NguoiBan = $nguoi_ban_id
            AND TrangThaiDonHang IN ('DaGiao', 'HoanThanh')
            AND DATE(NgayDatHang) = CURDATE()
            GROUP BY DATE(NgayDatHang)";
} 
elseif ($filter === 'week') {
    $sql = "SELECT YEARWEEK(NgayDatHang, 1) AS time, SUM(TongGiaTriDonHang) AS revenue, COUNT(*) AS orders
            FROM danhsachdonhang
            WHERE ID_NguoiBan = $nguoi_ban_id
            AND TrangThaiDonHang IN ('DaGiao', 'HoanThanh')
            AND YEARWEEK(NgayDatHang, 1) = YEARWEEK(NOW(), 1) 
            GROUP BY YEARWEEK(NgayDatHang, 1)";
} 
elseif ($filter === 'month') {
    $sql = "SELECT DATE_FORMAT(NgayDatHang, '%Y-%m') AS time, SUM(TongGiaTriDonHang) AS revenue, COUNT(*) AS orders
            FROM danhsachdonhang
            WHERE ID_NguoiBan = $nguoi_ban_id
            AND TrangThaiDonHang IN ('DaGiao', 'HoanThanh')
            AND MONTH(NgayDatHang) = MONTH(NOW())
            AND YEAR(NgayDatHang) = YEAR(NOW())
            GROUP BY DATE_FORMAT(NgayDatHang, '%Y-%m')";
}

$res = mysqli_query($conn, $sql);
$data = $res ? mysqli_fetch_assoc($res) : false;
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thống kê thu nhập</title>
    <style>
    body { font-family: Arial; background:#f5f5f5; padding:30px; }
    .box { background:white; padding:20px; width:350px; border-radius:10px;
            box-shadow:0 2px 10px rgba(0,0,0,.1); }
    h2 { margin-top:0; }
    .stat { font-size:22px; font-weight:bold; color:#007bff; }
    select, button { padding:7px 10px; margin-top:10px; }
    </style>
</head>

<style>
body { 
    font-family: "Segoe UI", sans-serif; 
    background: #f5f7fa; 
    display: flex;
    justify-content: center;
    align-items: flex-start; 
    padding: 50px 0;
    margin: 0; 
    min-height: 100vh; 
}


.box { 
    background: white; 
    padding: 30px; 
    width: 380px;
    border-radius: 14px; 
    box-shadow: 0 5px 20px rgba(0,0,0,0.1); 
    border: 1px solid #eee;
}

h2 { 
    margin-top: 0; 
    margin-bottom: 25px;
    color: #333;
    font-size: 24px;
    text-align: center;
}

/* Form Lọc */
.filter-form label {
    display: block;
    font-size: 14px;
    margin-bottom: 8px;
    color: #555;
}

select { 
    width: 180px;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 8px;
    margin-right: 10px;
    background-color: #fff;
    outline: none;
    transition: border-color 0.3s;
}

select:focus {
    border-color: #008cff;
}

.filter-form button { 
    padding: 10px 15px;
    background: #008cff;
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    cursor: pointer;
    transition: background 0.2s;
}

.filter-form button:hover {
    background: #006fd1;
}

hr {
    border: 0;
    border-top: 1px solid #eee;
    margin: 25px 0;
}


.result-stat p {
    margin-bottom: 12px;
    font-size: 16px;
    color: #444;
}

.result-stat b {
    display: inline-block;
    min-width: 90px;
}

.stat { 
    font-size: 26px; 
    font-weight: 700;
    color: #28a745; 
    display: block;
    margin-top: 5px;
}
.stat.orders {
    color: #ffc107;
}
.link-button {
    display: inline-block;
    padding: 10px 20px;
    background-color: #3b82f6;    
    color: white;
    font-weight: 600;
    border-radius: 10px;
    text-decoration: none;
    transition: 0.2s ease-in-out;
}

.link-button:hover {
    background-color: #2563eb;      
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
.back-btn {
    display: inline-block;
    padding: 8px 15px;
    padding-top: 1px;
    padding-bottom: 2px;
    background: #4697ddff;
    color: #fff;
    text-decoration: none;
    border-radius: 8px;
    margin-bottom: 15px;
}
.back-btn:hover {
    background: #005bb5;
}
</style>

<body>
<div class="box">
     
    <h2>📊 Thống kê thu nhập</h2>
    <a href="delivery_index.php" class="back-btn">Thoát</a>
    <form method="GET" class="filter-form"> <label>Chọn kiểu thống kê:</label><br>
        <select name="filter">
            <option value="day" <?= $filter=='day'?'selected':'' ?>>Theo ngày</option>
            <option value="week" <?= $filter=='week'?'selected':'' ?>>Theo tuần</option>
            <option value="month" <?= $filter=='month'?'selected':'' ?>>Theo tháng</option>
        </select>
        <button type="submit">Lọc</button>
    </form>

    <hr>

    <?php if ($data): ?>
    <div class="result-stat"> <p><b>Thời gian:</b> <?= htmlspecialchars($data['time']) ?></p>
        <p><b>Doanh thu:</b> 
            <span class="stat"><?= number_format($data['revenue'], 0, ',', '.') ?>₫</span>
        </p>
        <p><b>Số đơn:</b> 
            <span class="stat orders"><?= htmlspecialchars($data['orders']) ?></span>
        </p>
    </div>
    <?php else: ?>
        <p style="text-align:center; color:#6c757d;">Không có dữ liệu cho khoảng thời gian này!</p>
    <?php endif; ?>
</div>

</body>
</html>