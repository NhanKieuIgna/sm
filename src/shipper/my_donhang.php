<?php

session_start();
require_once __DIR__ . '/../../database/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'NguoiGiaoHang') {
    header("Location: ../login.php"); 
    exit();
}

$driver_id = $_SESSION['user_id'];
$orders = []; 
$sql = "SELECT 
            DH.ID_DonHang, DH.DiaChiGiaoHang, DH.TrangThaiDonHang,
            N.HoTen AS TenKhachHang, 
            DH.TongGiaTriDonHang, DH.SoTienCanThu_COD
        FROM danhsachdonhang AS DH
        LEFT JOIN nguoidung AS N ON DH.ID_NguoiMua = N.ID_NguoiDung
        WHERE DH.ID_NguoiGiaoHang = ?
        ORDER BY FIELD(DH.TrangThaiDonHang, 'DangVanChuyen', 'ChoGiaoHang', 'DangXuLy', 'DaGiao', 'HoanThanh', 'DaHuy') ASC,
                 DH.NgayDatHang DESC";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $driver_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        
        // --- TÍNH TOÁN PHÍ GIAO HÀNG (5% Tổng Giá Trị) ---
        $tong_gia_tri = (float)$row['TongGiaTriDonHang'];
        $phi_giao_hang = $tong_gia_tri * 0.05;
        // -----------------------------------------------------

        $orders[] = [
            "id" => "DH - " . str_pad($row['ID_DonHang'], 5, '0', STR_PAD_LEFT), 
            "customer" => htmlspecialchars($row['TenKhachHang'] ?? 'Khách hàng ẩn danh'),
            "address" => htmlspecialchars($row['DiaChiGiaoHang']),
            
            "total_value" => number_format($row['TongGiaTriDonHang'], 0, ',', '.') . '₫', 
            "status" => htmlspecialchars($row['TrangThaiDonHang']),
            "raw_id" => $row['ID_DonHang'] ,
            // Số tiền cần thu (COD) đã format
            "pgh" => number_format($row['SoTienCanThu_COD'], 0, ',', '.') . '₫',
            
            // Phí Giao Hàng đã format
            "shipping_fee" => number_format($phi_giao_hang, 0, ',', '.') . '₫' 
        ];
    }
}
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Danh sách đơn hàng</title>
    </head>
<style>

.order.da-giao .status, .order.hoan-thanh .status {
    color: #007bff; 
}
.order.dang-van-chuyen .status {
    color: #ff9800;
}
.order.cho-giao-hang .status {
    color: #dc3545; 
}
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #f0f2f5;
    margin: 0;
    line-height: 1.5;
}

.orders-page {
    display: flex;
    min-height: 100vh;
}
.sidebar {
    width: 250px;
    background-color: #fff;
    padding: 20px;
    border-right: 1px solid #ddd;
    box-shadow: 2px 0 5px rgba(0,0,0,0.05);
}

.brand {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 30px;
    padding-left: 10px;
}

.sidebar .logo {
    font-size: 28px;
    font-weight: 800;
    color: #007bff;
}

.small {
    font-size: 12px;
    color: #888;
}

.nav-item {
    display: flex;
    align-items: center;
    padding: 12px 15px;
    margin-bottom: 8px;
    color: #333;
    text-decoration: none;
    border-radius: 8px;
    transition: all 0.3s;
    font-size: 15px;
    font-weight: 500;
}

.nav-item:hover, .nav-item.active {
    background-color: #e6f0ff;
    color: #007bff;
}
.main {
    flex: 1;
    padding: 25px 30px;
}

h2 {
    margin-bottom: 25px;
    color: #333;
    font-weight: 600;
}
.card {
    background-color: #fff;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.orders {
    display: flex;
    flex-direction: column;
    gap: 15px;
    max-height: 70vh;
    overflow-y: auto;
    padding-right: 5px;
}

.orders::-webkit-scrollbar {
    width: 8px;
}
.orders::-webkit-scrollbar-track {
    background: #f0f2f5;
    border-radius: 4px;
}
.orders::-webkit-scrollbar-thumb {
    background-color: #ccc;
    border-radius: 4px;
}
.orders::-webkit-scrollbar-thumb:hover {
    background-color: #999;
}

.order {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 18px 20px;
    background-color: #f9f9f9;
    border-left: 5px solid #007bff; 
    transition: all 0.3s;
}

.order:hover {
    background-color: #e6f0ff;
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(0,0,0,0.08);
}

.info {
    flex-grow: 1;
}

.badge {
    font-weight: 700;
    color: #666;
    margin-bottom: 4px;
    font-size: 12px;
}

.order h4 {
    margin: 0 0 4px 0;
    font-size: 16px;
    color: #222;
}

.order p {
    margin: 0 0 4px 0;
    font-size: 14px;
    color: #555;
}

.meta {
    font-size: 13px;
    color: #888;
    margin-top: 5px;
}

.status {
    font-weight: 700;
    padding: 3px 8px;
    border-radius: 4px;
    display: inline-block;
    font-size: 12px;
    color: white;
}
.cho-giao-hang, .dang-xu-ly {
    border-left-color: #ffc107;
    background-color: #fffbe6;
}
.cho-giao-hang .status, .dang-xu-ly .status {
    background-color: #ffc107;
    color: #333;
}

.dang-van-chuyen {
    border-left-color: #007bff;
}
.dang-van-chuyen .status {
    background-color: #007bff;
}

.da-giao {
    border-left-color: #28a745;
}
.da-giao .status {
    background-color: #28a745;
}

.hoan-thanh {
    border-left-color: #17a2b8;
}
.hoan-thanh .status {
    background-color: #17a2b8;
}

.da-huy, .khieu-nai {
    border-left-color: #dc3545;
    opacity: 0.7;
}
.da-huy .status, .khieu-nai .status {
    background-color: #dc3545;
}

.actions {
    margin-left: 20px;
    white-space: nowrap; }

.actions .btn-ghost {
    padding: 8px 14px;
    border-radius: 6px;
    border: 1px solid #007bff;
    background-color: transparent;
    color: #007bff;
    text-decoration: none;
    font-size: 14px;
    font-weight: 600;
    transition: all 0.3s;
}

.actions .btn-ghost:hover {
    background-color: #007bff;
    color: #fff;
    box-shadow: 0 2px 6px rgba(0, 123, 255, 0.4);
}

@media (max-width: 768px) {
    .orders-page {
        flex-direction: column;
    }
    .sidebar {
        width: 100%;
        border-right: none;
        border-bottom: 1px solid #ddd;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }
    .main {
        padding: 20px 15px;
    }
    .order {
        flex-direction: column;
        align-items: flex-start;
    }
    .actions {
        margin-top: 10px;
        margin-left: 0;
        width: 100%;
    }
    .actions .btn-ghost {
        width: 100%;
        text-align: center;
    }
}
</style>

<body>
<?php  include "header_deli.php"; ?>

<div class="orders-page">
    <aside class="sidebar">
        <div class="brand">
            <div class="logo">SM</div>
            <div>
                <div style="font-weight:700">Trang chủ</div>
                <div class="small">Phiên bản giao hàng</div>
            </div>
        </div>
        <nav>
            <a class="nav-item" href="delivery_index.php">🏠 Tổng quan</a>
            <a class="nav-item" href="my_donhang.php">📦 Đơn hàng</a>
            <a class="nav-item" href="Thongke.php">💰 Thu nhập</a>
            <a class="nav-item" href="ls_giaohang.php">📜 Lịch sử</a>
        </nav>
    </aside>

    <main class="main">
        <h2>Danh sách đơn hàng của bạn</h2>

        <div class="card">
            <div class="orders">
                <?php if (empty($orders)): ?>
                    <p style="text-align: center; color: #666; padding: 20px;">🎉 Bạn chưa có đơn hàng nào được giao!</p>
                <?php endif; ?>
                
                <?php foreach($orders as $order): ?>
                <div class="order <?php echo strtolower(str_replace(" ", "-", $order['status'])); ?>">
                    <div class="info">
                        <div class="badge">Mã đơn: <?php echo $order['id']; ?></div>
                        <h4>Khách: <?php echo $order['customer']; ?></h4>
                        <p>Địa chỉ giao hàng: <?php echo $order['address']; ?></p>
                        
                                                <p style="font-weight: 600; color: #007bff;">Phí Ship:  <?php echo $order['shipping_fee']; ?></p>
                        <p style="font-weight: 600; color: #007bff;">Số tiền cần thu (COD): <?php echo $order['pgh']; ?></p>
                        
                        <div class="meta">
                            Trạng thái: <span class="status" style="color: #222;"><?php echo $order['status']; ?></span>
                        </div>
                    </div>
                    <div class="actions" style="display: flex;flex-direction: column;gap: 8px;">
                        <a href="chi_tiet_don.php?order=<?php echo $order['raw_id']; ?>" class="btn btn-ghost">Xem chi tiết</a>
                        <?php $status = $order['status']; ?>
                        <?php if ($status === 'DangVanChuyen') { ?>
                            <a href="confirm_GH.php?id=<?php echo $order['raw_id']; ?>" class="btn btn-ghost">Xác nhận Hoàn thành</a>
                        <?php } ?>
                        <?php if ($status === 'DangVanChuyen') { ?>
                            <a href="HuyGiaoHang.php?id=<?php echo $order['raw_id']; ?>" class="btn btn-ghost">Hủy Đơn</a>
                        <?php } ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>
</div>

<?php  include "footer_deli.php"; ?>
</body>
</html>

