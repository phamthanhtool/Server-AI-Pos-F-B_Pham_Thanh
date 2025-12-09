<?php
include "config.php";

$id = $_GET['id'];

$conn->query("DELETE FROM menu_items WHERE id=$id");

echo "<script>alert('Đã xoá món!'); window.location='menu_list.php';</script>";
