<?php
session_start();
require_once __DIR__ . '/../database/db.php';

// If already logged in, redirect to home
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$error = '';
$success = '';

if(isset($_POST['submit_reset'])){
    $email_or_username = trim($_POST['email_or_username']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);
    
    // Validation
    if(empty($email_or_username)){
        $error = 'Vui lòng nhập email hoặc tên đăng nhập';
    } else if(empty($new_password) || empty($confirm_password)){
        $error = 'Vui lòng nhập đầy đủ mật khẩu mới';
    } else if(strlen($new_password) < 6){
        $error = 'Mật khẩu phải có ít nhất 6 ký tự';
    } else if($new_password !== $confirm_password){
        $error = 'Mật khẩu xác nhận không khớp';
    } else {
        // Check if user exists
        $email_or_username_escaped = mysqli_real_escape_string($conn, $email_or_username);
        $sql = "SELECT * FROM nguoidung WHERE Email = '$email_or_username_escaped' OR TenDangNhap = '$email_or_username_escaped'";
        $result = mysqli_query($conn, $sql);
        
        if($result && mysqli_num_rows($result) > 0){
            $user = mysqli_fetch_assoc($result);
            $user_id = $user['ID_NguoiDung'];
            
            // Update password directly
            $password_escaped = mysqli_real_escape_string($conn, $new_password);
            $update_sql = "UPDATE nguoidung SET MatKhau_Hash = '$password_escaped' WHERE ID_NguoiDung = $user_id";
            
            if(mysqli_query($conn, $update_sql)){
                $success = 'Đổi mật khẩu thành công! Đang chuyển đến trang đăng nhập...';
                // Auto redirect after 3 seconds
                header("refresh:3;url=login.php");
            } else {
                $error = 'Có lỗi xảy ra, vui lòng thử lại sau';
            }
        } else {
            $error = 'Email hoặc tên đăng nhập không tồn tại trong hệ thống';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đổi mật khẩu - SM</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Forgot Password CSS - Matching Index/Login Page Style */

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .login-container {
            background: white;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            width: 100%;
            max-width: 420px;
            animation: fadeInUp 0.5s ease;
            position: relative;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .back-link {
            position: absolute;
            top: 15px;
            left: 15px;
            color: #2d8659;
            text-decoration: none;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: color 0.3s ease;
        }

        .back-link:hover {
            color: #3ba372;
        }

        .back-link i {
            font-size: 12px;
        }

        .login-container h2 {
            text-align: center;
            color: #2d8659;
            margin-bottom: 10px;
            margin-top: 15px;
            font-size: 28px;
            font-weight: 700;
        }

        .login-container p.description {
            text-align: center;
            color: #666;
            font-size: 14px;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .input-box {
            margin-bottom: 20px;
            position: relative;
        }

        .input-box label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 600;
            font-size: 14px;
        }

        .input-box input {
            width: 100%;
            padding: 12px 45px 12px 16px;
            border: 2px solid #2d8659;
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.3s ease;
            outline: none;
        }

        .input-box input:focus {
            border-color: #3ba372;
            box-shadow: 0 0 0 3px rgba(45, 134, 89, 0.1);
        }

        .input-box input::placeholder {
            color: #aaa;
        }

        .toggle-password {
            position: absolute;
            right: 15px;
            top: 43px;
            cursor: pointer;
            color: #999;
            transition: color 0.3s ease;
        }

        .toggle-password:hover {
            color: #2d8659;
        }

        .password-strength {
            margin-top: 5px;
            font-size: 12px;
        }

        .strength-bar {
            height: 4px;
            background: #e0e0e0;
            border-radius: 2px;
            margin-top: 5px;
            overflow: hidden;
        }

        .strength-bar-fill {
            height: 100%;
            width: 0%;
            transition: width 0.3s ease, background-color 0.3s ease;
        }

        .strength-weak { background-color: #dc3545; width: 33%; }
        .strength-medium { background-color: #ffc107; width: 66%; }
        .strength-strong { background-color: #28a745; width: 100%; }

        .btn {
            width: 100%;
            padding: 14px;
            background: #2d8659;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.3s ease, background-color 0.3s ease;
            margin-top: 10px;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(45, 134, 89, 0.4);
            background: #3ba372;
        }

        .btn:active {
            transform: translateY(0);
        }

        .signup-text {
            text-align: center;
            margin-top: 20px;
            color: #666;
            font-size: 14px;
        }

        .signup-text a {
            color: #2d8659;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .signup-text a:hover {
            color: #3ba372;
            text-decoration: underline;
        }

        /* Error message styling */
        .error-message {
            background-color: #ffe6e6;
            border: 1px solid #fcc;
            border-radius: 6px;
            padding: 10px;
            margin: 15px 0;
            color: #c33;
            font-size: 14px;
            text-align: center;
        }

        /* Success message styling */
        .success-message {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            border-radius: 6px;
            padding: 10px;
            margin: 15px 0;
            color: #155724;
            font-size: 14px;
            text-align: center;
        }

        .requirements {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .requirements h4 {
            font-size: 13px;
            color: #555;
            margin-bottom: 10px;
        }

        .requirements ul {
            list-style: none;
            padding: 0;
        }

        .requirements li {
            font-size: 12px;
            color: #666;
            padding: 3px 0;
            padding-left: 20px;
            position: relative;
        }

        .requirements li:before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #28a745;
        }

        /* Responsive Design */
        @media (max-width: 480px) {
            .login-container {
                padding: 30px 20px;
            }

            .login-container h2 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <a href="login.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Quay lại đăng nhập
        </a>
        
        <h2>Đổi mật khẩu</h2>
        <p class="description">Nhập email hoặc tên đăng nhập và mật khẩu mới</p>
        
        <form action="" method="POST">
            <div class="input-box">
                <label for="email_or_username">Email hoặc Tên đăng nhập</label>
                <input type="text" id="email_or_username" name="email_or_username" placeholder="Nhập email hoặc tên đăng nhập" required>
            </div>

            <div class="requirements">
                <h4>Yêu cầu mật khẩu mới:</h4>
                <ul>
                    <li>Ít nhất 6 ký tự</li>
                    <li>Nên bao gồm chữ và số</li>
                    <li>Hai mật khẩu phải khớp nhau</li>
                </ul>
            </div>

            <div class="input-box">
                <label for="new_password">Mật khẩu mới</label>
                <input type="password" id="new_password" name="new_password" placeholder="Nhập mật khẩu mới" required>
                <i class="fas fa-eye toggle-password" onclick="togglePassword('new_password')"></i>
                <div class="password-strength">
                    <div class="strength-bar">
                        <div class="strength-bar-fill" id="strength-fill"></div>
                    </div>
                    <span id="strength-text"></span>
                </div>
            </div>

            <div class="input-box">
                <label for="confirm_password">Xác nhận mật khẩu</label>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Nhập lại mật khẩu mới" required>
                <i class="fas fa-eye toggle-password" onclick="togglePassword('confirm_password')"></i>
            </div>

            <?php if (!empty($error)): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="success-message"><?php echo $success; ?></div>
            <?php endif; ?>

            <button type="submit" name="submit_reset" class="btn">Đổi mật khẩu</button>

            <p class="signup-text">
                Đã nhớ mật khẩu? <a href="login.php">Đăng nhập</a>
                <span style="margin: 0 5px;">|</span>
                <a href="register.php">Đăng ký</a>
            </p>
        </form>
    </div>

    <script>
        // Toggle password visibility
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = field.nextElementSibling;
            
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Password strength indicator
        // document.getElementById('new_password').addEventListener('input', function() {
        //     const password = this.value;
        //     const strengthFill = document.getElementById('strength-fill');
        //     const strengthText = document.getElementById('strength-text');
            
        //     let strength = 0;
        //     if (password.length >= 6) strength++;
        //     if (password.length >= 10) strength++;
        //     if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
        //     if (/[0-9]/.test(password)) strength++;
        //     if (/[^a-zA-Z0-9]/.test(password)) strength++;
            
        //     strengthFill.className = 'strength-bar-fill';
            
        //     if (password.length === 0) {
        //         strengthFill.style.width = '0%';
        //         strengthText.textContent = '';
        //     } else if (strength <= 2) {
        //         strengthFill.classList.add('strength-weak');
        //         strengthText.textContent = 'Yếu';
        //         strengthText.style.color = '#dc3545';
        //     } else if (strength <= 3) {
        //         strengthFill.classList.add('strength-medium');
        //         strengthText.textContent = 'Trung bình';
        //         strengthText.style.color = '#ffc107';
        //     } else {
        //         strengthFill.classList.add('strength-strong');
        //         strengthText.textContent = 'Mạnh';
        //         strengthText.style.color = '#28a745';
        //     }
        // });
    </script>
</body>
</html>