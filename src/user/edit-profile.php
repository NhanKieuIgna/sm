<?php
session_start();
require_once __DIR__ . '/../../database/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Get current user data
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM nguoidung WHERE ID_NguoiDung = '$user_id'";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

if (!$user) {
    header('Location: ../login.php');
    exit();
}

// Check if user is a buyer and/or seller
if ($user['VaiTro'] !== 'NguoiMua' && $user['VaiTro'] !== 'NguoiBan') {
    header('Location: ../index.php');
    exit();
}

// Helper functions
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validatePhone($phone) {
    // Vietnamese phone number validation (10-11 digits, may start with 0 or +84)
    return preg_match('/^(\+84|0)[1-9][0-9]{8,9}$/', $phone);
}

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitizeInput($_POST['full_name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $address = sanitizeInput($_POST['address'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $change_password = isset($_POST['change_password']);
    
    $errors = [];
    
    // Validation
    if (empty($full_name)) {
        $errors[] = 'Họ tên không được để trống';
    }
    
    if (empty($email)) {
        $errors[] = 'Email không được để trống';
    } elseif (!validateEmail($email)) {
        $errors[] = 'Email không hợp lệ';
    }
    
    if (empty($phone)) {
        $errors[] = 'Số điện thoại không được để trống';
    } elseif (!validatePhone($phone)) {
        $errors[] = 'Số điện thoại không hợp lệ';
    }
    
    // Check if email or phone already exists (excluding current user)
    if (empty($errors)) {
        $email_escaped = mysqli_real_escape_string($conn, $email);
        $phone_escaped = mysqli_real_escape_string($conn, $phone);
        
        // Check email
        $query_email = "SELECT ID_NguoiDung FROM nguoidung WHERE Email = '$email_escaped' AND ID_NguoiDung != $user_id";
        $result_email = mysqli_query($conn, $query_email);
        if($result_email && mysqli_num_rows($result_email) > 0){
            $errors[] = 'Email đã được sử dụng bởi tài khoản khác';
        }
        
        // Check phone
        $query_phone = "SELECT ID_NguoiDung FROM nguoidung WHERE SoDienThoai = '$phone_escaped' AND ID_NguoiDung != $user_id";
        $result_phone = mysqli_query($conn, $query_phone);
        if($result_phone && mysqli_num_rows($result_phone) > 0){
            $errors[] = 'Số điện thoại đã được sử dụng bởi tài khoản khác';
        }
    }
    
    // Password validation (only if user wants to change password)
    if ($change_password) {
        if (empty($password)) {
            $errors[] = 'Mật khẩu mới không được để trống';
        } elseif (strlen($password) < 8) {
            $errors[] = 'Mật khẩu phải có ít nhất 8 ký tự';
        }
        
        if ($password !== $confirm_password) {
            $errors[] = 'Mật khẩu xác nhận không khớp';
        }
    }
    
    // Update database if no errors
    if (empty($errors)) {
        $full_name_escaped = mysqli_real_escape_string($conn, $full_name);
        $email_escaped = mysqli_real_escape_string($conn, $email);
        $phone_escaped = mysqli_real_escape_string($conn, $phone);
        $address_escaped = mysqli_real_escape_string($conn, $address);
        
        // Build update query
        $update_fields = "HoTen = '$full_name_escaped', Email = '$email_escaped', SoDienThoai = '$phone_escaped', DiaChiGiaoHangMacDinh = '$address_escaped'";
        
        // Add password update if user wants to change it
        if ($change_password && !empty($password)) {
            $password_escaped = mysqli_real_escape_string($conn, $password);
            $update_fields .= ", MatKhau_Hash = '$password_escaped'";
        }
        
        $update_query = "UPDATE nguoidung SET $update_fields WHERE ID_NguoiDung = $user_id";
        
        if (mysqli_query($conn, $update_query)) {
            // Update session
            $_SESSION['fullname'] = $full_name;
            
            // Reload user data
            $query = "SELECT * FROM nguoidung WHERE ID_NguoiDung = '$user_id'";
            $result = mysqli_query($conn, $query);
            $user = mysqli_fetch_assoc($result);
            
            $success = 'Cập nhật thông tin thành công!';
        } else {
            $errors[] = 'Có lỗi xảy ra khi cập nhật thông tin: ' . mysqli_error($conn);
        }
    }
    
    $error = implode('<br>', $errors);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa hồ sơ - SM</title>
    <link rel="stylesheet" href="../css/stylea.css">
    <link rel="stylesheet" href="../css/edit-profile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="edit-profile-page">
    <div class="edit-profile-container">
        <div class="edit-profile-card">
            <div class="edit-profile-header">
                <a href="../index.php" class="logo">
                    <span class="logo-text">SM</span>
                </a>
                <h1>Chỉnh sửa hồ sơ</h1>
                <p>Cập nhật thông tin cá nhân của bạn</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="edit-profile-form" data-validate>
                <div class="form-row">
                    <div class="form-group">
                        <label for="username" class="form-label">
                            <i class="fas fa-user"></i>
                            Tên đăng nhập
                        </label>
                        <input type="text" id="username" name="username" class="form-control" 
                               value="<?php echo htmlspecialchars($user['TenDangNhap'] ?? ''); ?>" 
                               disabled>
                        <small class="form-text" style="color: #666; font-size: 12px; margin-top: 5px;">Tên đăng nhập không thể thay đổi</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="full_name" class="form-label">
                            <i class="fas fa-id-card"></i>
                            Họ và tên
                        </label>
                        <input type="text" id="full_name" name="full_name" class="form-control" 
                               value="<?php echo htmlspecialchars($user['HoTen'] ?? ''); ?>" 
                               required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="email" class="form-label">
                            <i class="fas fa-envelope"></i>
                            Email
                        </label>
                        <input type="email" id="email" name="email" class="form-control" 
                               value="<?php echo htmlspecialchars($user['Email'] ?? ''); ?>" 
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone" class="form-label">
                            <i class="fas fa-phone"></i>
                            Số điện thoại
                        </label>
                        <input type="tel" id="phone" name="phone" class="form-control" 
                               value="<?php echo htmlspecialchars($user['SoDienThoai'] ?? ''); ?>" 
                               required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="address" class="form-label">
                        <i class="fas fa-home"></i>
                        Địa chỉ giao hàng mặc định
                    </label>
                    <textarea id="address" name="address" class="form-control" rows="3" 
                              placeholder="Địa chỉ cụ thể để nhận hàng..."><?php echo htmlspecialchars($user['DiaChiGiaoHangMacDinh'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="change_password" id="change_password" onchange="togglePasswordFields()">
                        <span class="checkmark"></span>
                        Thay đổi mật khẩu
                    </label>
                </div>
                
                <div id="password_fields" style="display: none;">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="password" class="form-label">
                                <i class="fas fa-lock"></i>
                                Mật khẩu mới
                            </label>
                            <div class="password-input">
                                <input type="password" id="password" name="password" class="form-control">
                                <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password" class="form-label">
                                <i class="fas fa-lock"></i>
                                Xác nhận mật khẩu mới
                            </label>
                            <div class="password-input">
                                <input type="password" id="confirm_password" name="confirm_password" class="form-control">
                                <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="form-row" style="margin-top: 10px;">
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-save"></i>
                        Lưu thay đổi
                    </button>
                    <a href="../index.php" class="btn" style="background: #6c757d; color: white; text-align: center;">
                        <i class="fas fa-times"></i>
                        Hủy
                    </a>
                </div>
            </form>
            
            <div class="edit-profile-footer">
                <p><a href="../index.php">← Quay lại trang chủ</a></p>
            </div>
        </div>
    </div>
    
    <script>
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const button = input.nextElementSibling;
            const icon = button.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
        
        function togglePasswordFields() {
            const checkbox = document.getElementById('change_password');
            const passwordFields = document.getElementById('password_fields');
            const passwordInput = document.getElementById('password');
            const confirmPasswordInput = document.getElementById('confirm_password');
            
            if (checkbox.checked) {
                passwordFields.style.display = 'block';
                passwordInput.required = true;
                confirmPasswordInput.required = true;
            } else {
                passwordFields.style.display = 'none';
                passwordInput.required = false;
                confirmPasswordInput.required = false;
                passwordInput.value = '';
                confirmPasswordInput.value = '';
            }
        }
    </script>
</body>
</html>

