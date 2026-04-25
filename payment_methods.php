<?php
require_once 'includes/session_check.php';

$page_title = "Payment Methods";

include 'includes/header.php';
?>

<!-- Sidebar -->
<aside>
  <div class="logo">🍴 DineClick</div>
  <div class="sidebar-menu">
    <a href="cart.php">My Cart</a>
    <a href="orders.php">My Orders</a>
    <a href="order_history.php">Order History</a>
    <a href="notifications.php">Notifications</a>
    <a href="profile.php">Profile Settings</a>
    <a href="logout.php">Logout</a>
  </div>
</aside>

<!-- Main Content -->
<main>
  <header>
    <h3>Payment Methods</h3>
    <nav>
      <a href="index.php">Home</a>
      <a href="menu.php">Menu</a>
      <a href="orders.php">My Orders</a>
      <a href="profile.php">Profile</a>
    </nav>
  </header>

  <div style="padding: 30px; overflow-y: auto;">
    <h2 style="margin-bottom: 25px; font-weight: 600;">Available Payment Methods</h2>
    
    <p style="color: #7D6E6E; margin-bottom: 30px; font-size: 1rem;">
      Choose your preferred payment method during checkout. All transactions are secure and encrypted.
    </p>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 30px;">
      
      <!-- GCash -->
      <div style="background: #fff; border-radius: 12px; box-shadow: 0 2px 6px rgba(0,0,0,0.08); padding: 25px; text-align: center; transition: all 0.3s ease; border: 2px solid transparent;">
        <img src="assets/images/gcash-logo.png" alt="GCash Logo" style="width: 150px; height: 110px; margin-bottom: 15px; object-fit: contain;">
        <h4 style="margin: 10px 0; font-weight: 600; font-size: 1.1rem; color: #000;">GCash E-Wallet</h4>
        <p style="color: #7D6E6E; font-size: 0.9rem; margin-bottom: 15px;">
          Fast and secure mobile wallet payment. Instant confirmation.
        </p>
        <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; text-align: left; margin-top: 15px;">
          <div style="font-size: 0.85rem; color: #7D6E6E; margin-bottom: 8px;">
            <strong style="color: #000;">✓</strong> Instant payment
          </div>
          <div style="font-size: 0.85rem; color: #7D6E6E; margin-bottom: 8px;">
            <strong style="color: #000;">✓</strong> No transaction fee
          </div>
          <div style="font-size: 0.85rem; color: #7D6E6E;">
            <strong style="color: #000;">✓</strong> Secure encryption
          </div>
        </div>
      </div>

      <!-- Cash on Delivery -->
      <div style="background: #fff; border-radius: 12px; box-shadow: 0 2px 6px rgba(0,0,0,0.08); padding: 25px; text-align: center; transition: all 0.3s ease; border: 2px solid transparent;">
        <img src="https://cdn-icons-png.flaticon.com/512/2331/2331970.png" alt="Cash Icon" style="width: 100px; height: 105px; margin-bottom: 15px; object-fit: contain;">
        <h4 style="margin: 10px 0; font-weight: 600; font-size: 1.1rem; color: #000;">Cash on Delivery</h4>
        <p style="color: #7D6E6E; font-size: 0.9rem; margin-bottom: 15px;">
          Pay with cash when your order arrives. Simple and convenient.
        </p>
        <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; text-align: left; margin-top: 15px;">
          <div style="font-size: 0.85rem; color: #7D6E6E; margin-bottom: 8px;">
            <strong style="color: #000;">✓</strong> Pay upon delivery
          </div>
          <div style="font-size: 0.85rem; color: #7D6E6E; margin-bottom: 8px;">
            <strong style="color: #000;">✓</strong> No online payment needed
          </div>
          <div style="font-size: 0.85rem; color: #7D6E6E;">
            <strong style="color: #000;">✓</strong> Preferred by many
          </div>
        </div>
      </div>

      <!-- PayMaya -->
      <div style="background: #fff; border-radius: 12px; box-shadow: 0 2px 6px rgba(0,0,0,0.08); padding: 25px; text-align: center; transition: all 0.3s ease; border: 2px solid transparent;">
        <div style="width: 150px; height: 110px; margin: 0 auto 15px; display: flex; align-items: center; justify-content: center; font-size: 3rem; color: #00AB4E;">
          💳
        </div>
        <h4 style="margin: 10px 0; font-weight: 600; font-size: 1.1rem; color: #000;">PayMaya</h4>
        <p style="color: #7D6E6E; font-size: 0.9rem; margin-bottom: 15px;">
          Digital payment with your PayMaya account or card.
        </p>
        <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; text-align: left; margin-top: 15px;">
          <div style="font-size: 0.85rem; color: #7D6E6E; margin-bottom: 8px;">
            <strong style="color: #000;">✓</strong> Card or wallet
          </div>
          <div style="font-size: 0.85rem; color: #7D6E6E; margin-bottom: 8px;">
            <strong style="color: #000;">✓</strong> Instant confirmation
          </div>
          <div style="font-size: 0.85rem; color: #7D6E6E;">
            <strong style="color: #000;">✓</strong> Safe & secure
          </div>
        </div>
      </div>

      <!-- Bank Transfer -->
      <div style="background: #fff; border-radius: 12px; box-shadow: 0 2px 6px rgba(0,0,0,0.08); padding: 25px; text-align: center; transition: all 0.3s ease; border: 2px solid transparent;">
        <div style="width: 150px; height: 110px; margin: 0 auto 15px; display: flex; align-items: center; justify-content: center; font-size: 3rem; color: #0066CC;">
          🏦
        </div>
        <h4 style="margin: 10px 0; font-weight: 600; font-size: 1.1rem; color: #000;">Bank Transfer</h4>
        <p style="color: #7D6E6E; font-size: 0.9rem; margin-bottom: 15px;">
          Direct bank transfer to our account. Requires verification.
        </p>
        <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; text-align: left; margin-top: 15px;">
          <div style="font-size: 0.85rem; color: #7D6E6E; margin-bottom: 8px;">
            <strong style="color: #000;">✓</strong> Any bank account
          </div>
          <div style="font-size: 0.85rem; color: #7D6E6E; margin-bottom: 8px;">
            <strong style="color: #000;">✓</strong> Large transactions
          </div>
          <div style="font-size: 0.85rem; color: #7D6E6E;">
            <strong style="color: #000;">✓</strong> 24-48 hours processing
          </div>
        </div>
      </div>
    </div>

    <!-- Information Box -->
    <div style="background: #fff3cd; padding: 20px; border-radius: 12px; border-left: 4px solid #ffc107; margin-bottom: 25px;">
      <h4 style="margin: 0 0 10px 0; color: #856404; font-size: 1.1rem;">💡 Payment Information</h4>
      <ul style="margin: 0; padding-left: 20px; color: #856404;">
        <li style="margin-bottom: 8px;">All payment methods are available during checkout</li>
        <li style="margin-bottom: 8px;">Online payments are processed securely through encrypted channels</li>
        <li style="margin-bottom: 8px;">Cash on Delivery is subject to availability in your area</li>
        <li>For payment issues, contact our support team immediately</li>
      </ul>
    </div>

    <!-- CTA Button -->
    <div style="text-align: center; padding: 30px 0;">
      <a href="menu.php" style="display: inline-block; padding: 14px 40px; background: #B76E09; color: #fff; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 1.1rem; transition: background 0.3s;">
        Start Ordering
      </a>
    </div>
  </div>

<?php include 'includes/footer.php'; ?>

<style>
div[style*="box-shadow: 0 2px 6px"]:hover {
  transform: translateY(-5px);
  box-shadow: 0 6px 14px rgba(0,0,0,0.12) !important;
  border-color: #B76E09 !important;
}
</style>
