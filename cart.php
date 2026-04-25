<?php
require_once 'includes/session_check.php';

$page_title = "My Cart";
$database = new Database();
$db = $database->getConnection();

$user_id = $_SESSION['user_id'];

// Get cart items
$query = "SELECT c.*, p.name, p.price, p.image, p.stock 
          FROM cart c 
          JOIN products p ON c.product_id = p.id 
          WHERE c.user_id = :user_id 
          ORDER BY c.created_at DESC";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$cart_items = $stmt->fetchAll();

// Calculate totals
$subtotal = 0;
foreach ($cart_items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$delivery_fee = 50.00;
$total = $subtotal + $delivery_fee;

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<!-- Main Content -->
<main>
  <header>
    <h3>My Cart</h3>
    <nav>
      <a href="index.php">Home</a>
      <a href="menu.php">Menu</a>
      <a href="orders.php">My Orders</a>
      <a href="profile.php">Profile</a>
    </nav>
  </header>

  <div class="cart-container" style="padding: 30px; overflow-y: auto;">
    <h2 style="margin-bottom: 25px; font-weight: 600;">Items in Your Cart</h2>
    
    <?php if (count($cart_items) > 0): ?>
      <div class="cart-items" style="display: flex; flex-direction: column; gap: 18px;">
        <?php foreach ($cart_items as $item): ?>
          <div class="cart-item" data-cart-id="<?php echo $item['id']; ?>" style="background: #fff; border-radius: 10px; display: flex; align-items: center; padding: 15px; box-shadow: 0 1px 4px rgba(0,0,0,0.08);">
            <?php 
            $image_path = !empty($item['image']) ? UPLOAD_URL . $item['image'] : 'https://via.placeholder.com/80';
            ?>
            <img src="<?php echo htmlspecialchars($image_path); ?>" 
                 alt="<?php echo htmlspecialchars($item['name']); ?>"
                 style="width: 80px; height: 80px; border-radius: 8px; object-fit: cover; margin-right: 20px;">
            
            <div class="item-info" style="flex: 1;">
              <h4 style="margin: 0; font-size: 1.1rem; color: #000;">
                <?php echo htmlspecialchars($item['name']); ?>
              </h4>
              <p style="margin: 4px 0; color: #7D6E6E; font-size: 0.9rem;">
                <?php echo format_price($item['price']); ?> each
              </p>
              <div class="quantity" style="display: flex; align-items: center; gap: 8px; margin-top: 10px;">
                <button class="qty-btn decrease" data-cart-id="<?php echo $item['id']; ?>" 
                        style="width: 28px; height: 28px; border: none; background-color: #B76E09; color: #fff; border-radius: 5px; cursor: pointer;">-</button>
                <span class="qty-display" style="font-weight: 600; min-width: 30px; text-align: center;">
                  <?php echo $item['quantity']; ?>
                </span>
                <button class="qty-btn increase" data-cart-id="<?php echo $item['id']; ?>" 
                        style="width: 28px; height: 28px; border: none; background-color: #B76E09; color: #fff; border-radius: 5px; cursor: pointer;">+</button>
                <button class="remove-btn" data-cart-id="<?php echo $item['id']; ?>" 
                        style="margin-left: 15px; padding: 6px 12px; background: #dc3545; color: #fff; border: none; border-radius: 5px; cursor: pointer; font-size: 0.85rem;">
                  Remove
                </button>
              </div>
            </div>
            
            <div class="item-price" style="text-align: right; font-weight: 600; color: #000; font-size: 1rem; margin-left: 20px;">
              <?php echo format_price($item['price'] * $item['quantity']); ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Summary -->
      <div class="cart-summary" style="margin-top: 30px; background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 1px 4px rgba(0,0,0,0.08); max-width: 400px; margin-left: auto;">
        <h3 style="margin-bottom: 15px;">Order Summary</h3>
        <div class="summary-row" style="display: flex; justify-content: space-between; margin: 8px 0; color: #7D6E6E;">
          <span>Subtotal</span>
          <span id="subtotal"><?php echo format_price($subtotal); ?></span>
        </div>
        <div class="summary-row" style="display: flex; justify-content: space-between; margin: 8px 0; color: #7D6E6E;">
          <span>Delivery Fee</span>
          <span id="delivery-fee"><?php echo format_price($delivery_fee); ?></span>
        </div>
        <div class="summary-row total" style="display: flex; justify-content: space-between; margin: 8px 0; color: #000; font-weight: 700; font-size: 1.1rem; padding-top: 10px; border-top: 1px solid #ddd;">
          <span>Total</span>
          <span id="total"><?php echo format_price($total); ?></span>
        </div>
        <a href="checkout.php">
          <button class="checkout-btn" style="width: 100%; padding: 12px; background-color: #B76E09; color: #fff; border: none; border-radius: 8px; font-weight: 600; font-size: 1rem; cursor: pointer; margin-top: 15px;">
            Proceed to Checkout
          </button>
        </a>
      </div>
    <?php else: ?>
      <div style="text-align: center; padding: 60px 20px;">
        <p style="font-size: 1.2rem; color: #7D6E6E; margin-bottom: 20px;">Your cart is empty</p>
        <a href="menu.php" style="display: inline-block; padding: 12px 30px; background: #B76E09; color: #fff; text-decoration: none; border-radius: 8px; font-weight: 600;">
          Browse Menu
        </a>
      </div>
    <?php endif; ?>
  </div>

<?php 
$additional_js = "
<script>
$(document).ready(function() {
    // Increase quantity
    $('.qty-btn.increase').click(function() {
        updateCart($(this).data('cart-id'), 'increase');
    });
    
    // Decrease quantity
    $('.qty-btn.decrease').click(function() {
        updateCart($(this).data('cart-id'), 'decrease');
    });
    
    // Remove item
    $('.remove-btn').click(function() {
        const cartId = $(this).data('cart-id');
        
        Swal.fire({
            title: 'Remove Item?',
            text: 'Are you sure you want to remove this item from your cart?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#B76E09',
            cancelButtonColor: '#7D6E6E',
            confirmButtonText: 'Yes, remove it'
        }).then((result) => {
            if (result.isConfirmed) {
                updateCart(cartId, 'remove');
            }
        });
    });
});

function updateCart(cartId, action) {
    $.ajax({
        url: 'ajax/update_cart.php',
        type: 'POST',
        data: {
            cart_id: cartId,
            action: action
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                if (action === 'remove' || (action === 'decrease' && response.message.includes('removed'))) {
                    $('.cart-item[data-cart-id=\"' + cartId + '\"]').fadeOut(300, function() {
                        $(this).remove();
                        updateTotals();
                        
                        // Reload if cart is empty
                        if ($('.cart-item').length === 0) {
                            location.reload();
                        }
                    });
                } else {
                    // Update quantity display
                    const cartItem = $('.cart-item[data-cart-id=\"' + cartId + '\"]');
                    cartItem.find('.qty-display').text(response.new_quantity);
                    updateTotals();
                }
                
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: response.message,
                    showConfirmButton: false,
                    timer: 1000
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.message
                });
            }
        },
        error: function() {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred. Please try again.'
            });
        }
    });
}

function updateTotals() {
    let subtotal = 0;
    
    $('.cart-item').each(function() {
        const priceText = $(this).find('.item-info p').first().text();
        const price = parseFloat(priceText.replace('₱', '').replace(',', ''));
        const quantity = parseInt($(this).find('.qty-display').text());
        const itemTotal = price * quantity;
        
        $(this).find('.item-price').text('₱' + itemTotal.toFixed(2));
        subtotal += itemTotal;
    });
    
    const deliveryFee = 50.00;
    const total = subtotal + deliveryFee;
    
    $('#subtotal').text('₱' + subtotal.toFixed(2));
    $('#total').text('₱' + total.toFixed(2));
}
</script>
";

include 'includes/footer.php'; 
?>

<style>
.cart-item {
  transition: box-shadow 0.3s ease;
}
.cart-item:hover {
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.qty-btn:hover {
  background-color: #945a07;
}
.remove-btn:hover {
  background-color: #c82333;
}
.checkout-btn:hover {
  background-color: #945a07;
}
</style>
