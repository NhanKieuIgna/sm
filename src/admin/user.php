<?php
include 'C:\wamp64\www\SM\database\db.php';

$key = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$where = $key ? "WHERE HoTen LIKE '%$key%' OR Email LIKE '%$key%' OR TenDangNhap LIKE '%$key%'" : '';
$sql = "SELECT * FROM nguoidung $where ORDER BY ID_NguoiDung DESC";
$data = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <title>Quản lý người dùng</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
</head>
<body>
<div class="container">
    <!-- Header, Tabs, Tài khoản, Đăng xuất... (bạn tự ráp theo wireframe) -->
    <form class="form-inline my-2">
      <input name="search" value="<?=htmlspecialchars($key)?>" class="form-control mr-2" placeholder="Tìm kiếm...">
      <button class="btn btn-primary">Tìm</button>
    </form>
    <table class="table table-bordered table-striped">
        <thead><tr>
        <th>ID</th><th>Họ tên</th><th>Email</th><th>Role</th><th>Trạng thái</th><th>Thao tác</th>
        </tr></thead>
        <tbody>
        <?php while($u=$data->fetch_assoc()): ?>
            <tr>
                <td><?=$u['ID_NguoiDung']?></td>
                <td><?=$u['HoTen']?></td>
                <td><?=$u['Email']?></td>
                <td><?=$u['VaiTro']?></td>
                <td><?= $u['TrangThaiHoatDong'] ? "Hoạt động" : "<span class='text-danger'>BỊ KHÓA</span>"?></td>
                <td>
                    <?php if($u['TrangThaiHoatDong']): ?>
                        <a href="ban_user.php?id=<?=$u['ID_NguoiDung']?>&ban=0">Ban</a>
                    <?php else: ?>
                        <a href="ban_user.php?id=<?=$u['ID_NguoiDung']?>&ban=1">UnBan</a>
                    <?php endif; ?>
                    | <a href="edit_user.php?id=<?=$u['ID_NguoiDung']?>">Sửa</a>
                    | <a href="del_user.php?id=<?=$u['ID_NguoiDung']?>" onclick="return confirm('Xóa user này?')">Xóa</a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
    <a class="btn btn-success" href="edit_user.php">Thêm tài khoản</a>
</div>
</body>
</html>