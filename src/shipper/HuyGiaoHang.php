<?php
session_start();
require_once __DIR__ . '/../../database/db.php'; 

// if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'NguoiGiaoHang') {
//     header("Location: ../login.php"); 
//     exit();
// }

$driver_id = $_SESSION['user_id'];
$order_id = $_GET['id'] ?? null; 
$redirect_url = "my_donhang.php";

// Kiểm tra ID đơn hàng
if (empty($order_id)) {
    $_SESSION['error_message'] = "Thiếu ID đơn hàng để hủy.";
    header("Location: $redirect_url");
    exit();
}

// --- 2. KIỂM TRA TRẠNG THÁI VÀ QUYỀN SỞ HỮU HIỆN TẠI ---
$sql_check = "SELECT TrangThaiDonHang, ID_NguoiGiaoHang 
              FROM danhsachdonhang 
              WHERE ID_DonHang = ?";
$stmt_check = mysqli_prepare($conn, $sql_check);
mysqli_stmt_bind_param($stmt_check, "i", $order_id);
mysqli_stmt_execute($stmt_check);
$result_check = mysqli_stmt_get_result($stmt_check);
$order = mysqli_fetch_assoc($result_check);
mysqli_stmt_close($stmt_check);

$current_status = $order['TrangThaiDonHang'] ?? '';
$current_driver = $order['ID_NguoiGiaoHang'] ?? null;

// Các trạng thái mà Người Giao Hàng được phép hủy đơn (từ chối nhận)
$valid_cancel_statuses = ['ChoGiaoHang', 'DangXuLy', 'DangVanChuyen'];

if (!$order) {
    $_SESSION['error_message'] = "Đơn hàng không tồn tại.";
} else if (!in_array($current_status, $valid_cancel_statuses)) {
    $_SESSION['error_message'] = "Không thể hủy đơn hàng **#{$order_id}**. Trạng thái hiện tại là **{$current_status}**.";
} else if ($current_driver != $driver_id) {
    $_SESSION['error_message'] = "Bạn không có quyền hủy đơn hàng này vì bạn không phải người phụ trách.";
} else {
    // --- 3. CẬP NHẬT TRẠNG THÁI HỦY ĐƠN VÀO DB ---
    $new_status = 'ChoGiaoHang'; // Đưa đơn hàng trở lại pool chờ Người Giao Hàng mới

    $sql_update = "UPDATE danhsachdonhang 
                   SET ID_NguoiGiaoHang = NULL, TrangThaiDonHang = ?
                   WHERE ID_DonHang = ? AND ID_NguoiGiaoHang = ?";
    
    $stmt_update = mysqli_prepare($conn, $sql_update);
    // Bind các tham số: (string) cho new_status, (integer) cho order_id, (integer) cho driver_id
    mysqli_stmt_bind_param($stmt_update, "sii", $new_status, $order_id, $driver_id);
    
    if (mysqli_stmt_execute($stmt_update)) {
        
        // Ghi lại lịch sử trạng thái
        $log_note = "Người giao hàng ID {$driver_id} đã hủy nhận đơn hàng. Đơn hàng quay về trạng thái chờ.";
        $sql_log = "INSERT INTO lichsutrangthai (ID_DonHang, TrangThaiMoi, GhiChu) VALUES (?, ?, ?)";
        $stmt_log = mysqli_prepare($conn, $sql_log);
        mysqli_stmt_bind_param($stmt_log, "iss", $order_id, $new_status, $log_note);
        mysqli_stmt_execute($stmt_log);
        mysqli_stmt_close($stmt_log);
        
        $_SESSION['success_message'] = "Đã hủy nhận đơn hàng **#{$order_id}** thành công! Đơn hàng đã được đưa về trạng thái chờ Người Giao Hàng khác.";
        
    } else {
        $_SESSION['error_message'] = "Lỗi khi cập nhật đơn hàng: " . mysqli_error($conn);
    }
    mysqli_stmt_close($stmt_update);
}
header("Location: " . $redirect_url);
exit();
?>