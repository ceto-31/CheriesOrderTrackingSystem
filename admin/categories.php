<?php
require_once '../includes/admin_check.php';

$page_title = "Manage Categories";
$database = new Database();
$db = $database->getConnection();

// Get all categories
$categories = $db->query("SELECT * FROM categories ORDER BY name")->fetchAll();

include '../includes/header.php';
?>

<?php include '../includes/admin_nav.php'; ?>

  <div style="flex: 1; padding: 30px; overflow-y: auto;">
    <?php if (isset($_SESSION['success'])): ?>
      <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
      <h2 style="margin: 0; font-weight: 600;">All Categories</h2>
      <button id="addCategoryBtn" style="padding: 12px 24px; background: #B76E09; color: #fff; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 1rem;">
        + Add Category
      </button>
    </div>
    
    <div style="background: #fff; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.08); padding: 25px;">
      <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px;">
        <?php foreach ($categories as $category): ?>
          <div style="background: #f8f9fa; padding: 20px; border-radius: 10px; border-left: 4px solid #B76E09;">
            <h3 style="margin: 0 0 10px 0; color: #000; font-size: 1.2rem;">
              <?php echo htmlspecialchars($category['name']); ?>
            </h3>
            <p style="margin: 0 0 15px 0; color: #7D6E6E; font-size: 0.9rem;">
              <?php echo htmlspecialchars($category['description'] ?? 'No description'); ?>
            </p>
            <div style="display: flex; gap: 10px;">
              <button class="edit-cat-btn" data-id="<?php echo $category['id']; ?>" 
                      style="flex: 1; padding: 8px; background: #17a2b8; color: #fff; border: none; border-radius: 5px; cursor: pointer; font-weight: 600;">
                Edit
              </button>
              <button class="delete-cat-btn" data-id="<?php echo $category['id']; ?>" 
                      style="flex: 1; padding: 8px; background: #dc3545; color: #fff; border: none; border-radius: 5px; cursor: pointer; font-weight: 600;">
                Delete
              </button>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

<?php 
$categories_json = json_encode($categories);

$additional_js = "
<script>
const categories = $categories_json;

$('#addCategoryBtn').click(function() {
    showCategoryForm();
});

$('.edit-cat-btn').click(function() {
    const catId = $(this).data('id');
    const category = categories.find(c => c.id == catId);
    showCategoryForm(category);
});

$('.delete-cat-btn').click(function() {
    const catId = $(this).data('id');
    
    Swal.fire({
        title: 'Delete Category?',
        text: 'Products in this category will remain but without a category.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#7D6E6E',
        confirmButtonText: 'Yes, delete it'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'category_delete.php',
                type: 'POST',
                data: { category_id: catId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            confirmButtonColor: '#B76E09'
                        }).then(() => location.reload());
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message
                        });
                    }
                }
            });
        }
    });
});

function showCategoryForm(category = null) {
    const isEdit = category !== null;
    
    Swal.fire({
        title: isEdit ? 'Edit Category' : 'Add Category',
        html: `
            <input type=\"hidden\" id=\"cat_id\" value=\"\${isEdit ? category.id : ''}\">
            <div style=\"text-align: left; margin-bottom: 15px;\">
                <label style=\"display: block; margin-bottom: 5px; font-weight: 600;\">Category Name *</label>
                <input type=\"text\" id=\"cat_name\" value=\"\${isEdit ? category.name : ''}\" 
                       style=\"width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;\">
            </div>
            <div style=\"text-align: left;\">
                <label style=\"display: block; margin-bottom: 5px; font-weight: 600;\">Description</label>
                <textarea id=\"cat_desc\" rows=\"3\" 
                          style=\"width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;\">\${isEdit ? (category.description || '') : ''}</textarea>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: isEdit ? 'Update' : 'Add',
        confirmButtonColor: '#B76E09',
        cancelButtonColor: '#7D6E6E',
        preConfirm: () => {
            const name = $('#cat_name').val();
            if (!name) {
                Swal.showValidationMessage('Category name is required');
                return false;
            }
            
            return $.ajax({
                url: 'category_save.php',
                type: 'POST',
                data: {
                    category_id: $('#cat_id').val(),
                    name: name,
                    description: $('#cat_desc').val()
                },
                dataType: 'json'
            });
        }
    }).then((result) => {
        if (result.isConfirmed && result.value.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: result.value.message,
                confirmButtonColor: '#B76E09'
            }).then(() => location.reload());
        }
    });
}
</script>
";

include '../includes/footer.php'; 
?>
