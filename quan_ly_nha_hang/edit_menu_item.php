<?php
include "config.php";
$id = $_GET['id'];

$item = $conn->query("SELECT * FROM menu_items WHERE id=$id")->fetch_assoc();
$cats = $conn->query("SELECT * FROM menu_categories ORDER BY name ASC");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Sửa Món Ăn</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

<div class="container mt-5">
    <div class="card p-4 col-md-6 mx-auto">
        <h3 class="text-center mb-3">✏️ Sửa món ăn</h3>

        <form method="POST" action="update_menu_item.php" enctype="multipart/form-data">

            <input type="hidden" name="id" value="<?= $item['id'] ?>">

            <div class="mb-3">
                <label class="form-label">Tên món</label>
                <input type="text" name="name" value="<?= $item['name'] ?>" required class="form-control">
            </div>

            <div class="mb-3">
                <label class="form-label">Danh mục</label>
                <select name="category_id" class="form-select">
                    <?php while($c = $cats->fetch_assoc()): ?>
                        <option value="<?= $c['id'] ?>" 
                            <?= ($c['id'] == $item['category_id']) ? "selected" : "" ?>>
                            <?= $c['name'] ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Giá (VNĐ)</label>
                <input type="number" step="100" min="0" name="price" 
                       value="<?= $item['price'] ?>" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Ảnh mới (tuỳ chọn)</label>
                <input type="file" name="image" class="form-control">
                <small class="text-muted">Ảnh hiện tại: <?= $item['image'] ?></small>
            </div>

            <div class="mb-3">
                <label class="form-label">Mô tả</label>
                <textarea name="description" rows="3" class="form-control"><?= $item['description'] ?></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Trạng thái</label>
                <select name="status" class="form-select">
                    <option value="available"     <?= $item['status']=="available"?"selected":"" ?>>Còn hàng</option>
                    <option value="out_of_stock" <?= $item['status']=="out_of_stock"?"selected":"" ?>>Hết hàng</option>
                    <option value="hidden"       <?= $item['status']=="hidden"?"selected":"" ?>>Ẩn</option>
                </select>
            </div>

            <button class="btn btn-primary w-100">Cập nhật</button>
        </form>
    </div>
</div>

</body>
</html>
