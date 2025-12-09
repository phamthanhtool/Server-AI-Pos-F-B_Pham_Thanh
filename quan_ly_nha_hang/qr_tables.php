<?php
include "config.php";

$tables = $conn->query("SELECT * FROM tables ORDER BY table_no ASC");

// URL gốc của trang order (sau này bạn đổi sang domain/ngrok của bạn)
$BASE_URL = "http://localhost/quan_ly_nha_hang/menu_order.php";
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>QR Order theo bàn</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <h2>📱 QR Code Order theo bàn</h2>
    <div class="row g-4 mt-3">
        <?php while($t = $tables->fetch_assoc()): 
            $link = $BASE_URL . "?table_id=" . $t['id'];
        ?>
        <div class="col-md-4">
            <div class="card p-3 text-center">
                <h4>Bàn <?= htmlspecialchars($t['table_no']) ?></h4>
                <p><small><?= $link ?></small></p>
                <img 
                    src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=<?= urlencode($link) ?>" 
                    alt="QR Bàn <?= htmlspecialchars($t['table_no']) ?>">
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>
</body>
</html>
