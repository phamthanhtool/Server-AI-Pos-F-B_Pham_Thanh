<?php
include "config.php";

$id          = $_POST['id'];
$name        = $_POST['name'];
$category_id = $_POST['category_id'];
$price       = $_POST['price'];
$description = $_POST['description'];
$status      = $_POST['status'];

$imageName = null;

// Nếu có upload ảnh mới
if (!empty($_FILES['image']['name'])) {
    $imageName = time() . "_" . basename($_FILES['image']['name']);
    $target = "uploads/menu/" . $imageName;
    move_uploaded_file($_FILES['image']['tmp_name'], $target);

    $sql = "UPDATE menu_items 
            SET category_id=?, name=?, price=?, image=?, description=?, status=?
            WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isdsssi", $category_id, $name, $price, $imageName, $description, $status, $id);
} else {
    $sql = "UPDATE menu_items 
            SET category_id=?, name=?, price=?, description=?, status=?
            WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isdssi", $category_id, $name, $price, $description, $status, $id);
}

if ($stmt->execute()) {
    echo "<script>alert('Cập nhật thành công!'); window.location='menu_list.php';</script>";
} else {
    echo "Lỗi: " . $stmt->error;
}
