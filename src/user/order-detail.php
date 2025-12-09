<?php
session_start();
require_once __DIR__ . '/../../database/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = intval($_GET['id'] ?? 0);

if ($order_id <= 0) {
    header('Location: my-orders.php');
    exit();
}

// Get order details
$order_query = "SELECT 
                    dh.*,
                    ng.HoTen AS TenNguoiMua,
                    ng.SoDienThoai AS SoDienThoaiNguoiMua,
                    ng.Email AS EmailNguoiMua
                 FROM danhsachdonhang dh
                 JOIN nguoidung ng ON dh.ID_NguoiMua = ng.ID_NguoiDung
                 WHERE dh.ID_DonHang = $order_id AND dh.ID_NguoiMua = $user_id";

$order_result = mysqli_query($conn, $order_query);
$order = mysqli_fetch_assoc($order_result);

if (!$order) {
    header('Location: my-orders.php');
    exit();
}

// Get order items
$items_query = "SELECT 
                    ctdh.*,
                    sp.TenSanPham,
                    ha.URL_HinhAnh,
                    sp.MoTa
                 FROM chitietdonhang ctdh
                 JOIN sanpham sp ON ctdh.ID_SanPham = sp.ID_SanPham
                 LEFT JOIN hinhanhsanpham ha ON sp.ID_SanPham = ha.ID_SanPham
                 WHERE ctdh.ID_DonHang = $order_id";

$items_result = mysqli_query($conn, $items_query);
$items = [];
if ($items_result) {
    while ($row = mysqli_fetch_assoc($items_result)) {
        $items[] = $row;
    }
}

// Status labels
$status_labels = [
    'ChoXacNhan' => 'Chờ xác nhận',
    'ChoGiaoHang' => 'Chờ giao hàng',
    'DangVanChuyen' => 'Đang giao',
    'HoanThanh' => 'Hoàn thành',
    'DaHuy' => 'Đã hủy'
];

// Payment method labels
$payment_methods = [
    'cod' => 'Thanh toán khi nhận hàng',
    'bank_transfer' => 'Chuyển khoản ngân hàng',
    'e_wallet' => 'Ví điện tử'
];

$status_classes = [
    'ChoXacNhan' => 'status-pending',
    'ChoGiaoHang' => 'status-waiting',
    'DangVanChuyen' => 'status-delivering',
    'HoanThanh' => 'status-completed',
    'DaHuy' => 'status-cancelled'
];
// Lấy danh mục
$sql_categories = "SELECT * FROM danhmuc ORDER BY TenDanhMuc";
$result_categories = mysqli_query($conn, $sql_categories);
$categories = [];
if ($result_categories) {
    while ($row = mysqli_fetch_assoc($result_categories)) {
        $categories[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết đơn hàng #DH<?php echo str_pad($order_id, 6, '0', STR_PAD_LEFT); ?> - SM</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/my-orders.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
 <?php include 'header.php'; ?>
    <main class="order-detail-page">
        <div class="breadcrumb">
            <a href="../index.php">Trang chủ</a> <span> > </span> 
            <a href="my-orders.php">Đơn hàng của tôi</a> <span> > </span> 
            <strong>Chi tiết đơn hàng</strong>
        </div>

        <div class="order-detail-content">
            <div class="detail-left">
                <h1 class="page-title">Thông tin đơn hàng</h1>

                <div class="detail-card">
                    <div class="detail-row">
                        <div class="detail-label">Mã đơn:</div>
                        <div class="detail-value">#DH<?php echo str_pad($order_id, 6, '0', STR_PAD_LEFT); ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Ngày đặt:</div>
                        <div class="detail-value"><?php echo date('d/m/Y H:i', strtotime($order['NgayDatHang'])); ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Phương thức thanh toán:</div>
                        <div class="detail-value">Thanh toán khi nhận hàng (COD)</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Địa chỉ giao hàng:</div>
                        <div class="detail-value"><?php echo htmlspecialchars($order['DiaChiGiaoHang']); ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Trạng thái giao hàng:</div>
                        <div class="detail-value">
                            <span class="order-status <?php echo $status_classes[$order['TrangThaiDonHang']] ?? ''; ?>">
                                <?php echo $status_labels[$order['TrangThaiDonHang']] ?? $order['TrangThaiDonHang']; ?>
                            </span>
                        </div>
                    </div>
                    <?php if (!empty($order['GhiChu'])): ?>
                    <div class="detail-row">
                        <div class="detail-label">Ghi chú:</div>
                        <div class="detail-value"><?php echo htmlspecialchars($order['GhiChu']); ?></div>
                    </div>
                    <?php endif; ?>
                </div>

                <h2 class="section-title">Sản phẩm trong đơn hàng</h2>
                <div class="order-items-list">
                    <?php foreach ($items as $item): ?>
                        <div class="order-item-detail">
                            <?php if (!empty($item['URL_HinhAnh'])): ?>
                                <img src="<?php echo "../" ?><?php echo htmlspecialchars($item['URL_HinhAnh']); ?>" 
                                     alt="<?php echo htmlspecialchars($item['TenSanPham']); ?>" 
                                     class="item-image">
                            <?php else: ?>
                                <div class="item-image-placeholder">
                                    <i class="fas fa-image"></i>
                                </div>
                            <?php endif; ?>
                            <div class="item-details">
                                <div class="item-name"><?php echo htmlspecialchars($item['TenSanPham']); ?></div>
                                <div class="item-meta">
                                    Số lượng: <?php echo $item['SoLuongMua']; ?> | 
                                    Giá: <?php echo number_format($item['GiaTaiThoiDiemDat'], 0, ',', '.'); ?>₫
                                </div>
                            </div>
                            <div class="item-total">
                                <?php echo number_format($item['GiaTaiThoiDiemDat'] * $item['SoLuongMua'], 0, ',', '.'); ?>₫
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <aside class="detail-actions">
                <div class="action-card">
                    <h3>Tổng tiền</h3>
                    <div class="total-amount">
                        <?php echo number_format($order['SoTienCanThu_COD'], 0, ',', '.'); ?>₫
                    </div>

                    <div class="action-buttons">
                        <?php if ($order['TrangThaiDonHang'] === 'ChoXacNhan' || $order['TrangThaiDonHang'] === 'ChoGiaoHang'): ?>
                            <a href="my-orders.php?cancel=<?php echo $order_id; ?>" 
                               class="btn btn-danger btn-block"
                               onclick="return confirm('Bạn có chắc chắn muốn hủy đơn hàng này?');">
                                <i class="fas fa-times"></i>
                                Hủy đơn hàng
                            </a>
                        <?php endif; ?>

                        <?php if ($order['TrangThaiDonHang'] === 'DangVanChuyen' || $order['TrangThaiDonHang'] === 'ChoGiaoHang'): ?>
                            <a href="#" class="btn btn-secondary btn-block">
                                <i class="fas fa-comments"></i>
                                Liên hệ người bán
                            </a>
                        <?php endif; ?>

                        <?php if ($order['TrangThaiDonHang'] === 'HoanThanh'): ?>
                            <a href="review.php?order_id=<?php echo $order_id; ?>" class="btn btn-primary btn-block">
                                <i class="fas fa-star"></i>
                                Gửi đánh giá
                            </a>
                        <?php endif; ?>

                        <a href="my-orders.php" class="btn btn-outline btn-block">
                            <i class="fas fa-arrow-left"></i>
                            Quay lại danh sách
                        </a>
                    </div>
                </div>
            </aside>
        </div>
    </main>
</body>
</html>

