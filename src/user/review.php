<?php
session_start();
require_once __DIR__ . '/../../database/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = intval($_GET['order_id'] ?? 0);

if ($order_id <= 0) {
    header('Location: my-orders.php');
    exit();
}

// Get order and product details
$order_query = "SELECT dh.*, sp.TenSanPham, sp.ID_SanPham, ha.URL_HinhAnh, nd.HoTen AS TenNguoiBan
                FROM danhsachdonhang dh
                JOIN chitietdonhang ctdh ON dh.ID_DonHang = ctdh.ID_DonHang
                JOIN sanpham sp ON ctdh.ID_SanPham = sp.ID_SanPham
                LEFT JOIN hinhanhsanpham ha ON sp.ID_SanPham = ha.ID_SanPham
                LEFT JOIN nguoidung nd ON dh.ID_NguoiBan = nd.ID_NguoiDung
                WHERE dh.ID_DonHang = $order_id AND dh.ID_NguoiMua = $user_id
                LIMIT 1";

$order_result = mysqli_query($conn, $order_query);
$order = mysqli_fetch_assoc($order_result);

if (!$order) {
    header('Location: my-orders.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SM - Đánh giá sản phẩm</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="review.css">
</head>
<body>
    <div class="browser-bar">
        <div class="browser-nav">
            <span></span><span></span><span></span>
        </div>
        <span>https://www.sm.vn/review</span>
    </div>

    <div class="promo-banner">
        <p>Nền tảng mua đồ cũ vì một trái đất xanh hơn!</p>
        <p>Cam kết hoàn tiền 100% nếu sản phẩm không đúng mô tả!</p>
    </div>

    <header class="header">
        <div class="header-top">
            <a href="../index.php" class="logo">
                <div class="logo-icon"><img src="../img/logo.jpg" alt="logo"></div>
            </a>
            <div class="category-dropdown">
                <button class="category-btn">Tất cả danh mục</button>
                <div class="category-menu">
                    <a href="list-product.html">Sách</a>
                    <a href="#">Thiết bị</a>
                    <a href="#">Đồ gia dụng</a>
                    <a href="#">Đồ chơi</a>
                </div>
            </div>
            <div class="search-container">
                <input type="text" class="search-bar" placeholder=" Tìm kiếm">
                <span class="search-icon">&#x1F50E;&#xFE0E;</span>
            </div>
            <div class="user-actions">
                <a href="#" class="bell-icon"><img class="bell-icon-img" src="https://cdn-icons-png.flaticon.com/128/3602/3602145.png" alt="bell"></a>
                <span>|</span>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <span><u><?php echo htmlspecialchars($_SESSION['fullname']); ?></u></span>
                    <span>|</span>
                    <a href="../logout.php"><u>Đăng Xuất</u></a>
                <?php else: ?>
                    <a href="../login.php"><u>Đăng nhập</u></a>
                    <span>|</span>
                    <a href="../register.php"><u>Đăng ký</u></a>
                <?php endif; ?>
                <span>|</span>
                <a href="cart.php" class="cart-icon"><img class="cart-icon-img" src="https://cdn-icons-png.flaticon.com/128/1170/1170678.png" alt="cart"></a>
            </div>
        </div>
        <div class="nav-categories">
            <a href="../index.php">Trang chủ</a>
            <a href="#">Đồ cho nam</a>
            <a href="#">Đồ cho nữ</a>
            <a href="#">Đồ cho mẹ và bé</a>
            <a href="#">Đồ gia dụng</a>
            <a href="#">Đồ chơi</a>
        </div>
    </header>

    <main class="review-page">
        <div class="breadcrumb">
            <u><a href="../index.php">Trang chủ</a></u> <span> > </span> 
            <u><a href="my-orders.php">Đơn hàng của tôi</a></u> <span> > </span> 
            <strong>Đánh giá sản phẩm</strong>
        </div>

        <section class="review-card">
            <div class="product-summary">
                <?php if (!empty($order['URL_HinhAnh'])): ?>
                    <div class="product-thumb" style="background-image: url('<?php echo htmlspecialchars($order['URL_HinhAnh']); ?>'); background-size: cover; background-position: center;"></div>
                <?php else: ?>
                    <div class="product-thumb" style="background: #f0f0f0; display: flex; align-items: center; justify-content: center;">📦</div>
                <?php endif; ?>
                <div>
                    <h2><?php echo htmlspecialchars($order['TenSanPham']); ?></h2>
                    <p>Người bán: <?php echo htmlspecialchars($order['TenNguoiBan'] ?? 'N/A'); ?></p>
                    <p>Giá: <?php echo number_format($order['SoTienCanThu_COD'], 0, ',', '.'); ?>₫</p>
                </div>
            </div>

            <div class="user-rating">
                <h3>Đánh giá của bạn</h3>
                <div class="stars-input">
                    <span>☆</span><span>☆</span><span>☆</span><span>☆</span><span>☆</span>
                </div>
                <textarea placeholder="Viết đánh giá..."></textarea>
                <div class="review-actions">
                    <a href="my-orders.php" class="btn-outline">Quay lại</a>
                    <button class="btn-primary" onclick="submitReview()">Gửi đánh giá</button>
                </div>
            </div>

            <div class="existing-reviews">
                <div class="review-item">
                    <div class="avatar-circle">U1</div>
                    <div>
                        <div class="review-header">
                            <strong>User1</strong>
                            <span class="stars">★★★★★</span>
                        </div>
                        <p>Sách đẹp, đóng gói kĩ và giao nhanh.</p>
                    </div>
                </div>
                <div class="review-item">
                    <div class="avatar-circle">U2</div>
                    <div>
                        <div class="review-header">
                            <strong>User2</strong>
                            <span class="stars">★★★★☆</span>
                        </div>
                        <p>Hài lòng với chất lượng, sẽ ủng hộ tiếp.</p>
                    </div>
                </div>
            </div>

            <div class="seller-message">
                <h3>Gửi tin nhắn cho người bán</h3>
                <textarea placeholder="Viết tin nhắn..."></textarea>
                <div class="review-actions">
                    <button class="btn-outline">Quay lại</button>
                    <button class="btn-primary">Gửi tin nhắn</button>
                </div>
            </div>
        </section>
    </main>

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

