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

if (!$user || $user['VaiTro'] !== 'NguoiMua') {
    header('Location: ../index.php');
    exit();
}

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name'] ?? '');
    $phone = mysqli_real_escape_string($conn, $_POST['phone'] ?? '');
    $email = mysqli_real_escape_string($conn, $_POST['email'] ?? '');
    $address = mysqli_real_escape_string($conn, $_POST['address'] ?? '');
    $payment_method = mysqli_real_escape_string($conn, $_POST['payment_method'] ?? '');
    $note = mysqli_real_escape_string($conn, $_POST['note'] ?? '');
    
    // Get cart items from session or POST
    $cart_items = $_POST['cart_items'] ?? [];
    
    if (empty($full_name)) {
        $error = 'Họ tên không được để trống';
    } elseif (empty($phone)) {
        $error = 'Số điện thoại không được để trống';
    } elseif (empty($email)) {
        $error = 'Email không được để trống';
    } elseif (empty($address)) {
        $error = 'Địa chỉ không được để trống';
    } elseif (empty($payment_method)) {
        $error = 'Vui lòng chọn phương thức thanh toán';
    } elseif (empty($cart_items) || !is_array($cart_items)) {
        $error = 'Giỏ hàng trống';
    } else {
        // Calculate total
        $total_amount = 0;
        foreach ($cart_items as $item) {
            if (isset($item['price']) && isset($item['quantity'])) {
                $total_amount += floatval($item['price']) * intval($item['quantity']);
            }
        }
        
        // Insert order
        $order_date = date('Y-m-d H:i:s');
        $order_status = 'ChoXacNhan';
        
        $insert_order = "INSERT INTO danhsachdonhang 
                        (ID_NguoiMua, NgayDatHang, DiaChiGiaoHang, SoTienCanThu_COD, TrangThaiDonHang, GhiChu) 
                        VALUES 
                        ($user_id, '$order_date', '$address', $total_amount, '$order_status', '$note')";
        
        if (mysqli_query($conn, $insert_order)) {
            $order_id = mysqli_insert_id($conn);
            
            // Insert order details
            $all_success = true;
            foreach ($cart_items as $item) {
                $product_id = intval($item['product_id'] ?? 0);
                $quantity = intval($item['quantity'] ?? 0);
                $price = floatval($item['price'] ?? 0);
                
                if ($product_id > 0 && $quantity > 0) {
                    $insert_detail = "INSERT INTO chitietdonhang 
                                    (ID_DonHang, ID_SanPham, SoLuongMua, GiaBan) 
                                    VALUES 
                                    ($order_id, $product_id, $quantity, $price)";
                    
                    if (!mysqli_query($conn, $insert_detail)) {
                        $all_success = false;
                        break;
                    }
                }
            }
            
            if ($all_success) {
                // Clear cart
                unset($_SESSION['cart']);
                $success = 'Đặt hàng thành công! Mã đơn hàng: #' . $order_id;
                // Redirect after 3 seconds
                header("refresh:3;url=my-orders.php");
            } else {
                $error = 'Có lỗi xảy ra khi lưu chi tiết đơn hàng';
            }
        } else {
            $error = 'Có lỗi xảy ra khi đặt hàng: ' . mysqli_error($conn);
        }
    }
}

// Get user's default address
$default_address = $user['DiaChiGiaoHangMacDinh'] ?? '';
$default_phone = $user['SoDienThoai'] ?? '';
$default_email = $user['Email'] ?? '';
$default_name = $user['HoTen'] ?? '';

// Get cart items from session (if exists)
$cart = $_SESSION['cart'] ?? [];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán - SM</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="checkout.css">
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

    <main class="checkout-page">
        <div class="breadcrumb">
            <a href="../index.php">Trang chủ</a> <span> > </span> 
            <a href="cart.html">Giỏ hàng</a> <span> > </span> 
            <strong>Thanh toán</strong>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <div class="checkout-content">
            <div class="checkout-left">
                <h2 class="section-title">Thông tin giao hàng</h2>
                
                <form method="POST" class="checkout-form">
                    <div class="form-group">
                        <label for="full_name">
                            <i class="fas fa-user"></i>
                            Họ và tên <span class="required">*</span>
                        </label>
                        <input type="text" id="full_name" name="full_name" 
                               value="<?php echo htmlspecialchars($default_name); ?>" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="phone">
                                <i class="fas fa-phone"></i>
                                Số điện thoại <span class="required">*</span>
                            </label>
                            <input type="tel" id="phone" name="phone" 
                                   value="<?php echo htmlspecialchars($default_phone); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="email">
                                <i class="fas fa-envelope"></i>
                                Email <span class="required">*</span>
                            </label>
                            <input type="email" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($default_email); ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="address">
                            <i class="fas fa-map-marker-alt"></i>
                            Địa chỉ giao hàng <span class="required">*</span>
                        </label>
                        <textarea id="address" name="address" rows="3" required><?php echo htmlspecialchars($default_address); ?></textarea>
                    </div>

                    <h3 class="section-subtitle">Phương thức thanh toán</h3>
                    <div class="payment-methods">
                        <label class="payment-option">
                            <input type="radio" name="payment_method" value="cod" required>
                            <div class="payment-info">
                                <i class="fas fa-money-bill-wave"></i>
                                <span>Thanh toán khi nhận hàng (COD)</span>
                            </div>
                        </label>
                        <label class="payment-option">
                            <input type="radio" name="payment_method" value="bank_transfer">
                            <div class="payment-info">
                                <i class="fas fa-university"></i>
                                <span>Chuyển khoản ngân hàng</span>
                            </div>
                        </label>
                        <label class="payment-option">
                            <input type="radio" name="payment_method" value="e_wallet">
                            <div class="payment-info">
                                <i class="fas fa-wallet"></i>
                                <span>Ví điện tử</span>
                            </div>
                        </label>
                    </div>

                    <div class="form-group">
                        <label for="note">
                            <i class="fas fa-sticky-note"></i>
                            Ghi chú cho người bán
                        </label>
                        <textarea id="note" name="note" rows="3" 
                                  placeholder="Ví dụ: Giao hàng nhanh giúp mình nhé..."></textarea>
                    </div>

                    <!-- Hidden cart items -->
                    <?php if (!empty($cart)): ?>
                        <?php foreach ($cart as $index => $item): ?>
                            <input type="hidden" name="cart_items[<?php echo $index; ?>][product_id]" 
                                   value="<?php echo htmlspecialchars($item['product_id'] ?? ''); ?>">
                            <input type="hidden" name="cart_items[<?php echo $index; ?>][quantity]" 
                                   value="<?php echo htmlspecialchars($item['quantity'] ?? ''); ?>">
                            <input type="hidden" name="cart_items[<?php echo $index; ?>][price]" 
                                   value="<?php echo htmlspecialchars($item['price'] ?? ''); ?>">
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <div class="form-actions">
                        <a href="cart.html" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i>
                            Quay lại giỏ hàng
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-check"></i>
                            Đặt hàng
                        </button>
                    </div>
                </form>
            </div>

            <aside class="checkout-summary">
                <div class="summary-card">
                    <h3>Đơn hàng của bạn</h3>
                    <div class="order-items">
                        <?php 
                        $total = 0;
                        if (!empty($cart)): 
                            foreach ($cart as $item): 
                                $item_total = floatval($item['price'] ?? 0) * intval($item['quantity'] ?? 0);
                                $total += $item_total;
                        ?>
                            <div class="order-item">
                                <div class="item-name"><?php echo htmlspecialchars($item['name'] ?? 'Sản phẩm'); ?></div>
                                <div class="item-quantity">x<?php echo htmlspecialchars($item['quantity'] ?? 1); ?></div>
                                <div class="item-price"><?php echo number_format($item_total, 0, ',', '.'); ?>₫</div>
                            </div>
                        <?php 
                            endforeach;
                        else: 
                        ?>
                            <p class="empty-cart">Giỏ hàng trống</p>
                        <?php endif; ?>
                    </div>

                    <div class="summary-totals">
                        <div class="summary-row">
                            <span>Tạm tính</span>
                            <span><?php echo number_format($total, 0, ',', '.'); ?>₫</span>
                        </div>
                        <div class="summary-row">
                            <span>Phí vận chuyển</span>
                            <span class="free-ship">Miễn phí</span>
                        </div>
                        <div class="summary-row total">
                            <span>Tổng cộng</span>
                            <span><?php echo number_format($total, 0, ',', '.'); ?>₫</span>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </main>
</body>
</html>

