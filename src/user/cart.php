<?php
session_start();
require_once __DIR__ . '/../../database/db.php';
// Khởi tạo giỏ hàng nếu chưa có
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
$coupon = $_POST['coupon'] ?? '';
//  $selected_items = $_POST['item-checkbox'];
// Tính tổng tiền
$total = 0;
$ship_price = 0;
$cart_items = $_SESSION['cart'];


foreach ($cart_items as $item) {
        
        $total += $item['price'] * $item['quantity'];
    
}
$ship_price = $total * 0.05; // Phí vận chuyển cố định
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
    <title>SM - Giỏ hàng của tôi</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/cart.css">
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
                    <span><u><a href="../user/edit-profile.php"><?php echo $_SESSION['fullname']; ?></a></u></span>
                    <span>|</span>
                    <a href="my-orders.php"><u>Đơn hàng của tôi</u></a>
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
        
            <?php 
            $displayed_categories = array_slice($categories, 0, 6); // 6 danh mục đầu tiên
            $remaining_categories = array_slice($categories, 6); // Các danh mục còn lại
            
            if (!empty($displayed_categories)): 
                foreach ($displayed_categories as $category): 
            ?>
                <a href="user/list-product.php?category=<?php echo $category['ID_DanhMuc']; ?>"><?php echo htmlspecialchars($category['TenDanhMuc']); ?></a>
            <?php 
                endforeach;
                
                // Hiển thị các danh mục còn lại (ẩn ban đầu)
                if (!empty($remaining_categories)): 
                    foreach ($remaining_categories as $category): 
            ?>
                <a href="user/list-product.php?category=<?php echo $category['ID_DanhMuc']; ?>" class="extra-category" style="display: none;"><?php echo htmlspecialchars($category['TenDanhMuc']); ?></a>
            <?php 
                    endforeach;
                endif;
                
                // Nút "Tất cả danh mục" nếu có nhiều hơn 6 danh mục
                if (!empty($remaining_categories)): 
            ?>
                <a href="#" id="toggleCategories" style="color: #ff6b6b; font-weight: 500;">Tất cả danh mục ▼</a>
            <?php 
                endif;
            else: 
            ?>
                <a href="#">Chưa có danh mục</a>
            <?php endif; ?>
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
                            <input type="checkbox" 
                                class="item-checkbox" 
                                data-id="<?php echo $product_id; ?>"
                                data-price="<?php echo $item['price']; ?>"
                                data-qty="<?php echo $item['quantity']; ?>"
                                data-ship="<?php echo $ship_price; ?>"
                                data-total="<?php echo $item['price'] * $item['quantity']; ?>"
                            >
                            <script>
                                // document.getElementById("item-checkbox").addEventListener("change", function() {
                                //     if (this.checked) {
                                //         this.value = "1";    // khi tick
                                //     } else {
                                //         this.value = "0";    // khi bỏ tick
                                //     }
                                // });
                            </script>
                        </div>
                        <div class="item-thumb">
                            <?php if (!empty($item['image'])): ?>
                                <img src="<?php echo '../' ?><?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
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
                        <span><?php echo number_format($ship_price, 0, ',', '.'); ?>₫</span>
                    </div>
                    <div class="summary-row total">
                        <span>Tổng thanh toán</span>
                        <span id="final-total"><?php echo number_format($total + $ship_price, 0, ',', '.'); ?>₫</span>
                    </div>
                    <form action="" method="post">
                        <div class="coupon">
                            <label for="coupon">Nhập mã giảm giá</label>
                            <input type="text" id="coupon" placeholder="VD: SM2024" name="coupon">
                            <button class="btn-outline">Áp dụng</button>
                        </div>
                    </form>
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
        const checked = document.querySelectorAll('.item-checkbox:checked');

        if (checked.length === 0) {
            alert("Vui lòng chọn ít nhất 1 sản phẩm!");
            return;
        }

        // Tạo form gửi dữ liệu
        const form = document.createElement("form");
        form.method = "POST";
        form.action = "checkout.php";

        checked.forEach((cb, index) => {
            form.innerHTML += `
                <input type="hidden" name="selected[${index}][product_id]" value="${cb.dataset.id}">
                <input type="hidden" name="selected[${index}][quantity]" value="${cb.dataset.qty}">
                <input type="hidden" name="selected[${index}][price]" value="${cb.dataset.price}">
            `;
        });

        document.body.appendChild(form);
        form.submit();
    }

        // select checkbox functionality
        // document.querySelectorAll('.item-checkbox')?.forEach(cb => {
        //     cb.addEventListener('change', function() {
        //         const selectAll = document.getElementById('selectAll');
        //         if (selectAll) {
        //             selectAll.checked = document.querySelectorAll('.item-checkbox:checked').length === document.querySelectorAll('.item-checkbox').length;
        //         }
        //     });
        // });
        // Select all checkbox functionality
        // document.getElementById('selectAll')?.addEventListener('change', function() {
        //     const checkboxes = document.querySelectorAll('.item-checkbox');
        //     checkboxes.forEach(cb => cb.checked = this.checked);
        // });
        function updateSummary() {
    const checkboxes = document.querySelectorAll('.item-checkbox');
    let total = 0;

    checkboxes.forEach(cb => {
        if (cb.checked) {
            const coupon = document.getElementById('coupon').value;
            const price = parseFloat(cb.dataset.price);
            const qty = parseInt(cb.dataset.qty);
            const ship = parseFloat(cb.dataset.ship);
          
                total += price * qty + ship;
            
        }
    });

    // Cập nhật hiển thị
    document.getElementById("total-price").textContent = total.toLocaleString('vi-VN') + "₫";
    document.getElementById("final-total").textContent = total.toLocaleString('vi-VN') + "₫";
}

// Khi tick từng sản phẩm
document.querySelectorAll('.item-checkbox').forEach(cb => {
    cb.addEventListener('change', updateSummary);
});

// Khi tick chọn tất cả
document.getElementById('selectAll')?.addEventListener('change', function () {
    const checkboxes = document.querySelectorAll('.item-checkbox');
    checkboxes.forEach(cb => cb.checked = this.checked);
    updateSummary();
});
    </script>
</body>

</html>
            