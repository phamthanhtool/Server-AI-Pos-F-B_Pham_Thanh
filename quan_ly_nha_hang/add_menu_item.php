<?php 
include "config.php";

// Lấy danh sách danh mục
$cats = $conn->query("SELECT * FROM menu_categories ORDER BY name ASC");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Thêm Món Ăn</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
    body { background: #f0f4f8; }
    .card {
        border-radius: 12px;
        box-shadow: 0 4px 14px rgba(0,0,0,0.08);
    }
</style>
</head>

<body>

<div class="container mt-5">
    <div class="card p-4 col-md-6 mx-auto">
        <h3 class="text-center mb-3">🍽️ Thêm Món Ăn</h3>

        <form method="POST" action="save_menu_item.php" enctype="multipart/form-data">

            <!-- Tên món -->
            <div class="mb-3">
                <label class="form-label">Tên món ăn</label>
                <input type="text" name="name" class="form-control" required placeholder="VD: Gà rang muối">
            </div>

            <!-- Danh mục -->
            <div class="mb-3">
                <label class="form-label">Danh mục</label>
                <select name="category_id" class="form-select" required>
                    <option value="">-- Chọn danh mục --</option>
                    <?php while ($c = $cats->fetch_assoc()): ?>
                        <option value="<?= $c['id'] ?>"><?= $c['name'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- Giá -->
            <div class="mb-3">
                <label class="form-label">Giá (VNĐ)</label>
                <input type="number" name="price" class="form-control" min="0" step="100" required placeholder="VD: 45000">
            </div>

            <!-- Ảnh -->
            <div class="mb-3">
                <label class="form-label">Ảnh món</label>
                <input type="file" name="image" class="form-control" accept="image/*">
            </div>

            <!-- Mô tả -->
            <div class="mb-3">
                <label class="form-label">Mô tả (tuỳ chọn)</label>
                <textarea name="description" class="form-control" rows="3"></textarea>
            </div>

            <!-- Trạng thái -->
            <div class="mb-3">
                <label class="form-label">Trạng thái</label>
                <select name="status" class="form-select" required>
                    <option value="available">Còn hàng</option>
                    <option value="out_of_stock">Hết hàng</option>
                    <option value="hidden">Ẩn khỏi menu</option>
                </select>
            </div>

            <!-- Nút gửi -->
            <button class="btn btn-primary w-100">Lưu món ăn</button>
        </form>
    </div>
</div>

</body>
</html>
