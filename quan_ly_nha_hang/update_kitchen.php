<?php
include "config.php";

$order_id   = intval($_GET['order_id']);
$index      = intval($_GET['item_index']);
$newStatus  = $_GET['status'];

// Lấy đơn hàng
$order = $conn->query("SELECT * FROM orders WHERE id = $order_id")->fetch_assoc();

$statusArr = array_map('trim', explode(',', $order['status']));

// Nếu số lượng status < số món → bổ sung “waiting”
$items = json_decode($order['items'], true);
while (count($statusArr) < count($items)) {
    $statusArr[] = "waiting";
}

// Cập nhật đúng vị trí món
$statusArr[$index] = $newStatus;

// Ghép lại thành chuỗi "done, cooking, waiting"
$newStatusString = implode(", ", $statusArr);

// Lưu vào DB
$conn->query("UPDATE orders SET status = '$newStatusString' WHERE id = $order_id");

// Quay lại trang bếp
header("Location: kitchen.php");
exit;
