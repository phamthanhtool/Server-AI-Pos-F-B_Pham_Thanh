<?php
include "config.php";

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data["table_id"]) || !isset($data["cart"])) {
    die("Thiếu dữ liệu");
}

$table_id = intval($data["table_id"]);
$cart = $data["cart"];

// Chuyển giỏ hàng thành mảng items chuẩn
$items = [];
$total = 0;

foreach ($cart as $c) {
    $items[] = [
        "id"    => intval($c["id"]),
        "name"  => $c["name"],
        "qty"   => intval($c["qty"]),
        "price" => floatval($c["price"])
    ];
    $total += $c["price"] * $c["qty"];
}

// ==========================
//  TẠO STATUS THEO SỐ MÓN
// ==========================
// - queued   = món mới đặt, đang chờ bếp
// => mỗi món mới đặt đều bắt đầu là queued
$statusArr = array_fill(0, count($items), "queued");
$statusString = implode(", ", $statusArr);

$items_json = $conn->real_escape_string(json_encode($items, JSON_UNESCAPED_UNICODE));

$session_id = "sess_" . uniqid();
$source = "manual";

// Lưu đơn
$sql = "
    INSERT INTO orders (table_id, items, total, status, source, session_id, created_at)
    VALUES ($table_id, '$items_json', $total, '$statusString', '$source', '$session_id', NOW())
";

if ($conn->query($sql)) {
    echo "OK|" . $conn->insert_id;
} else {
    echo "ERR: " . $conn->error;
}
?>
