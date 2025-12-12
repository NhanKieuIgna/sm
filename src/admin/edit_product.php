<?php
session_start();

require_once __DIR__ . '/../../database/db.php';
include __DIR__ . '/header.php';


$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

// Chuẩn bị dữ liệu
$cats = $conn->query("SELECT ID_DanhMuc, TenDanhMuc FROM danhmuc ORDER BY TenDanhMuc");
$sellers = $conn->query("SELECT ID_NguoiDung, HoTen FROM nguoidung WHERE VaiTro = 'NguoiBan' ORDER BY HoTen");

// Xử lý POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pid = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $TenSanPham = trim($_POST['TenSanPham'] ?? '');
    $ID_NguoiBan = isset($_POST['ID_NguoiBan']) ? (int)$_POST['ID_NguoiBan'] : 0;
    $MoTa = trim($_POST['MoTa'] ?? '');
    $Gia = (float)str_replace(',', '', ($_POST['Gia'] ?? '0'));
    $KichThuoc = trim($_POST['KichThuoc'] ?? '');
    $ID_DanhMuc = isset($_POST['ID_DanhMuc']) && is_numeric($_POST['ID_DanhMuc']) ? (int)$_POST['ID_DanhMuc'] : null;
    $TrangThaiDangBan = in_array($_POST['TrangThaiDangBan'] ?? 'DangBan', ['ChoDuyet','DangBan','DaBan','BiGoBo']) ? $_POST['TrangThaiDangBan'] : 'ChoDuyet';

    $errors = [];
    if ($TenSanPham === '') $errors[] = 'Tên sản phẩm bắt buộc.';
    if ($ID_NguoiBan <= 0) $errors[] = 'Chọn người bán.';
    if ($Gia <= 0) $errors[] = 'Giá phải lớn hơn 0.';

    if (empty($errors)) {
        $conn->begin_transaction();
        try {
            if ($pid > 0) {
                $sql = "UPDATE sanpham SET ID_NguoiBan=?, TenSanPham=?, MoTa=?, Gia=?, KichThuoc=?, ID_DanhMuc=?, TrangThaiDangBan=? WHERE ID_SanPham=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('issdsssi', $ID_NguoiBan, $TenSanPham, $MoTa, $Gia, $KichThuoc, $ID_DanhMuc, $TrangThaiDangBan, $pid);
                if (!$stmt->execute()) throw new Exception($stmt->error);
                $stmt->close();
            } else {
                throw new Exception('Không tìm thấy sản phẩm.');
            }

            $conn->commit();
            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Cập nhật sản phẩm thành công.'];
            header('Location: product.php');
            exit();
        } catch (Exception $e) {
            $conn->rollback();
            $flash = ['type' => 'danger', 'msg' => 'Lỗi: ' . $e->getMessage()];
        }
    } else {
        $flash = ['type' => 'danger', 'msg' => implode(' ', $errors)];
    }
}

// Load dữ liệu sản phẩm
$form = [
    'id' => 0,
    'TenSanPham' => '',
    'ID_NguoiBan' => '',
    'MoTa' => '',
    'Gia' => '',
    'KichThuoc' => '',
    'ID_DanhMuc' => '',
    'TrangThaiDangBan' => 'DangBan'
];

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $qid = (int)$_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM sanpham WHERE ID_SanPham = ?");
    $stmt->bind_param('i', $qid);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($r = $res->fetch_assoc()) {
        $form = array_merge($form, $r);
        $form['id'] = $qid;
    } else {
        $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Không tìm thấy sản phẩm.'];
        header('Location: product.php');
        exit();
    }
    $stmt->close();
} else {
    $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'ID sản phẩm không hợp lệ.'];
    header('Location: product.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <title>Sửa sản phẩm</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Sửa sản phẩm</h4>
                </div>
                <div class="card-body">
                    <?php if ($flash): ?>
                        <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars($flash['msg']) ?>
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                        </div>
                    <?php endif; ?>

                    <form method="post">
                        <input type="hidden" name="id" value="<?= htmlspecialchars($form['id']) ?>">
                        <div class="form-group">
                            <label>Tên sản phẩm</label>
                            <input name="TenSanPham" class="form-control" value="<?= htmlspecialchars($form['TenSanPham']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Người bán</label>
                            <select name="ID_NguoiBan" class="form-control" required>
                                <option value="">-- chọn --</option>
                                <?php while ($s = $sellers->fetch_assoc()): ?>
                                    <option value="<?= $s['ID_NguoiDung'] ?>" <?= $form['ID_NguoiBan']==$s['ID_NguoiDung'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($s['HoTen']) ?>
                                    </option>
                                <?php endwhile; $sellers->data_seek(0); ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Danh mục</label>
                            <select name="ID_DanhMuc" class="form-control">
                                <option value="">-- không --</option>
                                <?php while ($c = $cats->fetch_assoc()): ?>
                                    <option value="<?= $c['ID_DanhMuc'] ?>" <?= $form['ID_DanhMuc']==$c['ID_DanhMuc'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($c['TenDanhMuc']) ?>
                                    </option>
                                <?php endwhile; $cats->data_seek(0); ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Mô tả</label>
                            <textarea name="MoTa" class="form-control" rows="4"><?= htmlspecialchars($form['MoTa']) ?></textarea>
                        </div>
                        <div class="form-group">
                            <label>Giá (VNĐ)</label>
                            <input name="Gia" class="form-control" value="<?= htmlspecialchars($form['Gia']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Kích thước</label>
                            <input name="KichThuoc" class="form-control" value="<?= htmlspecialchars($form['KichThuoc']) ?>">
                        </div>
                        <div class="form-group">
                            <label>Trạng thái</label>
                            <select name="TrangThaiDangBan" class="form-control">
                                <option value="ChoDuyet" <?= $form['TrangThaiDangBan']=='ChoDuyet'?'selected':'' ?>>Chờ duyệt</option>
                                <option value="DangBan" <?= $form['TrangThaiDangBan']=='DangBan'?'selected':'' ?>>Đang bán</option>
                                <option value="DaBan" <?= $form['TrangThaiDangBan']=='DaBan'?'selected':'' ?>>Đã bán</option>
                                <option value="BiGoBo" <?= $form['TrangThaiDangBan']=='BiGoBo'?'selected':'' ?>>Bị gỡ bỏ</option>
                            </select>
                        </div>
                        <button class="btn btn-primary btn-block">Cập nhật</button>
                        <a href="product.php" class="btn btn-secondary btn-block mt-2">Hủy</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.slim.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
</body>
</html>