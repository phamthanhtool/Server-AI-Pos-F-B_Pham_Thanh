<?php
include "config.php";

$name = $_POST['name'];
$description = $_POST['description'];

$conn->query("INSERT INTO menu_categories(name, description)
              VALUES('$name', '$description')");

echo "<script>alert('Thêm danh mục thành công!'); window.location='category_list.php';</script>";
