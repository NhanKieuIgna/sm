<?php
session_start();
require_once __DIR__ . '/../database/db.php';

// Kiểm tra và khởi tạo giỏ hàng trong session
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Lấy thông tin từ request
$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
$quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
$action = isset($_POST['action']) ? $_POST['action'] : 'add';

if ($product_id <= 0) {
    if (isset($_POST['ajax'])) {
        echo json_encode(['success' => false, 'message' => 'ID sản phẩm không hợp lệ']);
        exit();
    }
    header('Location: index.php');
    exit();
}

// Kết nối database để lấy thông tin sản phẩm
require_once('../database/db.php');

$sql = "SELECT sp.*, ha.URL_HinhAnh 
        FROM sanpham sp 
        LEFT JOIN hinhanhsanpham ha ON sp.ID_SanPham = ha.ID_SanPham 
        WHERE sp.ID_SanPham = ? AND sp.TrangThaiDangBan = 'DangBan'
        GROUP BY sp.ID_SanPham";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $product_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$product = mysqli_fetch_assoc($result);

if (!$product) {
    if (isset($_POST['ajax'])) {
        echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại']);
        exit();
    }
    header('Location: index.php');
    exit();
}
// Kiểm tra số lượng tồn kho
if (!isset($product['SoLuong']) || $product['SoLuong'] < 1) {
    if (isset($_POST['ajax'])) {
        echo json_encode(['success' => false, 'message' => 'Sản phẩm đã hết hàng']);
        exit();
    }
    header('Location: index.php');
    exit();
}

// Xử lý các hành động
switch ($action) {
    case 'add':
        // Kiểm tra xem sản phẩm đã có trong giỏ chưa
        if (isset($_SESSION['cart'][$product_id])) {
            // Tăng số lượng
            $_SESSION['cart'][$product_id]['quantity'] += $quantity;
            
            // Kiểm tra không vượt quá số lượng tồn kho
            if ($_SESSION['cart'][$product_id]['quantity'] > $product['SoLuong']) {
                $_SESSION['cart'][$product_id]['quantity'] = $product['SoLuong'];
            }
        } else {
            // Thêm sản phẩm mới vào giỏ
            $_SESSION['cart'][$product_id] = [
                'id' => $product['ID_SanPham'],
                'name' => $product['TenSanPham'],
                'price' => $product['Gia'],
                'image' => $product['URL_HinhAnh'],
                'condition' => $product['TinhTrang'],
                'max_quantity' => $product['SoLuong'],
                'quantity' => min($quantity, $product['SoLuong'])
            ];
        }
        $message = 'Đã thêm sản phẩm vào giỏ hàng';
        break;
        
    case 'update':
        // Cập nhật số lượng
        if (isset($_SESSION['cart'][$product_id])) {
            if ($quantity <= 0) {
                unset($_SESSION['cart'][$product_id]);
                $message = 'Đã xóa sản phẩm khỏi giỏ hàng';
            } else {
                $_SESSION['cart'][$product_id]['quantity'] = min($quantity, $product['SoLuong']);
                $message = 'Đã cập nhật số lượng';
            }
        }
        break;
        
    case 'remove':
        // Xóa sản phẩm khỏi giỏ
        if (isset($_SESSION['cart'][$product_id])) {
            unset($_SESSION['cart'][$product_id]);
            $message = 'Đã xóa sản phẩm khỏi giỏ hàng';
        }
        break;
}

// Tính tổng số sản phẩm trong giỏ
$cart_count = 0;
foreach ($_SESSION['cart'] as $item) {
    $cart_count += $item['quantity'];
}

// Trả về kết quả
if (isset($_POST['ajax'])) {
    echo json_encode([
        'success' => true, 
        'message' => $message ?? 'Thành công',
        'cart_count' => $cart_count
    ]);
} else {
    // Redirect về trang trước đó hoặc giỏ hàng
    $redirect = isset($_POST['redirect']) ? $_POST['redirect'] : 'user/cart.php';
    header('Location: ' . $redirect);
}
exit();
?>
