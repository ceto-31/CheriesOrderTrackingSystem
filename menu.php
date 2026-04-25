<?php
require_once 'includes/session_check.php';

$page_title = "Menu";
$database = new Database();
$db = $database->getConnection();

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
