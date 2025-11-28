<?php
session_start();
require "sm/dp.php"; 

if (!isset($_SESSION['user_id']) || $_SESSION['vaitro'] !== 'NguoiGiaoHang') {
    header("Location: login.php"); 
    exit();
}

$driver_id = $_SESSION['user_id'];
$driver_name = "";
$driver_avatar = "";
$driver_region = ""; 
$total_orders = 0;
$total_cod_amount = 0.00;
$sql_profile = "SELECT 
                    nd.HoTen, nd.AnhDaiDien, hgh.KhuVucHoatDong 
                FROM nguoidung nd
                JOIN hosonguoigiaohang hgh ON nd.ID_NguoiDung = hgh.ID_NguoiDung
                WHERE nd.ID_NguoiDung = $driver_id";
$res_profile = mysqli_query($conn, $sql_profile);

if ($res_profile && mysqli_num_rows($res_profile) > 0) {
    $driver_data = mysqli_fetch_assoc($res_profile);
    $driver_name = $driver_data['HoTen'];
    $driver_avatar = $driver_data['AnhDaiDien'];
    $driver_region = $driver_data['KhuVucHoatDong']; 
} else {
    $driver_name = $_SESSION['ten'] ?? 'Tài xế ẩn danh';
    $driver_region = 'Chưa xác định'; 
}
$sql_stats = "SELECT 
                COUNT(ID_DonHang) AS total_orders,
                SUM(SoTienCanThu_COD) AS total_cod_amount
              FROM danhsachdonhang 
              WHERE ID_NguoiGiaoHang = $driver_id 
              AND TrangThaiDonHang IN ('DangVanChuyen')";
$res_stats = mysqli_query($conn, $sql_stats);

if ($res_stats && mysqli_num_rows($res_stats) > 0) {
    $stats = mysqli_fetch_assoc($res_stats);
    $total_orders = $stats['total_orders'];
    $total_cod_amount = $stats['total_cod_amount'];
}
$total_revenue = number_format($total_cod_amount ?: 0, 0, ',', '.');
$driver_region_safe = mysqli_real_escape_string($conn, $driver_region);

$sql_orders = "SELECT 
    dh.ID_DonHang, dh.NgayDatHang, dh.DiaChiGiaoHang, dh.SoTienCanThu_COD, dh.TrangThaiDonHang,
    ngm.HoTen AS TenNguoiMua, ngm.SoDienThoai AS SDTNguoiMua,
    (SELECT SUM(SoLuongMua) FROM chitietdonhang WHERE ID_DonHang = dh.ID_DonHang) AS SoMonHang,
    (SELECT sp.TenSanPham FROM chitietdonhang ct 
     JOIN sanpham sp ON ct.ID_SanPham = sp.ID_SanPham 
     WHERE ct.ID_DonHang = dh.ID_DonHang LIMIT 1) AS TenSanPhamDauTien
    
    FROM danhsachdonhang dh
    JOIN nguoidung ngm ON dh.ID_NguoiMua = ngm.ID_NguoiDung
    WHERE dh.ID_NguoiGiaoHang IS NULL 
    AND dh.TrangThaiDonHang IN ('ChoXacNhan', 'ChoGiaoHang') 
    AND dh.DiaChiGiaoHang LIKE '%" . $driver_region_safe . "%' 

    ORDER BY dh.NgayDatHang DESC";
    
$res_orders = mysqli_query($conn, $sql_orders);
$orders_list = [];
if ($res_orders) {
    while ($row = mysqli_fetch_assoc($res_orders)) {
        $orders_list[] = $row;
    }
}
function getDistance($order_id) {
    return rand(10, 50) / 10; 
}
?>

<!doctype html>
<html lang="vi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Trang Người Giao Hàng - <?php echo htmlspecialchars($driver_region); ?></title>
<?php  include "header_deli.php"; ?>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
body { background-color: #f0f2f5; color: #333; }


.app { display: flex; min-height: 100vh; }
.sidebar { width: 28%; background-color: #fff; padding: 25px 20px; border-right: 1px solid #e0e0e0; }
.main { flex: 1; padding: 25px; overflow-y: auto; }
.brand .logo { font-size: 28px; font-weight: 700; color: #007bff; margin-bottom: 10px; }
.profile { display: flex; gap: 15px; margin: 20px 0; flex-direction: column; align-items: center; text-align: center; } 
.profile .avatar img { width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 3px solid #007bff; padding: 3px; }
.avatar-initial { display: flex; justify-content: center; align-items: center; width: 120px; height: 120px; border-radius: 50%; background-color: #007bff; color: white; font-size: 40px; font-weight: 700; }
.stat-row { display: flex; justify-content: space-between; margin-bottom: 20px; }
.pill { display: inline-block; padding: 4px 8px; border-radius: 12px; font-size: 12px; background-color: #e9ecef; color: #495057; }
.nav-item { display: block; text-decoration: none; color: #555; margin: 12px 0; padding: 10px 12px; border-radius: 8px; transition: all 0.3s; }
.nav-item:hover { background-color: #f0f2f5; color: #007bff; }
.topbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; }
.topbar h2 { font-weight: 600; color: #333; }
.search { display: flex; gap: 10px; margin-top: 10px; }
.search input { padding: 8px 12px; border: 1px solid #ccc; border-radius: 6-px; width: 220px; }
.search button { padding: 8px 16px; border: none; border-radius: 6px; background-color: #007bff; color: #fff; cursor: pointer; transition: background 0.3s; }
.search button:hover { background-color: #0056b3; }
.card { background-color: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
.card .orders { display: flex; flex-direction: column; gap: 15px; max-height: 600px; overflow-y: auto; padding-right: 5px; }
.order { display: flex; justify-content: space-between; align-items: center; padding: 18px 20px; background-color: #f9f9f9; border-radius: 10px; border: 1px solid #e0e0e0; flex-wrap: wrap; }
.order:hover { background-color: #e6f0ff; transform: translateY(-2px); box-shadow: 0 6px 12px rgba(0,0,0,0.08); }
.order .info-main { flex: 1 1 60%; min-width: 250px; } 
.order .actions { flex: 0 0 auto; margin-top: 5px; } 
.order .badge { font-weight: 700; font-size: 14px; margin-bottom: 6px; color: #007bff; }
.order h4 { margin-bottom: 6px; font-size: 16px; }
.order .meta { color: #666; font-size: 13px; line-height: 1.6; }
.btn-primary { background-color: #28a745; color: #fff; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; }
.btn-primary:hover { background-color: #218838; }
.btn-ghost { background-color: transparent; color: #007bff; border: 1px solid #007bff; padding: 8px 16px; border-radius: 6px; text-decoration: none; margin-left: 8px; }
.btn-ghost:hover { background-color: #007bff; color: #fff; }

.notification { padding: 15px; margin: 20px 25px 0 25px; border-radius: 8px; font-weight: bold; text-align: center; }
.notification.success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
.notification.error, .notification.warning { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

@media (max-width: 768px) {
    .app { flex-direction: column; }
    .sidebar { width: 100%; }
    .notification { margin: 10px; }
}
</style>

</head>
<body>

<?php 
if (isset($_SESSION['message'])): 
?>
    <div class="notification <?php echo htmlspecialchars($_SESSION['message_type'] ?? 'info'); ?>">
        <?php echo $_SESSION['message']; ?>
    </div>
<?php 

    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
endif;
?>

<div class="app">
    <aside class="sidebar">
        <div class="brand">
            <div class="logo">SM</div>
            <div>
                <div style="font-weight:700">Delivery-Secondhand Market</div>
                <div class="small">Phiên bản giao hàng</div>
            </div>
        </div>

        <div class="profile">
            <div class="avatar">
                <a href="hoso.php" style="color: inherit; text-decoration: none;">
                    <?php 
                    if (!empty($driver_avatar) && file_exists('sm/uploads/' . $driver_avatar)): ?>
                        <img src="sm/uploads/<?php echo htmlspecialchars($driver_avatar); ?>" alt="Avatar">
                    <?php else: ?>
                        <span class="avatar-initial"><?php echo strtoupper(substr($driver_name, 0, 1) ?: 'T'); ?></span>
                    <?php endif; ?>
                </a>
            </div>

            <div style="font-weight:700; margin-top:5px;">
                <?php echo htmlspecialchars($driver_name); ?>
            </div>
            <div class="small">ID: G<?php echo $driver_id; ?> • Online</div>
            <div style="margin-top:8px;">
                <span class="pill">Khu vực: <strong><?php echo htmlspecialchars($driver_region); ?></strong></span>
                <span class="pill">Trạng thái: <strong>Sẵn sàng</strong></span>
            </div>
        </div>

        <div class="stat-row">
            <div class="stat">
                <div class="small">Đơn hàng đang giao</div>
                <div style="font-weight:700;font-size:18px"><?php echo $total_orders; ?></div>
            </div>
            <div class="stat">
                <div class="small">COD cần thu</div>
                <div style="font-weight:700;font-size:18px">₫<?php echo $total_revenue; ?></div>
            </div>
        </div>

        <nav>
            <a class="nav-item" href="delivery_index.php">🏠 Tổng quan</a>
            <a class="nav-item" href="my_donhang.php">📦 Đơn hàng của bạn</a>
            <a class="nav-item" href="Thongke.php">💰 Thu nhập</a>
            <a class="nav-item" href="ls_giaohang.php">📜 Lịch sử</a>
            <a class="nav-item" href="logout.php" style="color: #dc3545;">➡️ Đăng xuất</a>
        </nav>
    </aside>

    <main class="main">
        <!-- <div class="topbar">
            <h2>Đơn hàng cần xử lý tại Khu vực: <?php echo htmlspecialchars($driver_region); ?></h2>
            <form class="search" method="GET">
                <input type="text" name="search_order" placeholder="Tìm đơn hàng...">
                <button type="submit">Tìm</button>
            </form>
        </div> -->

        <div class="card">
            <div style="font-weight:700;margin-bottom:12px">Đơn hàng CHƯA CÓ NGƯỜI NHẬN (<?php echo count($orders_list); ?>)</div>
            <div class="orders">
                <?php if (empty($orders_list)): ?>
                    <div style="text-align: center; padding: 20px; color: #6c757d;">
                        Không có đơn hàng nào đang chờ nhận trong khu vực **<?php echo htmlspecialchars($driver_region); ?>**.
                    </div>
                <?php else: ?>
                    <?php foreach ($orders_list as $order): ?>
                        <div class="order">
                            <div class="info-main">
                                <div class="badge">#DH<?php echo htmlspecialchars($order['ID_DonHang']); ?></div>
                                <h4>Giao cho: <?php echo htmlspecialchars($order['TenNguoiMua']); ?></h4>
                                <div class="meta">
                                    🛒 **<?php echo htmlspecialchars($order['TenSanPhamDauTien'] ?? 'Sản phẩm'); ?>** (<?php echo $order['SoMonHang']; ?> món) <br>
                                    ☎️ SĐT: <a href="tel:<?php echo htmlspecialchars($order['SDTNguoiMua']); ?>"><?php echo htmlspecialchars($order['SDTNguoiMua']); ?></a> <br>
                                    📍 Địa chỉ: <?php echo htmlspecialchars(substr($order['DiaChiGiaoHang'], 0, 50)) . '...'; ?> <br>
                                    💵 **COD: ₫<?php echo number_format($order['SoTienCanThu_COD'], 0, ',', '.'); ?>** • Cách bạn: <?php echo getDistance($order['ID_DonHang']); ?> km
                                </div>
                            </div>
                            <div class="actions">
                                <form method="POST" action="handle_nd.php" style="display:inline;">
                                    <input type="hidden" name="order_id" value="<?php echo $order['ID_DonHang']; ?>">
                                    <button type="submit" name="action" value="start_delivery" class="btn btn-primary">Nhận đơn</button>
                                </form>
                                <a href="chi_tiet_don.php?order=<?php echo $order['ID_DonHang']; ?>" class="btn btn-ghost">Chi Tiết</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<?php  include "footer_deli.php"; ?>
</body>
</html>
