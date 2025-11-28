<?php
session_start();

$dbPath = realpath(__DIR__ . '/../../database/db.php');
if (!$dbPath || !file_exists($dbPath)) die('Database file not found.');
require_once $dbPath;

// Xuất CSV nếu yêu cầu
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=users_statistics_' . date('Ymd_His') . '.csv');

    $out = fopen('php://output', 'w');
    fputcsv($out, ['Thống kê người dùng', 'Giá trị']);

    // Tổng
    $res = $conn->query("SELECT COUNT(*) as total FROM nguoidung");
    $total = $res->fetch_assoc()['total'] ?? 0;
    fputcsv($out, ['Tổng người dùng', $total]);

    // Theo vai trò
    fputcsv($out, []);
    fputcsv($out, ['Theo vai trò', 'Số lượng']);
    $stmt = $conn->prepare("SELECT VaiTro, COUNT(*) as cnt FROM nguoidung GROUP BY VaiTro");
    $stmt->execute();
    $r = $stmt->get_result();
    while ($row = $r->fetch_assoc()) {
        fputcsv($out, [$row['VaiTro'], $row['cnt']]);
    }
    $stmt->close();

    // Theo trạng thái hoạt động
    fputcsv($out, []);
    fputcsv($out, ['Theo trạng thái (TrangThaiHoatDong)', 'Số lượng']);
    $stmt = $conn->prepare("SELECT TrangThaiHoatDong, COUNT(*) as cnt FROM nguoidung GROUP BY TrangThaiHoatDong");
    $stmt->execute();
    $r = $stmt->get_result();
    while ($row = $r->fetch_assoc()) {
        $label = $row['TrangThaiHoatDong'] ? 'Hoạt động' : 'Bị khóa';
        fputcsv($out, [$label, $row['cnt']]);
    }
    $stmt->close();

    fclose($out);
    exit();
}

// Dữ liệu thống kê
$res = $conn->query("SELECT COUNT(*) as total FROM nguoidung");
$totalUsers = (int)($res->fetch_assoc()['total'] ?? 0);

// Theo vai trò
$roles = [];
$stmt = $conn->prepare("SELECT VaiTro, COUNT(*) as cnt FROM nguoidung GROUP BY VaiTro");
$stmt->execute();
$r = $stmt->get_result();
while ($row = $r->fetch_assoc()) $roles[$row['VaiTro']] = (int)$row['cnt'];
$stmt->close();

// Theo trạng thái
$states = [];
$stmt = $conn->prepare("SELECT TrangThaiHoatDong, COUNT(*) as cnt FROM nguoidung GROUP BY TrangThaiHoatDong");
$stmt->execute();
$r = $stmt->get_result();
while ($row = $r->fetch_assoc()) {
    $key = $row['TrangThaiHoatDong'] ? 'Hoạt động' : 'Bị khóa';
    $states[$key] = (int)$row['cnt'];
}
$stmt->close();

include __DIR__ . '/header.php';
?>
<!doctype html>
<html lang="vi">
<head>
<meta charset="utf-8">
<title>Thống kê người dùng</title>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container mt-4">
    <h3>Thống kê người dùng</h3>

    <div class="mb-3 d-flex justify-content-between align-items-center">
        <div> Tổng người dùng: <strong><?= $totalUsers ?></strong> </div>
        <div>
            <a href="?export=csv" class="btn btn-outline-primary btn-sm">Xuất CSV</a>
            <button class="btn btn-outline-secondary btn-sm" onclick="window.open('?print=1','_blank').print()">Xuất PDF</button>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <div class="card">
                <div class="card-header">Phân theo vai trò</div>
                <div class="card-body">
                    <table class="table table-sm">
                        <thead><tr><th>Vai trò</th><th>Số lượng</th></tr></thead>
                        <tbody>
                            <?php foreach ($roles as $roleName => $cnt): ?>
                                <tr>
                                    <td><?= htmlspecialchars($roleName) ?></td>
                                    <td><?= $cnt ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-3">
            <div class="card">
                <div class="card-header">Phân theo trạng thái</div>
                <div class="card-body">
                    <table class="table table-sm">
                        <thead><tr><th>Trạng thái</th><th>Số lượng</th></tr></thead>
                        <tbody>
                            <?php foreach ($states as $st => $cnt): ?>
                                <tr>
                                    <td><?= htmlspecialchars($st) ?></td>
                                    <td><?= $cnt ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Bảng chi tiết người dùng (có phân trang đơn giản) -->
    <div class="card mb-4">
        <div class="card-header">Danh sách (mới nhất trước)</div>
        <div class="card-body p-0">
            <div style="max-height:360px; overflow:auto;">
                <table class="table table-sm mb-0">
                    <thead class="thead-light"><tr>
                        <th>ID</th><th>Họ tên</th><th>Email</th><th>Vai trò</th><th>Trạng thái</th>
                    </tr></thead>
                    <tbody>
                        <?php
                        $stmt = $conn->prepare("SELECT ID_NguoiDung, HoTen, Email, VaiTro, TrangThaiHoatDong FROM nguoidung ORDER BY ID_NguoiDung DESC LIMIT 200");
                        $stmt->execute();
                        $r = $stmt->get_result();
                        while ($u = $r->fetch_assoc()):
                        ?>
                        <tr>
                            <td><?= $u['ID_NguoiDung'] ?></td>
                            <td><?= htmlspecialchars($u['HoTen']) ?></td>
                            <td><?= htmlspecialchars($u['Email']) ?></td>
                            <td><?= htmlspecialchars($u['VaiTro']) ?></td>
                            <td><?= $u['TrangThaiHoatDong'] ? 'Hoạt động' : 'Bị khóa' ?></td>
                        </tr>
                        <?php endwhile;
                        $stmt->close();
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <a href="user.php" class="btn btn-light">Quay lại quản lý người dùng</a>
</div>

<?php
// Print-friendly view (in ra HTML sạch để dùng print -> PDF)
if (isset($_GET['print']) && $_GET['print'] == '1') {
    // Gọi lại dữ liệu và render minimal page
    // (đã có data above; để đơn giản, dùng JS popup từ nút "Xuất PDF")
}
?>

</body>
</html>
?>