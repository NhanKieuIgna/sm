<?php
session_start();

require "sm/dp.php"; 


if (!isset($_SESSION['user_id']) || $_SESSION['vaitro'] !== 'NguoiGiaoHang') {
    header("Location: login.php");
    exit();
}

$driver_id = $_SESSION['user_id'];
$order_id = $_POST['order_id'] ?? null;
$action = $_POST['action'] ?? null;
if ($action === 'start_delivery' && $order_id) {
    $order_id_safe = (int)$order_id;
    $driver_id_safe = (int)$driver_id;
    

    mysqli_begin_transaction($conn);

    try {
        
        $sql_check = "SELECT ID_NguoiGiaoHang FROM danhsachdonhang WHERE ID_DonHang = ? FOR UPDATE";
        $stmt_check = mysqli_prepare($conn, $sql_check);
        mysqli_stmt_bind_param($stmt_check, 'i', $order_id_safe);
        mysqli_stmt_execute($stmt_check);
        $res_check = mysqli_stmt_get_result($stmt_check);
        $order_data = mysqli_fetch_assoc($res_check);
        mysqli_stmt_close($stmt_check);

        if ($order_data && $order_data['ID_NguoiGiaoHang'] === null) {
            
            $sql_update = "UPDATE danhsachdonhang 
                           SET ID_NguoiGiaoHang = ?, 
                               TrangThaiDonHang = 'DangVanChuyen' 
                           WHERE ID_DonHang = ?";
            $stmt_update = mysqli_prepare($conn, $sql_update);
            mysqli_stmt_bind_param($stmt_update, 'ii', $driver_id_safe, $order_id_safe);
            
            if (mysqli_stmt_execute($stmt_update)) {

                $trang_thai_moi = 'DangVanChuyen';
                $ghi_chu = "Tài xế ID $driver_id_safe đã nhận đơn và bắt đầu vận chuyển.";
                $sql_history = "INSERT INTO lichsutrangthai (ID_DonHang, TrangThaiMoi, GhiChu) 
                                VALUES (?, ?, ?)";
                $stmt_history = mysqli_prepare($conn, $sql_history);
                mysqli_stmt_bind_param($stmt_history, 'iss', $order_id_safe, $trang_thai_moi, $ghi_chu);
                mysqli_stmt_execute($stmt_history);
                mysqli_commit($conn);
                
                $_SESSION['message'] = "Đã nhận đơn hàng **#DH" . $order_id_safe . "** thành công! Đơn hàng đã chuyển sang mục 'Đơn hàng của bạn'.";
                $_SESSION['message_type'] = 'success';

            } else {
                
                mysqli_rollback($conn);
                $_SESSION['message'] = "Lỗi khi cập nhật CSDL: " . mysqli_error($conn);
                $_SESSION['message_type'] = 'error';
            }
            mysqli_stmt_close($stmt_update);
            
        } else {
          
            mysqli_rollback($conn); 
            $_SESSION['message'] = "Lỗi: Đơn hàng **#DH" . $order_id_safe . "** không tồn tại hoặc đã có người nhận trước đó.";
            $_SESSION['message_type'] = 'warning';
        }

    } catch (Exception $e) {
        mysqli_rollback($conn);
        $_SESSION['message'] = "Đã xảy ra lỗi hệ thống: " . $e->getMessage();
        $_SESSION['message_type'] = 'error';
    }
    
} else {
    $_SESSION['message'] = "Yêu cầu hành động không hợp lệ.";
    $_SESSION['message_type'] = 'error';
}
header("Location: delivery_index.php");
exit();
?>