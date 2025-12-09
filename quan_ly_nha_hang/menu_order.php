<?php
include "config.php";

if (!isset($_GET['table_id'])) {
    die("Thiếu tham số table_id");
}
$table_id = (int)$_GET['table_id'];

// Lấy thông tin bàn
$t = $conn->query("SELECT * FROM tables WHERE id = $table_id")->fetch_assoc();
if (!$t) die("Không tìm thấy bàn");

// Lấy danh mục để lọc
$cats = $conn->query("SELECT id, name FROM menu_categories ORDER BY name");

// Lấy menu
$items = $conn->query("
    SELECT m.*, c.name AS category_name 
    FROM menu_items m
    JOIN menu_categories c ON m.category_id = c.id
    WHERE m.status = 'available'
    ORDER BY c.id, m.name
");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Order Bàn <?= htmlspecialchars($t['table_no']) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body {
    background: #f5f7fb;
    font-family: "Segoe UI", sans-serif;
    transition: background 0.25s ease, color 0.25s ease;
}

/* ================= DARK MODE ================= */
body.dark-mode {
    background: #121212;
    color: #eee;
}

.dark-mode .item-card{
    background:#1f1f1f;
    box-shadow:0 4px 12px rgba(0,0,0,0.7);
}

.dark-mode .modal-content{
    background:#1e1e1e;
    color:#eee;
}

.dark-mode .chat-ai{
    background:#2a2a2a;
    color:#eee;
}
.dark-mode .chat-user{
    background:#004b7c;
    color:#fff;
}

.dark-mode #momo-chat{
    background:#1c1c1c;
}
.dark-mode #chat_area{
    background:#111;
}

/* ================= MENU ITEM ================= */
.item-card{
    border-radius:16px;
    box-shadow:0 4px 12px rgba(0,0,0,0.12);
    transition:0.2s;
    border:none;
    overflow:hidden;
}

/* Hover trên PC */
@media (hover: hover) {
    .item-card:hover{
        transform:translateY(-3px);
    }
}

/* Ảnh vuông 1:1 */
.item-card img{
    width:100%;
    aspect-ratio: 1 / 1;
    object-fit: cover;
    display:block;
}

/* Nút filter danh mục */
.filter-btn.active{
    background:#0d6efd;
    color:#fff !important;
}

/* ================= CART BUTTON FLOAT ================= */
.cart-btn{
    position:fixed;
    bottom:20px;
    right:20px;
    z-index:10;
    border-radius:50%;
    height:75px;
    width:75px;
    font-size:26px;
}

/* ================= CHAT MOMO ================= */
#momo-chat{
    position:fixed;
    bottom:100px;
    right:20px;
    width:360px;
    max-width:95vw;
    background:white;
    border-radius:15px;
    box-shadow:0 10px 40px rgba(0,0,0,0.3);
    display:none;
    flex-direction:column;
    overflow:hidden;
    z-index:9999;
}
#momo-header{
    background:#ff2e87;
    color:white;
    padding:12px;
    text-align:center;
    font-weight:bold;
    font-size:18px;
}

#chat_area{
    height:380px;
    overflow-y:auto;
    padding:12px;
    background:#fafafa;
}

.chat-bubble{
    margin:6px 0;
    padding:10px 14px;
    border-radius:12px;
    max-width:80%;
    line-height:1.35;
    font-size:15px;
}

.chat-user{ background:#d4edff; margin-left:auto; }
.chat-ai{ background:#eee; margin-right:auto; }

/* Bubble loading */
.chat-loading{
    display:inline-flex;
    gap:4px;
}
.chat-loading span{
    width:6px;
    height:6px;
    border-radius:50%;
    background:#999;
    display:inline-block;
    animation: loadingDot 0.9s infinite alternate;
}
.chat-loading span:nth-child(2){
    animation-delay:0.15s;
}
.chat-loading span:nth-child(3){
    animation-delay:0.3s;
}
@keyframes loadingDot{
    from{ transform:translateY(0); opacity:0.5; }
    to{ transform:translateY(-3px); opacity:1; }
}

#momo-open-btn{
    position:fixed;
    bottom:30px;
    right:20px;
    background:#ff2e87;
    border:none;
    padding:14px 15px;
    border-radius:50%;
    color:white;
    font-size:26px;
    cursor:pointer;
    box-shadow:0 8px 20px rgba(0,0,0,0.25);
    z-index:9999;
    transition:0.2s;
}

/* Hover trên PC */
@media (hover: hover) {
    #momo-open-btn:hover{
        transform:scale(1.08);
    }
}
</style>
</head>

<body>
<div class="container mt-3 mb-5">
    <div class="d-flex align-items-center mb-2">
        <div>
            <h3 class="mb-0">📖 Menu gọi món</h3>
            <p class="text-muted mb-0">
                Bàn: <b><?= htmlspecialchars($t['table_no']) ?></b>
            </p>
        </div>
        <button id="theme-toggle" class="btn btn-outline-secondary btn-sm ms-auto">
            🌙
        </button>
    </div>

    <input type="hidden" id="table_id" value="<?= $table_id ?>">

    <!-- BỘ LỌC DANH MỤC -->
    <div class="mb-3">
        <div class="d-flex flex-wrap gap-2">
            <button class="btn btn-sm btn-outline-secondary filter-btn active" data-cat-id="all">
                Tất cả
            </button>
            <?php while($c = $cats->fetch_assoc()): ?>
                <button class="btn btn-sm btn-outline-secondary filter-btn"
                        data-cat-id="<?= $c['id'] ?>">
                    <?= htmlspecialchars($c['name']) ?>
                </button>
            <?php endwhile; ?>
        </div>
    </div>

    <!-- LIST MÓN -->
    <div class="row g-3" id="menu-list">
    <?php while ($row = $items->fetch_assoc()): ?>
        <div class="col-6 col-md-4 col-lg-3 menu-item"
             data-cat-id="<?= $row['category_id'] ?>">
            <div class="card item-card">
                <?php if ($row['image']): ?>
                    <img src="uploads/menu/<?= $row['image'] ?>" 
                        alt="<?= htmlspecialchars($row['name']) ?>">
                <?php else: ?>
                    <img src="https://via.placeholder.com/400x400?text=No+Image" 
                        alt="<?= htmlspecialchars($row['name']) ?>">
                <?php endif; ?>

                <div class="p-3">
                    <h6 class="mb-1"><?= htmlspecialchars($row['name']) ?></h6>
                    <small class="text-muted d-block mb-1">
                        <?= htmlspecialchars($row['category_name']) ?>
                    </small>
                    <p class="mt-1 mb-2 fw-bold text-danger">
                        <?= number_format($row['price']) ?> đ
                    </p>

                    <button 
                        class="btn btn-primary w-100 add-to-cart"
                        data-id="<?= $row['id'] ?>"
                        data-name="<?= htmlspecialchars($row['name'], ENT_QUOTES) ?>"
                        data-price="<?= $row['price'] ?>"
                    >
                        ➕ Thêm vào giỏ
                    </button>
                </div>
            </div>
        </div>
    <?php endwhile; ?>
    </div>
</div>

<button class="btn btn-warning cart-btn" onclick="showCart()">🛒</button>

<!-- ================= MODAL GIỎ ================= -->
<div class="modal fade" id="cartModal">
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">🛒 Giỏ hàng</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"
                    aria-label="Close"></button>
        </div>
        <div class="modal-body" id="cartContent"></div>

        <div class="modal-footer">
            <button class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            <button class="btn btn-success" onclick="submitOrder()">Gửi order</button>
        </div>
    </div>
</div>
</div>

<!-- ================= CHAT MOMO ================= -->
<div id="momo-chat">
    <div id="momo-header">🤖 Momo Assistant</div>

    <div id="chat_area"></div>

    <div style="padding:10px;display:flex;gap:5px">
        <input id="chat_input" type="text" class="form-control"
            placeholder="Hỏi giá, đặt món, hủy món, xem trạng thái...">
        <button onclick="sendAI()" class="btn btn-primary">Gửi</button>
    </div>
</div>

<button id="momo-open-btn" onclick="toggleChat()">💬</button>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
let cart = [];
const TABLE_ID = <?= $table_id ?>;
let cartModalInstance = null;
let holdInterval = null;

/* ============ DARK MODE ============ */
const themeToggle = document.getElementById("theme-toggle");
function applyThemeFromStorage(){
    const saved = localStorage.getItem("theme");
    if (saved === "dark") {
        document.body.classList.add("dark-mode");
        themeToggle.textContent = "☀️";
    } else {
        document.body.classList.remove("dark-mode");
        themeToggle.textContent = "🌙";
    }
}
applyThemeFromStorage();

themeToggle.addEventListener("click", ()=>{
    document.body.classList.toggle("dark-mode");
    const isDark = document.body.classList.contains("dark-mode");
    localStorage.setItem("theme", isDark ? "dark" : "light");
    themeToggle.textContent = isDark ? "☀️" : "🌙";
});

/* ============ THÊM GIỎ HÀNG ============ */
document.querySelectorAll(".add-to-cart").forEach(btn=>{
    btn.addEventListener("click", ()=>{
        const id = btn.dataset.id;
        const name = btn.dataset.name;
        const price = Number(btn.dataset.price);

        const existing = cart.find(x => x.id === id);
        if (existing) {
            existing.qty++;
        } else {
            cart.push({ id, name, price, qty:1 });
        }
        alert("Đã thêm vào giỏ!");
    });
});

/* ============ RENDER GIỎ HÀNG ============ */
function renderCart(){
    const container = document.getElementById("cartContent");
    if (!cart.length){
        container.innerHTML = "<p>Chưa có món nào.</p>";
        return;
    }

    let total = 0;
    let html = "";

    cart.forEach(x=>{
        const line = x.price * x.qty;
        total += line;
        html += `
        <div class="cart-item d-flex justify-content-between align-items-center mb-2">
            <div class="me-2">
                <b>${x.name}</b><br>
                <small>${x.price.toLocaleString()} đ</small>
            </div>
            <div class="text-end">
                <div class="input-group input-group-sm" style="width:120px;">
                    <button class="btn btn-outline-secondary btn-qty"
                            data-id="${x.id}" data-action="dec">-</button>
                    <input type="text" class="form-control text-center" 
                           value="${x.qty}" readonly>
                    <button class="btn btn-outline-secondary btn-qty"
                            data-id="${x.id}" data-action="inc">+</button>
                </div>
                <small class="d-block mt-1 text-muted">
                    Dòng: ${line.toLocaleString()} đ
                </small>
            </div>
        </div>
        `;
    });

    html += `
        <hr>
        <h5>Tổng: <span class="text-danger">${total.toLocaleString()} đ</span></h5>
    `;

    container.innerHTML = html;

    attachQtyEvents();
}

/* ============ GIỮ NÚT + / - ============ */
function changeQty(id, action){
    const item = cart.find(x => x.id === id);
    if (!item) return;

    if (action === "inc"){
        item.qty++;
    } else if (action === "dec"){
        if (item.qty > 1) {
            item.qty--;
        } else {
            if (confirm("Xóa món này khỏi giỏ?")){
                cart = cart.filter(x => x.id !== id);
            }
        }
    }
    renderCart();
}

function attachQtyEvents(){
    const buttons = document.querySelectorAll(".btn-qty");
    buttons.forEach(btn=>{
        const id = btn.dataset.id;
        const action = btn.dataset.action;

        // Click ngắn
        btn.addEventListener("click", (e)=>{
            e.preventDefault();
            changeQty(id, action);
        });

        // Giữ chuột
        btn.addEventListener("mousedown", (e)=>{
            e.preventDefault();
            if (holdInterval) clearInterval(holdInterval);
            changeQty(id, action);
            holdInterval = setInterval(()=>changeQty(id, action), 200);
        });
        btn.addEventListener("mouseup", ()=>{
            if (holdInterval) clearInterval(holdInterval);
        });
        btn.addEventListener("mouseleave", ()=>{
            if (holdInterval) clearInterval(holdInterval);
        });

        // Touch trên điện thoại
        btn.addEventListener("touchstart", (e)=>{
            e.preventDefault();
            if (holdInterval) clearInterval(holdInterval);
            changeQty(id, action);
            holdInterval = setInterval(()=>changeQty(id, action), 200);
        }, {passive:false});

        btn.addEventListener("touchend", ()=>{
            if (holdInterval) clearInterval(holdInterval);
        });
    });
}

/* ============ SHOW GIỎ HÀNG ============ */
function showCart(){
    renderCart();
    if (!cartModalInstance){
        cartModalInstance = new bootstrap.Modal(document.getElementById("cartModal"));
    }
    cartModalInstance.show();
}

/* ============ GỬI ORDER ============ */
function submitOrder(){
    if (!cart.length) return alert("Giỏ hàng trống!");

    fetch("save_order.php",{
        method:"POST",
        headers:{ "Content-Type":"application/json" },
        body: JSON.stringify({ table_id: TABLE_ID, cart })
    })
    .then(r=>r.text())
    .then(res=>{
        if (res.startsWith("OK")){
            alert("Đặt món thành công!");
            cart = [];
            window.location = "order_status.php?table_id="+TABLE_ID;
        } else alert("Lỗi: "+res);
    })
    .catch(err=>{
        alert("Không gửi được order: " + err);
    });
}

/* ================== BỘ LỌC DANH MỤC ================== */
document.querySelectorAll(".filter-btn").forEach(btn=>{
    btn.addEventListener("click", ()=>{
        const catId = btn.dataset.catId;
        document.querySelectorAll(".filter-btn").forEach(b=>{
            b.classList.remove("active");
        });
        btn.classList.add("active");

        const items = document.querySelectorAll(".menu-item");
        items.forEach(it=>{
            const itemCat = it.dataset.catId;
            if (catId === "all" || catId === itemCat){
                it.style.display = "";
            } else {
                it.style.display = "none";
            }
        });
    });
});

/* ================= CHAT AI ================= */
function toggleChat(){
    const box = document.getElementById("momo-chat");
    const chatArea = document.getElementById("chat_area");

    if (box.style.display === "flex"){
        box.style.display = "none";
    } else {
        box.style.display = "flex";
        if (!box.dataset.init){
            addAI("Dạ em Momo đây, em hỗ trợ anh/chị xem giá, đặt món, hủy món, xem trạng thái order nha ♥");
            box.dataset.init = "1";
        }
    }
}

function addUser(msg){
    const area = document.getElementById("chat_area");
    area.innerHTML += `<div class="chat-bubble chat-user">${msg}</div>`;
    area.scrollTop = area.scrollHeight;
}
function addAI(msg){
    const area = document.getElementById("chat_area");
    area.innerHTML += `<div class="chat-bubble chat-ai">${msg}</div>`;
    area.scrollTop = area.scrollHeight;
}

function showLoadingBubble(){
    const area = document.getElementById("chat_area");
    const div = document.createElement("div");
    div.className = "chat-bubble chat-ai";
    div.id = "loading-bubble";
    div.innerHTML = `
        <div class="chat-loading">
            <span></span><span></span><span></span>
        </div>
    `;
    area.appendChild(div);
    area.scrollTop = area.scrollHeight;
}
function hideLoadingBubble(){
    const el = document.getElementById("loading-bubble");
    if (el) el.remove();
}

async function sendAI(){
    const input = document.getElementById("chat_input");
    const text = input.value.trim();
    if (!text) return;

    addUser(text);
    input.value = "";

    const form = new FormData();
    form.append("text", text);
    form.append("table_id", TABLE_ID);

    showLoadingBubble();

    try{
        const res = await fetch("http://127.0.0.1:8000/ai", { method:"POST", body:form });
        const data = await res.json();
        hideLoadingBubble();
        addAI(data.text || "Em trả lời bị lỗi rồi anh/chị ơi 😭");
    }catch(e){
        hideLoadingBubble();
        addAI("Em không kết nối được server AI 😭");
    }
}

// Enter để gửi chat
document.getElementById("chat_input").addEventListener("keydown", (e)=>{
    if (e.key === "Enter"){
        e.preventDefault();
        sendAI();
    }
});
</script>

</body>
</html>
