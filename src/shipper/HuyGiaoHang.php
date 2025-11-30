<?php
session_start();
require_once __DIR__ . '/../../database/db.php'; 
// if (!isset($_SESSION['user_id']) || $_SESSION['vaitro'] !== 'NguoiGiaoHang') {
//     header("Location: login.php");
//     exit();
// }

$driver_id = $_SESSION['user_id'];
$order_id = $_POST['order_id'] ?? null;
$redirect_url = "my_donhang.php";

if (empty($order_id)) {
    $_SESSION['error_message'] = "Thiếu ID đơn hàng.";
    header("Location: " . $redirect_url);
    exit();
}
$sql_check_status = "SELECT TrangThaiDonHang, ID_NguoiGiaoHang 
                     FROM danhsachdonhang 
                     WHERE ID_DonHang = ?";

$stmt_check = mysqli_prepare($conn, $sql_check_status);
mysqli_stmt_bind_param($stmt_check, "i", $order_id);
mysqli_stmt_execute($stmt_check);
$result_check = mysqli_stmt_get_result($stmt_check);
$order = mysqli_fetch_assoc($result_check);
mysqli_stmt_close($stmt_check);
if (!$order) {
    $_SESSION['error_message'] = "Đơn hàng không tồn tại.";
    header("Location: " . $redirect_url);
    exit();
}

$current_status = $order['TrangThaiDonHang'];
$current_driver = $order['ID_NguoiGiaoHang'];
$valid_statuses = ['ChoXacNhan', 'ChoGiaoHang', 'DangXuLy'];
if (!in_array($current_status, $valid_statuses)) {
    $_SESSION['error_message'] = "Không thể nhận đơn hàng này. Trạng thái hiện tại là **{$current_status}**.";
} 

else if ($current_driver !== NULL && $current_driver != $driver_id) {
    $_SESSION['error_message'] = "Đơn hàng này đã được một người giao hàng khác nhận.";
}
else { 
    $new_status = 'DangVanChuyen'; 
    
    $sql_update = "UPDATE danhsachdonhang 
                   SET ID_NguoiGiaoHang = ?, TrangThaiDonHang = ? 
                   WHERE ID_DonHang = ? AND (ID_NguoiGiaoHang IS NULL OR ID_NguoiGiaoHang = ?)";

    $stmt_update = mysqli_prepare($conn, $sql_update);
    mysqli_stmt_bind_param($stmt_update, "isii", $driver_id, $new_status, $order_id, $driver_id);
    
    if (mysqli_stmt_execute($stmt_update)) {
        $log_note = "Người giao hàng ID {$driver_id} đã nhận đơn và bắt đầu vận chuyển.";
        $sql_log = "INSERT INTO lichsutrangthai (ID_DonHang, TrangThaiMoi, GhiChu) VALUES (?, ?, ?)";
        $stmt_log = mysqli_prepare($conn, $sql_log);
        mysqli_stmt_bind_param($stmt_log, "iss", $order_id, $new_status, $log_note);
        mysqli_stmt_execute($stmt_log);
        mysqli_stmt_close($stmt_log);
        
        $_SESSION['success_message'] = "Đã nhận đơn hàng **#{$order_id}** thành công! Vui lòng bắt đầu giao.";
    } else {
        $_SESSION['error_message'] = "Lỗi khi cập nhật đơn hàng: " . mysqli_error($conn);
    }
    mysqli_stmt_close($stmt_update);
}

header("Location: " . $redirect_url);
exit();
?>