<?php
require_once '../includes/admin_check.php';

$page_title = "Manage Products";
$database = new Database();
$db = $database->getConnection();

// Get all products with category names
$query = "SELECT p.*, c.name as category_name FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          ORDER BY p.id DESC";
$products = $db->query($query)->fetchAll();

// Get categories for form
$categories = $db->query("SELECT * FROM categories ORDER BY name")->fetchAll();

include '../includes/header.php';
?>

<!-- Sidebar -->
<aside>
  <div class="logo">🍴 Cheries Admin</div>
  <div class="sidebar-menu">
    <a href="dashboard.php">Dashboard</a>
    <a href="products.php" class="active">Products</a>
    <a href="orders_manage.php">Manage Orders</a>
    <a href="categories.php">Categories</a>
    <a href="support_tickets.php">Support Tickets</a>
    <a href="reports.php">Sales Reports</a>
    <a href="../logout.php">Logout</a>
  </div>
</aside>

<!-- Main Content -->
<main>
  <header>
    <h3>Manage Products</h3>
    <nav>
      <a href="dashboard.php">Dashboard</a>
      <a href="products.php" class="active">Products</a>
      <a href="orders_manage.php">Orders</a>
      <a href="reports.php">Reports</a>
    </nav>
  </header>

  <div style="flex:1; padding:30px; overflow-y:auto;">
    <?php if (isset($_SESSION['success'])): ?>
      <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
      <div class="alert alert-error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
      <h2 style="margin: 0; font-weight: 600;">All Products</h2>
      <button id="addProductBtn" style="padding: 12px 24px; background: #B76E09; color: #fff; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 1rem;">
        + Add New Product
      </button>
    </div>
    
    <div style="background: #fff; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.08); overflow: hidden;">
      <table style="width:100%; border-collapse:collapse;">
        <thead>
          <tr style="background: #f8f9fa;">
            <th style="padding:12px 15px; text-align:left; font-weight:600;">Image</th>
            <th style="padding:12px 15px; text-align:left; font-weight:600;">Name</th>
            <th style="padding:12px 15px; text-align:left; font-weight:600;">Category</th>
            <th style="padding:12px 15px; text-align:left; font-weight:600;">Price</th>
            <th style="padding:12px 15px; text-align:left; font-weight:600;">Stock</th>
            <th style="padding:12px 15px; text-align:left; font-weight:600;">Status</th>
            <th style="padding:12px 15px; text-align:left; font-weight:600;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($products as $product): ?>
            <tr style="border-bottom: 1px solid #f0f0f0;">
              <td style="padding: 15px;">
                <?php 
                if (!empty($product['image']) && file_exists(UPLOAD_PATH . $product['image'])) {
                    $image_path = UPLOAD_URL . $product['image'];
                } else {
                    // Base64 placeholder image (no internet needed)
                    $image_path = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIGZpbGw9IiNFNUU1RTUiLz48cGF0aCBkPSJNMjAgMjVIMjVWMzBIMjBWMjVaTTM1IDI1SDQwVjMwSDM1VjI1Wk0yMCAzNUg0MFY0MEgyMFYzNVoiIGZpbGw9IiM5OTk5OTkiLz48L3N2Zz4=';
                }
                ?>
                <img src="<?php echo htmlspecialchars($image_path); ?>" 
                     alt="<?php echo htmlspecialchars($product['name']); ?>"
                     onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIGZpbGw9IiNFNUU1RTUiLz48cGF0aCBkPSJNMjAgMjVIMjVWMzBIMjBWMjVaTTM1IDI1SDQwVjMwSDM1VjI1Wk0yMCAzNUg0MFY0MEgyMFYzNVoiIGZpbGw9IiM5OTk5OTkiLz48L3N2Zz4='"
                     style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;">
              </td>
              <td style="padding:12px 15px; font-weight:600;">
                <?php echo htmlspecialchars($product['name']); ?>
              </td>
              <td style="padding:12px 15px; color:#7D6E6E;">
                <?php echo htmlspecialchars($product['category_name'] ?? 'N/A'); ?>
              </td>
              <td style="padding:12px 15px; font-weight:600; color:#B76E09;">
                <?php echo format_price($product['price']); ?>
              </td>
              <td style="padding:12px 15px;">
                <span style="<?php echo $product['stock'] <= 10 ? 'color: #dc3545; font-weight: 600;' : ''; ?>">
                  <?php echo $product['stock']; ?>
                </span>
              </td>
              <td style="padding:12px 15px;">
                <?php if ($product['is_available']): ?>
                  <span style="padding: 6px 12px; background: #28a745; color: #fff; border-radius: 15px; font-size: 0.85rem; font-weight: 600;">
                    Available
                  </span>
                <?php else: ?>
                  <span style="padding: 6px 12px; background: #dc3545; color: #fff; border-radius: 15px; font-size: 0.85rem; font-weight: 600;">
                    Unavailable
                  </span>
                <?php endif; ?>
              </td>
              <td style="padding:12px 15px;">
                <button class="edit-btn" data-id="<?php echo $product['id']; ?>" 
                        style="padding: 6px 12px; background: #17a2b8; color: #fff; border: none; border-radius: 5px; cursor: pointer; margin-right: 5px; font-size: 0.85rem; font-weight: 600;">
                  Edit
                </button>
                <button class="delete-btn" data-id="<?php echo $product['id']; ?>" 
                        style="padding: 6px 12px; background: #dc3545; color: #fff; border: none; border-radius: 5px; cursor: pointer; font-size: 0.85rem; font-weight: 600;">
                  Delete
                </button>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

<?php
$categories_json = json_encode($categories, JSON_HEX_TAG | JSON_HEX_AMP);
$products_json   = json_encode($products, JSON_HEX_TAG | JSON_HEX_AMP);
?>
<script>
const categories = <?php echo $categories_json; ?>;
const products   = <?php echo $products_json; ?>;

// Add Product
$('#addProductBtn').click(function() {
    showProductForm();
});

// Edit Product
$('.edit-btn').click(function() {
    const productId = $(this).data('id');
    const product = products.find(p => p.id == productId);
    showProductForm(product);
});

// Delete Product
$('.delete-btn').click(function() {
    const productId = $(this).data('id');
    
    Swal.fire({
        title: 'Delete Product?',
        text: 'This action cannot be undone!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#7D6E6E',
        confirmButtonText: 'Yes, delete it'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'product_delete.php',
                type: 'POST',
                data: { product_id: productId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: 'Product has been deleted.',
                            confirmButtonColor: '#B76E09'
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Delete Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to delete product: ' + error
                    });
                }
            });
        }
    });
});

function showProductForm(product = null) {
    const isEdit = product !== null;
    const title = isEdit ? 'Edit Product' : 'Add New Product';
    
    // Build category options
    let categoryOptions = '<option value="">Select Category</option>';
    categories.forEach(cat => {
        const selected = (isEdit && product.category_id == cat.id) ? 'selected' : '';
        categoryOptions += '<option value="' + cat.id + '" ' + selected + '>' + htmlEscape(cat.name) + '</option>';
    });
    
    const productName = isEdit ? htmlEscape(product.name) : '';
    const productDesc = isEdit ? htmlEscape(product.description || '') : '';
    const productPrice = isEdit ? product.price : '';
    const productStock = isEdit ? product.stock : '';
    const productId = isEdit ? product.id : '';
    const hasImage = isEdit && product.image;
    const isAvailable = isEdit ? product.is_available == 1 : true;
    
    Swal.fire({
        title: title,
        html: `
            <form id="productForm" enctype="multipart/form-data" style="text-align: left;">
                <input type="hidden" name="product_id" value="${productId}">
                
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: 600;">Product Name *</label>
                    <input type="text" name="name" required value="${productName}"
                           style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                </div>
                
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: 600;">Description</label>
                    <textarea name="description" rows="3" 
                              style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">${productDesc}</textarea>
                </div>
                
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: 600;">Category *</label>
                    <select name="category_id" required 
                            style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                        ${categoryOptions}
                    </select>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div>
                        <label style="display: block; margin-bottom: 5px; font-weight: 600;">Price (₱) *</label>
                        <input type="number" name="price" step="0.01" required value="${productPrice}"
                               style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 5px; font-weight: 600;">Stock *</label>
                        <input type="number" name="stock" required value="${productStock}"
                               style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                    </div>
                </div>
                
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: 600;">Product Image</label>
                    <input type="file" name="image" accept="image/*" 
                           style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                    ${hasImage ? '<small style="color: #7D6E6E;">Leave empty to keep current image</small>' : ''}
                </div>
                
                <div style="margin-bottom: 15px;">
                    <label style="display: flex; align-items: center; cursor: pointer;">
                        <input type="checkbox" name="is_available" value="1" 
                               ${isAvailable ? 'checked' : ''}
                               style="margin-right: 8px;">
                        <span style="font-weight: 600;">Available for sale</span>
                    </label>
                </div>
            </form>
        `,
        width: '600px',
        showCancelButton: true,
        confirmButtonText: isEdit ? 'Update Product' : 'Add Product',
        confirmButtonColor: '#B76E09',
        cancelButtonColor: '#7D6E6E',
        preConfirm: () => {
            const formData = new FormData($('#productForm')[0]);
            
            return $.ajax({
                url: 'product_save.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                    console.error('Response:', xhr.responseText);
                    Swal.showValidationMessage('Request failed: ' + error);
                }
            });
        },
        allowOutsideClick: () => !Swal.isLoading()
    }).then((result) => {
        if (result.isConfirmed && result.value) {
            if (result.value.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: result.value.message,
                    confirmButtonColor: '#B76E09'
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: result.value.message || 'Unknown error occurred'
                });
            }
        }
    });
}

function htmlEscape(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/\'/g, '&#039;');
}
</script>

<?php include '../includes/footer.php'; ?>
