<?php
session_start();

require_once 'C:\wamp64\www\SM\database\db.php';

// Lấy tham số từ URL
$userId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$action = isset($_GET['ban']) ? (int)$_GET['ban'] : -1; // 0 = khóa, 1 = gỡ khóa

// Validate
if ($userId <= 0 || !in_array($action, [0, 1])) {
    $_SESSION['flash'] = [
        'type' => 'error',
        'msg' => 'Tham số không hợp lệ!'
    ];
    header('Location: user.php');
    exit;
}

// Không cho phép khóa chính mình
if ($userId == $_SESSION['user_id']) {
    $_SESSION['flash'] = [
        'type' => 'error',
        'msg' => 'Bạn không thể khóa tài khoản của chính mình!'
    ];
    header('Location: user.php');
    exit;
}

// Kiểm tra người dùng có tồn tại không
$sqlCheck = "SELECT ID_NguoiDung, HoTen, TrangThaiHoatDong FROM nguoidung WHERE ID_NguoiDung = ?";
$stmtCheck = $conn->prepare($sqlCheck);
$stmtCheck->bind_param('i', $userId);
$stmtCheck->execute();
$resultCheck = $stmtCheck->get_result();

if ($resultCheck->num_rows === 0) {
    $_SESSION['flash'] = [
        'type' => 'error',
        'msg' => 'Người dùng không tồn tại!'
    ];
    $stmtCheck->close();
    header('Location: user.php');
    exit;
}

$user = $resultCheck->fetch_assoc();
$stmtCheck->close();

// Xác định trạng thái mới
$newStatus = $action; // 0 = khóa, 1 = mở khóa
$statusText = $newStatus ? 'mở khóa' : 'khóa';

// Kiểm tra trạng thái hiện tại
if ($user['TrangThaiHoatDong'] == $newStatus) {
    $_SESSION['flash'] = [
        'type' => 'error',
        'msg' => "Tài khoản đã ở trạng thái $statusText rồi!"
    ];
    header('Location: user.php');
    exit;
}

// Cập nhật trạng thái
$sqlUpdate = "UPDATE nguoidung SET TrangThaiHoatDong = ? WHERE ID_NguoiDung = ?";
$stmtUpdate = $conn->prepare($sqlUpdate);
$stmtUpdate->bind_param('ii', $newStatus, $userId);

if ($stmtUpdate->execute()) {
    $actionText = $newStatus ? 'Gỡ khóa' : 'Khóa';
    $message = $actionText . " tài khoản của " . htmlspecialchars($user['HoTen']) . " thành công!";
    $alertType = 'success';
} else {
    $message = "Có lỗi xảy ra khi $statusText tài khoản: " . $conn->error;
    $alertType = 'error';
}

$stmtUpdate->close();
$conn->close();

// Quay lại trang danh sách với các tham số tìm kiếm (nếu có)
$redirect = 'user.php';
if (isset($_GET['search']) && $_GET['search'] !== '') {
    $redirect .= '?search=' . urlencode($_GET['search']);
}
if (isset($_GET['page']) && $_GET['page'] > 1) {
    $redirect .= (strpos($redirect, '?') !== false ? '&' : '?') . 'page=' . (int)$_GET['page'];
}

// Hiển thị thông báo popup trước khi redirect
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<script>
Swal.fire({
    icon: '<?= $alertType ?>',
    title: '<?= $alertType === 'success' ? 'Thành công!' : 'Lỗi!' ?>',
    text: '<?= addslashes($message) ?>',
    showConfirmButton: true,
    timer: 2000
}).then(() => {
    window.location.href = '<?= $redirect ?>';
});
</script>
</body>
</html>
<?php
exit;
?>