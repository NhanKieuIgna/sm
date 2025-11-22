<?php
// Tên file: hoso.php

session_start();
// Đảm bảo file dp.php của bạn định nghĩa biến kết nối là $conn (MySQLi object)
require "sm/dp.php"; 

// --- 1. KIỂM TRA ĐĂNG NHẬP VÀ XÁC ĐỊNH ID ---
if (!isset($_SESSION['user_id']) || $_SESSION['vaitro'] !== 'NguoiGiaoHang') {
    header("Location: login.php"); 
    exit();
}

$driver_id = $_SESSION['user_id'];
$user = [];
$orders_count = 0;
$revenue = '0₫';

// Giả định $conn là đối tượng MySQLi đã có từ dp.php
// (Nếu file dp.php dùng mysqli_connect, bạn cần chỉnh lại để dùng $conn thay vì $db_connection)
// Nếu lỗi "Undefined variable $conn" xảy ra, hãy kiểm tra lại file dp.php.


// --- 2. LẤY THÔNG TIN CÁ NHÂN (Sử dụng Prepared Statement để bảo mật) ---
$sql_user = "SELECT ID_NguoiDung, HoTen, Email, SoDienThoai, AnhDaiDien, TrangThaiHoatDong 
             FROM nguoidung 
             WHERE ID_NguoiDung = ?";

// Chuẩn bị statement
$stmt_user = mysqli_prepare($conn, $sql_user);
mysqli_stmt_bind_param($stmt_user, "i", $driver_id);
mysqli_stmt_execute($stmt_user);

$res_user = mysqli_stmt_get_result($stmt_user);

if ($res_user && mysqli_num_rows($res_user) > 0) {
    $user = mysqli_fetch_assoc($res_user);
    
    // Chuẩn hóa tên biến cho HTML
    $user['id'] = "G" . $user['ID_NguoiDung'];
    $user['name'] = htmlspecialchars($user['HoTen']);
    $user['email'] = htmlspecialchars($user['Email']);
    $user['phone'] = htmlspecialchars($user['SoDienThoai']);
    $user['avatar'] = htmlspecialchars($user['AnhDaiDien'] ?: 'default_avatar.png'); 
    $user['status'] = htmlspecialchars($user['TrangThaiHoatDong'] ?: 'Offline'); 
} else {
    // Trường hợp không tìm thấy người dùng
    header("Location: logout.php"); 
    exit();
}
mysqli_stmt_close($stmt_user);


// --- 3. LẤY THỐNG KÊ HIỆU SUẤT (Sử dụng Prepared Statement) ---
$sql_stats = "SELECT 
                COUNT(ID_DonHang) AS orders_count,
                SUM(PhiGiaoHang) AS total_delivery_fee
              FROM danhsachdonhang 
              WHERE ID_NguoiGiaoHang = ? 
              AND TrangThaiDonHang = 'DaGiao'";

$stmt_stats = mysqli_prepare($conn, $sql_stats);
mysqli_stmt_bind_param($stmt_stats, "i", $driver_id);
mysqli_stmt_execute($stmt_stats);

$res_stats = mysqli_stmt_get_result($stmt_stats);
$stats = mysqli_fetch_assoc($res_stats);

$orders_count = $stats['orders_count'] ?: 0;
$revenue = number_format($stats['total_delivery_fee'] ?: 0, 0, ',', '.') . '₫'; 

mysqli_stmt_close($stmt_stats);
?>

<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Hồ sơ cá nhân | <?php echo $user['name']; ?></title>
</head>
<style>
    /* ---------------------------------- */
    /* CSS TỔNG THỂ VÀ BỐ CỤC */
    /* ---------------------------------- */
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f0f2f5;
        margin: 0;
    }

    .profile-page {
        display: flex;
        min-height: 100vh;
    }

    /* Sidebar */
    .sidebar {
        width: 250px;
        background-color: #fff;
        padding: 20px;
        border-right: 1px solid #ddd;
    }
    
    .sidebar .brand {
        display: flex;
        align-items: center;
        margin-bottom: 25px;
        gap: 10px;
    }
    
    .sidebar .logo {
        font-size: 28px;
        font-weight: 700;
        color: #007bff;
    }

    .nav-item {
        display: block;
        padding: 10px 12px;
        margin-bottom: 8px;
        color: #333;
        text-decoration: none;
        border-radius: 8px;
        transition: all 0.3s;
    }

    .nav-item:hover, .nav-item.active {
        background-color: #e6f2ff;
        color: #007bff;
    }
    
    .nav-item.active {
        font-weight: 600;
    }

    /* Main Content */
    .main {
        flex: 1;
        padding: 30px;
    }

    /* PROFILE HEADER (Thông tin cơ bản) */
    .profile-header {
        display: flex;
        gap: 25px;
        background-color: #fff;
        padding: 25px;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        margin-bottom: 25px;
        align-items: center;
    }

    .profile-header .avatar img {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #007bff;
        padding: 3px;
    }

    .user-info h2 {
        margin: 0 0 8px 0;
        font-size: 24px;
        color: #333;
    }

    .user-info p {
        margin-bottom: 6px;
        color: #555;
    }

    .status {
        font-weight: 600;
    }

    /* Nút */
    a.btn {
        display: inline-block;
        padding: 8px 16px;
        background-color: #007bff;
        color: #fff;
        text-decoration: none;
        border-radius: 6px;
        font-weight: 600;
        transition: background 0.3s;
        margin-top: 10px;
        font-size: 14px;
    }

    a.btn:hover {
        background-color: #0056b3;
    }

    /* PROFILE STATS (Thống kê hiệu suất) */
    .profile-stats {
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
    }

    .profile-stats .stat {
        flex: 1;
        min-width: 180px;
        background-color: #fff;
        padding: 20px;
        border-radius: 12px;
        text-align: center;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }

    .profile-stats .label {
        color: #666;
        margin-bottom: 8px;
        font-size: 14px;
    }

    .profile-stats .value {
        font-weight: 700;
        font-size: 24px;
    }

    /* RESPONSIVE */
    @media (max-width: 768px) {
        .profile-page {
            flex-direction: column;
        }
        .sidebar {
            width: 100%;
            border-right: none;
            border-bottom: 1px solid #ddd;
        }
        .profile-header {
            flex-direction: column;
            align-items: center;
            text-align: center;
        }
        .profile-stats {
            flex-direction: column;
        }
        .profile-stats .stat {
            min-width: unset;
        }
    }
</style>
<body>

<div class="profile-page">
    <aside class="sidebar">
        <div class="brand">
            <div class="logo">SM</div>
            <div>
                <div style="font-weight:700">DriverDash</div>
                <div class="small">Phiên bản giao hàng</div>
            </div>
        </div>
        <nav>
            <a class="nav-item" href="delivery_index.php">🏠 Tổng quan</a>
            <a class="nav-item" href="my_donhang.php">📦 Đơn hàng</a>
            <a class="nav-item" href="Thongke.php">💰 Thu nhập</a>
            <a class="nav-item" href="ls_giaohang.php">📜 Lịch sử</a>
            <hr style="margin: 15px 0; border: 0; border-top: 1px solid #eee;"> 
            <a class="nav-item" href="exit.php" style="color: #dc3545; font-weight: 600;">
                ➡️ Đăng xuất
            </a>
        </nav>
    </aside>

    <main class="main">
        <div class="profile-header">
            <div class="avatar">
                <img src="uploads/avatars/<?php echo $user['avatar']; ?>" alt="<?php echo $user['name']; ?>">
            </div>
            <div class="user-info">
                <h2><?php echo $user['name']; ?></h2>
                <?php 
                    // Logic màu sắc trạng thái
                    $status_color = ($user['status'] == 'Sẵn sàng') ? '#28a745' : '#ffc107'; 
                ?>
                <p>ID: <?php echo $user['id']; ?> • 
                    <span class="status" style="color:<?php echo $status_color; ?>">
                        <?php echo $user['status']; ?>
                    </span>
                </p>
                <p>Email: <?php echo $user['email']; ?></p>
                <p>Phone: <?php echo $user['phone']; ?></p>
                <a href="profile.php" class="btn">Chỉnh sửa hồ sơ</a>
            </div>
        </div>
        
        <div class="profile-stats">
            <div class="stat">
                <div class="label">Tổng đơn đã giao</div>
                <div class="value" style="color:#007bff;"><?php echo $orders_count; ?></div>
            </div>
            <div class="stat">
                <div class="label">Thu nhập (Phí giao hàng)</div>
                <div class="value" style="color:#28a745;"><?php echo $revenue; ?></div>
            </div>
            <div class="stat">
                <div class="label">Đánh giá (Tạm thời)</div>
                <div class="value" style="color:#ffc107;">4.9/5 ⭐</div>
            </div>
        </div>
        
    </main>
</div>

</body>
</html>