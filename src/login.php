<?php
session_start();
require_once __DIR__ . '/../database/db.php';

if(isset($_POST['login'])){
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $sql = "SELECT * FROM nguoidung WHERE TenDangNhap = '$username' AND MatKhau_Hash = '$password'";
    $res = mysqli_query($conn, $sql);
    $row = mysqli_fetch_array($res);
    
    if($row){
        // Kiểm tra tài khoản có bị khóa không
        if($row['TrangThaiHoatDong'] == 0){
            $error = 'Tài khoản của bạn đã bị khóa.';
        } else {
            $_SESSION['user_id'] = $row['ID_NguoiDung'];
            $_SESSION['username'] = $row['TenDangNhap'];
            $_SESSION['role'] = $row['VaiTro'];
            $_SESSION['fullname'] = $row['HoTen'];
             if($_SESSION['role'] == 'NguoiGiaoHang'){
                header('location: shipper/delivery_index.php');
            }elseif($_SESSION['role'] == 'QuanTriVien'){
                header('location: admin/user.php');
            } else {
                header('location: index.php');
            }
            exit();
        }
    } else {
        $error = 'Tên đăng nhập hoặc mật khẩu không chính xác';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - SM</title>
    <link rel="stylesheet" href="./css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Login specific styles */
        body {
            background-color: #f5f5f5;
        }

        .login-section {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
            position: relative;
            padding: 20px;
        }

        .login-container {
            position: relative;
            background: #fff;
            padding: 40px;
            border-radius: 8px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-header h2 {
            font-size: 28px;
            font-weight: bold;
            color: #2d8659;
            margin-bottom: 10px;
        }

        .login-header p {
            color: #666;
            font-size: 14px;
        }

        .login-form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .form-group {
            position: relative;
        }

        .form-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #2d8659;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border: 2px solid #2d8659;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: #3ba372;
        }

        .login-btn {
            background: #2d8659;
            color: #fff;
            padding: 12px;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            font-weight: 500;
        }

        .login-btn:hover {
            background: #3ba372;
        }

        .login-footer {
            text-align: center;
            margin-top: 20px;
        }

        .login-footer a {
            color: #2d8659;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s ease;
        }

        .login-footer a:hover {
            color: #3ba372;
            text-decoration: underline;
        }

        .login-footer span {
            color: #999;
        }

        .back-home {
            position: absolute;
            top: 20px;
            left: 20px;
            color: #2d8659;
            text-decoration: none;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 5px;
            z-index: 1;
            background: rgba(255, 255, 255, 0.9);
            padding: 8px 15px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .back-home:hover {
            background: #fff;
            color: #3ba372;
        }

        .error-message {
            color: #dc3545;
            text-align: center;
            margin-bottom: 15px;
            font-size: 14px;
            padding: 10px;
            background: #ffe6e6;
            border-radius: 5px;
        }

        @media (max-width: 768px) {
            .login-container {
                margin: 20px;
                padding: 30px;
            }

            .back-home {
                position: relative;
                top: auto;
                left: auto;
                margin-bottom: 20px;
                display: inline-flex;
            }
        }
    </style>
</head>
<body>
    <section class="login-section">
        <a href="index.php" class="back-home">
            <i class="fas fa-arrow-left"></i> Trở về trang chủ
        </a>
        <div class="login-container">
            <div class="login-header">
                <h2>Đăng nhập</h2>
                <p>Chào mừng quay trở lại</p>
            </div>
            
            <form class="login-form" action="" method="POST">
                <div class="form-group">
                    <i class="fas fa-user"></i>
                    <input type="text" class="form-control" name="username" placeholder="Tên đăng nhập" required>
                </div>
                <div class="form-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" class="form-control" name="password" placeholder="Mật khẩu" required>
                </div>
                <?php if(isset($error)): ?>
                    <div class="error-message"><?php echo $error; ?></div>
                <?php endif; ?>
                <button type="submit" name="login" class="login-btn">Đăng nhập</button>
            </form>
            <div class="login-footer">
                <a href="forgot_password.php">Đổi mật khẩu?</a>
                <span style="margin: 0 10px; opacity: 0.5;">|</span>
                <a href="register.php">Đăng ký tài khoản</a>
            </div>
        </div>
    </section>

   
</body>
</html>
