 
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
                <label for="username">Tên đăng nhập</label>
                <input type="text" id="username" name="username" placeholder="Nhập tên đăng nhập" required>
            </div>

            <div class="input-box">
                <label for="password">Mật khẩu</label>
                <input type="password" id="password" name="password" placeholder="Nhập mật khẩu" required>
            </div>

            <button type="submit" name="submit" class="btn">Đăng nhập</button>

            <?php if (!empty($error)) : ?>
                <p style="color:red; text-align:center;"><?= $error ?></p>
            <?php endif; ?>

            <p class="signup-text">Chưa có tài khoản? <a href="#">Đăng ký</a></p>
        </form>
    </div>
</body>
</html>
 <!-- <?php
session_start();
require 'db.php'; // đảm bảo file db.php cùng thư mục và có $conn kết nối MySQL

if (isset($_POST['submit'])) {  // tên nút submit cần khớp với form
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Tránh lỗi SQL Injection
    $username = mysqli_real_escape_string($conn, $username);
    $password = mysqli_real_escape_string($conn, $password);

    // Kiểm tra người dùng
    $sql = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
    $res = mysqli_query($conn, $sql);

    if ($res && mysqli_num_rows($res) > 0) {
        $row = mysqli_fetch_assoc($res);
        $_SESSION['username'] = $row['username'];
        header('location: index.php');
        exit();
    } else {
        $error = 'Tên đăng nhập hoặc mật khẩu không chính xác';
    }
}
?> -->