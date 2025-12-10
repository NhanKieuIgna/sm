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

$error = '';
$success = '';

// Determine checkout mode: buy_now or from cart
$checkout_items = [];
$is_buy_now = false;

if (isset($_GET['buy_now']) && $_GET['buy_now'] == '1') {
    // Buy now mode - single product
    $is_buy_now = true;
    $product_id = intval($_GET['product_id'] ?? 0);
    $quantity = intval($_GET['quantity'] ?? 1);
    
    if ($product_id > 0 && $quantity > 0) {
        // Fetch product details from database
        $sql = "SELECT sp.*, ha.URL_HinhAnh 
                FROM sanpham sp 
                LEFT JOIN hinhanhsanpham ha ON sp.ID_SanPham = ha.ID_SanPham 
                WHERE sp.ID_SanPham = $product_id AND sp.TrangThaiDangBan = 'DangBan'
                LIMIT 1";
        $result = mysqli_query($conn, $sql);
        
        if ($product = mysqli_fetch_assoc($result)) {
            // Check if quantity is valid
            if ($quantity <= $product['SoLuong']) {
                $checkout_items[$product_id] = [
                    'product_id' => $product_id,
                    'name' => $product['TenSanPham'],
                    'price' => $product['Gia'],
                    'quantity' => $quantity,
                    'image' => $product['URL_HinhAnh'] ?? '',
                    'condition' => $product['TinhTrang'] ?? 'Mới'
                ];
            } else {
                $error = 'Số lượng sản phẩm không đủ';
            }
        } else {
            $error = 'Sản phẩm không tồn tại hoặc đã ngừng bán';
        }
    } else {
        $error = 'Thông tin sản phẩm không hợp lệ';
    }
} else if (isset($_POST['selected']) && is_array($_POST['selected'])) {
    // Cart checkout mode - get selected items from POST
    $selected_items = $_POST['selected'];
    
    foreach ($selected_items as $item) {
        $product_id = intval($item['product_id'] ?? 0);
        $quantity = intval($item['quantity'] ?? 0);
        $price = floatval($item['price'] ?? 0);
        
        if ($product_id > 0 && $quantity > 0 && $price > 0) {
            // Get product details from database
            $sql = "SELECT sp.TenSanPham, sp.TinhTrang, ha.URL_HinhAnh 
                    FROM sanpham sp 
                    LEFT JOIN hinhanhsanpham ha ON sp.ID_SanPham = ha.ID_SanPham 
                    WHERE sp.ID_SanPham = $product_id 
                    LIMIT 1";
            $result = mysqli_query($conn, $sql);
            
            if ($product = mysqli_fetch_assoc($result)) {
                $checkout_items[$product_id] = [
                    'product_id' => $product_id,
                    'name' => $product['TenSanPham'],
                    'price' => $price,
                    'quantity' => $quantity,
                    'image' => $product['URL_HinhAnh'] ?? '',
                    'condition' => $product['TinhTrang'] ?? 'Mới'
                ];
            }
        }
    }
} else {
    // Fallback to session cart
    $checkout_items = $_SESSION['cart'] ?? [];
}

// Xử lý mã giảm giá
if(isset($_POST['coupon']) && $_SERVER['REQUEST_METHOD'] === 'POST'){
    $_SESSION['coupon'] = trim($_POST['coupon']);
  if ( $_SESSION['coupon'] == 'SM') {
    $ship_price = 0; // Miễn phí ship
}else{
    $ship_price = 29999; // Phí ship mặc định
}
}

$ship_price = 29999; // Phí ship mặc định




// Handle form submission - only when checkout form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['full_name'])) {
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name'] ?? '');
    $phone = mysqli_real_escape_string($conn, $_POST['phone'] ?? '');
    $email = mysqli_real_escape_string($conn, $_POST['email'] ?? '');
    $address = mysqli_real_escape_string($conn, $_POST['address'] ?? '');
    $payment_method = mysqli_real_escape_string($conn, $_POST['payment_method'] ?? '');
    $note = mysqli_real_escape_string($conn, $_POST['note'] ?? '');
    $ship_price_nomarl = 29999;
    // Get cart items from session or POST
    $cart_items = $_POST['cart_items'] ?? $checkout_items;
    
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
        // Calculate total for products only (no shipping yet)
        $total_amount = 0;
        $total_amount_ship = 0;
        // foreach ($cart_items as $item) {
        //     if (isset($item['price']) && isset($item['quantity'])) {
        //         if(isset($_SESSION['coupon']) && $_SESSION['coupon'] == 'SM'){
        //         $total_amount += floatval($item['price']) * intval($item['quantity']);
        //         } else {
        //         $total_amount += floatval($item['price']) * intval($item['quantity']) + 29999;
        //         }
        //     }
        // }
         foreach ($cart_items as $item) {
            if (isset($item['price']) && isset($item['quantity'])) {
                
                $total_amount += floatval($item['price']) * intval($item['quantity']);
                
            }
        }
        // Add shipping fee once for the entire order
        $total_amount_ship = $total_amount + 29999;

        
        // Get seller ID from first product (assuming all products from same seller for now)
        $seller_id = null;
        foreach ($cart_items as $item) {
            $product_id = intval($item['product_id'] ?? 0);
            if ($product_id > 0) {
                $seller_query = "SELECT ID_NguoiBan FROM sanpham WHERE ID_SanPham = $product_id LIMIT 1";
                $seller_result = mysqli_query($conn, $seller_query);
                if ($seller_row = mysqli_fetch_assoc($seller_result)) {
                    $seller_id = $seller_row['ID_NguoiBan'];
                    break;
                }
            }
        }
        
        if (!$seller_id) {
            $error = 'Không tìm thấy thông tin người bán';
        } else {
            // Insert order
            $order_date = date('Y-m-d H:i:s');
            $order_status = 'ChoXacNhan';
             $ship_price_final = isset($_SESSION['coupon']) && $_SESSION['coupon'] == 'SM' ? 0 : $total_amount * 0.05 ;
             $total_amount_final = $total_amount + $total_amount * 0.05 ;
             $total_amount_apply_ship = $total_amount + $ship_price_final ;
            $insert_order = "INSERT INTO danhsachdonhang 
                            (ID_NguoiMua, ID_NguoiBan, NgayDatHang, DiaChiGiaoHang, SoTienCanThu_COD, TrangThaiDonHang, TongGiaTriDonHang, GhiChu, PhiGiaoHang) 
                            VALUES 
                            ($user_id, $seller_id, '$order_date', '$address', '$total_amount_apply_ship', '$order_status', '$total_amount_final', '$note', $ship_price_final)";
           
            if (mysqli_query($conn, $insert_order)) {
                
              
                $order_id = mysqli_insert_id($conn); // Lấy ID đơn hàng vừa tạo
            
            // Insert order details
            $all_success = true;
            foreach ($cart_items as $item) {
                $product_id = intval($item['product_id'] ?? 0);
                $quantity = intval($item['quantity'] ?? 0);
                $price = floatval($item['price'] ?? 0);
                
                if ($product_id > 0 && $quantity > 0) {
                    $insert_detail = "INSERT INTO chitietdonhang 
                                    (ID_DonHang, ID_SanPham, SoLuongMua, GiaTaiThoiDiemDat) 
                                    VALUES 
                                    ($order_id, $product_id, $quantity, $price)";
                    
                    if (!mysqli_query($conn, $insert_detail)) { // kiem tra tung lan luu chi tiet don hang
                        $all_success = false;
                        break;
                    }
                }
            }
            
            if ($all_success) {
                // Clear cart only if not in buy_now mode
                if (!$is_buy_now) {
                    unset($_SESSION['cart']);
                }
                $select_query = "SELECT * FROM chitietdonhang WHERE ID_DonHang = $order_id"; // Lấy chi tiết đơn hàng vừa tạo
                $select_result = mysqli_query($conn, $select_query);
                $result_order_details = mysqli_fetch_assoc($select_result);
                // lay cot soluong mua
                $quantity_ordered = $result_order_details['SoLuongMua'] ?? 0;
                $id_ordered_product = $result_order_details['ID_SanPham'] ?? 0;
                // Cập nhật số lượng sản phẩm trong kho
                $update_stock = "UPDATE sanpham 
                                 SET SoLuong = SoLuong - $quantity_ordered 
                                 WHERE ID_SanPham = $id_ordered_product AND SoLuong >= $quantity_ordered";
                mysqli_query($conn, $update_stock);

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
}

// Get user's default address
$default_address = $user['DiaChiGiaoHangMacDinh'] ?? '';
$default_phone = $user['SoDienThoai'] ?? '';
$default_email = $user['Email'] ?? '';
$default_name = $user['HoTen'] ?? '';

// Get cart items from session (if exists)
$cart = $_SESSION['cart'] ?? [];
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
    <title>Thanh toán - SM</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/checkout.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <?php include 'header.php'; ?>
    <main class="checkout-page">
        <div class="breadcrumb">
            <a href="../index.php">Trang chủ</a> <span> > </span> 
            <?php if ($is_buy_now): ?>
                <a href="../index.php">Danh sách sản phẩm</a> <span> > </span> 
            <?php else: ?>
                <a href="cart.php">Giỏ hàng</a> <span> > </span> 
            <?php endif; ?>
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
                        <label for="coupon">
                            <i class="fas fa-ticket-alt"></i>
                            Mã giảm giá
                        </label>
                        <input type="text" id="coupon" name="coupon" 
                               placeholder="Nhập mã giảm giá (VD: SM)" 
                              >
                        <?php if (!empty($coupon) && strtoupper(trim($coupon)) == 'SM'): ?>
                            <script>alert('Mã giảm giá đã được áp dụng');</script>
                        <?php elseif (!empty($coupon)): ?>
                            <script>alert('Mã giảm giá không hợp lệ');</script>
                        <?php endif; ?>
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
                    <?php if (!empty($checkout_items)): ?>
                        <?php foreach ($checkout_items as $product_id => $item): ?>
                            <input type="hidden" name="cart_items[<?php echo htmlspecialchars($product_id); ?>][product_id]" 
                                   value="<?php echo htmlspecialchars($item['product_id'] ?? $product_id); ?>">
                            <input type="hidden" name="cart_items[<?php echo htmlspecialchars($product_id); ?>][quantity]" 
                                   value="<?php echo htmlspecialchars($item['quantity'] ?? 1); ?>">
                            <input type="hidden" name="cart_items[<?php echo htmlspecialchars($product_id); ?>][price]" 
                                   value="<?php echo htmlspecialchars($item['price'] ?? 0); ?>">
                        <?php endforeach; ?>
                    <?php endif; ?>


                    <div class="form-actions">
                        <a href="cart.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i>
                            Quay lại giỏ hàng
                        </a>
                        <a href="../index.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i>
                            Quay lại trang chủ
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
                        $ship_price = 0;
                        $total = 0;
                        if (!empty($checkout_items)): 
                            foreach ($checkout_items as $item): 
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
                            
                            $ship_price = isset($_SESSION['coupon']) && $_SESSION['coupon'] == 'SM' ? 0 : $total * 0.05 ;
                            $total = $total + $ship_price ;
                           
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
                            <span class="<?php echo ($ship_price == 0) ? 'free-ship' : ''; ?>">
                                <?php echo ($ship_price == 0) ? 'Miễn phí' : number_format($ship_price, 0, ',', '.') . '₫'; ?>
                            </span>
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

