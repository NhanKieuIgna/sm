<?php
session_start();
require_once __DIR__ . '/../../database/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Check if user is a buyer
$user_id = $_SESSION['user_id'];
$query_user = "SELECT * FROM nguoidung WHERE ID_NguoiDung = '$user_id'";
$result_user = mysqli_query($conn, $query_user);
$user = mysqli_fetch_assoc($result_user);

if (!$user || $user['VaiTro'] !== 'NguoiMua' && $user['VaiTro'] !== 'NguoiBan') {
    header('Location: ../index.php');
    exit();
}

// Handle cancel order
if (isset($_GET['cancel']) && $_GET['cancel']) {
    $order_id = intval($_GET['cancel']);
    $update_query = "UPDATE danhsachdonhang SET TrangThaiDonHang = 'DaHuy' WHERE ID_DonHang = $order_id AND ID_NguoiMua = $user_id";
    if (mysqli_query($conn, $update_query)) {
        $success = 'Đã hủy đơn hàng thành công';
    } else {
        $error = 'Có lỗi xảy ra khi hủy đơn hàng';
    }
}

// Get filter status
$status_filter = $_GET['status'] ?? 'all';
$status_map = [
    'all' => '',
    'pending' => "AND TrangThaiDonHang = 'ChoXacNhan'",
    'delivering' => "AND TrangThaiDonHang IN ('ChoGiaoHang', 'DangVanChuyen')",
    'completed' => "AND TrangThaiDonHang = 'HoanThanh'",
    'cancelled' => "AND TrangThaiDonHang = 'DaHuy'"
];

$status_condition = $status_map[$status_filter] ?? '';

// Get orders
$orders_query = "SELECT 
                    dh.ID_DonHang,
                    dh.NgayDatHang,
                    dh.DiaChiGiaoHang,
                    dh.SoTienCanThu_COD,
                    dh.TrangThaiDonHang,
                    dh.GhiChu,
                    (SELECT COUNT(*) FROM chitietdonhang WHERE ID_DonHang = dh.ID_DonHang) AS SoSanPham
                 FROM danhsachdonhang dh
                 WHERE dh.ID_NguoiMua = $user_id $status_condition
                 ORDER BY dh.NgayDatHang DESC";

$orders_result = mysqli_query($conn, $orders_query);
$orders = [];
if ($orders_result) {
    while ($row = mysqli_fetch_assoc($orders_result)) {
        // Get first product for display
        $product_query = "SELECT 
                            sp.TenSanPham,
                            ha.URL_HinhAnh,
                            ctdh.SoLuongMua
                          FROM chitietdonhang ctdh
                          JOIN sanpham sp ON ctdh.ID_SanPham = sp.ID_SanPham
                          LEFT JOIN hinhanhsanpham ha ON sp.ID_SanPham = ha.ID_SanPham
                          WHERE ctdh.ID_DonHang = {$row['ID_DonHang']}
                          LIMIT 1";
        $product_result = mysqli_query($conn, $product_query);
        if ($product_result && mysqli_num_rows($product_result) > 0) {
            $product = mysqli_fetch_assoc($product_result);
            $row['product_name'] = $product['TenSanPham'];
            $row['product_image'] = $product['URL_HinhAnh'];
            $row['first_quantity'] = $product['SoLuongMua'];
        }
        $orders[] = $row;
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

$status_classes = [
    'ChoXacNhan' => 'status-pending',
    'ChoGiaoHang' => 'status-waiting',
    'DangVanChuyen' => 'status-delivering',
    'HoanThanh' => 'status-completed',
    'DaHuy' => 'status-cancelled'
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đơn hàng của tôi - SM</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="my-orders.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-top">
            <a href="../index.php" class="logo">
                <div class="logo-icon"><img src="../img/logo.jpg" alt="logo"></div>
            </a>
            <div class="search-container">
                <input type="text" class="search-bar" placeholder=" Tìm kiếm">
                <span class="search-icon">&#x1F50E;&#xFE0E;</span>
            </div>
            <div class="user-actions">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <span><u><?php echo $_SESSION['fullname']; ?></u></span>
                    <span>|</span>
                    <a href="../logout.php"><u>Đăng Xuất</u></a>
                <?php else: ?>    
                    <a href="../login.php"><u>Đăng nhập</u></a>
                    <span>|</span>
                    <a href="../register.php"><u>Đăng ký</u></a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main class="my-orders-page">
        <div class="breadcrumb">
            <a href="../index.php">Trang chủ</a> <span> > </span> 
            <strong>Đơn hàng của tôi</strong>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <h1 class="page-title">Đơn hàng của tôi</h1>

        <!-- Status Tabs -->
        <div class="status-tabs">
            <a href="?status=all" class="tab <?php echo $status_filter === 'all' ? 'active' : ''; ?>">
                Tất cả
            </a>
            <a href="?status=pending" class="tab <?php echo $status_filter === 'pending' ? 'active' : ''; ?>">
                Chờ xác nhận
            </a>
            <a href="?status=delivering" class="tab <?php echo $status_filter === 'delivering' ? 'active' : ''; ?>">
                Đang giao
            </a>
            <a href="?status=completed" class="tab <?php echo $status_filter === 'completed' ? 'active' : ''; ?>">
                Hoàn thành
            </a>
            <a href="?status=cancelled" class="tab <?php echo $status_filter === 'cancelled' ? 'active' : ''; ?>">
                Đã hủy
            </a>
        </div>

        <!-- Orders List -->
        <div class="orders-list">
            <?php if (empty($orders)): ?>
                <div class="empty-orders">
                    <i class="fas fa-shopping-bag"></i>
                    <p>Bạn chưa có đơn hàng nào</p>
                    <a href="../index.php" class="btn btn-primary">Tiếp tục mua sắm</a>
                </div>
            <?php else: ?>
                <?php foreach ($orders as $order): ?>
                    <div class="order-card">
                        <div class="order-header">
                            <div class="order-id">
                                <strong>Mã đơn:</strong> #DH<?php echo str_pad($order['ID_DonHang'], 6, '0', STR_PAD_LEFT); ?>
                            </div>
                            <div class="order-date">
                                <?php echo date('d/m/Y', strtotime($order['NgayDatHang'])); ?>
                            </div>
                        </div>

                        <div class="order-body">
                            <div class="order-product">
                                <?php if (!empty($order['product_image'])): ?>
                                    <img src="<?php echo "../" ?><?php echo htmlspecialchars($order['product_image']); ?>" 
                                         alt="<?php echo htmlspecialchars($order['product_name'] ?? 'Sản phẩm'); ?>" 
                                         class="product-image">
                                <?php else: ?>
                                    <div class="product-image-placeholder">
                                        <i class="fas fa-image"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="product-info">
                                    <div class="product-name">
                                        <?php echo htmlspecialchars($order['product_name'] ?? 'Sản phẩm'); ?>
                                    </div>
                                    <?php if ($order['SoSanPham'] > 1): ?>
                                        <div class="product-meta">
                                            và <?php echo $order['SoSanPham'] - 1; ?> sản phẩm khác
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="product-quantity">
                                    x<?php echo $order['first_quantity'] ?? 1; ?>
                                </div>
                            </div>

                            <div class="order-total">
                                <strong>Tổng:</strong> <?php echo number_format($order['SoTienCanThu_COD'], 0, ',', '.'); ?>₫
                            </div>

                            <div class="order-status <?php echo $status_classes[$order['TrangThaiDonHang']] ?? ''; ?>">
                                <?php echo $status_labels[$order['TrangThaiDonHang']] ?? $order['TrangThaiDonHang']; ?>
                            </div>
                        </div>

                        <div class="order-actions">
                            <a href="order-detail.php?id=<?php echo $order['ID_DonHang']; ?>" class="btn btn-outline">
                                <i class="fas fa-eye"></i>
                                Xem chi tiết
                            </a>
                            
                            <?php if ($order['TrangThaiDonHang'] === 'ChoXacNhan' || $order['TrangThaiDonHang'] === 'ChoGiaoHang'): ?>
                                <a href="?cancel=<?php echo $order['ID_DonHang']; ?>&status=<?php echo $status_filter; ?>" 
                                   class="btn btn-danger"
                                   onclick="return confirm('Bạn có chắc chắn muốn hủy đơn hàng này?');">
                                    <i class="fas fa-times"></i>
                                    Hủy đơn hàng
                                </a>
                            <?php endif; ?>

                            <?php if ($order['TrangThaiDonHang'] === 'HoanThanh'): ?>
                                <a href="review.php?order_id=<?php echo $order['ID_DonHang']; ?>" class="btn btn-primary">
                                    <i class="fas fa-star"></i>
                                    Gửi đánh giá
                                </a>
                            <?php endif; ?>

                            <?php if ($order['TrangThaiDonHang'] === 'DangVanChuyen' || $order['TrangThaiDonHang'] === 'ChoGiaoHang'): ?>
                                <a href="#" class="btn btn-secondary">
                                    <i class="fas fa-comments"></i>
                                    Liên hệ người bán
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-column">
                <div class="logo" style="margin-bottom: 15px;">
                    <div class="logo-icon"><img src="../img/logo.jpg" alt="logo"></div>
                    <span style="color: white;">SM</span>
                </div>
                <p>SM - Nền tảng mua và bán đồ cũ uy tín và có đảm bảo tại Việt Nam.</p>
            </div>
            <div class="footer-column">
                <h3>Hỗ trợ khách hàng</h3>
                <a href="#">Trung tâm trợ giúp</a>
                <a href="#">An toàn mua bán</a>
                <a href="#">Liên hệ hỗ trợ</a>
            </div>
            <div class="footer-column">
                <h3>Về SM</h3>
                <a href="#">Giới thiệu</a>
                <a href="#">Quy chế hoạt động sàn</a>
                <a href="#">Chính sách bảo mật</a>
                <a href="#">Giải quyết tranh chấp</a>
                <a href="#">Tuyển dụng</a>
                <a href="#">Truyền thông</a>
            </div>
            <div class="footer-column">
                <h3>Liên kết</h3>
                <div class="social-icons">
                    <a href="#" class="social-icon"><img src="https://cdn-icons-png.flaticon.com/128/15047/15047435.png" alt="facebook"></a>
                    <a href="#" class="social-icon"><img src="https://cdn-icons-png.flaticon.com/128/145/145807.png" alt="in"></a>
                    <a href="#" class="social-icon"><img src="https://cdn-icons-png.flaticon.com/128/15707/15707749.png" alt="instagram"></a>
                </div>
                <p style="margin-top: 20px;">Email: abc@gmail.com</p>
                <p>CSKH: 19003003 (1.000đ/phút)</p>
                <p>Địa chỉ: Tầng 18, Toà nhà abc, 6 Trần Hưng Đạo, Phường Quy Nhơn Tỉnh Gia Lai, Việt Nam</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 SM. Tất cả quyền được bảo lưu.</p>
        </div>
    </footer>
</body>
</html>

