<?php
include "config.php";

$rs = $conn->query("SELECT id, name, price FROM menu_items WHERE status='available'");
$data = [];
while ($r = $rs->fetch_assoc()) {
    $data[] = $r;
}

echo json_encode($data, JSON_UNESCAPED_UNICODE);
