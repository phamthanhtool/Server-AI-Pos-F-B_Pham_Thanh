from openai import OpenAI
import json
import time
import mysql.connector

from fastapi import FastAPI, Form
from fastapi.middleware.cors import CORSMiddleware
import uvicorn

# ============================================================
#  OPENAI API
# ============================================================
API_KEY = ""
client = OpenAI(api_key=API_KEY)

# ============================================================
#  MYSQL DATABASE
# ============================================================
db = mysql.connector.connect(
    host="localhost",
    user="root",
    password="",
    database="quan_ly_nha_hang"
)
cursor = db.cursor()

# ============================================================
#  DATABASE FUNCTIONS
# ============================================================

def db_get_current_order(table_id):
    """Lấy order mới nhất của bàn (theo id DESC)."""
    sql = "SELECT id, items, status FROM orders WHERE table_id=%s ORDER BY id DESC LIMIT 1"
    cursor.execute(sql, (table_id,))
    row = cursor.fetchone()

    if not row:
        return None, [], ""  # chưa có order

    order_id = row[0]
    try:
        items = json.loads(row[1])
    except:
        items = []

    status_str = row[2] if row[2] else ""

    return order_id, items, status_str


def db_add_order_item(table_id, item_name, qty):
    """
    Thêm món vào đơn hiện tại:
      - Nếu chưa có đơn → tạo mới
      - Nếu có rồi → append vào items
    items: JSON array:
      [{"name":..., "qty":..., "price":0, "status":"queued"}, ...]
    status (cột orders.status) dạng: "queued, queued, cooking, ..."
    """
    order_id, items, status_str = db_get_current_order(table_id)

    if order_id is None:
        # Chưa có order → tạo mới
        new_items = [{
            "name": item_name,
            "qty": qty,
            "price": 0,
            "status": "queued"
        }]

        # tạo status list tương ứng số món
        status_list = ["queued"] * len(new_items)
        new_status = ", ".join(status_list)

        sql = """
            INSERT INTO orders (table_id, items, total, status, source)
            VALUES (%s, %s, %s, %s, %s)
        """
        cursor.execute(sql, (table_id, json.dumps(new_items, ensure_ascii=False), 0, new_status, "ai"))
        db.commit()
        return f"Đã tạo order mới và thêm {qty} {item_name}."

    # Nếu đã có order → push món mới vào items
    items.append({
        "name": item_name,
        "qty": qty,
        "price": 0,
        "status": "queued"
    })

    # xử lý status_list
    if status_str:
        status_list = [s.strip() for s in status_str.split(",")]
    else:
        status_list = []

    # đảm bảo status_list đủ độ dài
    while len(status_list) < len(items) - 1:
        status_list.append("queued")

    # thêm trạng thái cho món mới
    status_list.append("queued")
    new_status = ", ".join(status_list)

    sql = "UPDATE orders SET items=%s, status=%s WHERE id=%s"
    cursor.execute(sql, (json.dumps(items, ensure_ascii=False), new_status, order_id))
    db.commit()

    return f"Đã thêm {qty} {item_name} vào order hiện tại."


def db_cancel_item(table_id, item_name):
    """Đánh dấu trạng thái món trong cột orders.status = canceled (nếu đang queued/waiting)."""
    order_id, items, status_str = db_get_current_order(table_id)
    if order_id is None:
        return "Bàn này chưa có order nào."

    # Chuyển chuỗi status -> list
    if status_str:
        status_list = [s.strip() for s in status_str.split(",")]
    else:
        status_list = ["queued"] * len(items)

    # Tìm món
    found = False
    for idx, obj in enumerate(items):
        if obj["name"].lower() == item_name.lower():
            current_status = status_list[idx] if idx < len(status_list) else "queued"
            # Chỉ cho hủy khi queued/waiting
            if current_status not in ("queued", "waiting"):
                return "Món này đã vào bếp, không thể hủy."
            status_list[idx] = "canceled"
            found = True
            break

    if not found:
        return "Không tìm thấy món trong đơn."

    new_status = ", ".join(status_list)
    sql = "UPDATE orders SET status=%s WHERE id=%s"
    cursor.execute(sql, (new_status, order_id))
    db.commit()

    return "Đã hủy món thành công."


def db_get_status(table_id):
    """Trả về (foods, statuses) theo đơn mới nhất của bàn."""
    order_id, items, status_str = db_get_current_order(table_id)
    if order_id is None:
        return [], []

    if status_str:
        order_statuses = [s.strip() for s in status_str.split(",")]
    else:
        order_statuses = []

    while len(order_statuses) < len(items):
        order_statuses.append("queued")

    foods = [i["name"] for i in items]
    return foods, order_statuses


def db_get_description(name):
    """Lấy mô tả món từ menu_items."""
    sql = "SELECT description FROM menu_items WHERE name=%s LIMIT 1"
    cursor.execute(sql, (name,))
    row = cursor.fetchone()
    return row[0] if row else "Không có mô tả."


# ============================================================
#  MEMORY (4h)
# ============================================================
conversation_history = []
CONTEXT_EXPIRE = 4 * 60 * 60

def add_to_history(role, content):
    conversation_history.append({
        "role": role,
        "content": content,
        "ts": time.time()
    })

def cleanup_history():
    now = time.time()
    global conversation_history
    conversation_history = [
        x for x in conversation_history
        if now - x["ts"] <= CONTEXT_EXPIRE
    ]

def build_history_messages():
    cleanup_history()
    msgs = [{"role": "system", "content": PROMPT_PHAN_TICH}]
    for x in conversation_history:
        msgs.append({"role": x["role"], "content": x["content"]})
    return msgs


# ============================================================
#  PROMPTS — GIỮ ĐÚNG FORMAT JSON
# ============================================================

PROMPT_PHAN_TICH = """
Hãy phân tích câu nói của người dùng và trả về đúng JSON:

{
  "hanh_dong": "",
  "doi_tuong": "",
  "so_luong": null,
  "can_tra_loi_trang_thai": false,
  "tra_loi": ""
}

Quy tắc:

1. Nếu người dùng muốn đặt món:
   - hanh_dong = "đặt món"
   - doi_tuong = tên món ăn
       + ví dụ: "2 phở bò 2 lẩu thái" → doi_tuong = "phở bò, lẩu thái"
   - so_luong = số lượng món (nếu người dùng nói rõ)
       + ví dụ: "2 phở bò 2 lẩu thái" → so_luong = "2, 2"
       + "cho tôi phở bò" → so_luong = null
   - can_tra_loi_trang_thai = false

   - tra_loi:
       + Nếu không có so_luong:
           → "Bạn muốn đặt bao nhiêu phần {doi_tuong} ạ?"
       + Nếu có so_luong:
           → "Tôi đã đặt {so_luong} {doi_tuong} cho bạn rồi ạ. Bạn muốn gọi thêm món nào nữa không?"

2. Nếu người dùng muốn hủy món:
   - hanh_dong = "hủy món"
   - doi_tuong = tên món ăn
   - can_tra_loi_trang_thai = false
   - so_luong = null
   - tra_loi = "Tôi đã hủy món đó cho bạn rồi ạ."

3. Nếu người dùng hỏi thông tin món ăn:
   - hanh_dong = "tra thông tin món ăn"
   - doi_tuong = tên món
   - so_luong = null
   - can_tra_loi_trang_thai = false
   - tra_loi = "Để tôi kiểm tra thông tin món {doi_tuong} cho bạn nhé!"

4. Nếu người dùng hỏi trạng thái món ăn (vd: 'xong chưa', 'tới đâu rồi', 'sao lâu vậy'):
   - hanh_dong = "trạng thái món ăn"
   - doi_tuong = tên món (nếu đoán được, nếu không để rỗng)
   - so_luong = null
   - can_tra_loi_trang_thai = true
   - tra_loi = "Để tôi kiểm tra trạng thái món {doi_tuong} cho bạn nhé!"

5. Nếu không rõ:
   - hanh_dong = "không xác định"
   - tra_loi = trả lời theo câu hỏi
"""

PROMPT_MO_TA_MON = """
Hãy dựa vào thông tin món ăn bên dưới để trả lời cho khách:

Tên món: {name}
Mô tả: {description}
Nguyên liệu: {ingredients}
Điểm đặc biệt: {special}

Trả về đúng JSON:
{{
  "tra_loi": ""
}}
"""

PROMPT_TRA_LOI = """
Hãy tạo câu trả lời cho người dùng dựa trên danh sách món và trạng thái:

{items}

Quy tắc:
- "done": món đã hoàn thành và chuẩn bị mang ra.
- "cooking": món đang được chế biến.
- "serving": món đang được mang ra bàn.
- "queued": món đang chờ bếp.
- "canceled": món đã bị hủy.
- "not found": món chưa được đặt.

CHỈ trả về đúng JSON:
{{
  "tra_loi": ""
}}
"""


# ============================================================
#  AI FUNCTIONS
# ============================================================

def phan_tich_cau_noi(text):
    add_to_history("user", text)

    resp = client.chat.completions.create(
        model="gpt-4o-mini",
        messages=build_history_messages()
    )
    raw = resp.choices[0].message.content.strip()

    add_to_history("assistant", raw)
    return json.loads(raw)


def tao_cau_tra_loi(foods, statuses):
    items = "\n".join([f"- {a}: {b}" for a, b in zip(foods, statuses)])
    prompt = PROMPT_TRA_LOI.format(items=items)

    resp = client.chat.completions.create(
        model="gpt-4o-mini",
        messages=[{"role": "system", "content": prompt}]
    )

    raw = resp.choices[0].message.content.strip()
    return json.loads(raw)["tra_loi"]


def tra_loi_mo_ta(name):
    description = db_get_description(name)
    prompt = PROMPT_MO_TA_MON.format(
        name=name,
        description=description,
        ingredients="",
        special=""
    )

    resp = client.chat.completions.create(
        model="gpt-4o-mini",
        messages=[{"role": "system", "content": prompt}]
    )

    raw = resp.choices[0].message.content.strip()
    return json.loads(raw)["tra_loi"]


# ============================================================
#  FASTAPI SERVER
# ============================================================

app = FastAPI()

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_methods=["*"],
    allow_headers=["*"],
)


@app.post("/ai")
async def ai_chat(text: str = Form(...), table_id: int = Form(...)):
    print("\n==============================")
    print(f"📥 NHẬN YÊU CẦU AI:")
    print(f"- Bàn: {table_id}")
    print(f"- User nói: {text}")
    print("------------------------------")

    # 1. PHÂN TÍCH CÂU NÓI
    try:
        kq = phan_tich_cau_noi(text)
        print("📌 KẾT QUẢ PHÂN TÍCH:")
        print(json.dumps(kq, indent=4, ensure_ascii=False))
    except Exception as e:
        print("❌ LỖI PHÂN TÍCH:", e)
        return {"text": f"AI bị lỗi khi phân tích: {e}"}

    hanh_dong = kq.get("hanh_dong")
    doi_tuong = kq.get("doi_tuong")
    so_luong = kq.get("so_luong")
    tra_loi = kq.get("tra_loi", "Em chưa hiểu ý anh lắm ạ.")
    can_trang_thai = kq.get("can_tra_loi_trang_thai", False)

    # 2. HỎI TRẠNG THÁI MÓN
    if can_trang_thai:
        foods, statuses = db_get_status(table_id)
        print("📌 TRẠNG THÁI MÓN TỪ DATABASE:")
        print("foods =", foods)
        print("statuses =", statuses)

        if not foods:
            print("➡ AI trả lời: Hiện bàn bạn chưa đặt món nào.")
            return {"text": "Hiện bàn bạn chưa đặt món nào."}

        reply = tao_cau_tra_loi(foods, statuses)
        print("➡ AI trả lời:", reply)
        return {"text": reply}

    # 3. HỎI THÔNG TIN MÓN
    if hanh_dong == "tra thông tin món ăn" and doi_tuong:
        reply = tra_loi_mo_ta(doi_tuong)
        print("📌 THÔNG TIN MÓN:", doi_tuong)
        print("➡ AI trả lời:", reply)
        return {"text": reply}

    # 4. ĐẶT MÓN
    if hanh_dong == "đặt món" and doi_tuong:
        print("📌 YÊU CẦU ĐẶT MÓN:")
        print(f"- Món: {doi_tuong}")
        print(f"- Số lượng: {so_luong}")

        if so_luong is not None:
            try:
                qty = int(so_luong)
            except:
                qty = 1

            db_msg = db_add_order_item(table_id, doi_tuong, qty)

            full_reply = f"{tra_loi} ({db_msg})"
            print("➡ AI trả lời:", full_reply)
            return {"text": full_reply}

        print("➡ AI trả lời:", tra_loi)
        return {"text": tra_loi}

    # 5. HỦY MÓN
    if hanh_dong == "hủy món" and doi_tuong:
        print("📌 YÊU CẦU HỦY MÓN:", doi_tuong)
        db_msg = db_cancel_item(table_id, doi_tuong)

        full_reply = f"{tra_loi} ({db_msg})"
        print("➡ AI trả lời:", full_reply)
        return {"text": full_reply}

    # 6. CÁC TRƯỜNG HỢP KHÁC
    print("➡ AI trả lời:", tra_loi)
    return {"text": tra_loi}


# ============================================================
#  CHẠY SERVER
# ============================================================

if __name__ == "__main__":
    uvicorn.run(app, host="127.0.0.1", port=8000)
