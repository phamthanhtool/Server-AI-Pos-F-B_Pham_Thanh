<?php
include "config.php";

$orders = $conn->query("
    SELECT o.*, t.table_no 
    FROM orders o
    JOIN tables t ON o.table_id = t.id
    ORDER BY o.id DESC
");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Bếp – Quản lý món</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <h2>👨‍🍳 Giao diện bếp</h2>

<?php while($o = $orders->fetch_assoc()): ?>

    <?php 
        // Danh sách món
        $items = json_decode($o['items'], true);

        // Tách trạng thái thành array
        $statusArr = array_map('trim', explode(',', $o['status']));
    ?>

    <h4 class="mt-4">Đơn #<?= $o['id'] ?> – Bàn <?= htmlspecialchars($o['table_no']) ?></h4>
    <small>Toàn bộ trạng thái đơn: <?= htmlspecialchars($o['status']) ?></small>

    <table class="table table-bordered mt-2">
        <tr>
            <th>Món</th>
            <th>SL</th>
            <th>Trạng thái</th>
            <th>Thao tác</th>
        </tr>

        <?php foreach ($items as $index => $m): ?>

        <?php
            // Lấy status của món theo index
            $itemStatus = $statusArr[$index] ?? "queued"; 
        ?>

        <tr>
            <td><?= htmlspecialchars($m['name']) ?></td>
            <td><?= $m['qty'] ?></td>

            <td>
                <?php
                    if ($itemStatus == 'waiting') echo '<span class="badge bg-warning">Chờ</span>';
                    if ($itemStatus == 'queued') echo '<span class="badge bg-secondary">Chờ bếp</span>';
                    if ($itemStatus == 'cooking') echo '<span class="badge bg-primary">Đang nấu</span>';
                    if ($itemStatus == 'done') echo '<span class="badge bg-success">Xong</span>';
                    if ($itemStatus == 'canceled') echo '<span class="badge bg-danger">Đã hủy</span>';

                ?>
            </td>

            <td>
                <?php
                    if ($itemStatus == 'canceled') {
                        echo '<span class="text-muted">Đã hủy</span>';
                    } else {
                    ?>
                        <!-- queued -->
                        <a href="update_kitchen.php?order_id=<?= $o['id'] ?>&item_index=<?= $index ?>&status=queued"
                        class="btn btn-sm btn-secondary">Chờ</a>

                        <!-- cooking -->
                        <a href="update_kitchen.php?order_id=<?= $o['id'] ?>&item_index=<?= $index ?>&status=cooking"
                        class="btn btn-sm btn-primary">Đang nấu</a>

                        <!-- done -->
                        <a href="update_kitchen.php?order_id=<?= $o['id'] ?>&item_index=<?= $index ?>&status=done"
                        class="btn btn-sm btn-success">Hoàn thành</a>

                        <!-- serving -->
                        <a href="update_kitchen.php?order_id=<?= $o['id'] ?>&item_index=<?= $index ?>&status=serving"
                        class="btn btn-sm btn-info text-white">Mang ra bàn</a>
                    <?php
                    }
                    ?>

            </td>
        </tr>

        <?php endforeach; ?>
    </table>

<?php endwhile; ?>

</div>
</body>
</html>
