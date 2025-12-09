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
$_SESSION['ID_NguoiBan'] = $order['ID_NguoiBan'];
if (!$order) {
    header('Location: my-orders.php');
    exit();
}

// Get existing reviews for this product
$reviews_query = "SELECT dg.*, nd.HoTen 
                  FROM danhgia_nhanxet dg
                  JOIN nguoidung nd ON dg.ID_NguoiDanhGia = nd.ID_NguoiDung
                  WHERE dg.ID_DonHang = {$order['ID_SanPham']}
                  ORDER BY dg.NgayDanhGia DESC";
$reviews_result = mysqli_query($conn, $reviews_query);

// Check if user already reviewed this product
$user_review_query = "SELECT * FROM danhgia_nhanxet WHERE ID_DonHang = {$order['ID_SanPham']} AND ID_NguoiDanhgia = $user_id";
$user_review_result = mysqli_query($conn, $user_review_query);
$user_has_reviewed = mysqli_num_rows($user_review_result) > 0;

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
    <title>SM - Đánh giá sản phẩm</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/review.css">
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

   <?php include 'header.php'; ?>

    <main class="review-page">
        <div class="breadcrumb">
            <u><a href="../index.php">Trang chủ</a></u> <span> > </span> 
            <u><a href="my-orders.php">Đơn hàng của tôi</a></u> <span> > </span> 
            <strong>Đánh giá sản phẩm</strong>
        </div>

        <section class="review-card">
            <div class="product-summary">
                <?php if (!empty($order['URL_HinhAnh'])): ?>
                    <div class="product-thumb" style="background-image: url('<?php echo '../' ?><?php echo htmlspecialchars($order['URL_HinhAnh']); ?>'); background-size: cover; background-position: center;"></div>
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
                <?php if ($user_has_reviewed): ?>
                    <p style="color: #2d8659; font-weight: 500;">✓ Bạn đã đánh giá sản phẩm này</p>
                <?php else: ?>
                    <form id="reviewForm" action="process_review.php" method="POST">
                        <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                        <input type="hidden" name="product_id" value="<?php echo $order['ID_SanPham']; ?>">
                        <input type="hidden" name="rating" id="ratingValue" value="0">
                        
                        <div class="stars-input" id="starsInput">
                            <span data-rating="1">☆</span>
                            <span data-rating="2">☆</span>
                            <span data-rating="3">☆</span>
                            <span data-rating="4">☆</span>
                            <span data-rating="5">☆</span>
                        </div>
                        <p id="ratingError" style="color: red; font-size: 13px; margin-top: 5px; display: none;">Vui lòng chọn số sao</p>
                        
                        <textarea name="comment" placeholder="Viết đánh giá..." rows="4"></textarea>
                        
                        <div class="review-actions">
                            <a href="my-orders.php" class="btn-outline">Quay lại</a>
                            <button type="submit" class="btn-primary">Gửi đánh giá</button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>

            <div class="existing-reviews">
                <h3 style="margin-bottom: 15px;">Đánh giá từ khách hàng khác</h3>
                <?php if (mysqli_num_rows($reviews_result) > 0): ?>
                    <?php while ($review = mysqli_fetch_assoc($reviews_result)): ?>
                        <div class="review-item">
                            <div class="avatar-circle"><?php echo strtoupper(substr($review['HoTen'], 0, 2)); ?></div>
                            <div style="flex: 1;">
                                <div class="review-header">
                                    <strong><?php echo htmlspecialchars($review['HoTen']); ?></strong>
                                    <span class="stars">
                                        <?php 
                                        $rating = intval($review['SoSao']);
                                        for ($i = 1; $i <= 5; $i++) {
                                            echo ($i <= $rating) ? '★' : '☆';
                                        }
                                        ?>
                                    </span>
                                </div>
                                <p style="margin: 5px 0; font-size: 13px; color: #666;">
                                    <?php echo date('d/m/Y', strtotime($review['NgayDanhGia'])); ?>
                                </p>
                                <?php if (!empty($review['NoiDung'])): ?>
                                    <p><?php echo htmlspecialchars($review['NoiDung']); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="text-align: center; color: #999; padding: 20px;">Chưa có đánh giá nào cho sản phẩm này</p>
                <?php endif; ?>
            </div>

            <div class="seller-message">
                <h3>Gửi tin nhắn cho người bán</h3>
                <textarea placeholder="Viết tin nhắn..."></textarea>
                <div class="review-actions">
                    <button class="btn-outline"><a href="my-orders.php">Quay lại</a></button>
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

    <script>
        // Star rating interaction
        const starsInput = document.getElementById('starsInput');
        const ratingValue = document.getElementById('ratingValue');
        const ratingError = document.getElementById('ratingError');
        
        if (starsInput) {
            const stars = starsInput.querySelectorAll('span');
            let selectedRating = 0;
            
            stars.forEach((star, index) => {
                // Hover effect
                star.addEventListener('mouseenter', function() {
                    const rating = parseInt(this.getAttribute('data-rating'));
                    highlightStars(rating);
                });
                
                // Click to select
                star.addEventListener('click', function() {
                    selectedRating = parseInt(this.getAttribute('data-rating'));
                    ratingValue.value = selectedRating;
                    highlightStars(selectedRating);
                    ratingError.style.display = 'none';
                });
            });
            
            // Reset on mouse leave
            starsInput.addEventListener('mouseleave', function() {
                highlightStars(selectedRating);
            });
            
            function highlightStars(count) {
                stars.forEach((star, index) => {
                    if (index < count) {
                        star.textContent = '★';
                    } else {
                        star.textContent = '☆';
                    }
                });
            }
        }
        
        // Form validation
        const reviewForm = document.getElementById('reviewForm');
        if (reviewForm) {
            reviewForm.addEventListener('submit', function(e) {
                const rating = parseInt(ratingValue.value);
                if (rating === 0) {
                    e.preventDefault();
                    ratingError.style.display = 'block';
                    return false;
                }
            });
        }
    </script>
</body>
</html>

