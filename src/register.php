<?php
session_start();
require_once __DIR__ . '/../database/db.php';

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

function uploadFile($file, $uploadDir, $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'], $maxSize = 5242880) {
    // $maxSize = 5MB in bytes
    $errors = [];
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        if ($file['error'] === UPLOAD_ERR_NO_FILE) {
            return ['success' => false, 'error' => 'Vui lòng chọn file'];
        }
        return ['success' => false, 'error' => 'Lỗi khi upload file'];
    }
    
    // Check file size
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'error' => 'File quá lớn. Kích thước tối đa là 5MB'];
    }
    
    // Check file type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $allowedTypes)) {
        return ['success' => false, 'error' => 'Chỉ chấp nhận file ảnh định dạng JPG, JPEG hoặc PNG'];
    }
    
    // Create upload directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('shipper_', true) . '_' . time() . '.' . $extension;
    $filepath = $uploadDir . '/' . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => true, 'filename' => $filename, 'filepath' => $filepath];
    } else {
        return ['success' => false, 'error' => 'Không thể lưu file'];
    }
}

// Redirect if already logged in
// if (isset($_SESSION['user_id'])) {
//     header('Location: index.php');
//     exit();
// }

$error = '';
$success = '';
$user_type = $_GET['type'] ?? 'buyer';

// Validate user type
if (!in_array($user_type, ['buyer', 'seller', 'shipper'])) {
    $user_type = 'buyer';
}

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username'] ?? ''); 
    $role = NULL;
    if($user_type == 'buyer'){
        $role = "NguoiMua";
    }
    if($user_type == 'seller'){
        $role = "NguoiBan";
    }
    if($user_type == 'shipper'){
        $role = "NguoiGiaoHang";
    }
    
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $full_name = sanitizeInput($_POST['full_name'] ?? '');
    $address = sanitizeInput($_POST['address'] ?? '');
    $location = sanitizeInput($_POST['location'] ?? '');
    $agreed = isset($_POST['agreed']);
    
    // Validation
    $errors = [];
    
    // Additional fields for shippers
    $vehicle_number = ''; 
    $vehicle_type = '';
    $working_area = '';
    $id_card_front = '';
    $id_card_back = '';
    $driver_license = '';
    $avatar_path = '';
    $id_card_front_path = '';
    $id_card_back_path = '';
    $driver_license_path = '';
    $gender = '';
    $birth_year = '';
    
    if ($user_type === 'shipper') {
        $vehicle_number = sanitizeInput($_POST['vehicle_number'] ?? '');
        $vehicle_type = sanitizeInput($_POST['vehicle_type'] ?? '');
        $working_area = sanitizeInput($_POST['working_area'] ?? '');
        $gender = sanitizeInput($_POST['gender'] ?? '');
        $birth_year = sanitizeInput($_POST['birth_year'] ?? '');
        
        // Handle file uploads
        $uploadDir = __DIR__ . '/uploads/shipper_documents';
        
        // Upload ID card front
        if (isset($_FILES['id_card_front']) && $_FILES['id_card_front']['error'] !== UPLOAD_ERR_NO_FILE) {
            $uploadResult = uploadFile($_FILES['id_card_front'], $uploadDir);
            if ($uploadResult['success']) {
                $id_card_front_path = $uploadResult['filename'];
            } else {
                $errors[] = 'Ảnh CMND mặt trước: ' . $uploadResult['error'];
            }
        }
        
        // Upload ID card back
        if (isset($_FILES['id_card_back']) && $_FILES['id_card_back']['error'] !== UPLOAD_ERR_NO_FILE) {
            $uploadResult = uploadFile($_FILES['id_card_back'], $uploadDir);
            if ($uploadResult['success']) {
                $id_card_back_path = $uploadResult['filename'];
            } else {
                $errors[] = 'Ảnh CMND mặt sau: ' . $uploadResult['error'];
            }
        }
        
        // Upload driver license
        if (isset($_FILES['driver_license']) && $_FILES['driver_license']['error'] !== UPLOAD_ERR_NO_FILE) {
            $uploadResult = uploadFile($_FILES['driver_license'], $uploadDir);
            if ($uploadResult['success']) {
                $driver_license_path = $uploadResult['filename'];
            } else {
                $errors[] = 'Ảnh bằng lái xe: ' . $uploadResult['error'];
            }
        }

        // Upload avatar
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
            $uploadResult = uploadFile($_FILES['avatar'], $uploadDir);
            if ($uploadResult['success']) {
                $avatar_path = $uploadResult['filename'];
            } else {
                $errors[] = 'Ảnh đại diện: ' . $uploadResult['error'];
            }
        }
    }
    
    if (empty($username)) {
        $errors[] = 'Tên đăng nhập không được để trống';
    } elseif (strlen($username) < 3) {
        $errors[] = 'Tên đăng nhập phải có ít nhất 3 ký tự';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors[] = 'Tên đăng nhập chỉ được chứa chữ cái, số và dấu gạch dưới';
    }
    
    if (empty($email)) {
        $errors[] = 'Email không được để trống';
    } elseif (!validateEmail($email)) {
        $errors[] = 'Email không hợp lệ';
    }
    
    if (empty($password)) {
        $errors[] = 'Mật khẩu không được để trống';
    } elseif (strlen($password) < 8) {
        $errors[] = 'Mật khẩu phải có ít nhất 8 ký tự';
    }
    
    if ($password !== $confirm_password) {
        $errors[] = 'Mật khẩu xác nhận không khớp';
    }
    
    if (empty($phone)) {
        $errors[] = 'Số điện thoại không được để trống';
    } elseif (!validatePhone($phone)) {
        $errors[] = 'Số điện thoại không hợp lệ';
    }
    
    if (empty($full_name)) {
        $errors[] = 'Họ tên không được để trống';
    }
    
    if (empty($location)) {
        $errors[] = 'Địa điểm không được để trống';
    }
    
    if (!$agreed) {
        $errors[] = 'Bạn phải đồng ý với điều khoản sử dụng';
    }
    
    // Check if username, email or phone already exists
    if (empty($errors)) {
        $username_escaped = mysqli_real_escape_string($conn, $username);
        $email_escaped = mysqli_real_escape_string($conn, $email);
        $phone_escaped = mysqli_real_escape_string($conn, $phone);
        // Check username
        $query_username = "SELECT ID_NguoiDung FROM nguoidung WHERE TenDangNhap = '$username_escaped'";
        $result_username = mysqli_query($conn, $query_username);
        if($result_username && mysqli_num_rows($result_username) > 0){
            $errors[] = 'Tên đăng nhập đã tồn tại';
        }
        
        // Check email
        $query_email = "SELECT ID_NguoiDung FROM nguoidung WHERE Email = '$email_escaped'";
        $result_email = mysqli_query($conn, $query_email);
        if($result_email && mysqli_num_rows($result_email) > 0){
            $errors[] = 'Email đã tồn tại';
        }
        
        // Check phone
        $query_phone = "SELECT ID_NguoiDung FROM nguoidung WHERE SoDienThoai = '$phone_escaped'";
        $result_phone = mysqli_query($conn, $query_phone);
        if($result_phone && mysqli_num_rows($result_phone) > 0){
            $errors[] = 'Số điện thoại đã tồn tại';
        }
    }
    
    // Validate shipper-specific fields
    if ($user_type === 'shipper') {
        if (empty($vehicle_number)) {
            $errors[] = 'Biển số xe không được để trống';
        }
        if (empty($vehicle_type)) {
            $errors[] = 'Loại phương tiện không được để trống';
        }
        if (empty($working_area)) {
            $errors[] = 'Khu vực hoạt động không được để trống';
        }
        if (empty($gender)) {
            $errors[] = 'Giới tính không được để trống';
        }
        if (empty($birth_year)) {
            $errors[] = 'Năm sinh không được để trống';
        } elseif (!is_numeric($birth_year) || strlen($birth_year) !== 4) {
            $errors[] = 'Năm sinh phải là 4 chữ số';
        } elseif ((int)$birth_year < 1950 || (int)$birth_year > date('Y')) {
            $errors[] = 'Năm sinh không hợp lệ';
        }
        // Check if files were uploaded (only show "please upload" if file wasn't selected, not if upload failed)
        if (empty($id_card_front_path)) {
            if (!isset($_FILES['id_card_front']) || $_FILES['id_card_front']['error'] === UPLOAD_ERR_NO_FILE) {
                $errors[] = 'Vui lòng tải lên ảnh CMND mặt trước';
            }
        }
        if (empty($id_card_back_path)) {
            if (!isset($_FILES['id_card_back']) || $_FILES['id_card_back']['error'] === UPLOAD_ERR_NO_FILE) {
                $errors[] = 'Vui lòng tải lên ảnh CMND mặt sau';
            }
        }
        if (empty($driver_license_path)) {
            if (!isset($_FILES['driver_license']) || $_FILES['driver_license']['error'] === UPLOAD_ERR_NO_FILE) {
                $errors[] = 'Vui lòng tải lên ảnh bằng lái xe';
            }
        }
        if (empty($avatar_path)) {
            if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] === UPLOAD_ERR_NO_FILE) {
                $errors[] = 'Vui lòng tải lên ảnh đại diện';
            }
        }
    }
    
    if (empty($errors)) {
                // Escape all input values to prevent SQL injection
                $full_name_escaped = mysqli_real_escape_string($conn, $full_name);
                $email_escaped = mysqli_real_escape_string($conn, $email);
                $phone_escaped = mysqli_real_escape_string($conn, $phone);
                $password_escaped = mysqli_real_escape_string($conn, $password);
                $user_type_escaped = mysqli_real_escape_string($conn, $user_type);
                $location_escaped = mysqli_real_escape_string($conn, $location);
                $address_escaped = mysqli_real_escape_string($conn, $address);
                $username_escaped = mysqli_real_escape_string($conn, $username);
                $role_escaped = mysqli_real_escape_string($conn, $role);
                $vehicle_number_escaped = mysqli_real_escape_string($conn, $vehicle_number); // bien so xe
                $vehicle_type_escaped = mysqli_real_escape_string($conn, $vehicle_type); // loai xe
                $working_area_escaped = mysqli_real_escape_string($conn, $working_area); // khu vuc hoat dong
                $gender_escaped = mysqli_real_escape_string($conn, $gender);
                $birth_year_escaped = mysqli_real_escape_string($conn, $birth_year);
                $id_card_front_escaped = mysqli_real_escape_string($conn, $id_card_front_path);
                $id_card_back_escaped = mysqli_real_escape_string($conn, $id_card_back_path);
                $driver_license_escaped = mysqli_real_escape_string($conn, $driver_license_path);
                $avatar_escaped = mysqli_real_escape_string($conn, $avatar_path);
                $vehicle_number_escaped = mysqli_real_escape_string($conn, $vehicle_number); // bien so xe
                $vehicle_type_escaped = mysqli_real_escape_string($conn, $vehicle_type); // loai xe
                $working_area_escaped = mysqli_real_escape_string($conn, $working_area); // khu vuc hoat dong
                $gender_escaped = mysqli_real_escape_string($conn, $gender);
                $birth_year_escaped = mysqli_real_escape_string($conn, $birth_year);
                $id_card_front_escaped = mysqli_real_escape_string($conn, $id_card_front_path);
                $id_card_back_escaped = mysqli_real_escape_string($conn, $id_card_back_path);
                $driver_license_escaped = mysqli_real_escape_string($conn, $driver_license_path);
                // Build SQL query - add gender and birth_year for shippers
                $columns = "`HoTen`, `Email`, `SoDienThoai`, `MatKhau_Hash`, `VaiTro`, `DiaChiGiaoHangMacDinh`, `NgayTao`, `TenDangNhap`";
                $values = "'$full_name_escaped', '$email_escaped', '$phone_escaped', '$password_escaped', '$role_escaped', '$address_escaped', CURRENT_TIMESTAMP, '$username_escaped'";
                $sqlquery = "INSERT INTO `nguoidung` ($columns) VALUES ($values)";
                
                try {
                    if(mysqli_query($conn, $sqlquery)){
                       $userId = mysqli_insert_id($conn);
                        if($role === 'NguoiGiaoHang'){
                          
                           
                           // Insert vào bảng hosonguoigiaohang với ID_NguoiDung để liên kết với bảng nguoidung
                           $shipper_query = "INSERT INTO `hosonguoigiaohang` (`ID_NguoiDung`, `GioiTinh`, `NamSinh`, `Anh_CCCD_Truoc`, `Anh_CCCD_Sau`, `Anh_BangLaiXe`, `BienSoXe`, `KhuVucHoatDong`, `AnhDaiDien`) VALUES ($userId, '$gender_escaped', '$birth_year_escaped', '$id_card_front_escaped', '$id_card_back_escaped', '$driver_license_escaped', '$vehicle_number_escaped', '$working_area_escaped', '$avatar_escaped')";
                           
                           if (!mysqli_query($conn, $shipper_query)) {
                               $error_msg = mysqli_error($conn);
                               throw new Exception('Lỗi khi lưu thông tin người giao hàng: ' . $error_msg);
                           }
                        }
                       
                            // Tạo bảng wishlist cho người mua mới
                        $success = 'Đăng ký thành công! Bạn có thể đăng nhập ngay bây giờ.';
                        // Auto redirect to login after 3 seconds
                        header("refresh:3;url=login.php");
                    } else {
                        throw new Exception(mysqli_error($conn));
                    }
                } catch (mysqli_sql_exception $e) {
                    $error_code = $e->getCode();
                    $error_message = $e->getMessage();
                    
                    // Handle duplicate entry errors
                    if (strpos($error_message, 'Duplicate entry') !== false) {
                        if (strpos($error_message, 'TenDangNhap') !== false) {
                            $errors[] = 'Tên đăng nhập đã tồn tại';
                        } elseif (strpos($error_message, 'Email') !== false) {
                            $errors[] = 'Email đã tồn tại';
                        } elseif (strpos($error_message, 'SoDienThoai') !== false) {
                            $errors[] = 'Số điện thoại đã tồn tại';
                        } else {
                            $errors[] = 'Thông tin đã tồn tại trong hệ thống';
                        }
                    } else {
                        $errors[] = 'Đăng ký thất bại! ' . $error_message;
                    }
                    $error = implode('<br>', $errors);
                } catch (Exception $e) {
                    $error_message = $e->getMessage();
                    $errors[] = 'Đăng ký thất bại! ' . $error_message;
                    $error = implode('<br>', $errors);
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
    <title>Đăng ký - SM</title>
    <link rel="stylesheet" href="./css/stylea.css">
    <link rel="stylesheet" href="./css/auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <a href="index.php" class="logo">
                    <span class="logo-text">SM</span>
                </a>
                <h1>Đăng ký tài khoản</h1>
                <p>Tạo tài khoản mới để bắt đầu mua bán</p>
                
                <div class="user-type-tabs">
                    <a href="?type=buyer" class="<?php echo $user_type === 'buyer' ? 'active' : ''; ?>">
                        <i class="fas fa-shopping-cart"></i>
                        Người mua
                    </a>
                    <a href="?type=seller" class="<?php echo $user_type === 'seller' ? 'active' : ''; ?>">
                        <i class="fas fa-store"></i>
                        Người bán
                    </a>
                    <a href="?type=shipper" class="<?php echo $user_type === 'shipper' ? 'active' : ''; ?>">
                        <i class="fas fa-truck"></i>
                        Người giao hàng
                    </a>
                </div>
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
                    <p>Đang chuyển hướng đến trang đăng nhập...</p>
                </div>
            <?php endif; ?>
            
            <?php if (!$success): ?>
            <form method="POST" class="auth-form" data-validate enctype="multipart/form-data">
                <div class="form-row">
                    <div class="form-group">
                        <label for="username" class="form-label">
                            <i class="fas fa-user"></i>
                            Tên đăng nhập
                        </label>
                        <input type="text" id="username" name="username" class="form-control" 
                               value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" 
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="full_name" class="form-label">
                            <i class="fas fa-id-card"></i>
                            Họ và tên
                        </label>
                        <input type="text" id="full_name" name="full_name" class="form-control" 
                               value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>" 
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
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" 
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone" class="form-label">
                            <i class="fas fa-phone"></i>
                            Số điện thoại
                        </label>
                        <input type="tel" id="phone" name="phone" class="form-control" 
                               value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" 
                               required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="password" class="form-label">
                            <i class="fas fa-lock"></i>
                            Mật khẩu
                        </label>
                        <div class="password-input">
                            <input type="password" id="password" name="password" class="form-control" required>
                            <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password" class="form-label">
                            <i class="fas fa-lock"></i>
                            Xác nhận mật khẩu
                        </label>
                        <div class="password-input">
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                            <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="location" class="form-label">
                        <i class="fas fa-map-marker-alt"></i>
                        Địa điểm
                    </label>
                    <input type="text" id="location" name="location" class="form-control" 
                           placeholder="Ví dụ: Hà Nội, TP.HCM, Đà Nẵng..." 
                           value="<?php echo htmlspecialchars($_POST['location'] ?? ''); ?>" 
                           required>
                </div>
                
                <div class="form-group">
                    <label for="address" class="form-label">
                        <i class="fas fa-home"></i>
                        Địa chỉ chi tiết
                    </label>
                    <textarea id="address" name="address" class="form-control" rows="3" 
                              placeholder="Địa chỉ cụ thể để nhận hàng..."><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
                </div>
                
                <?php if ($user_type === 'shipper'): ?>
                <div class="shipper-fields">
                    <h3>Thông tin người giao hàng</h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="vehicle_number" class="form-label">
                                <i class="fas fa-motorcycle"></i>
                                Biển số xe
                            </label>
                            <input type="text" id="vehicle_number" name="vehicle_number" class="form-control" 
                                   value="<?php echo htmlspecialchars($_POST['vehicle_number'] ?? ''); ?>" 
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="vehicle_type" class="form-label">
                                <i class="fas fa-car"></i>
                                Loại phương tiện
                            </label>
                            <select id="vehicle_type" name="vehicle_type" class="form-control" required>
                                <option value="">Chọn loại phương tiện</option>
                                <option value="motorcycle" <?php echo ($_POST['vehicle_type'] ?? '') === 'motorcycle' ? 'selected' : ''; ?>>Xe máy</option>
                                <option value="car" <?php echo ($_POST['vehicle_type'] ?? '') === 'car' ? 'selected' : ''; ?>>Ô tô</option>
                                <option value="bicycle" <?php echo ($_POST['vehicle_type'] ?? '') === 'bicycle' ? 'selected' : ''; ?>>Xe đạp</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-venus-mars"></i>
                                Giới tính
                            </label>
                            <div class="radio-group" style="display: flex; gap: 20px; margin-top: 10px;">
                                <label class="radio-label" style="display: flex; align-items: center; cursor: pointer;">
                                    <input type="radio" name="gender" value="male" <?php echo ($_POST['gender'] ?? '') === 'male' ? 'checked' : ''; ?> required style="margin-right: 5px;">
                                    <span>Nam</span>
                                </label>
                                <label class="radio-label" style="display: flex; align-items: center; cursor: pointer;">
                                    <input type="radio" name="gender" value="female" <?php echo ($_POST['gender'] ?? '') === 'female' ? 'checked' : ''; ?> style="margin-right: 5px;">
                                    <span>Nữ</span>
                                </label>
                                <label class="radio-label" style="display: flex; align-items: center; cursor: pointer;">
                                    <input type="radio" name="gender" value="other" <?php echo ($_POST['gender'] ?? '') === 'other' ? 'checked' : ''; ?> style="margin-right: 5px;">
                                    <span>Khác</span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="birth_year" class="form-label">
                                <i class="fas fa-calendar"></i>
                                Năm sinh
                            </label>
                            <input type="number" id="birth_year" name="birth_year" class="form-control" 
                                   min="1950" max="<?php echo date('Y'); ?>" 
                                   value="<?php echo htmlspecialchars($_POST['birth_year'] ?? ''); ?>" 
                                   placeholder="Ví dụ: 1990" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="working_area" class="form-label">
                            <i class="fas fa-map"></i>
                            Khu vực hoạt động
                        </label>
                        <textarea id="working_area" name="working_area" class="form-control" rows="2" 
                                  placeholder="Ví dụ: Quận 1, 2, 3 TP.HCM hoặc toàn thành phố" 
                                  required><?php echo htmlspecialchars($_POST['working_area'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="id_card_front" class="form-label">
                                <i class="fas fa-id-card"></i>
                                Ảnh CMND/CCCD mặt trước
                            </label>
                            <input type="file" id="id_card_front" name="id_card_front" class="form-control" 
                                   accept="image/jpeg,image/jpg,image/png" required>
                            <small class="form-text">Định dạng: JPG, JPEG, PNG. Kích thước tối đa: 5MB</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="id_card_back" class="form-label">
                                <i class="fas fa-id-card"></i>
                                Ảnh CMND/CCCD mặt sau
                            </label>
                            <input type="file" id="id_card_back" name="id_card_back" class="form-control" 
                                   accept="image/jpeg,image/jpg,image/png" required>
                            <small class="form-text">Định dạng: JPG, JPEG, PNG. Kích thước tối đa: 5MB</small>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="driver_license" class="form-label">
                            <i class="fas fa-id-badge"></i>
                            Ảnh bằng lái xe
                        </label>
                        <input type="file" id="driver_license" name="driver_license" class="form-control" 
                               accept="image/jpeg,image/jpg,image/png" required>
                        <small class="form-text">Định dạng: JPG, JPEG, PNG. Kích thước tối đa: 5MB</small>
                    </div>

                    <div class="form-group">
                        <label for="avatar" class="form-label">
                            <i class="fas fa-user-circle"></i>
                            Ảnh đại diện
                        </label>
                        <input type="file" id="avatar" name="avatar" class="form-control" 
                               accept="image/jpeg,image/jpg,image/png" required>
                        <small class="form-text">Ảnh chân dung rõ nét để xác minh danh tính.</small>
                    </div>
                    
                    <div class="upload-section">
                        <p><strong>Lưu ý:</strong> Vui lòng tải lên ảnh CMND/CCCD và bằng lái xe rõ ràng, đầy đủ thông tin. Ảnh sẽ được sử dụng để xác minh danh tính và giấy phép lái xe của bạn.</p>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="agreed" required>
                        <span class="checkmark"></span>
                        Tôi đồng ý với <a href="terms.php" target="_blank">Điều khoản sử dụng</a> và <a href="privacy.php" target="_blank">Chính sách bảo mật</a>
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-user-plus"></i>
                    Đăng ký tài khoản
                </button>
            </form>
            <?php endif; ?>
            
            <div class="auth-footer">
                <p>Đã có tài khoản? <a href="login.php">Đăng nhập ngay</a></p>
            </div>
        </div>
        
        <div class="auth-features">
            <h2>Lợi ích khi tham gia SM</h2>
            <div class="feature-list">
                <?php if ($user_type === 'buyer'): ?>
                    <div class="feature-item">
                        <i class="fas fa-search"></i>
                        <div>
                            <h3>Tìm kiếm dễ dàng</h3>
                            <p>Tìm kiếm sản phẩm theo nhiều tiêu chí khác nhau</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-shield-alt"></i>
                        <div>
                            <h3>Mua sắm an toàn</h3>
                            <p>Đảm bảo hoàn tiền 100% nếu sản phẩm không đúng mô tả</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-truck"></i>
                        <div>
                            <h3>Giao hàng nhanh</h3>
                            <p>Liên kết với nhiều đơn vị vận chuyển uy tín</p>
                        </div>
                    </div>
                <?php elseif ($user_type === 'seller'): ?>
                    <div class="feature-item">
                        <i class="fas fa-store"></i>
                        <div>
                            <h3>Cửa hàng trực tuyến</h3>
                            <p>Tạo cửa hàng riêng và quản lý sản phẩm dễ dàng</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-chart-line"></i>
                        <div>
                            <h3>Theo dõi doanh thu</h3>
                            <p>Thống kê chi tiết về doanh số và đơn hàng</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-users"></i>
                        <div>
                            <h3>Tiếp cận khách hàng</h3>
                            <p>Kết nối với hàng nghìn người mua tiềm năng</p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="feature-item">
                        <i class="fas fa-dollar-sign"></i>
                        <div>
                            <h3>Thu nhập linh hoạt</h3>
                            <p>Kiếm tiền theo giờ, theo đơn hàng hoàn thành</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-clock"></i>
                        <div>
                            <h3>Thời gian linh hoạt</h3>
                            <p>Làm việc theo thời gian phù hợp với bạn</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <div>
                            <h3>Khu vực linh hoạt</h3>
                            <p>Chọn khu vực giao hàng phù hợp</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
   <!-- <script src="assets/js/main.js"></script>
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
        
        // Password strength indicator
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strength = getPasswordStrength(password);
            updatePasswordStrength(strength);
        });
        
        function getPasswordStrength(password) {
            let strength = 0;
            if (password.length >= 8) strength++;
            if (/[a-z]/.test(password)) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;
            return strength;
        }
        
        function updatePasswordStrength(strength) {
            let strengthText = '';
            let strengthClass = '';
            
            switch(strength) {
                case 0:
                case 1:
                    strengthText = 'Rất yếu';
                    strengthClass = 'very-weak';
                    break;
                case 2:
                    strengthText = 'Yếu';
                    strengthClass = 'weak';
                    break;
                case 3:
                    strengthText = 'Trung bình';
                    strengthClass = 'medium';
                    break;
                case 4:
                    strengthText = 'Mạnh';
                    strengthClass = 'strong';
                    break;
                case 5:
                    strengthText = 'Rất mạnh';
                    strengthClass = 'very-strong';
                    break;
            }
            
            let indicator = document.querySelector('.password-strength');
            if (!indicator) {
                indicator = document.createElement('div');
                indicator.className = 'password-strength';
                document.getElementById('password').parentNode.appendChild(indicator);
            }
            
            indicator.textContent = strengthText;
            indicator.className = 'password-strength ' + strengthClass;
        }
    </script> -->
</body>
</html>
