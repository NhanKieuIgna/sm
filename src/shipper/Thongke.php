<?php

session_start(); 
require_once __DIR__ . '/../../database/db.php';
// if (!isset($_SESSION['user_id']) || $_SESSION['vaitro'] !== 'NguoiGiaoHang') {
//     header("Location: login.php"); 
//     exit();
// }

$driver_id = $_SESSION['user_id'];
$filter = $_GET['filter'] ?? 'day'; 
$sql = "";
$data = null;
$error = "";
$time_label = "";

// --- XỬ LÝ KHOẢNG THỜI GIAN ---
if ($filter === 'day') {
 
    $sql_time_condition = "AND DATE(ThoiGianHoanThanh) = CURDATE()";
    $select_time = "DATE_FORMAT(ThoiGianHoanThanh, '%d/%m/%Y') AS time";
    $time_label = "Hôm nay";
} elseif ($filter === 'week') {
  
    $sql_time_condition = "AND YEARWEEK(ThoiGianHoanThanh, 1) = YEARWEEK(NOW(), 1)"; 

    $select_time = "CONCAT('Tuần ', WEEK(ThoiGianHoanThanh, 1), ' (', YEAR(ThoiGianHoanThanh), ')') AS time";
    $time_label = "Tuần này";
} elseif ($filter === 'month') {
   
    $sql_time_condition = "AND MONTH(ThoiGianHoanThanh) = MONTH(NOW()) AND YEAR(ThoiGianHoanThanh) = YEAR(NOW())";
    $select_time = "DATE_FORMAT(ThoiGianHoanThanh, 'Tháng %m/%Y') AS time";
    $time_label = "Tháng này";
} else {
    $error = "Kiểu lọc không hợp lệ.";
}

if (empty($error)) {
    // --- CHỈNH SỬA TRUY VẤN SQL ---
    $sql = "SELECT 
                $select_time, 
                -- Thu nhập được tính bằng 5% tổng giá trị đơn hàng
                SUM(TongGiaTriDonHang * 0.05) AS total_fee_income, 
                COUNT(*) AS completed_orders,
                IFNULL(SUM(SoTienCanThu_COD), 0) AS total_cod_collected
            FROM danhsachdonhang
            WHERE ID_NguoiGiaoHang = $driver_id
            AND TrangThaiDonHang IN ('HoanThanh', 'DaGiao') 
            $sql_time_condition
            GROUP BY time";
    // --- KẾT THÚC CHỈNH SỬA ---

    $res = mysqli_query($conn, $sql);
    if ($res) {
        $data = mysqli_fetch_assoc($res);

        if (!$data) {
            // Cập nhật tên cột mặc định
            $data = ['total_fee_income' => 0, 'completed_orders' => 0, 'total_cod_collected' => 0, 'time' => $time_label];
        }
    } else {
        $error = "Lỗi truy vấn CSDL: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thống kê thu nhập - Tài xế</title>
    <style>
    /* CSS được tối ưu */
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
        width: 400px;
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
    .filter-form { margin-bottom: 20px; }
    .filter-form label {
        display: block;
        font-size: 14px;
        margin-bottom: 8px;
        color: #555;
        font-weight: 600;
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


    /* Thống kê */
    .result-stat p {
        margin-bottom: 15px;
        font-size: 16px;
        color: #444;
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 5px 0;
    }
    
    .result-stat strong {
        font-weight: 700;
    }

    .stat-value { 
        font-size: 22px; 
        font-weight: 700;
        display: block;
    }

    .income { color: #28a745; } 
    .orders { color: #ffc107; } 
    .cod { color: #007bff; } 
    
    .back-btn {
        display: inline-block;
        padding: 8px 15px;
        background: #4697dd;
        color: #fff;
        text-decoration: none;
        border-radius: 8px;
        margin-bottom: 15px;
        transition: background 0.2s;
    }
    .back-btn:hover {
        background: #005bb5;
    }
    .error-message {
        text-align: center; 
        color: #dc3545; 
        padding: 15px;
        border: 1px solid #f5c6cb;
        background-color: #f8d7da;
        border-radius: 8px;
    }
    </style>
</head>

<body>
<div class="box">
    <a href="delivery_index.php" class="back-btn">← Về trang chính</a>
    <h2>💰 Thống kê Thu nhập Giao hàng</h2>
    
    <?php if (!empty($error)): ?>
        <div class="error-message">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <form method="GET" class="filter-form"> 
        <label>Lọc theo khoảng thời gian:</label>
        <select name="filter">
            <option value="day" <?= $filter=='day'?'selected':'' ?>>Theo ngày</option>
            <option value="week" <?= $filter=='week'?'selected':'' ?>>Theo tuần</option>
            <option value="month" <?= $filter=='month'?'selected':'' ?>>Theo tháng</option>
        </select>
        <button type="submit">Lọc</button>
    </form>

    <hr>

    <?php if ($data): ?>
    <div class="result-stat"> 
        
        <p>
            <strong>Khoảng thời gian:</strong>
            <span style="font-weight:600;"><?= htmlspecialchars($data['time']) ?></span>
        </p>
        
        <p>
            <strong>Tổng thu nhập (Phí giao - 5% GTĐH):</strong>
            <span class="stat-value income">
                <?= number_format($data['total_fee_income'], 0, ',', '.') ?>₫
            </span>
        </p>
        
        <p>
            <strong>Tổng COD đã thu:</strong> 
            <span class="stat-value cod">
                <?= number_format($data['total_cod_collected'], 0, ',', '.') ?>₫
            </span>
        </p>

        <p>
            <strong>Số đơn hàng đã hoàn thành:</strong> 
            <span class="stat-value orders">
                <?= htmlspecialchars($data['completed_orders']) ?>
            </span>
        </p>


    </div>
    <?php else: ?>
        <p style="text-align:center; color:#6c757d;">Không có dữ liệu đơn hàng đã hoàn thành cho khoảng thời gian này!</p>
    <?php endif; ?>
</div>

</body>
</html>
