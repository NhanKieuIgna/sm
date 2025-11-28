<?php
session_start();
require "sm/dp.php"; 
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';
$sql_check_user = "SELECT MatKhau_Hash FROM nguoidung WHERE ID_NguoiDung = ?";
$stmt_check = mysqli_prepare($conn, $sql_check_user);
mysqli_stmt_bind_param($stmt_check, "i", $user_id);
mysqli_stmt_execute($stmt_check);
$res_check = mysqli_stmt_get_result($stmt_check);
$user_data = mysqli_fetch_assoc($res_check);
mysqli_stmt_close($stmt_check);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    if (!password_verify($password, $user_data['MatKhau_Hash'])) {
        $error = "Mật khẩu không chính xác.";
    } else {
        $sql_delete = "DELETE FROM nguoidung WHERE ID_NguoiDung = ?";
        $stmt_delete = mysqli_prepare($conn, $sql_delete);
        mysqli_stmt_bind_param($stmt_delete, "i", $user_id);
        
        if (mysqli_stmt_execute($stmt_delete)) {
            session_unset();
            session_destroy();
            $success = "Tài khoản của bạn đã được xóa thành công. Bạn sẽ được chuyển hướng sau 3 giây.";
            header("Refresh: 3; url=login.php"); 
            mysqli_stmt_close($stmt_delete);
            exit();
        } else {
            $error = "Lỗi hệ thống: Không thể xóa tài khoản. Vui lòng thử lại sau.";
            mysqli_stmt_close($stmt_delete);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Xóa Tài Khoản</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f8f9fa; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .container { background-color: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); width: 100%; max-width: 400px; text-align: center; }
        h2 { color: #dc3545; margin-bottom: 20px; }
        .alert-error { color: #721c24; background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
        .alert-success { color: #155724; background-color: #d4edda; border: 1px solid #c3e6cb; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
        .warning { color: #ffc107; font-weight: bold; margin-bottom: 20px; }
        input[type="password"] { width: 90%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; }
        button { background-color: #dc3545; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; width: 100%; transition: background-color 0.3s; }
        button:hover { background-color: #c82333; }
        a { color: #007bff; text-decoration: none; margin-top: 10px; display: block; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Xóa Tài Khoản</h2>
        
        <?php if ($success): ?>
            <div class="alert-success"><?php echo $success; ?></div>
        <?php else: ?>
            <p class="warning">
                ⚠️ CẢNH BÁO: Thao tác này sẽ xóa vĩnh viễn tất cả dữ liệu liên quan đến tài khoản của bạn và không thể hoàn tác.
            </p>
            
            <?php if ($error): ?>
                <div class="alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST">
                <label for="password">Xác nhận mật khẩu:</label>
                <input type="password" id="password" name="password" required placeholder="Nhập mật khẩu của bạn">
                <button type="submit">Xác Nhận Xóa Tài Khoản</button>
            </form>
            <a href="hoso.php">Quay lại Hồ sơ</a>
        <?php endif; ?>
    </div>
</body>
</html>