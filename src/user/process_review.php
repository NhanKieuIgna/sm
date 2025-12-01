<?php
session_start();
require_once __DIR__ . '/../../database/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Check if form is submitted via POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: my-orders.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = intval($_POST['order_id'] ?? 0);
$product_id = intval($_POST['product_id'] ?? 0);
$rating = intval($_POST['rating'] ?? 0);
$comment = trim($_POST['comment'] ?? '');
$seller_id = intval($_SESSION['ID_NguoiBan'] ?? 0);
// Validate inputs
if ($order_id <= 0 || $product_id <= 0 || $rating < 1 || $rating > 5) {
    $_SESSION['error'] = 'Dữ liệu không hợp lệ';
    header('Location: review.php?order_id=' . $order_id);
    exit();
}

// Verify that the order belongs to the user
$verify_query = "SELECT ID_DonHang FROM danhsachdonhang 
                 WHERE ID_DonHang = $order_id AND ID_NguoiMua = $user_id";
$verify_result = mysqli_query($conn, $verify_query);

if (mysqli_num_rows($verify_result) === 0) {
    $_SESSION['error'] = 'Đơn hàng không hợp lệ';
    header('Location: my-orders.php');
    exit();
}

// Check if user already reviewed this product
$check_query = "SELECT ID_DanhGia FROM danhgia_nhanxet 
                WHERE ID_DonHang = $product_id AND ID_NguoiDanhGia = $user_id";
$check_result = mysqli_query($conn, $check_query);

if (mysqli_num_rows($check_result) > 0) {
    $_SESSION['error'] = 'Bạn đã đánh giá sản phẩm này rồi';
    header('Location: review.php?order_id=' . $order_id);
    exit();
}

// Escape comment for SQL
$comment_escaped = mysqli_real_escape_string($conn, $comment);

// Insert review into database
$insert_query = "INSERT INTO danhgia_nhanxet (ID_DonHang,ID_NguoiDanhGia, ID_NguoiDuocDanhGia, SoSao, NhanXet, NgayDanhGia) 
                 VALUES ($product_id, $user_id, $seller_id, $rating, '$comment_escaped', NOW())";

if (mysqli_query($conn, $insert_query)) {
    $_SESSION['success'] = 'Cảm ơn bạn đã đánh giá sản phẩm!';
    header('Location: my-orders.php');
} else {
    $_SESSION['error'] = 'Có lỗi xảy ra, vui lòng thử lại';
    header('Location: review.php?order_id=' . $order_id);
}

exit();
?>
