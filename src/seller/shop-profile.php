
<?php 
    require_once 'header.php';
    require_once __DIR__ . '/../../database/db.php'; 
    $page = 'shop-profile';

    if (!isset($_SESSION['user_id'])) {
        echo "<script>window.location.href='../login.php';</script>"; 
        exit();
    }
    
    $user_id = $_SESSION['user_id'];

    $sql = "SELECT * FROM nguoidung WHERE ID_NguoiDung = '$user_id'";
    $result = mysqli_query($conn, $sql);
    $user = mysqli_fetch_assoc($result);
    // Hiện ảnh mặc định nếu chưa có ảnh đại diện
    $avatar_url = !empty($user['AnhDaiDien']) ? $user['AnhDaiDien'] : "https://cdn-icons-png.flaticon.com/512/149/149071.png";
?>

<style>
    body { background-color: #f5f5f5; }
</style>

<div style="display: flex; min-height: 100vh;">
    <?php include 'sidebar.php'; ?>
    
    <div style="flex: 1; display: flex; flex-direction: column;">
        
        <h2 style="padding: 20px 20px 0; font-size: 24px; color: #333;">Hồ sơ Shop</h2>

        <div class="profile-card">
            
            <div class="profile-left">
                <div class="avatar-frame">
                    <img src="<?php echo htmlspecialchars($avatar_url); ?>" alt="Avatar Shop">
                </div>
                <h3 style="margin-bottom: 10px;"><?php echo htmlspecialchars($user['HoTen']); ?></h3>
                <p style="margin-top: 15px; color: #666; font-size: 13px;">
                    Tham gia ngày: <?php echo date('d/m/Y', strtotime($user['NgayTao'])); ?>
                </p>
            </div>

            <div class="profile-right">
                <div class="info-group">
                    <div class="info-label">Mã Shop (ID)</div>
                    <div class="info-value">#<?php echo $user['ID_NguoiDung']; ?></div>
                </div>

                <div class="info-group">
                    <div class="info-label">Tên hiển thị</div>
                    <div class="info-value"><?php echo htmlspecialchars($user['HoTen']); ?></div>
                </div>

                <div class="info-group">
                    <div class="info-label">Email</div>
                    <div class="info-value"><?php echo htmlspecialchars($user['Email']); ?></div>
                </div>

                <div class="info-group">
                    <div class="info-label">Số điện thoại</div>
                    <div class="info-value"><?php echo htmlspecialchars($user['SoDienThoai']); ?></div>
                </div>

                <div class="info-group">
                    <div class="info-label">Địa chỉ lấy hàng mặc định</div>
                    <div class="info-value">
                        <?php 
                            if (!empty($user['DiaChiGiaoHangMacDinh'])) {
                                echo htmlspecialchars($user['DiaChiGiaoHangMacDinh']);
                            } else {
                                echo "<span style='color: #999; font-style: italic;'>Chưa cập nhật địa chỉ</span>";
                            }
                        ?>
                    </div>
                </div>

                <div style="margin-top: 30px;">
                    <a href="../user/edit-profile.php" class="btn-edit-profile">
                        <i class="fa-solid fa-pen-to-square"></i> Chỉnh sửa hồ sơ
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>