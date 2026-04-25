<?php
/* =====================================================================
 * cashier/order_entry.php
 * Controller-first POS interface for cashier order entry.
 * ===================================================================== */
require_once '../includes/cashier_check.php';

$page_title = 'Order Entry';
$db = (new Database())->getConnection();

// ── Fetch all available products with category ───────────────────────
$rows = $db->query(
    "SELECT p.id, p.name, p.price, p.stock, p.image,
            COALESCE(c.name, 'Uncategorized') AS category_name
     FROM   products p
     LEFT   JOIN categories c ON c.id = p.category_id
     WHERE  p.is_available = 1 AND p.stock > 0
     ORDER  BY c.name, p.name"
)->fetchAll(PDO::FETCH_ASSOC);

// Group by category for tab rendering
$by_cat = [];
foreach ($rows as $r) {
    $by_cat[$r['category_name']][] = $r;
}
$cats = array_keys($by_cat);

// JSON for JavaScript cart engine
$products_json = json_encode($rows, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);

// Walk-in user id (used as user_id for cashier orders)
$walkin_stmt = $db->prepare("SELECT id FROM users WHERE email = 'walkin@cheries.com' LIMIT 1");
$walkin_stmt->execute();
$walkin_id   = (int)($walkin_stmt->fetchColumn() ?: 0);

include '../includes/header.php';
?>
<?php include '../includes/cashier_nav.php'; ?>

  <div style="flex:1; display:flex; overflow:hidden; height:calc(100vh - 65px);">

    <!-- ══ LEFT PANEL — Products ══════════════════════════════════════════ -->
    <div style="flex:1; overflow-y:auto; padding:22px 18px 22px 22px;">

      <!-- Category Tabs -->
      <div id="catTabs" style="display:flex; gap:8px; flex-wrap:wrap; margin-bottom:18px;">
        <button class="cat-tab active" data-cat="__all__"
                style="padding:7px 16px; border-radius:20px; border:2px solid #a66b27;
                       background:#a66b27; color:#fff; font-weight:600; cursor:pointer;
                       font-size:.85rem; font-family:inherit;">All</button>
        <?php foreach ($cats as $cat): ?>
          <button class="cat-tab" data-cat="<?php echo htmlspecialchars($cat, ENT_QUOTES, 'UTF-8'); ?>"
                  style="padding:7px 16px; border-radius:20px; border:2px solid #a66b27;
                         background:#fff; color:#a66b27; font-weight:600; cursor:pointer;
                         font-size:.85rem; font-family:inherit;">
            <?php echo htmlspecialchars($cat, ENT_QUOTES, 'UTF-8'); ?>
          </button>
        <?php endforeach; ?>
      </div>

      <!-- Search bar -->
      <div style="margin-bottom:18px;">
        <input id="productSearch" type="text" placeholder="🔍 Search products…"
               style="width:100%; max-width:360px; padding:9px 14px; border:1px solid #ddd;
                      border-radius:8px; font-size:.9rem; font-family:inherit; outline:none;">
      </div>

      <!-- Product Grid -->
      <div id="productGrid"
           style="display:grid; grid-template-columns:repeat(auto-fill,minmax(165px,1fr)); gap:14px;">
        <?php foreach ($rows as $p): ?>
          <?php
            $img = $p['image']
                ? '../assets/images/products/' . htmlspecialchars($p['image'], ENT_QUOTES, 'UTF-8')
                : 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTIwIiBoZWlnaHQ9IjEwMCIgdmlld0JveD0iMCAwIDEyMCAxMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHJlY3Qgd2lkdGg9IjEyMCIgaGVpZ2h0PSIxMDAiIGZpbGw9IiNmNWYwZWEiLz48dGV4dCB4PSI2MCIgeT0iNTUiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGZpbGw9IiNhNjZiMjciIGZvbnQtc2l6ZT0iMzIiPvCfjbU8L3RleHQ+PC9zdmc+';
          ?>
          <div class="prod-card"
               data-id="<?php echo (int)$p['id']; ?>"
               data-name="<?php echo htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8'); ?>"
               data-price="<?php echo (float)$p['price']; ?>"
               data-stock="<?php echo (int)$p['stock']; ?>"
               data-cat="<?php echo htmlspecialchars($p['category_name'], ENT_QUOTES, 'UTF-8'); ?>"
               onclick="addToCart(this)"
               style="background:#fff; border-radius:12px; overflow:hidden; cursor:pointer;
                      box-shadow:0 2px 6px rgba(0,0,0,.08); transition:transform .15s,box-shadow .15s;
                      border:2px solid transparent;"
               onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='0 6px 16px rgba(166,107,39,.18)';"
               onmouseout="this.style.transform='';this.style.boxShadow='0 2px 6px rgba(0,0,0,.08)';">
            <img src="<?php echo $img; ?>"
                 alt="<?php echo htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8'); ?>"
                 style="width:100%; height:110px; object-fit:cover;"
                 onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTIwIiBoZWlnaHQ9IjExMCIgdmlld0JveD0iMCAwIDEyMCAxMTAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHJlY3Qgd2lkdGg9IjEyMCIgaGVpZ2h0PSIxMTAiIGZpbGw9IiNmNWYwZWEiLz48dGV4dCB4PSI2MCIgeT0iNjAiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGZpbGw9IiNhNjZiMjciIGZvbnQtc2l6ZT0iMzYiPvCfjbU8L3RleHQ+PC9zdmc+'">
            <div style="padding:10px 12px 12px;">
              <div style="font-weight:600; font-size:.85rem; color:#2d1a09; line-height:1.3; margin-bottom:4px;
                          white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                <?php echo htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8'); ?>
              </div>
              <div style="color:#a66b27; font-weight:700; font-size:.92rem;">
                <?php echo format_price($p['price']); ?>
              </div>
              <div style="font-size:.75rem; color:#999; margin-top:2px;">
                Stock: <?php echo (int)$p['stock']; ?>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <?php if (empty($rows)): ?>
        <div style="text-align:center; padding:60px 20px; color:#999;">
          <div style="font-size:3rem; margin-bottom:12px;">🍽️</div>
          <p>No products available.</p>
        </div>
      <?php endif; ?>

    </div><!-- /left panel -->

    <!-- ══ RIGHT PANEL — Order Cart ═══════════════════════════════════════ -->
    <div style="width:320px; min-width:280px; background:#fff; border-left:1px solid #e8e0d5;
                display:flex; flex-direction:column; overflow:hidden;">

      <!-- Cart Header -->
      <div style="padding:16px 18px; background:#a66b27; color:#fff; display:flex;
                  justify-content:space-between; align-items:center;">
        <span style="font-weight:700; font-size:1rem;">🧾 Current Order</span>
        <button onclick="clearCart()"
                style="background:rgba(0,0,0,.2); border:none; color:#fff; padding:4px 10px;
                       border-radius:6px; cursor:pointer; font-size:.8rem; font-family:inherit;">
          Clear
        </button>
      </div>

      <!-- Customer Name -->
      <div style="padding:12px 18px; border-bottom:1px solid #f0e8dd;">
        <label style="font-size:.78rem; font-weight:600; color:#7D6E6E; display:block; margin-bottom:4px;">
          Customer Name (optional)
        </label>
        <input id="customerName" type="text" placeholder="Walk-in Customer"
               style="width:100%; padding:8px 11px; border:1px solid #ddd; border-radius:7px;
                      font-size:.88rem; font-family:inherit; outline:none; box-sizing:border-box;">
      </div>

      <!-- Cart Items -->
      <div id="cartItems" style="flex:1; overflow-y:auto; padding:10px 12px;">
        <div id="emptyCart" style="text-align:center; padding:40px 10px; color:#bbb;">
          <div style="font-size:2.5rem; margin-bottom:8px;">🛒</div>
          <p style="font-size:.85rem;">Click a product to add it</p>
        </div>
      </div>

      <!-- Totals & Payment -->
      <div style="padding:14px 18px; border-top:1px solid #f0e8dd; background:#fdfaf7;">

        <div style="display:flex; justify-content:space-between; margin-bottom:6px; font-size:.88rem;">
          <span style="color:#7D6E6E;">Subtotal</span>
          <span id="subtotalDisplay" style="font-weight:600;">₱0.00</span>
        </div>
        <div style="display:flex; justify-content:space-between; margin-bottom:12px; font-size:1rem; font-weight:700; color:#a66b27;">
          <span>Total</span>
          <span id="totalDisplay">₱0.00</span>
        </div>

        <!-- Payment Method -->
        <div style="margin-bottom:12px;">
          <label style="font-size:.78rem; font-weight:600; color:#7D6E6E; display:block; margin-bottom:4px;">
            Payment Method
          </label>
          <select id="paymentMethod"
                  style="width:100%; padding:8px 11px; border:1px solid #ddd; border-radius:7px;
                         font-size:.88rem; font-family:inherit; outline:none;">
            <option value="Cash">Cash</option>
            <option value="GCash">GCash</option>
            <option value="PayMaya">PayMaya</option>
            <option value="Card">Credit / Debit Card</option>
          </select>
        </div>

        <!-- Place Order Button -->
        <button id="placeOrderBtn" onclick="submitOrder()" disabled
                style="width:100%; padding:13px; background:#a66b27; color:#fff; border:none;
                       border-radius:9px; font-weight:700; font-size:.95rem; cursor:pointer;
                       font-family:inherit; transition:background .2s; opacity:.5;">
          Place Order
        </button>
      </div>

    </div><!-- /right panel -->

  </div><!-- /flex wrapper -->

<?php
$walkin_id_js = $walkin_id;
$additional_js = '
<script>
/* ── Data ──────────────────────────────────────────────────────────── */
const PRODUCTS = ' . $products_json . ';
let cart = {};   // { productId: { id, name, price, qty, stock } }

/* ── Product search + category filter ─────────────────────────────── */
let activeCat = "__all__";

document.querySelectorAll(".cat-tab").forEach(btn => {
  btn.addEventListener("click", () => {
    document.querySelectorAll(".cat-tab").forEach(b => {
      b.style.background = "#fff";
      b.style.color      = "#a66b27";
    });
    btn.style.background = "#a66b27";
    btn.style.color      = "#fff";
    activeCat = btn.dataset.cat;
    filterProducts();
  });
});

document.getElementById("productSearch").addEventListener("input", filterProducts);

function filterProducts() {
  const q   = document.getElementById("productSearch").value.toLowerCase().trim();
  document.querySelectorAll(".prod-card").forEach(card => {
    const nameMatch = card.dataset.name.toLowerCase().includes(q);
    const catMatch  = activeCat === "__all__" || card.dataset.cat === activeCat;
    card.style.display = (nameMatch && catMatch) ? "" : "none";
  });
}

/* ── Cart logic ────────────────────────────────────────────────────── */
function addToCart(el) {
  const id    = parseInt(el.dataset.id);
  const name  = el.dataset.name;
  const price = parseFloat(el.dataset.price);
  const stock = parseInt(el.dataset.stock);

  if (cart[id]) {
    if (cart[id].qty >= cart[id].stock) {
      Swal.fire({ icon:"warning", title:"Stock Limit", text:"No more stock available.", confirmButtonColor:"#a66b27", timer:1400, showConfirmButton:false });
      return;
    }
    cart[id].qty++;
  } else {
    cart[id] = { id, name, price, qty: 1, stock };
  }
  renderCart();
  flashCard(el);
}

function flashCard(el) {
  el.style.border = "2px solid #a66b27";
  el.style.background = "#fdf5ec";
  setTimeout(() => { el.style.border = "2px solid transparent"; el.style.background = "#fff"; }, 300);
}

function changeQty(id, delta) {
  if (!cart[id]) return;
  cart[id].qty = Math.max(0, Math.min(cart[id].stock, cart[id].qty + delta));
  if (cart[id].qty === 0) delete cart[id];
  renderCart();
}

function removeItem(id) {
  delete cart[id];
  renderCart();
}

function clearCart() {
  cart = {};
  renderCart();
}

function renderCart() {
  const container = document.getElementById("cartItems");
  const empty     = document.getElementById("emptyCart");
  const ids       = Object.keys(cart);
  const btn       = document.getElementById("placeOrderBtn");

  if (ids.length === 0) {
    empty.style.display    = "";
    container.innerHTML    = "";
    container.appendChild(empty);
    document.getElementById("subtotalDisplay").textContent = "₱0.00";
    document.getElementById("totalDisplay").textContent    = "₱0.00";
    btn.disabled = true;
    btn.style.opacity = ".5";
    btn.style.cursor  = "not-allowed";
    return;
  }

  empty.style.display = "none";
  let html = "";
  let total = 0;

  ids.forEach(id => {
    const item = cart[id];
    const sub  = item.price * item.qty;
    total     += sub;
    html += `
      <div style="display:flex; align-items:center; gap:8px; padding:9px 4px; border-bottom:1px solid #f5f0ea;">
        <div style="flex:1; min-width:0;">
          <div style="font-size:.82rem; font-weight:600; color:#2d1a09; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${escHtml(item.name)}</div>
          <div style="font-size:.78rem; color:#a66b27; font-weight:600;">₱${item.price.toLocaleString("en-PH",{minimumFractionDigits:2})}</div>
        </div>
        <div style="display:flex; align-items:center; gap:5px;">
          <button onclick="changeQty(${id},-1)"
                  style="width:26px;height:26px;border-radius:50%;border:1px solid #ddd;background:#f5f0ea;
                         cursor:pointer;font-size:.9rem;line-height:1;font-family:inherit;">−</button>
          <span style="font-weight:700; min-width:20px; text-align:center; font-size:.9rem;">${item.qty}</span>
          <button onclick="changeQty(${id},1)"
                  style="width:26px;height:26px;border-radius:50%;border:1px solid #ddd;background:#f5f0ea;
                         cursor:pointer;font-size:.9rem;line-height:1;font-family:inherit;">+</button>
        </div>
        <div style="font-size:.82rem; font-weight:700; color:#2d1a09; min-width:58px; text-align:right;">
          ₱${sub.toLocaleString("en-PH",{minimumFractionDigits:2})}
        </div>
        <button onclick="removeItem(${id})"
                style="background:none;border:none;color:#dc3545;cursor:pointer;font-size:.9rem;padding:2px;">✕</button>
      </div>`;
  });

  container.innerHTML = html;
  const fmt = v => "₱" + v.toLocaleString("en-PH",{minimumFractionDigits:2});
  document.getElementById("subtotalDisplay").textContent = fmt(total);
  document.getElementById("totalDisplay").textContent    = fmt(total);

  btn.disabled      = false;
  btn.style.opacity = "1";
  btn.style.cursor  = "pointer";
}

/* ── Submit order ──────────────────────────────────────────────────── */
function submitOrder() {
  const ids = Object.keys(cart);
  if (ids.length === 0) return;

  const items = ids.map(id => ({ product_id: parseInt(id), qty: cart[id].qty }));
  const customerName  = document.getElementById("customerName").value.trim() || "Walk-in Customer";
  const paymentMethod = document.getElementById("paymentMethod").value;

  Swal.fire({
    title: "Confirm Order?",
    html:  `<b>${customerName}</b><br>${items.length} item(s) &nbsp;·&nbsp; <b>${document.getElementById("totalDisplay").textContent}</b>`,
    icon:  "question",
    showCancelButton:    true,
    confirmButtonText:   "Place Order",
    cancelButtonText:    "Review",
    confirmButtonColor:  "#a66b27",
    cancelButtonColor:   "#7D6E6E"
  }).then(result => {
    if (!result.isConfirmed) return;

    document.getElementById("placeOrderBtn").disabled = true;

    $.ajax({
      url:      "ajax/submit_order.php",
      type:     "POST",
      data:     JSON.stringify({ customer_name: customerName, payment_method: paymentMethod, items }),
      contentType: "application/json",
      dataType: "json",
      success: function(res) {
        if (res.success) {
          Swal.fire({
            icon:  "success",
            title: "Order Placed!",
            html:  `Order <b>#${res.order_id}</b> created.<br>Total: <b>₱${res.total.toLocaleString("en-PH",{minimumFractionDigits:2})}</b>`,
            confirmButtonColor: "#a66b27"
          }).then(() => {
            cart = {};
            renderCart();
            document.getElementById("customerName").value = "";
            document.getElementById("placeOrderBtn").disabled = false;
          });
        } else {
          Swal.fire({ icon:"error", title:"Error", text: res.message, confirmButtonColor:"#a66b27" });
          document.getElementById("placeOrderBtn").disabled = false;
        }
      },
      error: function(xhr) {
        Swal.fire({ icon:"error", title:"Server Error", text: "Could not submit order. Try again.", confirmButtonColor:"#a66b27" });
        document.getElementById("placeOrderBtn").disabled = false;
      }
    });
  });
}

/* ── Escape helper ─────────────────────────────────────────────────── */
function escHtml(str) {
  return String(str).replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;");
}
</script>

<style>
body { background: #f5f0ea; }
/* Override base body flex so the two-panel layout fills the viewport */
body > main { overflow: hidden; }
</style>
';

include '../includes/footer.php';
?>
