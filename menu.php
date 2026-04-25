<?php
require_once 'includes/session_check.php';

$page_title = "Menu";
$database   = new Database();
$db         = $database->getConnection();

// Categories (for filter pills)
$categories = $db->query("SELECT * FROM categories ORDER BY name")->fetchAll();

include 'includes/header.php';
?>

<!-- ═══════════════════════════════ SIDEBAR ═════════════════════════════ -->
<aside>
  <div class="logo">🍴 <?php echo SITE_NAME; ?></div>
  <div class="sidebar-menu">
    <a href="cart.php">My Cart</a>
    <a href="orders.php">My Orders</a>
    <a href="order_history.php">Order History</a>
    <a href="notifications.php">Notifications</a>
    <a href="profile.php">Profile</a>
    <a href="logout.php">Logout</a>
  </div>
</aside>

<!-- ═══════════════════════════════ MAIN ════════════════════════════════ -->
<main>
  <header>
    <h3>Our Menu</h3>
    <nav>
      <a href="index.php">Home</a>
      <a href="menu.php" class="active">Menu</a>
      <a href="orders.php">My Orders</a>
      <a href="profile.php">Profile</a>
    </nav>
  </header>

  <section id="menu-section" style="flex:1; padding:30px; overflow-y:auto;">

    <!-- ── Search + Filter bar ──────────────────────────────────────── -->
    <div style="margin-bottom:24px;">

      <!-- Search input + sort -->
      <div style="display:flex; gap:12px; flex-wrap:wrap; align-items:center; margin-bottom:14px;">
        <div style="position:relative; flex:1; min-width:220px;">
          <span style="position:absolute; left:13px; top:50%; transform:translateY(-50%); color:#aaa; pointer-events:none;">🔍</span>
          <input id="searchInput" type="text" placeholder="Search food, drink…"
                 style="width:100%; padding:10px 14px 10px 38px; border:1px solid #ddd;
                        border-radius:8px; outline:none; font-size:.95rem; box-sizing:border-box;">
        </div>

        <select id="sortSelect"
                style="padding:10px 14px; border:1px solid #ddd; border-radius:8px; outline:none; background:#fff; font-size:.95rem;">
          <option value="name_asc">Name A–Z</option>
          <option value="name_desc">Name Z–A</option>
          <option value="price_asc">Price ↑</option>
          <option value="price_desc">Price ↓</option>
        </select>

        <button id="clearFilters"
                style="padding:10px 18px; background:#7D6E6E; color:#fff; border:none;
                       border-radius:8px; cursor:pointer; font-weight:600; display:none;">
          ✕ Clear
        </button>
      </div>

      <!-- Category filter pills -->
      <div id="catPills" style="display:flex; gap:8px; flex-wrap:wrap;">
        <button class="cat-pill active" data-cat="0"
                style="padding:7px 18px; border-radius:20px; border:2px solid #B76E09;
                       background:#B76E09; color:#fff; cursor:pointer; font-weight:600; font-size:.85rem; transition:all .2s;">
          All
        </button>
        <?php foreach ($categories as $cat): ?>
          <button class="cat-pill" data-cat="<?php echo (int)$cat['id']; ?>"
                  style="padding:7px 18px; border-radius:20px; border:2px solid #B76E09;
                         background:#fff; color:#B76E09; cursor:pointer; font-weight:600; font-size:.85rem; transition:all .2s;">
            <?php echo htmlspecialchars($cat['icon'] . ' ' . $cat['name'], ENT_QUOTES, 'UTF-8'); ?>
          </button>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- ── Results count ────────────────────────────────────────────── -->
    <div id="resultsInfo"
         style="font-size:.85rem; color:#7D6E6E; margin-bottom:18px;">
      Loading menu…
    </div>

    <!-- ── Product grid ─────────────────────────────────────────────── -->
    <div id="menuGrid"
         style="display:grid; grid-template-columns:repeat(auto-fill,minmax(240px,1fr)); gap:22px;">
      <!-- filled by AJAX -->
    </div>

    <!-- ── Spinner ──────────────────────────────────────────────────── -->
    <div id="menuSpinner" style="text-align:center; padding:40px; display:none;">
      <div style="width:40px; height:40px; border:4px solid #ddd; border-top-color:#B76E09;
                  border-radius:50%; animation:spin .7s linear infinite; margin:0 auto;"></div>
    </div>

  </section>
</main>

<?php
$additional_js = '
<style>
@keyframes spin { to { transform: rotate(360deg); } }

.menu-card {
  background:#fff; border-radius:12px;
  box-shadow:0 2px 6px rgba(0,0,0,.08);
  overflow:hidden; display:flex; flex-direction:column;
  transition:transform .25s, box-shadow .25s;
}
.menu-card:hover { transform:translateY(-5px); box-shadow:0 8px 18px rgba(0,0,0,.13); }
.menu-card img   { width:100%; height:160px; object-fit:cover; }
.menu-card-body  { padding:15px; text-align:center; flex:1; display:flex; flex-direction:column; }
.menu-card-body h4 { margin:0 0 6px; font-size:1.05rem; color:#000; }
.menu-card-body p  { color:#7D6E6E; font-size:.88rem; margin:0 0 10px; flex:1; }
.menu-card-price   { font-weight:700; color:#B76E09; font-size:1.05rem; margin-bottom:8px; }
.menu-card-stock   { font-size:.78rem; margin-bottom:10px; }
.stock-ok   { color:#28a745; }
.stock-low  { color:#fd7e14; font-weight:600; }
.stock-none { color:#dc3545; }
.btn-add {
  background:#B76E09; color:#fff; border:none;
  padding:9px 16px; border-radius:8px; cursor:pointer;
  font-weight:600; font-size:.9rem; transition:background .25s;
  width:100%;
}
.btn-add:hover:not(:disabled) { background:#a55f06; }
.btn-add:disabled { background:#ccc; color:#888; cursor:not-allowed; }

.cat-pill.active   { background:#B76E09 !important; color:#fff !important; }
.cat-pill:hover    { opacity:.85; }

@media(max-width:576px){
  #menu-section { padding:16px !important; }
}
</style>

<script>
(function(){
  "use strict";

  /* ── State ── */
  let activeCat  = 0;
  let searchVal  = "";
  let sortVal    = "name_asc";
  let debounceId = null;

  /* ── DOM refs ── */
  const grid        = document.getElementById("menuGrid");
  const spinner     = document.getElementById("menuSpinner");
  const info        = document.getElementById("resultsInfo");
  const clearBtn    = document.getElementById("clearFilters");
  const searchInput = document.getElementById("searchInput");
  const sortSelect  = document.getElementById("sortSelect");

  /* ── Fetch products via AJAX ── */
  function loadMenu() {
    spinner.style.display = "block";
    grid.style.display    = "none";
    info.textContent      = "";

    $.ajax({
      url:      "ajax/menu_products.php",
      method:   "GET",
      data:     { category: activeCat, search: searchVal, sort: sortVal },
      dataType: "json",
      success: function(res) {
        spinner.style.display = "none";
        grid.style.display    = "grid";

        if (!res.success) {
          grid.innerHTML = \'<p style="grid-column:1/-1;text-align:center;color:#dc3545;">\' + (res.message||"Error loading menu.") + \'</p>\';
          return;
        }

        const items = res.products;
        info.textContent = items.length + " item" + (items.length !== 1 ? "s" : "") + " found";

        if (items.length === 0) {
          grid.innerHTML = \'<div style="grid-column:1/-1;text-align:center;padding:50px;color:#7D6E6E;">\' +
            \'<p style="font-size:2rem;margin-bottom:12px;">🍽️</p>\' +
            \'<p style="font-weight:600;">No items match your search.</p>\' +
            \'<p style="font-size:.9rem;">Try a different keyword or category.</p></div>\';
          return;
        }

        grid.innerHTML = items.map(buildCard).join("");
        /* bind Add-to-Cart after render */
        grid.querySelectorAll(".btn-add[data-id]").forEach(function(btn){
          btn.addEventListener("click", addToCart);
        });
      },
      error: function() {
        spinner.style.display = "none";
        grid.style.display    = "grid";
        grid.innerHTML = \'<p style="grid-column:1/-1;text-align:center;color:#dc3545;">Failed to load menu. Please refresh.</p>\';
      }
    });
  }

  /* ── Build a product card HTML ── */
  function buildCard(p) {
    const imgSrc = p.image_url || "https://placehold.co/300x200/f5f5f5/B76E09?text=No+Image";
    let stockClass = "stock-ok", stockLabel = "In Stock (" + p.stock + ")";
    if (p.stock === 0)      { stockClass = "stock-none"; stockLabel = "Out of Stock"; }
    else if (p.low_stock)   { stockClass = "stock-low";  stockLabel = "⚠ Low Stock (" + p.stock + ")"; }

    const btnAttrs = p.stock === 0
      ? \'disabled\' 
      : \'data-id="\' + p.id + \'" data-name="\' + escHtml(p.name) + \'"\';

    return \`
      <div class="menu-card">
        <img src="\${escHtml(imgSrc)}" alt="\${escHtml(p.name)}" loading="lazy">
        <div class="menu-card-body">
          <span style="font-size:.75rem;color:#7D6E6E;margin-bottom:4px;">\${escHtml(p.category_name||"")}</span>
          <h4>\${escHtml(p.name)}</h4>
          <p>\${escHtml(p.description||"")}</p>
          <div class="menu-card-price">\${p.price_formatted}</div>
          <div class="menu-card-stock \${stockClass}">\${stockLabel}</div>
          <button class="btn-add" \${btnAttrs}>\${p.stock===0?"Out of Stock":"Add to Cart"}</button>
        </div>
      </div>
    \`;
  }

  /* ── Add to cart handler ── */
  function addToCart() {
    const btn     = this;
    const pid     = btn.dataset.id;
    const pname   = btn.dataset.name;
    btn.disabled  = true;
    btn.textContent = "Adding…";

    $.ajax({
      url:      "ajax/add_to_cart.php",
      method:   "POST",
      data:     { product_id: pid, quantity: 1 },
      dataType: "json",
      success: function(r) {
        btn.disabled    = false;
        btn.textContent = "Add to Cart";
        if (r.success) {
          Swal.fire({ icon:"success", title:"Added!", text: pname + " added to your cart.",
                      showConfirmButton:false, timer:1400, toast:true, position:"top-end" });
        } else {
          Swal.fire({ icon:"error", title:"Oops", text: r.message || "Could not add to cart." });
        }
      },
      error: function() {
        btn.disabled    = false;
        btn.textContent = "Add to Cart";
        Swal.fire({ icon:"error", title:"Error", text:"Network error – please try again." });
      }
    });
  }

  /* ── Category pill click ── */
  document.querySelectorAll(".cat-pill").forEach(function(pill){
    pill.addEventListener("click", function(){
      document.querySelectorAll(".cat-pill").forEach(p => p.classList.remove("active"));
      this.classList.add("active");
      activeCat = parseInt(this.dataset.cat, 10);
      updateClearBtn();
      loadMenu();
    });
  });

  /* ── Search with debounce ── */
  searchInput.addEventListener("input", function(){
    clearTimeout(debounceId);
    searchVal = this.value.trim();
    updateClearBtn();
    debounceId = setTimeout(loadMenu, 320);
  });

  /* ── Sort change ── */
  sortSelect.addEventListener("change", function(){
    sortVal = this.value;
    loadMenu();
  });

  /* ── Clear button ── */
  clearBtn.addEventListener("click", function(){
    searchInput.value = "";
    searchVal         = "";
    activeCat         = 0;
    sortVal           = "name_asc";
    sortSelect.value  = "name_asc";
    document.querySelectorAll(".cat-pill").forEach(p => p.classList.remove("active"));
    document.querySelector(\'.cat-pill[data-cat="0"]\').classList.add("active");
    updateClearBtn();
    loadMenu();
  });

  function updateClearBtn(){
    clearBtn.style.display = (searchVal !== "" || activeCat !== 0) ? "inline-block" : "none";
  }

  /* ── XSS helper ── */
  function escHtml(str) {
    return String(str)
      .replace(/&/g,"&amp;").replace(/</g,"&lt;")
      .replace(/>/g,"&gt;").replace(/"/g,"&quot;");
  }

  /* ── Initial load ── */
  loadMenu();
})();
</script>
';
include 'includes/footer.php';
?>

// Get categories
$cat_query = "SELECT * FROM categories ORDER BY name";
$cat_stmt = $db->query($cat_query);
$categories = $cat_stmt->fetchAll();

// Get filter category
$category_filter = isset($_GET['category']) ? intval($_GET['category']) : 0;
$search = isset($_GET['search']) ? clean_input($_GET['search']) : '';

// Build query
$query = "SELECT p.*, c.name as category_name FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          WHERE p.is_available = 1";

if ($category_filter > 0) {
    $query .= " AND p.category_id = :category_id";
}

if (!empty($search)) {
    $query .= " AND (p.name LIKE :search OR p.description LIKE :search)";
}

$query .= " ORDER BY p.name";

$stmt = $db->prepare($query);

if ($category_filter > 0) {
    $stmt->bindParam(':category_id', $category_filter);
}

if (!empty($search)) {
    $search_param = "%{$search}%";
    $stmt->bindParam(':search', $search_param);
}

$stmt->execute();
$products = $stmt->fetchAll();

include 'includes/header.php';
?>

<!-- Sidebar -->
<aside>
  <div class="logo">🍴 DineClick</div>
  <div class="sidebar-menu">
    <a href="cart.php">My Cart</a>
    <a href="orders.php">My Orders</a>
    <a href="order_history.php">Order History</a>
    <a href="profile.php">Profile Settings</a>
    <a href="logout.php">Logout</a>
  </div>
</aside>

<!-- Main -->
<main>
  <header>
    <h3>Our Menu</h3>
    <nav>
      <a href="index.php">Home</a>
      <a href="menu.php" class="active">Menu</a>
      <a href="orders.php">My Orders</a>
      <a href="profile.php">Profile</a>
    </nav>
  </header>

  <section class="menu-section" style="flex: 1; padding: 30px; overflow-y: auto;">
    
    <!-- Search and Filter -->
    <div style="margin-bottom: 25px; display: flex; gap: 15px; flex-wrap: wrap; align-items: center;">
      <form method="GET" style="display: flex; gap: 10px; flex: 1;">
        <input type="text" name="search" placeholder="Search products..." 
               value="<?php echo htmlspecialchars($search); ?>"
               style="flex: 1; padding: 10px 15px; border: 1px solid #ddd; border-radius: 8px; outline: none;">
        
        <select name="category" style="padding: 10px 15px; border: 1px solid #ddd; border-radius: 8px; outline: none;">
          <option value="0">All Categories</option>
          <?php foreach ($categories as $cat): ?>
            <option value="<?php echo $cat['id']; ?>" <?php echo $category_filter == $cat['id'] ? 'selected' : ''; ?>>
              <?php echo htmlspecialchars($cat['name']); ?>
            </option>
          <?php endforeach; ?>
        </select>
        
        <button type="submit" style="padding: 10px 20px; background: #B76E09; color: #fff; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">
          Search
        </button>
        
        <?php if ($category_filter > 0 || !empty($search)): ?>
          <a href="menu.php" style="padding: 10px 20px; background: #7D6E6E; color: #fff; text-decoration: none; border-radius: 8px; font-weight: 600;">
            Clear
          </a>
        <?php endif; ?>
      </form>
    </div>

    <div class="menu-header" style="font-size: 1.4rem; font-weight: 600; margin-bottom: 20px;">
      <?php 
      if ($category_filter > 0) {
          $cat_name = array_filter($categories, function($c) use ($category_filter) {
              return $c['id'] == $category_filter;
          });
          echo !empty($cat_name) ? htmlspecialchars(reset($cat_name)['name']) : 'Explore Our Menu';
      } else {
          echo 'Explore Our Menu';
      }
      ?>
    </div>
    
    <div class="menu-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px;">
      <?php if (count($products) > 0): ?>
        <?php foreach ($products as $product): ?>
          <div class="menu-item" style="background: #fff; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.08); overflow: hidden; display: flex; flex-direction: column;">
            <?php 
            $image_path = !empty($product['image']) ? UPLOAD_URL . $product['image'] : 'https://via.placeholder.com/300x200?text=No+Image';
            ?>
            <img src="<?php echo htmlspecialchars($image_path); ?>" 
                 alt="<?php echo htmlspecialchars($product['name']); ?>"
                 style="width: 100%; height: 160px; object-fit: cover;">
            
            <div class="menu-info" style="padding: 15px; text-align: center;">
              <h4 style="margin: 8px 0; font-size: 1.1rem; color: #000;">
                <?php echo htmlspecialchars($product['name']); ?>
              </h4>
              <p style="color: #7D6E6E; font-size: 0.9rem; margin-bottom: 10px;">
                <?php echo htmlspecialchars($product['description']); ?>
              </p>
              <div class="price" style="font-weight: 700; color: #B76E09; margin-bottom: 12px;">
                <?php echo format_price($product['price']); ?>
              </div>
              <p style="font-size: 0.85rem; color: #7D6E6E; margin-bottom: 10px;">
                Stock: <?php echo $product['stock']; ?>
              </p>
              <?php if ($product['stock'] > 0): ?>
                <button class="add-to-cart-btn" data-product-id="<?php echo $product['id']; ?>" 
                        data-product-name="<?php echo htmlspecialchars($product['name']); ?>"
                        style="background-color: #B76E09; color: #fff; border: none; padding: 8px 14px; border-radius: 8px; cursor: pointer; transition: background-color 0.3s ease; font-weight: 600;">
                  Add to Cart
                </button>
              <?php else: ?>
                <button disabled style="background-color: #ccc; color: #666; border: none; padding: 8px 14px; border-radius: 8px; cursor: not-allowed; font-weight: 600;">
                  Out of Stock
                </button>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div style="grid-column: 1/-1; text-align: center; padding: 40px; color: #7D6E6E;">
          <p>No products found.</p>
        </div>
      <?php endif; ?>
    </div>
  </section>

<?php 
$additional_js = "
<script>
$(document).ready(function() {
    $('.add-to-cart-btn').click(function() {
        const productId = $(this).data('product-id');
        const productName = $(this).data('product-name');
        const button = $(this);
        
        button.prop('disabled', true).text('Adding...');
        
        $.ajax({
            url: 'ajax/add_to_cart.php',
            type: 'POST',
            data: {
                product_id: productId,
                quantity: 1
            },
            dataType: 'json',
            success: function(response) {
                button.prop('disabled', false).text('Add to Cart');
                
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Added to Cart!',
                        text: productName + ' has been added to your cart.',
                        showConfirmButton: false,
                        timer: 1500
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'Failed to add to cart'
                    });
                }
            },
            error: function() {
                button.prop('disabled', false).text('Add to Cart');
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred. Please try again.'
                });
            }
        });
    });
});
</script>
";

include 'includes/footer.php'; 
?>

<style>
.menu-item {
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.menu-item:hover {
  transform: translateY(-5px);
  box-shadow: 0 6px 14px rgba(0,0,0,0.12);
}
.add-to-cart-btn:hover:not(:disabled) {
  background-color: #a55f06;
}
</style>
