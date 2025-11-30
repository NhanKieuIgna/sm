<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// Thông tin hiển thị
$displayName = $_SESSION['HoTen'] ?? $_SESSION['TenDangNhap'] ?? 'Quản trị viên';
$current = basename($_SERVER['PHP_SELF']);

// helper active
function is_active($name, $current) {
    return $current === $name ? 'active' : '';
}
?>
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom mb-3">
    <a class="navbar-brand font-weight-bold" href="user.php">Admin Panel</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#adminNavbar" aria-controls="adminNavbar" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="adminNavbar">
        <ul class="navbar-nav mr-auto">
            <li class="nav-item <?= is_active('user.php', $current) ?>">
                <a class="nav-link" href="user.php">Quản lý người dùng</a>
            </li>
            <li class="nav-item <?= is_active('product.php', $current) ?>">
                <a class="nav-link" href="product.php">Quản lý sản phẩm</a>
            </li>
            <li class="nav-item <?= is_active('stats.php', $current) ?>">
                <a class="nav-link" href="stats.php">Thống kê</a>
            </li>
        </ul>

        <div class="form-inline my-2 my-lg-0">
            <span class="mr-3 text-muted">Xin chào, <?= htmlspecialchars($displayName) ?></span>
            <a class="btn btn-outline-secondary btn-sm" href="../logout.php">Đăng xuất</a>
        </div>
    </div>
</nav>