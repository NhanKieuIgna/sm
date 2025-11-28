<?php
session_start();

// Kiểm tra quyền admin và include header
require_once realpath(__DIR__ . '/../../database/db.php');
include __DIR__ . '/header.php';

// Nếu truyền ?type=users hoặc ?type=products thì redirect thẳng
$type = $_GET['type'] ?? '';
if ($type === 'users') header('Location: stats_users.php');
if ($type === 'products') header('Location: stats_products.php');
?>
<!doctype html>
<html lang="vi">
<head>
<meta charset="utf-8">
<title>Thống kê</title>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
<style>
    .choice-card { cursor:pointer; transition: transform .12s ease; }
    .choice-card:hover { transform: translateY(-4px); box-shadow: 0 6px 18px rgba(0,0,0,0.08); }
    .choice-icon { font-size:40px; width:72px; height:72px; display:flex; align-items:center; justify-content:center; border-radius:8px; background:#f5f7fb; }
</style>
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="m-0">Thống kê</h3>
    </div>

    <div class="row">
        <div class="col-md-6 mb-4">
            <a href="stats_products.php" class="text-body text-decoration-none">
                <div class="card p-3 choice-card h-100">
                    <div class="d-flex">
                        <div class="choice-icon mr-3">
                            <svg width="28" height="28" viewBox="0 0 24 24"><path fill="#4e73df" d="M12 2L2 7v7c0 5 3.8 9.7 10 13 6.2-3.3 10-8 10-13V7l-10-5z"/></svg>
                        </div>
                        <div>
                            <h5>Thống kê sản phẩm</h5>
                            <p class="mb-0 text-muted">Biểu đồ phân bố theo danh mục, xuất CSV / in (PDF).</p>
                            <small class="text-muted">Xem chi tiết &rarr;</small>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-6 mb-4">
            <a href="stats_transactions.php" class="text-body text-decoration-none">
                <div class="card p-3 choice-card h-100">
                    <div class="d-flex">
                        <div class="choice-icon mr-3">
                            <svg width="28" height="28" viewBox="0 0 24 24"><path fill="#36b9cc" d="M3 13h2v-2H3v2zm0 4h2v-2H3v2zm0-8h2V7H3v2zm4 4h14v-2H7v2zm0 4h14v-2H7v2zM7 7v2h14V7H7z"/><circle fill="#36b9cc" cx="5" cy="8" r="1.5"/><circle fill="#36b9cc" cx="5" cy="13" r="1.5"/><circle fill="#36b9cc" cx="5" cy="18" r="1.5"/></svg>
                        </div>
                        <div>
                            <h5>Thống kê giao dịch</h5>
                            <p class="mb-0 text-muted">Phân tích đơn hàng theo trạng thái, xuất CSV / in (PDF).</p>
                            <small class="text-muted">Xem chi tiết &rarr;</small>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-6 mb-4">
            <a href="stats_users.php" class="text-body text-decoration-none">
                <div class="card p-3 choice-card h-100">
                    <div class="d-flex">
                        <div class="choice-icon mr-3">
                            <svg width="28" height="28" viewBox="0 0 24 24"><path fill="#1cc88a" d="M12 12c2.7 0 5-2.3 5-5s-2.3-5-5-5-5 2.3-5 5 2.3 5 5 5zm7 2h-2c0-2.2-3.6-3.5-5-3.5s-5 1.3-5 3.5H5c-1.7 0-3 1.3-3 3v1h20v-1c0-1.7-1.3-3-3-3z"/></svg>
                        </div>
                        <div>
                            <h5>Thống kê người dùng</h5>
                            <p class="mb-0 text-muted">Phân nhóm theo vai trò / trạng thái, xuất CSV / in (PDF).</p>
                            <small class="text-muted">Xem chi tiết &rarr;</small>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>

</body>
</html>
<script>
    document.querySelectorAll('.choice-card').forEach(card => {
        card.addEventListener('click', function() {
            const url = this.closest('a').href;
            const type = url.substring(url.lastIndexOf('=') + 1);
            // Thay đổi URL mà không tải lại trang
            history.pushState(null, '', '?type=' + type);
            // Gọi lại chính nó để tải dữ liệu mới
            location.reload();
        });
    });

    // Bắt sự kiện popstate để xử lý nút quay lại của trình duyệt
    window.addEventListener('popstate', function(event) {
        // Tải lại trang với type từ URL
        const type = new URLSearchParams(window.location.search).get('type');
        if (type) {
            // Thay đổi URL mà không tải lại trang
            history.replaceState(null, '', '?type=' + type);
            // Gọi lại chính nó để tải dữ liệu mới
            location.reload();
        }
    });

    // Fetch và hiển thị nội dung thống kê
    function fetchStats(type, push = true) {
        const url = '<?= $_SERVER['PHP_SELF'] ?>?type=' + encodeURIComponent(type);
        fetch(url)
            .then(response => response.text())
            .then(html => {
                // Render fetched HTML inside content
                content.innerHTML = html;
                // an toàn: kiểm tra tồn tại và catch lỗi
                try {
                    const anchors = (content && typeof content.querySelectorAll === 'function')
                        ? content.querySelectorAll('a')
                        : [];
                    anchors.forEach(a => {
                        const href = a.getAttribute && a.getAttribute('href');
                        const text = a.textContent || '';
                        if (href && (href.includes('export=') || /xuất|xuất file|xuất csv|in/i.test(text))) {
                            a.setAttribute('target', '_blank');
                        }
                    });
                } catch (e) {
                    console.error('Error processing anchors in stats fetch:', e);
                }
                if (push) history.pushState({url}, '', '?type=' + encodeURIComponent(url.replace('.php','')));
            }).catch(err=>{
                console.error('Error fetching stats:', err);
            });
    }

    // Kiểm tra và lấy type từ URL ban đầu
    (function() {
        const params = new URLSearchParams(window.location.search);
        const type = params.get('type');
        if (type) {
            // Thay đổi URL mà không tải lại trang
            history.replaceState(null, '', '?type=' + type);
            // Gọi lại chính nó để tải dữ liệu mới
            location.reload();
        }
    })();
</script>