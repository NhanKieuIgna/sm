<?php
session_start();

// Kết nối DB
$dbPath = realpath(__DIR__ . '/../../database/db.php');
if (!$dbPath || !file_exists($dbPath)) {
    die('Database file not found.');
}
require_once $dbPath;

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$errors = [];
$data = [
    'HoTen' => '',
    'Email' => '',
    'SoDienThoai' => '',
    'TenDangNhap' => '',
    'VaiTro' => 'NguoiMua',
    'TrangThaiHoatDong' => 1
];

// ===============================
// 1. Load dữ liệu khi sửa
// ===============================
if ($id > 0) {
    $stmt = $conn->prepare("
        SELECT ID_NguoiDung, HoTen, Email, SoDienThoai, TenDangNhap, VaiTro, TrangThaiHoatDong 
        FROM nguoidung 
        WHERE ID_NguoiDung = ?
    ");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res && $res->num_rows > 0) {
        $data = array_merge($data, $res->fetch_assoc());
    } else {
        $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Người dùng không tồn tại.'];
        header('Location: user.php');
        exit;
    }
    $stmt->close();
}

// ===============================
// 2. Xử lý form POST
// ===============================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Lấy dữ liệu
    $HoTen = trim($_POST['HoTen'] ?? '');
    $Email = trim($_POST['Email'] ?? '');
    $SoDienThoai = trim($_POST['SoDienThoai'] ?? '');
    $TenDangNhap = trim($_POST['TenDangNhap'] ?? '');
    $VaiTro = $_POST['VaiTro'] ?? 'NguoiMua';
    $TrangThaiHoatDong = isset($_POST['TrangThaiHoatDong']) ? 1 : 0;
    $MatKhau = $_POST['MatKhau'] ?? '';

    // Valid
    $errors = [];
    if ($HoTen === '') $errors[] = 'Họ tên bắt buộc.';
    if (!filter_var($Email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email không hợp lệ.';
    if ($TenDangNhap === '') $errors[] = 'Tên đăng nhập bắt buộc.';

    // ===============================
    // Check trùng Email / SDT / Username
    // ===============================
    $sql = "SELECT ID_NguoiDung FROM nguoidung 
            WHERE (Email=? OR SoDienThoai=? OR TenDangNhap=?)";
    if ($id > 0) {
        $sql .= " AND ID_NguoiDung != ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sssi', $Email, $SoDienThoai, $TenDangNhap, $id);
    } else {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sss', $Email, $SoDienThoai, $TenDangNhap);
    }
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $res->num_rows > 0) {
        $errors[] = 'Email / SĐT / Tên đăng nhập đã tồn tại.';
    }
    $stmt->close();

    // ===============================
    // Nếu không lỗi → Insert/Update
    // ===============================
    if (empty($errors)) {

        // UPDATE
        if ($id > 0) {
            if ($MatKhau !== '') {
                $hash = password_hash($MatKhau, PASSWORD_DEFAULT);
                $sql = "UPDATE nguoidung 
                        SET HoTen=?, Email=?, SoDienThoai=?, TenDangNhap=?, MatKhau_Hash=?, VaiTro=?, TrangThaiHoatDong=? 
                        WHERE ID_NguoiDung=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('ssssssii', 
                    $HoTen, $Email, $SoDienThoai, $TenDangNhap, $hash, $VaiTro, $TrangThaiHoatDong, $id
                );
            } else {
                $sql = "UPDATE nguoidung 
                        SET HoTen=?, Email=?, SoDienThoai=?, TenDangNhap=?, VaiTro=?, TrangThaiHoatDong=? 
                        WHERE ID_NguoiDung=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('ssssssi', 
                    $HoTen, $Email, $SoDienThoai, $TenDangNhap, $VaiTro, $TrangThaiHoatDong, $id
                );
            }

            if ($stmt->execute()) {
                echo "<script>
                    alert('Cập nhật người dùng thành công!');
                    window.location.href = 'user.php';
                </script>";
                exit;
            } else {
                $errors[] = 'Lỗi khi cập nhật: ' . $conn->error;
            }
            $stmt->close();

        } 
        // INSERT
        else {
            $hash = password_hash($MatKhau ?: bin2hex(random_bytes(8)), PASSWORD_DEFAULT);
            $sql = "INSERT INTO nguoidung (HoTen, Email, SoDienThoai, MatKhau_Hash, VaiTro, TrangThaiHoatDong, TenDangNhap)
                    VALUES (?,?,?,?,?,?,?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('sssssis', 
                $HoTen, $Email, $SoDienThoai, $hash, $VaiTro, $TrangThaiHoatDong, $TenDangNhap
            );

            if ($stmt->execute()) {
                echo "<script>
                    alert('Tạo người dùng thành công!');
                    window.location.href = 'user.php';
                </script>";
                exit;
            } else {
                $errors[] = 'Lỗi khi tạo người dùng: ' . $conn->error;
            }
            $stmt->close();
        }
    }

    // Nếu có lỗi → giữ dữ liệu lại
    $data = [
        'HoTen' => $HoTen,
        'Email' => $Email,
        'SoDienThoai' => $SoDienThoai,
        'TenDangNhap' => $TenDangNhap,
        'VaiTro' => $VaiTro,
        'TrangThaiHoatDong' => $TrangThaiHoatDong
    ];
}

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $id > 0 ? 'Sửa' : 'Thêm' ?> người dùng</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><?= $id > 0 ? 'Sửa thông tin' : 'Thêm người dùng mới' ?></h4>
                </div>
                <div class="card-body">

                   

                    <!-- Hiển thị lỗi validation -->
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>Lỗi:</strong>
                            <ul class="mb-0">
                                <?php foreach ($errors as $err): ?>
                                    <li><?= htmlspecialchars($err) ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <!-- Form -->
                    <form method="POST" novalidate>

                        <div class="form-group">
                            <label for="HoTen">Họ tên <span class="text-danger">*</span></label>
                            <input type="text" id="HoTen" name="HoTen" class="form-control" 
                                   value="<?= htmlspecialchars($data['HoTen']) ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="Email">Email <span class="text-danger">*</span></label>
                            <input type="email" id="Email" name="Email" class="form-control" 
                                   value="<?= htmlspecialchars($data['Email']) ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="SoDienThoai">Số điện thoại</label>
                            <input type="text" id="SoDienThoai" name="SoDienThoai" class="form-control" 
                                   value="<?= htmlspecialchars($data['SoDienThoai']) ?>" placeholder="0912345678">
                        </div>

                        <div class="form-group">
                            <label for="TenDangNhap">Tên đăng nhập <span class="text-danger">*</span></label>
                            <input type="text" id="TenDangNhap" name="TenDangNhap" class="form-control" 
                                   value="<?= htmlspecialchars($data['TenDangNhap']) ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="MatKhau">Mật khẩu 
                                <?php if ($id > 0): ?>
                                    <small class="text-muted">(để trống nếu không đổi)</small>
                                <?php else: ?>
                                    <span class="text-danger">*</span>
                                <?php endif; ?>
                            </label>
                            <input type="password" id="MatKhau" name="MatKhau" class="form-control" 
                                   <?= $id === 0 ? 'required' : '' ?> placeholder="Nhập mật khẩu">
                        </div>

                        <div class="form-group">
                            <label for="VaiTro">Vai trò <span class="text-danger">*</span></label>
                            <select id="VaiTro" name="VaiTro" class="form-control" required>
                                <option value="NguoiMua" <?= $data['VaiTro'] === 'NguoiMua' ? 'selected' : '' ?>>Người mua</option>
                                <option value="NguoiBan" <?= $data['VaiTro'] === 'NguoiBan' ? 'selected' : '' ?>>Người bán</option>
                                <option value="NguoiGiaoHang" <?= $data['VaiTro'] === 'NguoiGiaoHang' ? 'selected' : '' ?>>Người giao hàng</option>
                                <option value="QuanTriVien" <?= $data['VaiTro'] === 'QuanTriVien' ? 'selected' : '' ?>>Quản trị viên</option>
                            </select>
                        </div>

                        <div class="form-group form-check">
                            <input type="checkbox" id="TrangThaiHoatDong" name="TrangThaiHoatDong" 
                                   class="form-check-input" value="1" <?= $data['TrangThaiHoatDong'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="TrangThaiHoatDong">
                                Hoạt động
                            </label>
                        </div>

                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary btn-block">
                                <?= $id > 0 ? 'Cập nhật' : 'Tạo người dùng' ?>
                            </button>

                           
                            <a href="user.php" class="btn btn-secondary btn-block mt-2">Hủy</a>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
</body>
</html>
