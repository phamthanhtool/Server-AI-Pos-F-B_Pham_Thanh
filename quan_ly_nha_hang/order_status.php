<?php
include "config.php";

if (!isset($_GET['table_id'])) die("Thiếu table_id");
$table_id = (int)$_GET['table_id'];

// Lấy danh sách đơn theo bàn
$orders = $conn->query("
    SELECT * FROM orders
    WHERE table_id = $table_id
    ORDER BY id DESC
");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Trạng thái order</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>

<body>
<div class="container mt-4">
    <h3>🛒 Trạng thái món đã order</h3>

<?php while ($o = $orders->fetch_assoc()): ?>

    <?php
        // Danh sách món
        $items = json_decode($o['items'], true);

        // Chuyển "queued, cooking, done" → ["queued","cooking","done"]
        $statusArr = array_map('trim', explode(',', $o['status']));
    ?>

    <h5 class="mt-3">
        Đơn #<?= $o['id'] ?> – 
        <span class="text-muted">Trạng thái tổng:</span>
        <b><?= htmlspecialchars($o['status']) ?></b>
    </h5>

    <table class="table table-bordered">
        <tr>
            <th>Món</th>
            <th>SL</th>
            <th>Trạng thái món</th>
            <th>Hủy?</th>
        </tr>

    <?php foreach ($items as $index => $m): ?>

        <?php
            // Đọc trạng thái món theo index
            $itemStatus = $statusArr[$index] ?? "queued";
        ?>

        <tr>
            <td><?= htmlspecialchars($m['name']) ?></td>
            <td><?= $m['qty'] ?></td>

            <td>
                <?php
                    if ($itemStatus == 'waiting' || $itemStatus == 'queued')
                        echo '<span class="badge bg-warning">Chờ xác nhận</span>';

                    if ($itemStatus == 'cooking')
                        echo '<span class="badge bg-primary">Đang nấu</span>';

                    if ($itemStatus == 'done')
                        echo '<span class="badge bg-success">Đã xong</span>';

                    if ($itemStatus == 'serving')
                        echo '<span class="badge bg-info text-dark">Đang mang ra bàn</span>';

                    if ($itemStatus == 'cancelled')
                        echo '<span class="badge bg-danger">Đã hủy</span>';

                    if ($itemStatus == 'not found')
                        echo '<span class="badge bg-dark">Không tìm thấy</span>';
                ?>
            </td>

            <td>
                <?php if ($itemStatus == 'waiting' || $itemStatus == 'queued'): ?>

                    <!-- CHỈ ĐƯỢC HỦY NẾU waiting/queued -->
                    <a href="cancel_item.php?order_id=<?= $o['id'] ?>&item_index=<?= $index ?>&table_id=<?= $table_id ?>"
                       class="btn btn-sm btn-danger">
                       Hủy món
                    </a>

                <?php else: ?>
                    <span class="text-muted">Không thể hủy</span>
                <?php endif; ?>
            </td>

        </tr>

    <?php endforeach; ?>

    </table>

<?php endwhile; ?>

<a href="menu_order.php?table_id=<?= $table_id ?>" class="btn btn-secondary mt-3">⬅ Quay lại menu</a>

</div>
</body>
</html>
