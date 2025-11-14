<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Trang người giao hàng</title>
  <link rel="stylesheet" href="delivery.css">
</head>
  <?php include "header_deli.php"; ?>
<body>
  <div class="app">
    <aside class="sidebar">
      <div class="brand">
        <div class="logo">SM</div>
        <div>
          <div style="font-weight:700">DriverDash</div>
          <div class="small">Phiên bản giao hàng</div>
        </div>
      </div>
<!-- Khi nào có csdl thi thay bằng php -->
      <div class="profile">
        <div class="avatar"><a href="profile.php">Avatar</a></div>
        <div>
          <div style="font-weight:700">Tên Chủ TK</div>
          <div class="small">ID: G12345 • Online</div>
          <div style="margin-top:8px">
            <span class="pill">Trạng thái: <strong>Sẵn sàng</strong></span>
          </div>
        </div>
      </div>

      <div class="stat-row">
        <div class="stat">
          <div class="small">Đơn hôm nay</div>
          <div style="font-weight:700;font-size:18px">8</div>
        </div>
        <div class="stat">
          <div class="small">Doanh thu</div>
          <div style="font-weight:700;font-size:18px">₫1,520,000</div>
        </div>
      </div>

      <nav>
        <a class="nav-item" href="#">🏠 Tổng quan</a>
        <a class="nav-item" href="#">📦 Đơn hàng</a>
        <a class="nav-item" href="#">💰 Thu nhập</a>
        <a class="nav-item" href="ls_giaohang.php">📜 Lịch sử</a>
        <a class="nav-item" href="#">⚙️ Cài đặt</a>
      </nav>
    </aside>

    <main class="main">
      <div class="topbar">
        <h2>Đơn đang giao</h2>
        <div class="search">
          <input type="text" placeholder="Tìm đơn hàng...">
          <button class="btn btn-ghost">Tìm</button>
        </div>
      </div>

      <div class="card">
        <div style="font-weight:700;margin-bottom:12px">Danh sách đơn</div>
        <div class="orders">
          <div class="order">
            <div>
              <div class="badge">#DH00123</div>
              <h4>Giao: Nguyễn Văn A</h4>
              <div class="meta">2 món • 1.6 km</div>
            </div>
            <div class="actions">
              <button class="btn btn-primary">Nhận</button>
              <button class="btn btn-ghost">Chỉ đường</button>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
    <?php include "footer_deli.php"; ?>
</body>
</html>
