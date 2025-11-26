<?php
session_start();

// Kết nối DB và header
require_once 'C:\wamp64\www\SM\database\db.php';
include 'C:\wamp64\www\SM\src\admin\header.php';

// Flash
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

// Xử lý xóa
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $pid = (int)$_GET['delete'];
    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("DELETE FROM hinhanhsanpham WHERE ID_SanPham = ?");
        $stmt->bind_param('i', $pid);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("DELETE FROM chitietdonhang WHERE ID_SanPham = ?");
        $stmt->bind_param('i', $pid);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("DELETE FROM sanpham WHERE ID_SanPham = ?");
        $stmt->bind_param('i', $pid);
        $stmt->execute();
        if ($stmt->affected_rows === 0) throw new Exception('Không tìm thấy sản phẩm hoặc ràng buộc khác.');
        $stmt->close();

        $conn->commit();
        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Xóa sản phẩm thành công.'];
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Lỗi khi xóa: ' . $e->getMessage()];
    }
    header('Location: product.php');
    exit();
}

// Danh sách sản phẩm (tìm kiếm + phân trang)
$key = trim($_GET['search'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;

$where = "";
$params = [];
$types = "";
if ($key !== '') {
    $where = "WHERE sp.TenSanPham LIKE ? OR nd.HoTen LIKE ? OR sp.MoTa LIKE ?";
    $k = "%$key%";
    $params = [$k, $k, $k];
    $types = 'sss';
}

$sqlCount = "SELECT COUNT(*) as total FROM sanpham sp LEFT JOIN nguoidung nd ON sp.ID_NguoiBan = nd.ID_NguoiDung $where";
$stmt = $conn->prepare($sqlCount);
if ($types) $stmt->bind_param($types, ...$params);
$stmt->execute();
$res = $stmt->get_result();
$total = (int)$res->fetch_assoc()['total'];
$stmt->close();

$totalPages = max(1, ceil($total / $limit));
if ($page > $totalPages) $page = $totalPages;
$offset = ($page - 1) * $limit;

$sql = "SELECT sp.*, nd.HoTen AS NguoiBanName, dm.TenDanhMuc
        FROM sanpham sp
        LEFT JOIN nguoidung nd ON sp.ID_NguoiBan = nd.ID_NguoiDung
        LEFT JOIN danhmuc dm ON sp.ID_DanhMuc = dm.ID_DanhMuc
        $where
        ORDER BY sp.ID_SanPham DESC
        LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
if ($types) {
    $all_params = array_merge($params, [$limit, $offset]);
    $all_types = $types . 'ii';
    $stmt->bind_param($all_types, ...$all_params);
} else {
    $stmt->bind_param('ii', $limit, $offset);
}
$stmt->execute();
$products = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <title>Quản lý sản phẩm</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container mt-4">
    <h2 class="mb-4">Quản lý sản phẩm</h2>

    <?php if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($flash['msg']) ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endif; ?>

    <!-- Tìm kiếm -->
    <div class="card mb-4">
        <div class="card-body">
            <form class="form-inline" method="get">
                <input name="search" value="<?= htmlspecialchars($key) ?>" class="form-control mr-2" placeholder="Tìm tên, mô tả, người bán...">
                <button class="btn btn-outline-primary mr-2">Tìm</button>
                <?php if ($key): ?><a href="product.php" class="btn btn-secondary">Xóa bộ lọc</a><?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Thống kê -->
    <div class="alert alert-info">
        Tìm thấy <strong><?= $total ?></strong> sản phẩm
        <?php if ($key): ?> 
            theo từ khóa "<strong><?= htmlspecialchars($key) ?></strong>"
        <?php endif; ?>
    </div>

    <!-- Bảng sản phẩm -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>#</th>
                            <th>Tên sản phẩm</th>
                            <th>Người bán</th>
                            <th>Giá</th>
                            <th>Danh mục</th>
                            <th>Trạng thái</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($products->num_rows): $i = $offset + 1; ?>
                            <?php while ($p = $products->fetch_assoc()): ?>
                                <?php
                                    $statusDisplay = [
                                        'ChoDuyet' => 'Chờ duyệt',
                                        'DangBan' => 'Đang bán',
                                        'DaBan' => 'Đã bán',
                                        'BiGoBo' => 'Bị gỡ bỏ'
                                    ];
                                    $status = $p['TrangThaiDangBan'];
                                ?>
                                <tr>
                                    <td><?= $i++ ?></td>
                                    <td><?= htmlspecialchars($p['TenSanPham']) ?></td>
                                    <td><?= htmlspecialchars($p['NguoiBanName'] ?? 'N/A') ?></td>
                                    <td><?= number_format($p['Gia'], 0, ',', '.') ?>₫</td>
                                    <td><?= htmlspecialchars($p['TenDanhMuc'] ?? '') ?></td>
                                    <td>
                                        <span class="badge badge-<?= $status === 'DangBan' ? 'success' : ($status === 'DaBan' ? 'secondary' : ($status === 'ChoDuyet' ? 'warning' : 'danger')) ?>">
                                            <?= htmlspecialchars($statusDisplay[$status] ?? $status) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a class="btn btn-sm btn-warning" href="edit_product.php?id=<?= $p['ID_SanPham'] ?>">Sửa</a>
                                        <a class="btn btn-sm btn-danger" href="product.php?delete=<?= $p['ID_SanPham'] ?>"
                                           onclick="return confirm('Bạn chắc chắn muốn xóa sản phẩm này?')">Xóa</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="7" class="text-center text-muted">Không có sản phẩm</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <nav>
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                            <li class="page-item"><a class="page-link" href="product.php?search=<?= urlencode($key) ?>&page=1">Đầu</a></li>
                            <li class="page-item"><a class="page-link" href="product.php?search=<?= urlencode($key) ?>&page=<?= $page-1 ?>">Trước</a></li>
                        <?php endif; ?>
                        <?php for ($i = max(1, $page-2); $i <= min($totalPages, $page+2); $i++): ?>
                            <li class="page-item <?= $i===$page ? 'active' : '' ?>">
                                <a class="page-link" href="product.php?search=<?= urlencode($key) ?>&page=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        <?php if ($page < $totalPages): ?>
                            <li class="page-item"><a class="page-link" href="product.php?search=<?= urlencode($key) ?>&page=<?= $page+1 ?>">Sau</a></li>
                            <li class="page-item"><a class="page-link" href="product.php?search=<?= urlencode($key) ?>&page=<?= $totalPages ?>">Cuối</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.slim.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
</body>
</html>