<?php
include "config.php";

if (!isset($_GET['order_id']) || !isset($_GET['item_index']) || !isset($_GET['table_id'])) {
    die("Thiếu tham số bắt buộc!");
}

$order_id = (int)$_GET['order_id'];
$item_index = (int)$_GET['item_index'];
$table_id = (int)$_GET['table_id'];

// Lấy đơn hàng
$o = $conn->query("SELECT * FROM orders WHERE id = $order_id")->fetch_assoc();
if (!$o) die("Không tìm thấy đơn!");

$items = json_decode($o['items'], true);

// Lấy danh sách trạng thái dạng mảng
$statusArr = array_map('trim', explode(',', $o['status']));

// Nếu trạng thái không tồn tại → gán mặc định
$currentStatus = $statusArr[$item_index] ?? "queued";

// ===============================
// CHỈ CHO HỦY KHI waiting / queued
// ===============================
if (!in_array($currentStatus, ["waiting", "queued"])) {
    echo "<script>
            alert('Món này đã vào bếp và không thể hủy!');
            window.location='order_status.php?table_id={$table_id}';
          </script>";
    exit;
}

// ===============================
// TIẾN HÀNH HỦY
// ===============================
$statusArr[$item_index] = "cancelled";

// Ghép lại thành chuỗi lưu DB
$newStatus = implode(", ", $statusArr);

// Cập nhật DB
$conn->query("
    UPDATE orders 
    SET status = '$newStatus'
    WHERE id = $order_id
");

echo "<script>
        alert('Đã hủy món thành công!');
        window.location='order_status.php?table_id={$table_id}';
      </script>";
