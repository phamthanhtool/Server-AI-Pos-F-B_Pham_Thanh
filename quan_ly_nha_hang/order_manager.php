<?php
include "config.php";

$orders = $conn->query("
    SELECT o.*, t.table_no, u.name AS user_name
    FROM orders o
    LEFT JOIN tables t ON o.table_id = t.id
    LEFT JOIN users u ON o.user_id = u.id
    ORDER BY o.id DESC
");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Quản lý Order</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>

<body>
<div class="container mt-4">
    <h2>📦 Quản lý các đơn order</h2>

    <table class="table table-bordered mt-3">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Bàn</th>
                <th>Món</th>
                <th>Tổng tiền</th>
                <th>Trạng thái</th>
                <th>Nguồn</th>
                <th>Thời gian</th>
                <th>Hành động</th>
            </tr>
        </thead>

        <tbody>
            <?php while($r = $orders->fetch_assoc()): 
                $items = json_decode($r['items'], true);
            ?>
            <tr>
                <td><?= $r['id'] ?></td>
                <td><?= $r['table_no'] ?></td>

                <td>
                    <?php foreach ($items as $it): ?>
                        - <?= $it['name'] ?> (x<?= $it['qty'] ?>) <br>
                    <?php endforeach; ?>
                </td>

                <td class="text-danger fw-bold">
                    <?= number_format($r['total']) ?> đ
                </td>

                <td>
                    <?php if ($r['status'] == 'pending'): ?>
                        <span class="badge bg-warning">Chờ</span>
                    <?php elseif ($r['status'] == 'serving'): ?>
                        <span class="badge bg-primary">Đang phục vụ</span>
                    <?php elseif ($r['status'] == 'completed'): ?>
                        <span class="badge bg-success">Hoàn thành</span>
                    <?php else: ?>
                        <span class="badge bg-secondary">Hủy</span>
                    <?php endif; ?>
                </td>

                <td><?= $r['source'] ?></td>

                <td><?= $r['created_at'] ?></td>

                <td>
                    <a href="order_status.php?table_id=<?= $r['table_id'] ?>" class="btn btn-sm btn-info">Khách xem</a>
                    <a href="kitchen.php" class="btn btn-sm btn-danger">Bếp</a>
                </td>
            </tr>

            <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>
