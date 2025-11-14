<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Hồ sơ cá nhân tài xế</title>
  <link rel="stylesheet" href="driver-profile.css">
</head>
<body>
<style>
    body {
  font-family: Inter, sans-serif;
  background: #f5f7fa;
  margin: 0;
  padding: 40px;
  color: #1e293b;
}

.container {
  max-width: 900px;
  margin: auto;
  background: white;
  padding: 30px;
  border-radius: 16px;
  box-shadow: 0 6px 20px rgba(0,0,0,0.08);
}

h2 {
  text-align: center;
  color: #0b6efd;
  margin-bottom: 30px;
}

.profile-form {
  display: flex;
  gap: 30px;
  align-items: flex-start;
}

.avatar {
  flex: 1 1 200px;
  text-align: center;
}

.avatar-placeholder {
  width: 160px;
  height: 160px;
  border-radius: 50%;
  border: 3px solid #0b6efd;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f0f0f0;
  color: #555;
  font-weight: 600;
  font-size: 15px;
  margin: 0 auto 10px;
  overflow: hidden;
}

.avatar img {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  object-fit: cover;
  display: block;
}

/* Ẩn chữ khi có ảnh */
.avatar img:not([src=""]) + span { display: none; }

.upload-btn {
  background: #0b6efd;
  color: white;
  border: none;
  padding: 8px 14px;
  border-radius: 8px;
  cursor: pointer;
}

.info {
  flex: 2;
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.row {
  display: flex;
  gap: 20px;
}

.col {
  flex: 1;
  display: flex;
  flex-direction: column;
}

label {
  font-weight: 600;
  margin-bottom: 5px;
}

input {
  padding: 10px;
  border: 1px solid #d0d7de;
  border-radius: 8px;
  font-size: 15px;
}

.buttons {
  margin-top: 20px;
  display: flex;
  gap: 10px;
}

.btn {
  padding: 10px 16px;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  text-decoration: none;
  text-align: center;
}

.btn-save {
  background: #0b6efd;
  color: white;
}

.btn-exit {
  background: #ef4444;
  color: white;
}

</style>
<div class="container">
  <header>
    <h2>Hồ sơ cá nhân tài xế</h2>
  </header>

  <div class="profile-form">

    <!-- Avatar -->
    <div class="avatar">
      <div class="avatar-placeholder">
        <?php if (!empty($driver['avatar'])): ?>
          <img src="<?php echo htmlspecialchars($driver['avatar']); ?>" alt="Ảnh đại diện">
        <?php else: ?>
          <span>Ảnh đại diện</span>
        <?php endif; ?>
      </div>
      <button class="upload-btn">Chọn ảnh</button>
    </div>

    <!-- Form thông tin -->
    <div class="info">
      <div class="row">
        <div class="col">
          <label>Họ tên</label>
          <input type="text" value="<?php echo htmlspecialchars($driver['name']); ?>" readonly>
        </div>
        <div class="col">
          <label>Biển số xe</label>
          <input type="text" value="<?php echo htmlspecialchars($driver['license_plate']); ?>" readonly>
        </div>
      </div>

      <div class="row">
        <div class="col">
          <label>Số điện thoại</label>
          <input type="text" value="<?php echo htmlspecialchars($driver['phone']); ?>" readonly>
        </div>
        <div class="col">
          <label>Loại xe</label>
          <input type="text" value="<?php echo htmlspecialchars($driver['vehicle_type']); ?>" readonly>
        </div>
      </div>

      <div class="row">
        <div class="col">
          <label>Địa chỉ</label>
          <input type="text" value="<?php echo htmlspecialchars($driver['address']); ?>" readonly>
        </div>
        <div class="col">
          <label>Hạng bằng lái</label>
          <input type="text" value="<?php echo htmlspecialchars($driver['license_class']); ?>" readonly>
        </div>
      </div>

      <div class="row">
        <div class="col">
          <label>Ngày sinh</label>
          <input type="text" value="<?php echo htmlspecialchars($driver['birthdate']); ?>" readonly>
        </div>
        <div class="col">
          <label>Khu vực hoạt động</label>
          <input type="text" value="<?php echo htmlspecialchars($driver['region']); ?>" readonly>
        </div>
      </div>

      <div class="buttons">
        <button class="btn btn-save">Lưu</button>
        <a href="delivery_index.php" class="btn btn-exit">Thoát</a>
      </div>
    </div>

  </div>
</div>

</body>
</html>
<!-- <?php
session_start();
require 'db.php';

// Kiểm tra xem đã đăng nhập chưa
if (!isset($_SESSION['driver_id'])) {
    header("Location: login.php");
    exit;
}

$driver_id = $_SESSION['driver_id'];

// Lấy thông tin từ database
$sql = "SELECT * FROM drivers WHERE id = $driver_id";
$result = $conn->query($sql);
$driver = $result->fetch_assoc();
?> -->
