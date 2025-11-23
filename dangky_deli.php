<?php

$servername = "localhost";
$username = "root";
$password = ""; 
$dbname = "secondhand_market";

$tableName = "hosonguoigiaohang";
$parentTableName = "nguoidung"; 

$message = "";
$message_type = "";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
     die("Connection failed: " . $conn->connect_error);
}
function handleFileUpload($fileInputName, $target_dir, $conn) {
    if (!isset($_FILES[$fileInputName]) || $_FILES[$fileInputName]['error'] != UPLOAD_ERR_OK) {
        return null; 
    }

    $file_name = basename($_FILES[$fileInputName]["name"]);
    $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
    $new_file_name = uniqid() . "." . $file_extension; 
    $target_file = $target_dir . $new_file_name;
    if (move_uploaded_file($_FILES[$fileInputName]["tmp_name"], $target_file)) {
        return $conn->real_escape_string($new_file_name); 
    } else {
        return null; 
    }
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $hoten = $conn->real_escape_string($_POST['hoten_deli']); 
    $email = $conn->real_escape_string($_POST['email_deli']);
    $sodt = $conn->real_escape_string($_POST['sodt']);
    $matkhau = $conn->real_escape_string($_POST['matkhau']); 
    $namsinh = (int)$_POST['namsinh'];
    $gioitinh = $conn->real_escape_string($_POST['gioitinh']);
    $biensoxe = $conn->real_escape_string($_POST['biensoxe']);
    $khuvuchoatdong = $conn->real_escape_string($_POST['khuvuchoatdong']);
    $hashed_password = password_hash($matkhau, PASSWORD_DEFAULT);
    $target_dir = "uploads/";  
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    $anh_daidien = handleFileUpload('anh_daidien', $target_dir, $conn); 
    $anh_cccd_truoc = handleFileUpload('anh_cccd_truoc', $target_dir, $conn);
    $anh_cccd_sau = handleFileUpload('anh_cccd_sau', $target_dir, $conn); 
    $anh_banglaixe = handleFileUpload('anh_banglaixe', $target_dir, $conn); 
    if ($anh_daidien === null || $anh_cccd_truoc === null || $anh_cccd_sau === null || $anh_banglaixe === null) {
        $message = "Lỗi upload file hoặc thiếu một trong các file bắt buộc. Vui lòng kiểm tra lại.";
        $message_type = "error";
    } else {
        $vaiTro = 'NguoiGiaoHang'; 
        $sql_parent = "INSERT INTO $parentTableName (
                            HoTen, Email, SoDienThoai, MatKhau_Hash, VaiTro, AnhDaiDien
                        ) VALUES (
                            '$hoten', '$email', '$sodt', '$hashed_password', '$vaiTro', '$anh_daidien'
                        )";
                        
        if ($conn->query($sql_parent) === TRUE) {
            $ID_NguoiDung_moi = $conn->insert_id;
            $sql_child = "INSERT INTO $tableName (
                            ID_NguoiDung, GioiTinh, NamSinh, BienSoXe, KhuVucHoatDong, 
                            Anh_CCCD_Truoc, Anh_CCCD_Sau, Anh_BangLaiXe
                        ) VALUES (
                            $ID_NguoiDung_moi, '$gioitinh', $namsinh, '$biensoxe', '$khuvuchoatdong', 
                            '$anh_cccd_truoc', '$anh_cccd_sau', '$anh_banglaixe'
                        )";
                        
            if ($conn->query($sql_child) === TRUE) {
                $message = "Đăng ký thành công! Hồ sơ của bạn đang chờ xét duyệt.";
                $message_type = "success";
            } else {
               
                $conn->query("DELETE FROM $parentTableName WHERE ID_NguoiDung = $ID_NguoiDung_moi");
                $message = "Lỗi chèn hồ sơ giao hàng (Bảng con): " . $conn->error;
                $message_type = "error";
            }
        } else {
            $message = "Lỗi đăng ký người dùng (Bảng cha). Có thể Email hoặc Số điện thoại đã tồn tại: " . $conn->error;
            $message_type = "error";
        }
     }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Ký Ứng Viên</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
        }
        .submit-button {
            transition: all 0.2s ease-in-out;
        }
        .signup-text {
            margin-top: 15px;
            font-size: 14px;
        }
        .signup-text a {
            color: #5563DE;
            text-decoration: none;
        }
        .signup-text a:hover {
            text-decoration: underline;
        }
        .file-input-style {
          
            @apply w-full text-sm text-gray-500 
                   file:mr-4 file:py-2 file:px-4 
                   file:border-0 file:rounded-lg file:text-sm file:font-semibold 
                   file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100;
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-4">

    <div class="w-full max-w-2xl bg-white shadow-2xl rounded-xl p-8 md:p-10 border border-gray-100">
        <h1 class="text-3xl font-extrabold text-center text-gray-800 mb-6">Đăng Ký</h1>
        <p class="text-center text-gray-500 mb-8">Vui lòng điền đầy đủ thông tin để hoàn tất hồ sơ của bạn.</p>

        <?php if ($message): ?>
            <div id="message-box" class="p-4 mb-4 text-sm rounded-lg 
                <?php echo $message_type === 'success' ? 'bg-green-100 text-green-700 border border-green-400' : 'bg-red-100 text-red-700 border border-red-400'; ?>" 
                role="alert">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" enctype="multipart/form-data"> 

            <fieldset class="mb-8 border border-gray-200 rounded-lg p-6">
                <legend class="text-lg font-semibold text-gray-700 px-2">Thông tin Cơ bản</legend>
                
                <div class="mb-6">
                    <label for="anh_daidien" class="block text-sm font-medium text-gray-700 mb-2">Ảnh Đại Diện (*)</label>
                    <input type="file" id="anh_daidien" name="anh_daidien" accept="image/*" required class="file-input-style">
                </div>
                <div class="mb-4">
                    <label for="hoten_deli" class="block text-sm font-medium text-gray-700 mb-1">Họ và Tên (*)</label>
                    <input type="text" id="hoten_deli" name="hoten_deli" required 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="mb-4">
                        <label for="email_deli" class="block text-sm font-medium text-gray-700 mb-1">Email (*)</label>
                        <input type="email" id="email_deli" name="email_deli" required 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400">
                    </div>

                    <div class="mb-4">
                        <label for="namsinh" class="block text-sm font-medium text-gray-700 mb-1">Năm sinh (*)</label>
                        <input type="number" id="namsinh" name="namsinh" required min="1950" max="<?php echo date("Y") - 18; ?>" placeholder="VD: 1995" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Giới tính (*)</label>
                    <div class="flex space-x-6">
                        <label class="inline-flex items-center">
                            <input type="radio" name="gioitinh" value="Nam" required class="form-radio h-4 w-4 text-blue-600 border-gray-300">
                            <span class="ml-2 text-gray-700">Nam</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" name="gioitinh" value="Nữ" class="form-radio h-4 w-4 text-blue-600 border-gray-300">
                            <span class="ml-2 text-gray-700">Nữ</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" name="gioitinh" value="Khác" class="form-radio h-4 w-4 text-blue-600 border-gray-300">
                            <span class="ml-2 text-gray-700">Khác</span>
                        </label>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="mb-4">
                        <label for="sodt" class="block text-sm font-medium text-gray-700 mb-1">Số điện thoại (*)</label>
                        <input type="tel" id="sodt" name="sodt" required pattern="[0-9]{10,12}" placeholder="VD: 0901234567" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400">
                    </div>

                    <div class="mb-4">
                        <label for="matkhau" class="block text-sm font-medium text-gray-700 mb-1">Mật khẩu (*)</label>
                        <input type="password" id="matkhau" name="matkhau" required minlength="6" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
            </fieldset>

            <fieldset class="mb-8 border border-gray-200 rounded-lg p-6">
                <legend class="text-lg font-semibold text-gray-700 px-2">Thông tin Phương tiện & Hoạt động</legend>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="mb-4">
                        <label for="biensoxe" class="block text-sm font-medium text-gray-700 mb-1">Biển số xe (*)</label>
                        <input type="text" id="biensoxe" name="biensoxe" required placeholder="VD: 59A-123.45" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400">
                    </div>
    
                    <div class="mb-4">
                        <label for="khuvuchoatdong" class="block text-sm font-medium text-gray-700 mb-1">Khu vực hoạt động (*)</label>
                        <input type="text" id="khuvuchoatdong" name="khuvuchoatdong" required placeholder="VD: TP. Hồ Chí Minh" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400">
                    </div>
                </div>
            </fieldset>

            <fieldset class="mb-8 border border-gray-200 rounded-lg p-6">
                <legend class="text-lg font-semibold text-gray-700 px-2">Tài liệu Xác minh (*)</legend>
                <p class="text-sm text-gray-500 mb-4">Các tài liệu này là **bắt buộc** để hoàn tất hồ sơ.</p>

                <div class="mb-6">
                    <label for="anh_cccd_truoc" class="block text-sm font-medium text-gray-700 mb-2">1. Ảnh CCCD/CMND Mặt trước</label>
                    <input type="file" id="anh_cccd_truoc" name="anh_cccd_truoc" accept="image/*" required class="file-input-style">
                </div>

                <div class="mb-6">
                    <label for="anh_cccd_sau" class="block text-sm font-medium text-gray-700 mb-2">2. Ảnh CCCD/CMND Mặt sau</label>
                    <input type="file" id="anh_cccd_sau" name="anh_cccd_sau" accept="image/*" required class="file-input-style">
                </div>

                <div class="mb-4">
                    <label for="anh_banglaixe" class="block text-sm font-medium text-gray-700 mb-2">3. Ảnh Bằng lái xe</label>
                    <input type="file" id="anh_banglaixe" name="anh_banglaixe" accept="image/*" required class="file-input-style">
                </div>
            </fieldset>
            
            <button type="submit" class="submit-button w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-xl shadow-lg text-lg font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-500 focus:ring-opacity-50 transition duration-150 ease-in-out">
                HOÀN TẤT ĐĂNG KÝ
            </button>
            <p class="signup-text" style="text-align: center;">Bạn đã có tài khoản? <a href="login.php">Đăng nhập</a></p>
        </form>
    </div>
</body>
</html>