<?php
// ====== CẤU HÌNH DATABASE ======
$DB_HOST = "localhost";      // hoặc 127.0.0.1
$DB_USER = "root";           // mặc định của XAMPP
$DB_PASS = "";               // mặc định rỗng
$DB_NAME = "quan_ly_nha_hang";   // tên database của bạn

// ====== KẾT NỐI MYSQL ======
$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

// Kiểm tra lỗi
if ($conn->connect_errno) {
    die("❌ Lỗi kết nối MySQL: " . $conn->connect_error);
}

// Set UTF-8 chuẩn
$conn->set_charset("utf8mb4");
?>
