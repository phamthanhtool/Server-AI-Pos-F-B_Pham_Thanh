<?php
include "config.php";

// Đếm danh mục
$count_cat = $conn->query("SELECT COUNT(*) AS total FROM menu_categories")->fetch_assoc()['total'];

// Đếm món
$count_item = $conn->query("SELECT COUNT(*) AS total FROM menu_items")->fetch_assoc()['total'];

// Đếm số bàn
$count_table = $conn->query("SELECT COUNT(*) AS total FROM tables")->fetch_assoc()['total'];

// Đếm số order đang xử lý
$count_order = $conn->query("SELECT COUNT(*) AS total FROM orders WHERE status!='completed' AND status!='cancelled'")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Hệ Thống Quản Lý Nhà Hàng</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
    body { background: #eef2f8; }
    .section-title {
        font-weight: 700;
        margin-bottom: 20px;
    }
    .card-box {
        border-radius: 14px;
        padding: 22px;
        color: #fff;
        box-shadow: 0 6px 20px rgba(0,0,0,0.1);
        transition: .2s;
        height: 170px;
    }
    .card-box:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.2);
    }
    .fun-card {
        border-radius: 14px;
        height: 160px;
        box-shadow: 0 3px 12px rgba(0,0,0,0.1);
        transition: .1s;
    }
    .fun-card:hover {
        transform: scale(1.03);
    }
</style>

</head>

<body>

<!-- TOP NAV -->
<nav class="navbar navbar-dark bg-dark py-3">
    <div class="container-fluid">
        <span class="navbar-brand fs-4">🍽️ HỆ THỐNG QUẢN LÝ NHÀ HÀNG AI</span>
    </div>
</nav>

<div class="container mt-4">

    <!-- TỔNG QUAN -->
    <h3 class="section-title">📊 Tổng Quan Hệ Thống</h3>

    <div class="row g-4">

        <!-- Tổng danh mục -->
        <div class="col-md-6 col-lg-3">
            <div class="card-box bg-primary">
                <h5>Danh mục</h5>
                <h1><?= $count_cat ?></h1>
                <a href="category_list.php" class="btn btn-light btn-sm mt-2">Quản lý</a>
            </div>
        </div>

        <!-- Tổng món ăn -->
        <div class="col-md-6 col-lg-3">
            <div class="card-box bg-success">
                <h5>Món ăn</h5>
                <h1><?= $count_item ?></h1>
                <a href="menu_list.php" class="btn btn-light btn-sm mt-2">Quản lý</a>
            </div>
        </div>

        <!-- Số bàn ăn -->
        <div class="col-md-6 col-lg-3">
            <div class="card-box bg-info">
                <h5>Số bàn</h5>
                <h1><?= $count_table ?></h1>
                <a href="qr_tables.php" class="btn btn-light btn-sm mt-2">QR theo bàn</a>
            </div>
        </div>

        <!-- Order đang xử lý -->
        <div class="col-md-6 col-lg-3">
            <div class="card-box bg-warning">
                <h5>Order đang xử lý</h5>
                <h1><?= $count_order ?></h1>
                <a href="kitchen.php" class="btn btn-dark btn-sm mt-2">Xem bếp</a>
            </div>
        </div>

    </div>


    <!-- TÍNH NĂNG NHANH -->
    <hr class="my-5">
    <h3 class="section-title">⚡ Chức Năng Nhanh</h3>

    <div class="row g-4">

        <!-- MENU -->
        <div class="col-md-4">
            <div class="card fun-card p-4">
                <h5>📜 Danh sách món</h5>
                <p>Xem – Sửa – Xóa món ăn.</p>
                <a href="menu_list.php" class="btn btn-primary w-100">Quản lý món ăn</a>
            </div>
        </div>

        <!-- CATEGORY -->
        <div class="col-md-4">
            <div class="card fun-card p-4">
                <h5>📂 Danh mục món</h5>
                <p>Quản lý danh mục và nhóm món ăn.</p>
                <a href="category_list.php" class="btn btn-primary w-100">Quản lý danh mục</a>
            </div>
        </div>

        <!-- THÊM MÓN -->
        <div class="col-md-4">
            <div class="card fun-card p-4">
                <h5>➕ Thêm món mới</h5>
                <p>Thêm món ăn nhanh vào menu.</p>
                <a href="add_menu_item.php" class="btn btn-success w-100">Thêm món</a>
            </div>
        </div>

        <!-- QUÉT QR -->
        <div class="col-md-4">
            <div class="card fun-card p-4">
                <h5>🔳 QR Code theo bàn</h5>
                <p>Tạo QR cho khách order tại bàn.</p>
                <a href="qr_tables.php" class="btn btn-dark w-100">Tạo QR</a>
            </div>
        </div>

        <!-- GIAO DIỆN BẾP -->
        <div class="col-md-4">
            <div class="card fun-card p-4">
                <h5>👨‍🍳 Giao diện bếp</h5>
                <p>Xác nhận – nấu – hoàn thành món.</p>
                <a href="kitchen.php" class="btn btn-danger w-100">Vào bếp</a>
            </div>
        </div>

        <!-- ORDER -->
        <div class="col-md-4">
            <div class="card fun-card p-4">
                <h5>🧾 Quản lý Order</h5>
                <p>Xem toàn bộ order từ khách.</p>
                <a href="order_manager.php" class="btn btn-secondary w-100">Quản lý order</a>
            </div>
        </div>

    </div>

</div>

</body>
</html>
