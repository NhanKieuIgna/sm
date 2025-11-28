<?php
session_start();

$dbPath = realpath(__DIR__ . '/../../database/db.php');
if (!$dbPath || !file_exists($dbPath)) die('Database file not found.');
require_once $dbPath;

// Xuất CSV nếu yêu cầu
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=products_statistics_' . date('Ymd_His') . '.csv');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Thống kê sản phẩm', 'Giá trị']);

    // Tổng sản phẩm
    $res = $conn->query("SELECT COUNT(*) as total FROM sanpham");
    $total = $res->fetch_assoc()['total'] ?? 0;
    fputcsv($out, ['Tổng sản phẩm', $total]);

    // Theo danh mục
    fputcsv($out, []);
    fputcsv($out, ['Theo danh mục', 'Số lượng']);
    $stmt = $conn->prepare("SELECT dm.TenDanhMuc, COUNT(*) as cnt FROM sanpham sp LEFT JOIN danhmuc dm ON sp.ID_DanhMuc = dm.ID_DanhMuc GROUP BY sp.ID_DanhMuc");
    $stmt->execute();
    $r = $stmt->get_result();
    while ($row = $r->fetch_assoc()) fputcsv($out, [$row['TenDanhMuc'] ?? '(Không)', $row['cnt']]);
    $stmt->close();

    // Theo trạng thái
    fputcsv($out, []);
    fputcsv($out, ['Theo trạng thái', 'Số lượng']);
    $stmt = $conn->prepare("SELECT TrangThaiDangBan, COUNT(*) as cnt FROM sanpham GROUP BY TrangThaiDangBan");
    $stmt->execute();
    $r = $stmt->get_result();
    while ($row = $r->fetch_assoc()) fputcsv($out, [$row['TrangThaiDangBan'], $row['cnt']]);
    $stmt->close();

    fclose($out);
    exit();
}

// Lấy dữ liệu cho chart: theo danh mục
$cats = [];
$stmt = $conn->prepare("SELECT dm.TenDanhMuc AS label, COUNT(*) as value FROM sanpham sp LEFT JOIN danhmuc dm ON sp.ID_DanhMuc = dm.ID_DanhMuc GROUP BY sp.ID_DanhMuc");
$stmt->execute();
$r = $stmt->get_result();
while ($row = $r->fetch_assoc()) {
    $label = $row['label'] ?? '(Không)';
    $cats[] = ['label' => $label, 'value' => (int)$row['value']];
}
$stmt->close();

// Tổng sản phẩm
$res = $conn->query("SELECT COUNT(*) as total FROM sanpham");
$totalProducts = (int)($res->fetch_assoc()['total'] ?? 0);

include __DIR__ . '/header.php';
?>
<!doctype html>
<html lang="vi">
<head>
<meta charset="utf-8">
<title>Thống kê sản phẩm</title>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-light">
<div class="container mt-4">
    <h3>Thống kê sản phẩm</h3>

    <div class="mb-3 d-flex justify-content-between align-items-center">
        <div> Tổng sản phẩm: <strong><?= $totalProducts ?></strong> </div>
        <div>
            <a href="?export=csv" class="btn btn-outline-primary btn-sm">Xuất CSV</a>
            <button class="btn btn-outline-secondary btn-sm" onclick="window.open('?print=1','_blank').print()">Xuất PDF</button>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <canvas id="pieChart" height="160"></canvas>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">Chi tiết theo danh mục</div>
        <div class="card-body">
            <table class="table table-sm">
                <thead><tr><th>Danh mục</th><th>Số lượng</th></tr></thead>
                <tbody>
                    <?php foreach ($cats as $c): ?>
                        <tr>
                            <td><?= htmlspecialchars($c['label']) ?></td>
                            <td><?= $c['value'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <a href="product.php" class="btn btn-light">Quay lại quản lý sản phẩm</a>
</div>

<script>
const data = {
    labels: <?= json_encode(array_column($cats, 'label')) ?>,
    datasets: [{
        label: 'Số lượng',
        data: <?= json_encode(array_column($cats, 'value')) ?>,
        backgroundColor: [
            '#4e73df','#1cc88a','#36b9cc','#f6c23e','#e74a3b','#858796','#2e59d9','#17a673'
        ]
    }]
};
const config = {
    type: 'pie',
    data: data,
    options: { responsive: true, maintainAspectRatio: false }
};
const ctx = document.getElementById('pieChart').getContext('2d');
new Chart(ctx, config);
</script>

</body>
</html>
?>