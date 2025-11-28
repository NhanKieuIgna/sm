<?php
session_start();

$dbPath = realpath(__DIR__ . '/../../database/db.php');
if (!$dbPath || !file_exists($dbPath)) die('Database file not found.');
require_once $dbPath;

// Lấy tham số lọc từ URL
$filterType = $_GET['filter'] ?? 'all'; // all, day, week, month, year
$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';

// Xây dựng điều kiện WHERE cho query
$whereConditions = [];
$params = [];
$types = '';

if ($filterType === 'day' && $startDate) {
    $whereConditions[] = "DATE(dh.NgayDatHang) = ?";
    $params[] = $startDate;
    $types .= 's';
} elseif ($filterType === 'week' && $startDate) {
    $whereConditions[] = "YEARWEEK(dh.NgayDatHang, 1) = YEARWEEK(?, 1)";
    $params[] = $startDate;
    $types .= 's';
} elseif ($filterType === 'month' && $startDate) {
    $whereConditions[] = "DATE_FORMAT(dh.NgayDatHang, '%Y-%m') = DATE_FORMAT(?, '%Y-%m')";
    $params[] = $startDate;
    $types .= 's';
} elseif ($filterType === 'year' && $startDate) {
    $whereConditions[] = "YEAR(dh.NgayDatHang) = YEAR(?)";
    $params[] = $startDate;
    $types .= 's';
} elseif ($filterType === 'range' && $startDate && $endDate) {
    $whereConditions[] = "DATE(dh.NgayDatHang) BETWEEN ? AND ?";
    $params[] = $startDate;
    $params[] = $endDate;
    $types .= 'ss';
}

$whereClause = count($whereConditions) > 0 ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

// Xuất CSV nếu yêu cầu
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=transactions_statistics_' . date('Ymd_His') . '.csv');
    $out = fopen('php://output', 'w');
    
    // BOM cho UTF-8
    fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
    
    fputcsv($out, ['Thống kê giao dịch']);
    fputcsv($out, []);
    
    // Tổng quan
    $query = "SELECT 
        COUNT(*) as total_orders,
        SUM(TongGiaTriDonHang) as total_revenue,
        AVG(TongGiaTriDonHang) as avg_order_value
        FROM danhsachdonhang dh $whereClause";
    
    $stmt = $conn->prepare($query);
    if (count($params) > 0) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $summary = $result->fetch_assoc();
    $stmt->close();
    
    fputcsv($out, ['Tổng số đơn hàng', $summary['total_orders'] ?? 0]);
    fputcsv($out, ['Tổng doanh thu', number_format($summary['total_revenue'] ?? 0, 0, ',', '.') . ' VNĐ']);
    fputcsv($out, ['Giá trị đơn hàng trung bình', number_format($summary['avg_order_value'] ?? 0, 0, ',', '.') . ' VNĐ']);
    fputcsv($out, []);
    
    // Theo trạng thái
    fputcsv($out, ['Theo trạng thái đơn hàng', 'Số lượng', 'Tổng giá trị']);
    $query = "SELECT TrangThaiDonHang, COUNT(*) as cnt, SUM(TongGiaTriDonHang) as total 
              FROM danhsachdonhang dh $whereClause 
              GROUP BY TrangThaiDonHang";
    $stmt = $conn->prepare($query);
    if (count($params) > 0) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $r = $stmt->get_result();
    while ($row = $r->fetch_assoc()) {
        fputcsv($out, [
            $row['TrangThaiDonHang'], 
            $row['cnt'],
            number_format($row['total'] ?? 0, 0, ',', '.') . ' VNĐ'
        ]);
    }
    $stmt->close();
    fputcsv($out, []);
    
    // Danh sách giao dịch
    fputcsv($out, ['Mã ĐH', 'Người mua', 'Người bán', 'Ngày đặt', 'Trạng thái', 'Tổng giá trị']);
    $query = "SELECT 
        dh.ID_DonHang,
        nm.HoTen as NguoiMua,
        nb.HoTen as NguoiBan,
        dh.NgayDatHang,
        dh.TrangThaiDonHang,
        dh.TongGiaTriDonHang
        FROM danhsachdonhang dh
        LEFT JOIN nguoidung nm ON dh.ID_NguoiMua = nm.ID_NguoiDung
        LEFT JOIN nguoidung nb ON dh.ID_NguoiBan = nb.ID_NguoiDung
        $whereClause
        ORDER BY dh.NgayDatHang DESC";
    
    $stmt = $conn->prepare($query);
    if (count($params) > 0) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $r = $stmt->get_result();
    while ($row = $r->fetch_assoc()) {
        fputcsv($out, [
            $row['ID_DonHang'],
            $row['NguoiMua'],
            $row['NguoiBan'],
            $row['NgayDatHang'],
            $row['TrangThaiDonHang'],
            number_format($row['TongGiaTriDonHang'], 0, ',', '.') . ' VNĐ'
        ]);
    }
    $stmt->close();
    
    fclose($out);
    exit();
}

// Lấy dữ liệu tổng quan
$query = "SELECT 
    COUNT(*) as total_orders,
    SUM(TongGiaTriDonHang) as total_revenue,
    AVG(TongGiaTriDonHang) as avg_order_value,
    SUM(CASE WHEN TrangThaiDonHang = 'HoanThanh' THEN 1 ELSE 0 END) as completed_orders,
    SUM(CASE WHEN TrangThaiDonHang = 'DaHuy' THEN 1 ELSE 0 END) as cancelled_orders
    FROM danhsachdonhang dh $whereClause";

$stmt = $conn->prepare($query);
if (count($params) > 0) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$summary = $result->fetch_assoc();
$stmt->close();

// Lấy dữ liệu theo trạng thái
$statusData = [];
$query = "SELECT TrangThaiDonHang, COUNT(*) as cnt, SUM(TongGiaTriDonHang) as total 
          FROM danhsachdonhang dh $whereClause 
          GROUP BY TrangThaiDonHang";
$stmt = $conn->prepare($query);
if (count($params) > 0) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$r = $stmt->get_result();
while ($row = $r->fetch_assoc()) {
    $statusData[] = $row;
}
$stmt->close();

// Lấy danh sách giao dịch (phân trang)
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Đếm tổng số giao dịch
$countQuery = "SELECT COUNT(*) as total FROM danhsachdonhang dh $whereClause";
$stmt = $conn->prepare($countQuery);
if (count($params) > 0) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$totalTransactions = $result->fetch_assoc()['total'];
$stmt->close();
$totalPages = ceil($totalTransactions / $perPage);

// Lấy danh sách giao dịch
$transactions = [];
$query = "SELECT 
    dh.ID_DonHang,
    nm.HoTen as NguoiMua,
    nb.HoTen as NguoiBan,
    dh.NgayDatHang,
    dh.TrangThaiDonHang,
    dh.TongGiaTriDonHang,
    dh.PhuongThucThanhToan
    FROM danhsachdonhang dh
    LEFT JOIN nguoidung nm ON dh.ID_NguoiMua = nm.ID_NguoiDung
    LEFT JOIN nguoidung nb ON dh.ID_NguoiBan = nb.ID_NguoiDung
    $whereClause
    ORDER BY dh.NgayDatHang DESC
    LIMIT ? OFFSET ?";

$stmt = $conn->prepare($query);
$bindTypes = $types . 'ii';
$bindParams = array_merge($params, [$perPage, $offset]);
if (count($bindParams) > 0) {
    $stmt->bind_param($bindTypes, ...$bindParams);
}
$stmt->execute();
$r = $stmt->get_result();
while ($row = $r->fetch_assoc()) {
    $transactions[] = $row;
}
$stmt->close();

include __DIR__ . '/header.php';
?>
<!doctype html>
<html lang="vi">
<head>
<meta charset="utf-8">
<title>Thống kê giao dịch</title>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
    .stat-card {
        border-left: 4px solid #4e73df;
        transition: transform 0.2s;
    }
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .stat-value {
        font-size: 1.5rem;
        font-weight: bold;
        color: #4e73df;
    }
    .stat-label {
        font-size: 0.85rem;
        color: #858796;
        text-transform: uppercase;
    }
    .filter-section {
        background: #f8f9fc;
        padding: 1rem;
        border-radius: 0.35rem;
        margin-bottom: 1.5rem;
    }
    .status-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 0.25rem;
        font-size: 0.75rem;
        font-weight: 600;
    }
    .status-ChoXacNhan { background: #ffc107; color: #000; }
    .status-ChoGiaoHang { background: #17a2b8; color: #fff; }
    .status-DangXuLy { background: #007bff; color: #fff; }
    .status-DangVanChuyen { background: #6f42c1; color: #fff; }
    .status-DaGiao { background: #20c997; color: #fff; }
    .status-HoanThanh { background: #28a745; color: #fff; }
    .status-DaHuy { background: #dc3545; color: #fff; }
    .status-KhieuNai { background: #fd7e14; color: #fff; }
    .table-hover tbody tr:hover {
        background-color: #f8f9fc;
    }
    .pagination {
        margin-top: 1rem;
    }
</style>
</head>
<body class="bg-light">
<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="m-0">Thống kê giao dịch</h3>
        <div>
            <a href="stats.php" class="btn btn-light btn-sm">← Quay lại</a>
        </div>
    </div>

    <!-- Bộ lọc -->
    <div class="filter-section">
        <form method="GET" action="" class="form-inline">
            <label class="mr-2">Lọc theo:</label>
            <select name="filter" class="form-control form-control-sm mr-2" id="filterType">
                <option value="all" <?= $filterType === 'all' ? 'selected' : '' ?>>Tất cả</option>
                <option value="day" <?= $filterType === 'day' ? 'selected' : '' ?>>Theo ngày</option>
                <option value="week" <?= $filterType === 'week' ? 'selected' : '' ?>>Theo tuần</option>
                <option value="month" <?= $filterType === 'month' ? 'selected' : '' ?>>Theo tháng</option>
                <option value="year" <?= $filterType === 'year' ? 'selected' : '' ?>>Theo năm</option>
                <option value="range" <?= $filterType === 'range' ? 'selected' : '' ?>>Khoảng thời gian</option>
            </select>
            
            <div id="dateInputs" style="display: none;">
                <input type="date" name="start_date" class="form-control form-control-sm mr-2" 
                       value="<?= htmlspecialchars($startDate) ?>" id="startDate">
                <span id="endDateContainer" style="display: none;">
                    <label class="mr-2">đến</label>
                    <input type="date" name="end_date" class="form-control form-control-sm mr-2" 
                           value="<?= htmlspecialchars($endDate) ?>" id="endDate">
                </span>
            </div>
            
            <button type="submit" class="btn btn-primary btn-sm mr-2">Áp dụng</button>
            <a href="?export=csv<?= $filterType !== 'all' ? '&filter=' . $filterType . '&start_date=' . $startDate . '&end_date=' . $endDate : '' ?>" 
               class="btn btn-outline-success btn-sm">Xuất CSV</a>
        </form>
    </div>

    <!-- Tổng quan -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="stat-label">Tổng đơn hàng</div>
                    <div class="stat-value"><?= number_format($summary['total_orders'] ?? 0) ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card stat-card" style="border-left-color: #1cc88a;">
                <div class="card-body">
                    <div class="stat-label">Tổng doanh thu</div>
                    <div class="stat-value" style="color: #1cc88a;">
                        <?= number_format($summary['total_revenue'] ?? 0, 0, ',', '.') ?> ₫
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card stat-card" style="border-left-color: #36b9cc;">
                <div class="card-body">
                    <div class="stat-label">Giá trị TB/đơn</div>
                    <div class="stat-value" style="color: #36b9cc;">
                        <?= number_format($summary['avg_order_value'] ?? 0, 0, ',', '.') ?> ₫
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card stat-card" style="border-left-color: #f6c23e;">
                <div class="card-body">
                    <div class="stat-label">Hoàn thành / Hủy</div>
                    <div class="stat-value" style="color: #f6c23e; font-size: 1.2rem;">
                        <?= number_format($summary['completed_orders'] ?? 0) ?> / 
                        <?= number_format($summary['cancelled_orders'] ?? 0) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Biểu đồ theo trạng thái -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <strong>Phân bố theo trạng thái</strong>
                </div>
                <div class="card-body">
                    <canvas id="statusChart" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <strong>Chi tiết theo trạng thái</strong>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>Trạng thái</th>
                                <th class="text-right">Số lượng</th>
                                <th class="text-right">Tổng giá trị</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($statusData as $status): ?>
                            <tr>
                                <td>
                                    <span class="status-badge status-<?= $status['TrangThaiDonHang'] ?>">
                                        <?= $status['TrangThaiDonHang'] ?>
                                    </span>
                                </td>
                                <td class="text-right"><?= number_format($status['cnt']) ?></td>
                                <td class="text-right"><?= number_format($status['total'], 0, ',', '.') ?> ₫</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Danh sách giao dịch -->
    <div class="card">
        <div class="card-header">
            <strong>Danh sách giao dịch</strong>
            <span class="text-muted">(Tổng: <?= number_format($totalTransactions) ?> giao dịch)</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Mã ĐH</th>
                            <th>Người mua</th>
                            <th>Người bán</th>
                            <th>Ngày đặt</th>
                            <th>Trạng thái</th>
                            <th>PT Thanh toán</th>
                            <th class="text-right">Tổng giá trị</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($transactions) > 0): ?>
                            <?php foreach ($transactions as $trans): ?>
                            <tr>
                                <td><strong>#<?= $trans['ID_DonHang'] ?></strong></td>
                                <td><?= htmlspecialchars($trans['NguoiMua'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($trans['NguoiBan'] ?? 'N/A') ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($trans['NgayDatHang'])) ?></td>
                                <td>
                                    <span class="status-badge status-<?= $trans['TrangThaiDonHang'] ?>">
                                        <?= $trans['TrangThaiDonHang'] ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($trans['PhuongThucThanhToan'] ?? 'N/A') ?></td>
                                <td class="text-right">
                                    <strong><?= number_format($trans['TongGiaTriDonHang'], 0, ',', '.') ?> ₫</strong>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    Không có giao dịch nào
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <?php if ($totalPages > 1): ?>
        <div class="card-footer">
            <nav>
                <ul class="pagination pagination-sm mb-0 justify-content-center">
                    <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page - 1 ?><?= $filterType !== 'all' ? '&filter=' . $filterType . '&start_date=' . $startDate . '&end_date=' . $endDate : '' ?>">
                            « Trước
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?><?= $filterType !== 'all' ? '&filter=' . $filterType . '&start_date=' . $startDate . '&end_date=' . $endDate : '' ?>">
                            <?= $i ?>
                        </a>
                    </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page + 1 ?><?= $filterType !== 'all' ? '&filter=' . $filterType . '&start_date=' . $startDate . '&end_date=' . $endDate : '' ?>">
                            Sau »
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Xử lý hiển thị input date theo loại filter
const filterType = document.getElementById('filterType');
const dateInputs = document.getElementById('dateInputs');
const endDateContainer = document.getElementById('endDateContainer');
const startDate = document.getElementById('startDate');
const endDate = document.getElementById('endDate');

function updateDateInputs() {
    const value = filterType.value;
    if (value === 'all') {
        dateInputs.style.display = 'none';
    } else {
        dateInputs.style.display = 'inline-block';
        if (value === 'range') {
            endDateContainer.style.display = 'inline-block';
            endDate.required = true;
        } else {
            endDateContainer.style.display = 'none';
            endDate.required = false;
        }
        
        // Cập nhật type của input date
        if (value === 'month') {
            startDate.type = 'month';
        } else if (value === 'week') {
            startDate.type = 'week';
        } else {
            startDate.type = 'date';
        }
        startDate.required = true;
    }
}

filterType.addEventListener('change', updateDateInputs);
updateDateInputs();

// Biểu đồ trạng thái
const statusLabels = <?= json_encode(array_column($statusData, 'TrangThaiDonHang')) ?>;
const statusCounts = <?= json_encode(array_column($statusData, 'cnt')) ?>;

const statusColors = {
    'ChoXacNhan': '#ffc107',
    'ChoGiaoHang': '#17a2b8',
    'DangXuLy': '#007bff',
    'DangVanChuyen': '#6f42c1',
    'DaGiao': '#20c997',
    'HoanThanh': '#28a745',
    'DaHuy': '#dc3545',
    'KhieuNai': '#fd7e14'
};

const backgroundColors = statusLabels.map(label => statusColors[label] || '#858796');

const statusChartData = {
    labels: statusLabels,
    datasets: [{
        label: 'Số lượng đơn hàng',
        data: statusCounts,
        backgroundColor: backgroundColors,
        borderWidth: 1
    }]
};

const statusChartConfig = {
    type: 'doughnut',
    data: statusChartData,
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
            }
        }
    }
};

const statusCtx = document.getElementById('statusChart').getContext('2d');
new Chart(statusCtx, statusChartConfig);
</script>

</body>
</html>
