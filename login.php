<?php
session_start();
require 'sm/dp.php'; 

if (isset($_SESSION['user_id'])) {
  
    header("Location: delivery_index.php"); 
    exit();
}

$error = '';

if (isset($_POST['submit'])) { 

    $taikhoan_nhap = $_POST['username']; 
    $password_nhap = $_POST['password'];
    $sql = "SELECT ID_NguoiDung, MatKhau_Hash, VaiTro FROM nguoidung 
            WHERE Email = ? OR SoDienThoai = ? OR TenDangNhap = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("sss", $taikhoan_nhap, $taikhoan_nhap, $taikhoan_nhap);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows == 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password_nhap, $user['MatKhau_Hash'])) {
                $_SESSION['user_id'] = $user['ID_NguoiDung'];
                $_SESSION['vaitro'] = $user['VaiTro'];
                if ($user['VaiTro'] === 'NguoiGiaoHang') {
                    header('location: delivery_index.php');
                } else if ($user['VaiTro'] === 'QuanTriVien') {
                    header('location: admin/dashboard.php');
                } else {
                    // Mặc định cho NguoiMua/NguoiBan
                    header('location: index.html');
                }
                exit();
            } else {
                $error = 'Mật khẩu không chính xác.';
            }
        } else {
            $error = 'Tài khoản (Email/SĐT/Tên đăng nhập) không tồn tại.';
        }
        $stmt->close();
    } else {
        $error = 'Lỗi hệ thống trong quá trình chuẩn bị truy vấn.';
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
 <meta charset="UTF-8">
 <meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Đăng nhập</title>
 <link rel="stylesheet" href="login.css">
</head>
<body>
 <div class="login-container">

 <h2>Đăng nhập</h2>
 <form action="" method="post">
<div class="input-box">
<label for="username">Tài khoản (Email/SĐT)</label>
 <input type="text" id="username" name="username" placeholder="Nhập Email hoặc SĐT" required>
</div>

<div class="input-box">
<label for="password">Mật khẩu</label>
 <input type="password" id="password" name="password" placeholder="Nhập mật khẩu" required>
</div>

 <button type="submit" name="submit" class="btn">Đăng nhập</button>

 <?php if (!empty($error)) : ?>
 <p style="color:red; text-align:center;"><?= $error ?></p>
 <?php endif; ?>
 <p class="signup-text">Chưa có tài khoản? <a href="dangky_deli.php">Đăng ký</a></p>
</form>
 </div>
</body>
</html>