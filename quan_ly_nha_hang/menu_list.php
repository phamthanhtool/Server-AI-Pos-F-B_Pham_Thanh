<?php
include "config.php";

$sql = "SELECT m.*, c.name AS category_name 
        FROM menu_items m
        JOIN menu_categories c ON m.category_id = c.id
        ORDER BY m.id DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Danh Sách Món Ăn</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
.table img {
    width: 70px;
    height: 70px;
    object-fit: cover;
    border-radius: 8px;
}
</style>
</head>

<body>

<div class="container mt-4">
    <h2 class="mb-3">📋 Danh Sách Món Ăn</h2>

    <a href="add_menu_item.php" class="btn btn-success mb-3">➕ Thêm món mới</a>
  
    <a href="dashboard.php" class="btn btn-secondary">⬅ Quay lại trang chủ</a>



    <table class="table table-bordered table-hover">
        <thead class="table-primary">
            <tr>
                <th>ID</th>
                <th>Ảnh</th>
                <th>Tên món</th>
                <th>Danh mục</th>
                <th>Giá</th>
                <th>Trạng thái</th>
                <th style="width: 150px">Hành động</th>
            </tr>
        </thead>

        <tbody>
        <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>

                <td>
                    <?php if ($row['image']): ?>
                        <img src="uploads/menu/<?= $row['image'] ?>">
                    <?php else: ?>
                        <span class="text-muted">Không ảnh</span>
                    <?php endif; ?>
                </td>

                <td><?= $row['name'] ?></td>
                <td><?= $row['category_name'] ?></td>
                <td><?= number_format($row['price'], 0) ?> đ</td>
                <td>
                    <?php
                        if ($row['status'] == 'available') echo '<span class="badge bg-success">Còn hàng</span>';
                        else if ($row['status'] == 'out_of_stock') echo '<span class="badge bg-danger">Hết hàng</span>';
                        else echo '<span class="badge bg-secondary">Ẩn</span>';
                    ?>
                </td>

                <td>
                    <a href="edit_menu_item.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Sửa</a>
                    <a href="delete_menu_item.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger"
                       onclick="return confirm('Xoá món này?')">Xoá</a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>

    </table>
</div>

</body>
</html>
