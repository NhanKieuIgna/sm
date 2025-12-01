<?php
session_start();
require_once __DIR__ . '/../../database/db.php';

// // 1. Kiểm tra đăng nhập
// if (!isset($_SESSION['user_id'])) {
//     header("Location: login.php");
//     exit();
// }

$user_id = $_SESSION['user_id'];
$user_data = [];
$error = '';

// 2. Truy vấn dữ liệu từ cả hai bảng 'nguoidung' và 'hosonguoigiaohang'
$sql = "SELECT 
            nd.ID_NguoiDung, nd.HoTen, nd.Email, nd.SoDienThoai, nd.AnhDaiDien, nd.VaiTro, nd.TrangThaiHoatDong, nd.NgayTao,
            hsg.GioiTinh, hsg.NamSinh, hsg.Anh_CCCD_Truoc, hsg.Anh_CCCD_Sau, hsg.Anh_BangLaiXe, hsg.BienSoXe, hsg.KhuVucHoatDong, hsg.DiemDanhGiaTrungBinh, hsg.loaixe
        FROM nguoidung nd
        LEFT JOIN hosonguoigiaohang hsg ON nd.ID_NguoiDung = hsg.ID_NguoiDung
        WHERE nd.ID_NguoiDung = ?";

$stmt = mysqli_prepare($conn, $sql);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) > 0) {
        $user_data = mysqli_fetch_assoc($result);
    } else {
        $error = "Không tìm thấy thông tin người dùng.";
    }
    mysqli_stmt_close($stmt);
} else {
    $error = "Lỗi truy vấn cơ sở dữ liệu: " . mysqli_error($conn);
}

// 3. Hàm kiểm tra và hiển thị dữ liệu
function display_field($label, $value, $emoji = '') {
    if (!empty($value) || (is_numeric($value) && $value !== 0)) {
        echo "<p><strong>{$emoji} {$label}:</strong> <span>" . htmlspecialchars($value) . "</span></p>";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title> Hồ sơ Cá nhân - <?php echo htmlspecialchars($user_data['HoTen'] ?? 'Người dùng'); ?></title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f9; color: #333; margin: 0; padding: 20px; }
        .container { max-width: 800px; margin: 20px auto; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h2 { border-bottom: 2px solid #007bff; padding-bottom: 10px; margin-bottom: 20px; color: #007bff; }
        h3 { color: #555; margin-top: 25px; border-left: 5px solid #ffc107; padding-left: 10px; }
        .profile-info p { margin: 5px 0; font-size: 16px; }
        .profile-info strong { display: inline-block; width: 150px; color: #333; }
        .avatar { text-align: center; margin-bottom: 20px; }
        .avatar img { width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 3px solid #007bff; }
        .avatar span { font-size: 80px; color: #ccc; } /* Style cho emoji avatar */
        .error { color: red; text-align: center; }
        .document-link { margin-top: 15px; display: flex; flex-wrap: wrap; gap: 15px; }
        .document-link a { 
            color: #28a745; text-decoration: none; padding: 8px 12px; border: 1px solid #28a745; 
            border-radius: 5px; transition: background-color 0.3s;
        }
        .document-link a:hover { background-color: #e6ffed; }
        .image-preview { 
            margin-top: 20px; border: 1px solid #ddd; padding: 10px; border-radius: 5px; 
            text-align: center; background-color: #f9f9f9;
        }
        .image-preview img { 
            max-width: 100%; height: auto; border-radius: 5px; margin-top: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .hidden { display: none; }
.container hr {
    border: 0;
    height: 1px;
    background: #e0e0e0; 
    margin: 30px 0 20px 0; 
    padding: 2px solid  #dc3545;
}
.profile-footer {
    text-align: center;
    padding: 10px 0;
    font-size: 15px;
}
.profile-footer a {
    color: #007bff; 
    text-decoration: none; 
    padding: 5px 10px;
    margin: 0 5px;
    border-radius: 4px;
    transition: all 0.3s ease;
    font-weight: 600;
}


.profile-footer a:hover {
    background-color: #e6f2ff; 
    color: #0056b3; 
}


.profile-footer a[href="logout.php"] {
    color: #dc3545;
    border: 1px solid #dc3545;
}

.profile-footer a[href="logout.php"]:hover {
    background-color: #dc3545;
    color: white;
}
    </style>
</head>
<body>

<div class="container">
    <?php if ($error): ?>
        <p class="error"><?php echo $error; ?></p>
    <?php else: ?>
        <h2> Thông Tin Hồ Sơ Cá Nhân</h2> 
        
        <div class="avatar">
            <?php 
            $avatar_file = $user_data['AnhDaiDien'] ?? 'default_avatar.png';
            $is_shipper = ($user_data['VaiTro'] === 'NguoiGiaoHang');
            
            if ($avatar_file === 'default_avatar.png'): 
            ?>
                <?php if ($is_shipper): ?>
                    <span style="font-size: 80px; color: #007bff;">🚚</span> 
                <?php else: ?>
                    <span style="font-size: 80px; color: #ccc;">👤</span> 
                <?php endif; ?>
            <?php else: ?>
                <img src="../uploads/shipper_documents/<?php echo htmlspecialchars($avatar_file); ?>" alt="Ảnh đại diện">
            <?php endif; ?>
            
            <p style="margin-top: 10px;">Vai trò: 
                <strong><?php echo htmlspecialchars($user_data['VaiTro']); ?></strong>
            </p>
        </div>

        <h3>📞 Thông tin liên hệ</h3>
        <div class="profile-info">
            <?php 
                echo "<p><strong>Họ Tên:</strong> <span>" . htmlspecialchars($user_data['HoTen']) . "</span></p>";
                echo "<p><strong>📧 Email:</strong> <span>" . htmlspecialchars($user_data['Email']) . "</span></p>";
                echo "<p><strong>📞 Số điện thoại:</strong> <span>" . htmlspecialchars($user_data['SoDienThoai']) . "</span></p>";
                display_field('Ngày tham gia', date('d/m/Y', strtotime($user_data['NgayTao'])), '🗓️');
            ?>
        </div>
        
        <?php if ($is_shipper): ?>
            <hr>
            <h3>🛵 Hồ sơ Người Giao Hàng</h3> 
            <div class="profile-info">
                <?php
                    display_field('Giới tính', $user_data['GioiTinh'], '🚻');
                    display_field('Năm sinh', $user_data['NamSinh'], '🎂');
                    display_field('Loại xe', $user_data['loaixe'], '🏍️');
                    display_field('Biển số xe', $user_data['BienSoXe'], '🔢');
                    display_field('Khu vực hoạt động', $user_data['KhuVucHoatDong'], '🗺️');
                    display_field('Đánh giá TB', $user_data['DiemDanhGiaTrungBinh'], '⭐'); 
                ?>
                
                <?php if (!empty($user_data['Anh_CCCD_Truoc']) || !empty($user_data['Anh_BangLaiXe'])): ?>
                    <h4 style="margin-top: 20px; color: #007bff;">📄 Tài liệu xác minh:</h4>
                    <div class="document-link">
                        <?php 
                        $upload_path = '../uploads/shipper_documents/';
                        if (!empty($user_data['Anh_CCCD_Truoc'])) {
                            echo '<a href="#" class="view-image-link" data-src="' . htmlspecialchars($upload_path . $user_data['Anh_CCCD_Truoc']) . '">Xem CCCD Mặt trước</a>';
                        }
                        if (!empty($user_data['Anh_CCCD_Sau'])) {
                            echo '<a href="#" class="view-image-link" data-src="' . htmlspecialchars($upload_path . $user_data['Anh_CCCD_Sau']) . '">Xem CCCD Mặt sau</a>';
                        }
                        if (!empty($user_data['Anh_BangLaiXe'])) {
                            echo '<a href="#" class="view-image-link" data-src="' . htmlspecialchars($upload_path . $user_data['Anh_BangLaiXe']) . '">Xem Bằng Lái Xe</a>';
                        }
                        ?>
                    </div>
                    <div id="imagePreviewContainer" class="image-preview hidden">
                        <p id="imagePreviewTitle"></p>
                        <img id="imagePreview" src="" alt="Ảnh xác minh">
                    </div>
                <?php endif; ?>

            </div>
        <?php endif; ?>

        <hr> <div class="profile-footer">
            <a href="profile.php">Chỉnh sửa hồ sơ</a> 
            <a href="hoso.php">Thoát</a>
        </div>

    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const viewImageLinks = document.querySelectorAll('.view-image-link');
    const imagePreviewContainer = document.getElementById('imagePreviewContainer');
    const imagePreview = document.getElementById('imagePreview');
    const imagePreviewTitle = document.getElementById('imagePreviewTitle');

    viewImageLinks.forEach(link => {
        link.addEventListener('click', function(event) {
            event.preventDefault(); // Ngăn chặn hành vi mặc định của thẻ a (chuyển trang)
            
            const imageUrl = this.dataset.src; // Lấy đường dẫn ảnh từ thuộc tính data-src
            const imageTitle = this.textContent; // Lấy tiêu đề từ nội dung thẻ a

            // Hiển thị container nếu đang ẩn
            if (imagePreviewContainer.classList.contains('hidden')) {
                imagePreviewContainer.classList.remove('hidden');
            }
            
            // Cập nhật nguồn ảnh và tiêu đề
            imagePreview.src = imageUrl;
            imagePreviewTitle.textContent = imageTitle;

            // Cuộn xuống vị trí ảnh để người dùng dễ nhìn thấy
            imagePreviewContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });
});
</script>

</body>
</html>