<?php
session_start();
include "sm/dp.php";
if (!isset($_SESSION['user_id']) || $_SESSION['vaitro'] !== 'NguoiGiaoHang') {
    header("Location: login.php"); 
    exit();
}
$driver_id = $_SESSION['user_id'];
$driver = [];
$message = '';
$target_dir = "sm/uploads/"; 


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ho_ten = trim($_POST['ho_ten']);
    $sdt = trim($_POST['sdt']);
    $email = trim($_POST['email']);
    $dia_chi = trim($_POST['dia_chi']);
    $gioi_tinh = $_POST['gioi_tinh'];
    $nam_sinh = $_POST['nam_sinh']; 
    $bien_so_xe = trim($_POST['bien_so_xe']); 
    $khu_vuc = trim($_POST['khu_vuc']); 
    $old_avatar = $_POST['old_avatar'];

    mysqli_begin_transaction($conn);
    $success = true;

    try {
  
        if (isset($_FILES['anh_dai_dien']) && $_FILES['anh_dai_dien']['error'] === UPLOAD_ERR_OK) {
            $file_ext = strtolower(pathinfo($_FILES['anh_dai_dien']['name'], PATHINFO_EXTENSION));
            $new_file_name = $driver_id . '_avatar_' . time() . '.' . $file_ext;
            $target_file = $target_dir . $new_file_name;

            if (move_uploaded_file($_FILES['anh_dai_dien']['tmp_name'], $target_file)) {
                $avatar_name = $new_file_name;
            } else {
                $message = "<div style='color: red;'>Lỗi khi tải ảnh lên.</div>";
                $success = false;
            }
        } else {
            $avatar_name = $old_avatar;
        }
        
            if ($success) {
                $sql_update_user = "UPDATE nguoidung SET 
                                    HoTen = ?, SoDienThoai = ?, Email = ?, DiaChiGiaoHangMacDinh = ?, AnhDaiDien = ? 
                                    WHERE ID_NguoiDung = ?";

                $stmt_user = mysqli_prepare($conn, $sql_update_user);
                mysqli_stmt_bind_param($stmt_user, "sssssi", $ho_ten, $sdt, $email, $dia_chi, $avatar_name, $driver_id);
                
                if (!mysqli_stmt_execute($stmt_user)) {
                    throw new Exception("Lỗi cập nhật thông tin cơ bản: " . mysqli_error($conn));
                }
                mysqli_stmt_close($stmt_user);

                $sql_upsert_profile = "INSERT INTO hosonguoigiaohang 
                                    (ID_NguoiDung, GioiTinh, NamSinh, BienSoXe, KhuVucHoatDong)
                                    VALUES (?, ?, ?, ?, ?)
                                    ON DUPLICATE KEY UPDATE 
                                    GioiTinh = VALUES(GioiTinh), 
                                    NamSinh = VALUES(NamSinh), 
                                    BienSoXe = VALUES(BienSoXe), 
                                    KhuVucHoatDong = VALUES(KhuVucHoatDong)";
                
                $stmt_profile = mysqli_prepare($conn, $sql_upsert_profile);
                mysqli_stmt_bind_param($stmt_profile, "isiss", $driver_id, $gioi_tinh, $nam_sinh, $bien_so_xe, $khu_vuc);
                
                if (!mysqli_stmt_execute($stmt_profile)) {
                    throw new Exception("Lỗi cập nhật hồ sơ giao hàng: " . mysqli_error($conn));
                }
                mysqli_stmt_close($stmt_profile);

                mysqli_commit($conn);
                header("Location: hoso.php");
                exit();
            }

    } catch (Exception $e) {
        mysqli_rollback($conn);
        $message = "<div style='color: red;' class='message'>Lỗi: " . $e->getMessage() . "</div>";
    }
}
$sql = "SELECT 
            N.HoTen, N.Email, N.SoDienThoai, N.DiaChiGiaoHangMacDinh, N.AnhDaiDien,
            H.GioiTinh, H.NamSinh, H.BienSoXe, H.KhuVucHoatDong
        FROM nguoidung AS N
        LEFT JOIN hosonguoigiaohang AS H ON N.ID_NguoiDung = H.ID_NguoiDung
        WHERE N.ID_NguoiDung = ?";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $driver_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($result && $row = mysqli_fetch_assoc($result)) {
    $driver = $row;
    $driver['AnhDaiDien'] = htmlspecialchars($row['AnhDaiDien'] ?: 'default_avatar.png');
} else {
    $message = "<div style='color: red;' class='message'>Không tìm thấy dữ liệu người dùng.</div>";
}

mysqli_stmt_close($stmt);

?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Chỉnh sửa Hồ sơ</title>
</head>
<style>
body {
    font-family: Arial, sans-serif;
    background: #f4f7f6;
    padding: 20px;
}
.container {
    max-width: 700px;
    margin: 0 auto;
    background: #fff;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
h2 {
    text-align: center;
    color: #007bff;
    margin-bottom: 25px;
}
.form-group {
    margin-bottom: 20px;
}
label {
    display: block;
    font-weight: bold;
    margin-bottom: 5px;
    color: #333;
}
input[type="text"], input[type="email"], select, textarea, input[type="number"] {
    width: 100%;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
    box-sizing: border-box;
}
.avatar-preview {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 10px;
}
.avatar-preview img {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #007bff;
}
.btn-submit {
    background: #007bff;
    color: white;
    padding: 12px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
    transition: background 0.3s;
    width: 100%;
}
.btn-submit:hover {
    background: #0056b3;
}
.message {
    padding: 10px;
    margin-bottom: 15px;
    border-radius: 5px;
    text-align: center;
    font-weight: bold;
}
</style>
<body>

<div class="container">
    <h2>Chỉnh sửa Hồ sơ Người giao hàng</h2>

    <?php echo $message; ?>

    <form method="POST" action="profile.php" enctype="multipart/form-data">
        
        <h3>Thông tin cá nhân cơ bản</h3>
        
        <div class="form-group">
            <label>Ảnh Đại Diện</label>
            <div class="avatar-preview">
                <img src="<?php echo $target_dir . $driver['AnhDaiDien']; ?>" alt="Ảnh đại diện">
                <input type="file" name="anh_dai_dien">
                <input type="hidden" name="old_avatar" value="<?php echo $driver['AnhDaiDien']; ?>">
            </div>
        </div>

        <div class="form-group">
            <label for="ho_ten">Họ tên</label>
            <input type="text" id="ho_ten" name="ho_ten" value="<?php echo htmlspecialchars($driver['HoTen'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($driver['Email'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
            <label for="sdt">Số điện thoại</label>
            <input type="text" id="sdt" name="sdt" value="<?php echo htmlspecialchars($driver['SoDienThoai'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
            <label for="dia_chi">Địa chỉ Mặc định (Giao/Lấy hàng)</label>
            <textarea id="dia_chi" name="dia_chi"><?php echo htmlspecialchars($driver['DiaChiGiaoHangMacDinh'] ?? ''); ?></textarea>
        </div>

        <hr style="margin: 30px 0;">

        <h3>Hồ sơ Giao hàng </h3>

        <div class="form-group">
            <label for="gioi_tinh">Giới tính</label>
            <select id="gioi_tinh" name="gioi_tinh">
                <option value="Nam" <?php echo ($driver['GioiTinh'] == 'Nam') ? 'selected' : ''; ?>>Nam</option>
                <option value="Nu" <?php echo ($driver['GioiTinh'] == 'Nu') ? 'selected' : ''; ?>>Nữ</option>
                <option value="Khac" <?php echo ($driver['GioiTinh'] == 'Khac') ? 'selected' : ''; ?>>Khác</option>
            </select>
        </div>

        <div class="form-group">
            <label for="nam_sinh">Năm sinh</label>
            <input type="number" id="nam_sinh" name="nam_sinh" value="<?php echo htmlspecialchars($driver['NamSinh'] ?? ''); ?>" min="1900" max="<?php echo date('Y') - 18; ?>">
        </div>
        
        <div class="form-group">
            <label for="bien_so_xe">Biển số xe</label>
            <input type="text" id="bien_so_xe" name="bien_so_xe" value="<?php echo htmlspecialchars($driver['BienSoXe'] ?? ''); ?>">
        </div>

        <div class="form-group">
            <label for="khu_vuc">Khu vực hoạt động</label>
            <input type="text" id="khu_vuc" name="khu_vuc" value="<?php echo htmlspecialchars($driver['KhuVucHoatDong'] ?? ''); ?>">
        </div>
        
        <button type="submit" class="btn-submit">Lưu Cập Nhật Hồ Sơ</button>

    </form>
</div>

</body>
</html>