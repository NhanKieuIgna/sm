<?php
session_start();

// Khởi tạo giỏ hàng nếu chưa có
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Tính tổng tiền
$total = 0;
$cart_items = $_SESSION['cart'];
foreach ($cart_items as $item) {
    $total += $item['price'] * $item['quantity'];
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SM - Giỏ hàng của tôi</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="cart.css">
</head>

<body>
    <div class="browser-bar">
        <div class="browser-nav">
            <span></span><span></span><span></span>
        </div>
        <span>https://www.sm.vn/mycart</span>
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
                    <a href="#">Sách</a>
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
                <a href="#" class="bell-icon"><img class="bell-icon-img"
                        src="https://cdn-icons-png.flaticon.com/128/3602/3602145.png" alt="bell"></a>
                <span>|</span>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <span><u><a href="../buyer/edit-profile.php"><?php echo $_SESSION['fullname']; ?></a></u></span>
                    <span>|</span>
                    <a href="../logout.php"><u>Đăng Xuất</u></a>
                <?php else: ?>
                <a href="../login.php"><u>Đăng nhập</u></a>
                <span>|</span>
                <a href="../register.php"><u>Đăng ký</u></a>
                <?php endif; ?>
                <span>|</span>
                <a href="cart.php" class="cart-icon"><img class="cart-icon-img"
                        src="https://cdn-icons-png.flaticon.com/128/1170/1170678.png" alt="cart"></a>
            </div>
        </div>
        <div class="nav-categories">
            <a href="../index.php">Trang chủ</a>
            <a href="#">Sách</a>
            <a href="#">Đồ cho nam</a>
            <a href="#">Đồ cho nữ</a>
            <a href="#">Đồ cho mẹ và bé</a>
            <a href="#">Đồ gia dụng</a>
            <a href="#">Đồ chơi</a>
        </div>
    </header>

    <main class="cart-page">
        <div class="breadcrumb">
            <u><a href="../index.php">Trang chủ</a></u> <span> > </span> <strong>Giỏ hàng của tôi</strong>
        </div>

        <?php if (empty($cart_items)): ?>
            <div class="cart-content">
                <div style="text-align: center; padding: 50px 0;">
                    <h2>Giỏ hàng của bạn đang trống</h2>
                    <p style="margin: 20px 0;">Hãy thêm sản phẩm vào giỏ hàng để tiếp tục mua sắm</p>
                    <a href="../index.php" class="btn-primary" style="display: inline-block; padding: 12px 24px; text-decoration: none;">Tiếp tục mua sắm</a>
                </div>
            </div>
        <?php else: ?>
        <div class="cart-content">
            <section class="cart-left">
                <div class="cart-header">
                    <h2 class="section-title"><?php echo count($cart_items); ?> sản phẩm trong giỏ hàng</h2>
                    <div class="select-all">
                        <label><input type="checkbox" id="selectAll"> Chọn tất cả</label>
                    </div>
                </div>

                <div class="shipping-options">
                    <p><strong>Bảo vệ người mua:</strong> Bưu cục không nhận được hàng không đúng mô tả sẽ hoàn tiền
                        100%.</p>
                    <div class="shipping-buttons">
                        <button class="btn-outline">Giao hàng tiết kiệm</button>
                        <button class="btn-primary">Thêm địa chỉ mới</button>
                    </div>
                </div>

                <div class="cart-items">
                    <?php foreach ($cart_items as $product_id => $item): ?>
                    <div class="cart-item" data-product-id="<?php echo $product_id; ?>">
                        <div class="item-select">
                            <input type="checkbox" class="item-checkbox">
                        </div>
                        <div class="item-thumb">
                            <?php if (!empty($item['image'])): ?>
                                <img src="<?php echo"../" ?><?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                            <?php endif; ?>
                        </div>
                        <div class="item-info">
                            <div class="item-title"><?php echo htmlspecialchars($item['name']); ?></div>
                            <div class="item-meta">Tình trạng: <?php echo htmlspecialchars($item['condition']); ?></div>
                        </div>
                        <div class="item-quantity">
                            <button onclick="updateQuantity(<?php echo $product_id; ?>, -1)">-</button>
                            <span id="qty-<?php echo $product_id; ?>"><?php echo $item['quantity']; ?></span>
                            <button onclick="updateQuantity(<?php echo $product_id; ?>, 1)">+</button>
                        </div>
                        <div class="item-price"><?php echo number_format($item['price'], 0, ',', '.'); ?>₫</div>
                        <button class="item-remove" onclick="removeItem(<?php echo $product_id; ?>)">×</button>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="cart-note">
                    <label for="order-note">Ghi chú cho người bán</label>
                    <textarea id="order-note" placeholder="Ví dụ: Giao hàng nhanh giúp mình nhé..."></textarea>
                </div>
            </section>

            <aside class="cart-summary">
                <div class="summary-card">
                    <h3>Tóm tắt đơn hàng</h3>
                    <div class="summary-row">
                        <span>Tổng tiền hàng</span>
                        <span id="total-price"><?php echo number_format($total, 0, ',', '.'); ?>₫</span>
                    </div>
                    <div class="summary-row">
                        <span>Phí vận chuyển</span>
                        <span>Freeship</span>
                    </div>
                    <div class="summary-row total">
                        <span>Tổng thanh toán</span>
                        <span id="final-total"><?php echo number_format($total, 0, ',', '.'); ?>₫</span>
                    </div>
                    <div class="coupon">
                        <label for="coupon">Nhập mã giảm giá</label>
                        <input type="text" id="coupon" placeholder="VD: SM2024">
                        <button class="btn-outline">Áp dụng</button>
                    </div>
                    <div class="address-input">
                        <label for="address">Địa chỉ nhận hàng</label>
                        <input type="text" id="address" placeholder="Số nhà, phường/xã, quận/huyện, tỉnh/thành phố">
                    </div>
                    <p class="summary-note">Tôi xác nhận kiểm tra thông tin và đồng ý với các điều khoản của SM.</p>
                    <button class="btn-primary full-width" onclick="checkout()">Xác nhận</button>
                </div>
            </aside>
        </div>
        <?php endif; ?>
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
                    <a href="#" class="social-icon"><img src="https://cdn-icons-png.flaticon.com/128/15047/15047435.png"
                            alt="facebook"></a>
                    <a href="#" class="social-icon"><img src="https://cdn-icons-png.flaticon.com/128/145/145807.png"
                            alt="in"></a>
                    <a href="#" class="social-icon"><img src="https://cdn-icons-png.flaticon.com/128/15707/15707749.png"
                            alt="instagram"></a>
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

    <style>
        .item-remove {
            background: #ff6b6b;
            color: white;
            border: none;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 20px;
            line-height: 1;
            transition: background 0.3s;
        }

        .item-remove:hover {
            background: #ff5252;
        }

        .cart-item {
            position: relative;
        }
    </style>

    <script>
        function updateQuantity(productId, change) {
            const qtyElement = document.getElementById('qty-' + productId);
            let currentQty = parseInt(qtyElement.textContent);
            let newQty = currentQty + change;

            if (newQty < 1) {
                if (confirm('Bạn có muốn xóa sản phẩm này khỏi giỏ hàng?')) {
                    removeItem(productId);
                }
                return;
            }

            // Gửi request cập nhật
            const formData = new FormData();
            formData.append('product_id', productId);
            formData.append('quantity', newQty);
            formData.append('action', 'update');
            formData.append('ajax', '1');

            fetch('../add-to-cart.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra');
            });
        }

        function removeItem(productId) {
            const formData = new FormData();
            formData.append('product_id', productId);
            formData.append('action', 'remove');
            formData.append('ajax', '1');

            fetch('../add-to-cart.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra');
            });
        }

        function checkout() {
            alert('Chức năng thanh toán đang được phát triển');
        }

        // Select all checkbox functionality
        document.getElementById('selectAll')?.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.item-checkbox');
            checkboxes.forEach(cb => cb.checked = this.checked);
        });
    </script>
</body>

</html>