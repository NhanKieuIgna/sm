<?php
//--- ddoiw có csdl r thay ---
$thongKe = [
    "tong_don" => 21,
    "don_hoan_thanh" => 19,
    "don_thanh_cong" => 18,
    "don_that_bai" => 1,
    "don_can_xu_ly" => 2,
    "loi_nhuan" => 587933
];

$lichSu = [
    ["Nguyễn Văn B", "Tai nghe sr2", 199999, "11:34 27/08/2025", "Thành công"],
    ["Trần Tín k", "Xe đạp cho trẻ", 900000, "11:01 27/08/2025", "Thành công"],
    ["Nguyễn Ngọc H", "Truyện Doraemon", 75000, "10:47 27/08/2025", "Thành công"],
    ["Vũ Văn A", "Ốp lưng Iphone 17", 93000, "10:36 27/08/2025", "Thất bại"],
    ["Lê Thư J", "Chiên chống dính", 410000, "10:18 27/08/2025", "Thành công"],
    ["Lê Thư J", "Chiên chống dính", 410000, "10:18 27/08/2025", "Thành công"],
    ["Lê Thư J", "Chiên chống dính", 410000, "10:18 27/08/2025", "Thành công"],
    ["Lê Thư J", "Chiên chống dính", 410000, "10:18 27/08/2025", "Thành công"],
    ["Lê Thư J", "Chiên chống dính", 410000, "10:18 27/08/2025", "Thành công"],
    ["Lê Thư J", "Chiên chống dính", 410000, "10:18 27/08/2025", "Thành công"],
    ["Lê Thư J", "Chiên chống dính", 410000, "10:18 27/08/2025", "Thành công"],
    ["Lê Thư J", "Chiên chống dính", 410000, "10:18 27/08/2025", "Thành công"],
    ["Lê Thư J", "Chiên chống dính", 410000, "10:18 27/08/2025", "Thành công"]

];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Lịch sử giao hàng</title>
    <style>
  
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: Arial, sans-serif;
    background: #eef1f5;
    color: #333;
}


.header {
    background: #ffffff;
    padding: 12px 20px;
    display: flex;
    align-items: center;
    gap: 20px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.15);
}

.header button {
    padding: 5px 12px;
    font-size: 18px;
    border: none;
    background: #e0e0e0;
    border-radius: 6px;
    cursor: pointer;
}

.url-box {
    flex: 1;
}

.url-box input {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 15px;
}

.title {
    text-align: center;
    padding: 20px 0;
    font-size: 26px;
    font-weight: bold;
}

.container {
    width: 90%;
    margin: auto;
    display: flex;
    gap: 25px;
    margin-bottom: 40px;
}


.stat-box {
    width: 30%;
    background: #ffffff;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.15);
}

.stat-box h3 {
    font-size: 20px;
    margin-bottom: 15px;
}

.stat-item {
    padding: 10px 0;
    border-bottom: 1px solid #ddd;
    display: flex;
    justify-content: space-between;
    font-size: 15px;
}

.stat-item:last-child {
    border-bottom: none;
}


.history-box {
    width: 70%;
    background: #ffffff;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.15);
}

.history-box h3 {
    font-size: 20px;
    margin-bottom: 15px;
}


table {
    width: 100%;
    border-collapse: collapse;
}

thead {
    background: #dfe7ef;
    color: #333;
}

th, td {
    padding: 12px 10px;
    font-size: 14px;
    border-bottom: 1px solid #ddd;
}


tbody {
    display: block;
    max-height: 450px; 
    overflow-y: scroll;
}

thead tr {
    display: table;
    width: 100%;
    table-layout: fixed;
}

tbody tr {
    display: table;
    width: 100%;
    table-layout: fixed;
}

.success {
    color: #1d9b4a;
    font-weight: bold;
}

.fail {
    color: #d62828;
    font-weight: bold;
}

tbody::-webkit-scrollbar {
    width: 8px;
}

tbody::-webkit-scrollbar-thumb {
    background: #999;
    border-radius: 6px;
}


    </style>
</head>

<body>

    <div class="header">
        <button onclick="history.back()">⬅</button>
    </div>

    <div class="title">Lịch Sử Giao Hàng</div>

    <div class="container">

        <div class="stat-box">
            <h3>Thống Kê Giao hàng hôm nay</h3>

            <div class="stat-item"><span>Tổng số đơn hôm nay</span> <b><?= $thongKe["tong_don"] ?></b></div>
            <div class="stat-item"><span>Số đơn hoàn thành</span> <b><?= $thongKe["don_hoan_thanh"] ?></b></div>
            <div class="stat-item"><span>Giao thành công</span> <b><?= $thongKe["don_thanh_cong"] ?></b></div>
            <div class="stat-item"><span>Giao thất bại</span> <b><?= $thongKe["don_that_bai"] ?></b></div>
            <div class="stat-item"><span>Số đơn cần xử lý</span> <b><?= $thongKe["don_can_xu_ly"] ?></b></div>
            <div class="stat-item"><span>Lợi nhuận</span> <b><?= number_format($thongKe["loi_nhuan"]) ?>đ</b></div>
        </div>

        <!-- Lịch sử giao hàng -->
        <div class="history-box">
            <h3>Lịch sử giao hàng</h3>

            <table>
                <thead>
                    <tr>
                        <th>Tên khách hàng</th>
                        <th>Tên hàng</th>
                        <th>Giá</th>
                        <th>Thời gian giao hàng</th>
                        <th>Trạng thái</th>
                    </tr>
                </thead>

                <tbody>
                <?php foreach ($lichSu as $row): ?>
                    <tr>
                        <td><?= $row[0] ?></td>
                        <td><?= $row[1] ?></td>
                        <td><?= number_format($row[2]) ?>đ</td>
                        <td><?= $row[3] ?></td>
                        <td class="<?= $row[4] == 'Thành công' ? 'success' : 'fail' ?>">
                            <?= $row[4] ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

        </div>

    </div>

</body>
</html>
