<?php
session_start();
require_once __DIR__ . '/../../database/db.php';

// Kiểm tra phiên đăng nhập nên được bật lại khi triển khai
// if (!isset($_SESSION['user_id']) || $_SESSION['vaitro'] !== 'NguoiGiaoHang') {
//     header("Location: login.php");
//     exit();
// }

// --- KHAI BÁO BIẾN BAN ĐẦU ---
$driver_id = $_SESSION['user_id'];
$message = "";
$target_dir = "../uploads/shipper_documents/"; // Đảm bảo thư mục này tồn tại và có quyền ghi

// --- XỬ LÝ DỮ LIỆU GỬI ĐI (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1. Lấy dữ liệu từ form
    $ho_ten = $_POST['ho_ten'];
    $email = $_POST['email'];
    $sdt = $_POST['sdt'];
    $dia_chi = $_POST['dia_chi'];
    $gioi_tinh = $_POST['gioi_tinh'];
    $nam_sinh = $_POST['nam_sinh'];
    $bien_so = $_POST['bien_so_xe'];
    $khu_vuc = $_POST['khu_vuc'];
    $loai_xe = $_POST['loai_xe'];
    $old_avatar = $_POST['old_avatar'];
    $avatar_name = $old_avatar;

    // 2. Xử lý upload ảnh đại diện mới
    if (!empty($_FILES['anh_dai_dien']['name'])) {
        $ext = pathinfo($_FILES['anh_dai_dien']['name'], PATHINFO_EXTENSION);
        $avatar_name = $driver_id . "_avatar_" . time() . "." . $ext;
        
        // Kiểm tra lỗi upload
        if (!move_uploaded_file($_FILES['anh_dai_dien']['tmp_name'], $target_dir . $avatar_name)) {
             $message = "<div class='msg msg-error'>Lỗi khi tải lên ảnh đại diện!</div>";
        }
    }

    // 3. Cập nhật bảng nguoidung (Thông tin cơ bản)
    $sql1 = "UPDATE nguoidung SET HoTen=?, Email=?, SoDienThoai=?, DiaChiGiaoHangMacDinh=?, AnhDaiDien=? WHERE ID_NguoiDung=?";
    $stmt1 = $conn->prepare($sql1);
    
    // Thêm kiểm tra lỗi SQL cho UPDATE 1
    if ($stmt1 === false) { die('Lỗi chuẩn bị SQL 1: ' . $conn->error); }

    $stmt1->bind_param("sssssi", $ho_ten, $email, $sdt, $dia_chi, $avatar_name, $driver_id);
    $stmt1->execute();
    $stmt1->close();


    // 4. Cập nhật/Thêm mới bảng hosonguoigiaohang (Thông tin tài xế)
    $sql2 = "INSERT INTO hosonguoigiaohang 
             (ID_NguoiDung, GioiTinh, NamSinh, BienSoXe, KhuVucHoatDong, LoaiXe)
             VALUES (?, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE 
             GioiTinh=VALUES(GioiTinh),
             NamSinh=VALUES(NamSinh),
             BienSoXe=VALUES(BienSoXe),
             KhuVucHoatDong=VALUES(KhuVucHoatDong),
             LoaiXe=VALUES(LoaiXe)";

    $stmt2 = $conn->prepare($sql2);
    
    // Thêm kiểm tra lỗi SQL cho UPDATE 2
    if ($stmt2 === false) { die('Lỗi chuẩn bị SQL 2: ' . $conn->error); }

    $stmt2->bind_param("isssss", $driver_id, $gioi_tinh, $nam_sinh, $bien_so, $khu_vuc, $loai_xe);
    $stmt2->execute();
    $stmt2->close();

    // 5. Chuyển hướng sau khi xử lý POST thành công
    header("Location: profile.php?update=success");
    exit();
}

// --- TRUY VẤN DỮ LIỆU ĐỂ HIỂN THỊ (GET) ---

$sql = "SELECT n.HoTen, n.Email, n.SoDienThoai, n.DiaChiGiaoHangMacDinh, n.AnhDaiDien, 
                h.GioiTinh, h.NamSinh, h.BienSoXe, h.KhuVucHoatDong, h.LoaiXe
        FROM nguoidung n
        LEFT JOIN hosonguoigiaohang h ON n.ID_NguoiDung = h.ID_NguoiDung
        WHERE n.ID_NguoiDung=?";

$stmt = $conn->prepare($sql);

// Thêm kiểm tra lỗi SQL cho SELECT
if ($stmt === false) { die('Lỗi chuẩn bị SQL SELECT: ' . $conn->error); }

$stmt->bind_param("i", $driver_id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$data) {
    $message = "<div class='msg msg-error'>Không tìm thấy dữ liệu hồ sơ!</div>";
}

// Xử lý thông báo thành công sau khi chuyển hướng POST
if (isset($_GET['update']) && $_GET['update'] == 'success') {
    $message = "<div class='msg msg-success'>Cập nhật hồ sơ thành công!</div>";
}

if (isset($_GET['update']) && $_GET['update'] == 'success') {
    header("location: hoso.php"); 
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Chỉnh sửa hồ sơ</title>
<style>
/* (Giữ nguyên phần CSS của bạn) */
body {
    font-family: Arial;
    background: #e8f4ff;
    padding: 20px;
}
.container {
    max-width: 750px;
    margin: auto;
    background: #fff;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}
h2 {
    text-align: center;
    color: #007bff;
    margin-bottom: 20px;
}
.form-group {
    margin-bottom: 18px;
}
label {
    font-weight: bold;
    display: block;
    margin-bottom: 5px;
}
input, select, textarea {
    width: 100%;
    padding: 10px;
    border-radius: 6px;
    border: 1px solid #bcd4f6;
}
.avatar-box {
    display: flex;
    align-items: center;
    gap: 15px;
}
.avatar-box img {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    border: 2px solid #008cff;
    object-fit: cover;
}
.btn-save {
    width: 100%;
    padding: 12px;
    background: #0099ff;
    color: #fff;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 16px;
}
.btn-save:hover {
    background: #007ad6;
}
.msg {
    padding: 12px;
    text-align: center;
    border-radius: 6px;
    margin-bottom: 15px;
    font-weight: bold;
}
.msg-success { background: #d6f7d2; color: #0a7a0a; }
.msg-error { background: #ffd5d5; color: #b10000; }
</style>
</head>
<body>

<div class="container">

    <h2>Cập nhật hồ sơ</h2>

    <?= $message ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>Ảnh đại diện</label>
            <div class="avatar-box">
                <img src="<?= $target_dir . ($data['AnhDaiDien'] ?: 'default_avatar.png') ?>">
                <input type="file" name="anh_dai_dien">
                <input type="hidden" name="old_avatar" value="<?= $data['AnhDaiDien'] ?>">
            </div>
        </div>
        <div class="form-group">
            <label>Họ tên</label>
            <input type="text" name="ho_ten" value="<?= htmlspecialchars($data['HoTen'] ?? '') ?>" required>
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($data['Email'] ?? '') ?>" required>
        </div>

        <div class="form-group">
            <label>SĐT</label>
            <input type="text" name="sdt" value="<?= htmlspecialchars($data['SoDienThoai'] ?? '') ?>" required>
        </div>

        <div class="form-group">
            <label>Địa chỉ</label>
            <textarea name="dia_chi"><?= htmlspecialchars($data['DiaChiGiaoHangMacDinh'] ?? '') ?></textarea>
        </div>

        <hr>

        <h3 style="color:#007bff;">Hồ sơ giao hàng</h3>

        <div class="form-group">
            <label>Giới tính</label>
            <select name="gioi_tinh">
                <option value="Nam" <?= (($data['GioiTinh'] ?? '')=="Nam"?"selected":"") ?>>Nam</option>
                <option value="Nữ" <?= (($data['GioiTinh'] ?? '')=="Nữ"?"selected":"") ?>>Nữ</option>
                <option value="Khác" <?= (($data['GioiTinh'] ?? '')=="Khác"?"selected":"") ?>>Khác</option>
            </select>
        </div>

        <div class="form-group">
            <label>Năm sinh</label>
            <input type="number" name="nam_sinh" value="<?= htmlspecialchars($data['NamSinh'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label>Loại xe</label>
            <select name="loai_xe">
                <option value="">-- Chọn --</option>
                <option value="Xe máy" <?= (($data['LoaiXe'] ?? '')=="Xe máy"?"selected":"") ?>>Xe máy (&lt; 175cc)</option>
                <option value="Xe mô tô" <?= (($data['LoaiXe'] ?? '')=="Xe mô tô"?"selected":"") ?>>Xe mô tô (&gt; 175cc)</option>
                <option value="Xe ba gác" <?= (($data['LoaiXe'] ?? '')=="Xe ba gác"?"selected":"") ?>>Xe ba gác</option>
                <option value="Xe tải nhỏ" <?= (($data['LoaiXe'] ?? '')=="Xe tải nhỏ"?"selected":"") ?>>Xe tải nhỏ</option>
            </select>
        </div>

        <div class="form-group">
            <label>Biển số xe</label>
            <input type="text" name="bien_so_xe" value="<?= htmlspecialchars($data['BienSoXe'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label>Khu vực hoạt động</label>
            <input type="text" name="khu_vuc" value="<?= htmlspecialchars($data['KhuVucHoatDong'] ?? '') ?>">
        </div>

        <button type="submit" class="btn-save">Lưu thay đổi</button>

    </form>
</div>

</body>
</html>