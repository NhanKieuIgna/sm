<?php
session_start();

require_once 'C:\wamp64\www\SM\database\db.php';
include 'C:\wamp64\www\SM\src\admin\header.php';

// Flash message
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

// Tìm kiếm + phân trang
$key = trim($_GET['search'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;

$where = "";
$params = [];
$types = "";
if ($key !== '') {
    $where = "WHERE HoTen LIKE ? OR Email LIKE ? OR TenDangNhap LIKE ? OR SoDienThoai LIKE ?";
    $searchKey = "%$key%";
    $params = [$searchKey, $searchKey, $searchKey, $searchKey];
    $types = 'ssss';
}

// Lấy tổng số bản ghi
$sqlCount = "SELECT COUNT(*) as total FROM nguoidung $where";
$stmt = $conn->prepare($sqlCount);
if ($types) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$resultCount = $stmt->get_result();
$totalRecords = $resultCount->fetch_assoc()['total'];
$stmt->close();

$totalPages = max(1, ceil($totalRecords / $limit));
if ($page > $totalPages) $page = $totalPages;
$offset = ($page - 1) * $limit;

// Lấy dữ liệu người dùng
$sql = "SELECT * FROM nguoidung $where ORDER BY ID_NguoiDung ASC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);

if ($types) {
    $all_params = array_merge($params, [$limit, $offset]);
    $all_types = $types . 'ii';
    $stmt->bind_param($all_types, ...$all_params);
} else {
    $stmt->bind_param('ii', $limit, $offset);
}

$stmt->execute();
$data = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quản lý người dùng</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container mt-4">
    <h2 class="mb-4">Quản lý người dùng</h2>

    <!-- Flash message -->
    <?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($flash['msg']) ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

    <!-- Form tìm kiếm -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="get" class="form-inline">
                <input type="text" name="search" value="<?= htmlspecialchars($key) ?>" 
                       class="form-control mr-2" placeholder="Tìm kiếm theo tên, email, tên đăng nhập, SĐT...">
                <button type="submit" class="btn btn-primary mr-2">Tìm kiếm</button>
                <?php if ($key): ?>
                    <a href="user.php" class="btn btn-secondary">Xóa bộ lọc</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Thống kê -->
    <div class="alert alert-info">
        Tìm thấy <strong><?= $totalRecords ?></strong> người dùng
        <?php if ($key): ?> 
            theo từ khóa "<strong><?= htmlspecialchars($key) ?></strong>"
        <?php endif; ?>
    </div>

    <!-- Bảng người dùng -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>ID</th>
                            <th>Họ tên</th>
                            <th>Email</th>
                            <th>SĐT</th>
                            <th>Tên đăng nhập</th>
                            <th>Vai trò</th>
                            <th>Trạng thái</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($data->num_rows > 0): ?>
                            <?php while ($u = $data->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $u['ID_NguoiDung'] ?></td>
                                    <td><?= htmlspecialchars($u['HoTen']) ?></td>
                                    <td><?= htmlspecialchars($u['Email']) ?></td>
                                    <td><?= htmlspecialchars($u['SoDienThoai']) ?></td>
                                    <td><?= htmlspecialchars($u['TenDangNhap'] ?? 'N/A') ?></td>
                                    <td>
                                        <span class="badge badge-<?= $u['VaiTro'] === 'QuanTriVien' ? 'danger' : ($u['VaiTro'] === 'NguoiBan' ? 'warning' : ($u['VaiTro'] === 'NguoiGiaoHang' ? 'info' : 'primary')) ?>">
                                            <?= htmlspecialchars($u['VaiTro']) ?>
                                        </span>
                                    </td>
                                    <td>
                                       <?php if ($u['TrangThaiHoatDong']): ?>
                                            <span class="badge badge-success">Hoạt động</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">Bị khóa</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($u['ID_NguoiDung'] != $_SESSION['user_id']): ?>
                                            <?php if ($u['TrangThaiHoatDong']): ?>
                                                <a href="ban_users.php?id=<?= $u['ID_NguoiDung'] ?>&ban=0" class="btn btn-sm btn-warning">Khóa</a>
                                            <?php else: ?>
                                                <a href="ban_users.php?id=<?= $u['ID_NguoiDung'] ?>&ban=1" class="btn btn-sm btn-info">Gỡ khóa</a>
                                            <?php endif; ?>
                                            <a href="edit_user.php?id=<?= $u['ID_NguoiDung'] ?>" class="btn btn-sm btn-warning">Sửa</a>
                                            <a href="del_user.php?id=<?= $u['ID_NguoiDung'] ?>" class="btn btn-sm btn-danger"
                                               onclick="return confirm('Bạn chắc chắn muốn xóa người dùng này? Tất cả dữ liệu liên quan cũng sẽ bị xóa.')">
                                                Xóa
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted small">(Tài khoản hiện tại)</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted">Không tìm thấy người dùng nào</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="user.php?search=<?= urlencode($key) ?>&page=1">Đầu</a>
                            </li>
                            <li class="page-item">
                                <a class="page-link" href="user.php?search=<?= urlencode($key) ?>&page=<?= $page - 1 ?>">Trước</a>
                            </li>
                        <?php endif; ?>

                        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                <a class="page-link" href="user.php?search=<?= urlencode($key) ?>&page=<?= $i ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($page < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="user.php?search=<?= urlencode($key) ?>&page=<?= $page + 1 ?>">Sau</a>
                            </li>
                            <li class="page-item">
                                <a class="page-link" href="user.php?search=<?= urlencode($key) ?>&page=<?= $totalPages ?>">Cuối</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>

    <!-- Nút thêm tài khoản -->
    <div class="mt-4 mb-4">
        <a href="edit_user.php" class="btn btn-success btn-lg">+ Thêm tài khoản</a>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
</body>
</html>