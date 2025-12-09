<?php
include "config.php";

$name        = $_POST['name'];
$category_id = $_POST['category_id'];
$price       = $_POST['price'];
$description = $_POST['description'];
$status      = $_POST['status'];

$imageName = null;

// Xử lý upload ảnh
if (!empty($_FILES['image']['name'])) {
    $imageName = time() . "_" . basename($_FILES['image']['name']);
    $target = "uploads/menu/" . $imageName;

    // Tạo thư mục nếu chưa có
    if (!is_dir("uploads/menu")) {
        mkdir("uploads/menu", 0777, true);
    }

    move_uploaded_file($_FILES['image']['tmp_name'], $target);
}

// Lưu vào DB
$sql = "INSERT INTO menu_items (category_id, name, price, image, description, status)
        VALUES (?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("isdsss", $category_id, $name, $price, $imageName, $description, $status);

if ($stmt->execute()) {
    echo "<script>alert('Thêm món ăn thành công!'); window.location='add_menu_item.php';</script>";
} else {
    echo "Lỗi: " . $stmt->error;
}
