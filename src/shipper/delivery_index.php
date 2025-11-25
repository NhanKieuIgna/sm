<?php
session_start();
// Đảm bảo đường dẫn chính xác đến file kết nối
require_once __DIR__ . '/../database/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['vaitro'] !== 'NguoiGiaoHang') {
    header("Location: login.php"); 
    exit();
}

$driver_id = $_SESSION['user_id'];
$driver_name = "";
$total_orders = 0;
$total_revenue = 0.00; 

// Tiếp tục sử dụng $conn
$sql_profile = "SELECT HoTen FROM nguoidung WHERE ID_NguoiDung = $driver_id";
$res_profile = mysqli_query($conn, $sql_profile); // Sử dụng mysqli_query với $conn

if ($res_profile && mysqli_num_rows($res_profile) > 0) {
    $driver_data = mysqli_fetch_assoc($res_profile);
    $driver_name = $driver_data['HoTen'];
}

// Bổ sung logic thống kê và đơn hàng
// --- 2. LẤY THỐNG KÊ (Đơn hàng đang xử lý và Tiền COD cần thu) ---
$sql_stats = "SELECT 
                COUNT(ID_DonHang) AS total_orders,
                SUM(SoTienCanThu_COD) AS total_cod_amount
              FROM danhsachdonhang 
              WHERE ID_NguoiGiaoHang = $driver_id 
              AND TrangThaiDonHang IN ('ChoGiaoHang', 'DangVanChuyen')";

$res_stats = mysqli_query($conn, $sql_stats);
if ($res_stats && mysqli_num_rows($res_stats) > 0) {
    $stats = mysqli_fetch_assoc($res_stats);
    $total_orders = $stats['total_orders'];
    $total_revenue = number_format($stats['total_cod_amount'] ?: 0, 0, ',', '.'); 
}


// --- 3. LẤY DANH SÁCH ĐƠN HÀNG ĐANG CHỜ/ĐANG GIAO ---
$sql_orders = "SELECT 
                dh.ID_DonHang, dh.NgayDatHang, dh.DiaChiGiaoHang, dh.SoTienCanThu_COD,
                (SELECT SUM(SoLuongMua) FROM chitietdonhang WHERE ID_DonHang = dh.ID_DonHang) AS SoMonHang,
                ngm.HoTen AS TenNguoiMua
               FROM danhsachdonhang dh
               JOIN nguoidung ngm ON dh.ID_NguoiMua = ngm.ID_NguoiDung
               WHERE dh.ID_NguoiGiaoHang = $driver_id 
               AND dh.TrangThaiDonHang IN ('ChoGiaoHang', 'DangVanChuyen')
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
<!doctype html>
<html lang="vi">
<head>
 <meta charset="utf-8">
 <meta name="viewport" content="width=device-width, initial-scale=1">
 <title>Trang người giao hàng</title>
 <?php include "header_deli.php"; ?>
 <style>
    * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
    body { background-color: #f0f2f5; color: #333; }
    .app { display: flex; min-height: 100vh; }
    .sidebar { width: 260px; background-color: #fff; padding: 25px 20px; border-right: 1px solid #e0e0e0; }
    .brand .logo { font-size: 28px; font-weight: 700; color: #007bff; margin-bottom: 10px; }
    .profile { display: flex; gap: 15px; margin: 20px 0; }
    .profile .avatar { width: 55px; height: 55px; background-color: #ddd; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; color: #555; text-decoration: none; }
    .stat-row { display: flex; justify-content: space-between; margin-bottom: 20px; }
    .nav-item { display: block; text-decoration: none; color: #555; margin: 12px 0; padding: 10px 12px; border-radius: 8px; transition: all 0.3s; }
    .nav-item:hover { background-color: #f0f2f5; color: #007bff; }
    .main { flex: 1; padding: 25px; overflow-y: auto; }
    .topbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; }
    .topbar h2 { font-weight: 600; color: #333; }
    .search { display: flex; gap: 10px; margin-top: 10px; }
    .search input { padding: 8px 12px; border: 1px solid #ccc; border-radius: 6px; width: 220px; transition: border-color 0.3s; }
    .search input:focus { border-color: #007bff; outline: none; }
    .search button { padding: 8px 16px; border: none; border-radius: 6px; background-color: #007bff; color: #fff; cursor: pointer; transition: background 0.3s; }
    .search button:hover { background-color: #0056b3; }
    .card { background-color: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
    .card .orders { display: flex; flex-direction: column; gap: 15px; max-height: 600px; overflow-y: auto; padding-right: 5px; }
    .card .orders::-webkit-scrollbar { width: 8px; }
    .card .orders::-webkit-scrollbar-track { background: #f0f2f5; border-radius: 4px; }
    .card .orders::-webkit-scrollbar-thumb { background-color: #ccc; border-radius: 4px; }
    .card .orders::-webkit-scrollbar-thumb:hover { background-color: #999; }
    .order { display: flex; justify-content: space-between; align-items: center; padding: 18px 20px; background-color: #f9f9f9; border-radius: 10px; border: 1px solid #e0e0e0; transition: all 0.3s; }
    .order:hover { background-color: #e6f0ff; transform: translateY(-2px); box-shadow: 0 6px 12px rgba(0,0,0,0.08); }
    .order .badge { font-weight: 700; font-size: 14px; margin-bottom: 6px; color: #007bff; }
    .order h4 { margin-bottom: 6px; font-size: 16px; }
    .order .meta { color: #666; font-size: 13px; }
    .order .actions button { padding: 8px 16px; border-radius: 6px; border: none; cursor: pointer; margin-left: 8px; font-size: 14px; transition: all 0.3s; }
    .btn-primary { background-color: #28a745; color: #fff; }
    .btn-primary:hover { background-color: #218838; }
    .btn-ghost { background-color: transparent; color: #007bff; border: 1px solid #007bff; }
    .btn-ghost:hover { background-color: #007bff; color: #fff; }
    @media (max-width: 768px) {
    .app { flex-direction: column; }
    .sidebar { width: 100%; border-right: none; border-bottom: 1px solid #e0e0e0; }
    .topbar { flex-direction: column; align-items: flex-start; }
    .search input { width: 100%; }
    }
    .pill {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 12px;
        background-color: #e9ecef;
        color: #495057;
    }
 </style>
</head>
<body>
 <div class="app">
 <aside class="sidebar">
 <div class="brand">
 <div class="logo">SM</div>
 <div>
 <div style="font-weight:700">DriverDash</div>
 <div class="small">Phiên bản giao hàng</div>
 </div>
 </div>

 <div class="profile">
 <div class="avatar"><a href="profile.php" style="color: inherit;">
            <?php echo strtoupper(substr($driver_name, 0, 1) ?: 'TK'); ?>
        </a></div>
 <div>
 <div style="font-weight:700"><?php echo htmlspecialchars($driver_name); ?></div>
 <div class="small">ID: G<?php echo $driver_id; ?> • Online</div>
 <div style="margin-top:8px">
 <span class="pill">Trạng thái: <strong>Sẵn sàng</strong></span>
 </div>
 </div>
 </div>

 <div class="stat-row">
 <div class="stat">
 <div class="small">Đơn hàng đang giao</div>
 <div style="font-weight:700;font-size:18px"><?php echo $total_orders; ?></div>
 </div>
 <div class="stat">
 <div class="small">Tiền COD cần thu</div>
 <div style="font-weight:700;font-size:18px">₫<?php echo $total_revenue; ?></div>
 </div>
 </div>

 <nav>
 <a class="nav-item" href="delivery_index.php">🏠 Tổng quan</a>
 <a class="nav-item" href="my_donhang.php">📦 Đơn hàng của bạn</a>
 <a class="nav-item" href="Thongke.php">💰 Thu nhập</a>
 <a class="nav-item" href="ls_giaohang.php">📜 Lịch sử</a>
        <a class="nav-item" href="logout.php">➡️ Đăng xuất</a>
 </nav>
 </aside>

 <main class="main">
 <div class="topbar">
 <h2>Danh sách đơn hàng cần xử lý</h2>
 <form class="search" method="GET">
 <input type="text" name="search_order" placeholder="Tìm đơn hàng...">
 <button type="submit" class="btn btn-ghost">Tìm</button>
 </form>
 </div>

 <div class="card">
 <div style="font-weight:700;margin-bottom:12px">Đơn hàng đang chờ</div>
 <div class="orders">
 
            <?php if (empty($orders_list)): ?>
                <div style="text-align: center; padding: 20px; color: #6c757d;">
                    Không có đơn hàng nào cần bạn xử lý lúc này.
                </div>
            <?php else: ?>
                <?php foreach ($orders_list as $order): ?>
                <div class="order">
                    <div>
                        <div class="badge">#DH<?php echo htmlspecialchars($order['ID_DonHang']); ?></div>
                        <h4>Giao cho: <?php echo htmlspecialchars($order['TenNguoiMua']); ?></h4>
                        <div class="meta">
                            <?php echo $order['SoMonHang']; ?> món • 
                            COD: ₫<?php echo number_format($order['SoTienCanThu_COD'], 0, ',', '.'); ?> • 
                            <?php echo getDistance($order['ID_DonHang']); ?> km
                        </div>
                    </div>
                    <div class="actions">
                        <form method="POST" action="handle_delivery.php" style="display:inline;">
                             <input type="hidden" name="order_id" value="<?php echo $order['ID_DonHang']; ?>">
                             <button type="submit" name="action" value="start_delivery" class="btn btn-primary">Bắt đầu giao</button>
                        </form>
                        <a href="map_direction.php?order=<?php echo $order['ID_DonHang']; ?>" class="btn btn-ghost">Chỉ đường</a>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>

 </div>
 </div>
 </main>
 </div>
 </body>
 <?php include "footer_deli.php"; ?>
</html>