<?php include "config.php"; ?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Thêm Danh Mục</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

<div class="container mt-4">
    <div class="card p-4 col-md-6 mx-auto">
        <h3 class="mb-3">➕ Thêm Danh Mục</h3>

        <form method="POST" action="save_category.php">
            <div class="mb-3">
                <label class="form-label">Tên danh mục</label>
                <input type="text" name="name" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Mô tả</label>
                <textarea name="description" class="form-control"></textarea>
            </div>

            <button class="btn btn-primary w-100">Lưu</button>
        </form>
    </div>
</div>

</body>
</html>
