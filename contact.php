<?php
require_once 'includes/session_check.php';

$page_title = "Contact Us";

// Check if user has an active order with rider info
$database = new Database();
$db = $database->getConnection();
$user_id = $_SESSION['user_id'];

$active_order_query = "SELECT id, status FROM orders WHERE user_id = :user_id 
                       AND status IN ('Confirmed', 'Preparing', 'Out for Delivery') 
                       ORDER BY created_at DESC LIMIT 1";
$stmt = $db->prepare($active_order_query);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$active_order = $stmt->fetch();

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<!-- Main -->
<main>
  <header>
    <h3>Contact Us</h3>
    <nav>
      <a href="index.php">Home</a>
      <a href="menu.php">Menu</a>
      <a href="orders.php">My Orders</a>
      <a href="contact.php" class="active">Contact</a>
      <a href="profile.php">Profile</a>
    </nav>
  </header>

  <div class="dashboard" style="flex: 1; padding: 30px; overflow-y: auto;">
    <div class="section-title" style="font-size: 1.3rem; font-weight: 500; margin-bottom: 25px;">Get in Touch</div>

    <!-- Contact Restaurant -->
    <div class="contact-section" style="background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.08); margin-bottom: 25px;">
      <h4 style="margin: 0 0 15px; font-size: 1.1rem; color: #000;">📞 Contact the Restaurant</h4>
      <p style="color: #7D6E6E; margin-bottom: 15px;">Reach us through our social media channels or customer service hotline.</p>
      
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-top: 20px;">
        <div style="display: flex; align-items: center; gap: 10px;">
          <span style="font-size: 1.5rem;">📧</span>
          <div>
            <div style="font-weight: 600; color: #000;">Email</div>
            <div style="color: #7D6E6E; font-size: 0.9rem;">support@dineclick.com</div>
          </div>
        </div>
        
        <div style="display: flex; align-items: center; gap: 10px;">
          <span style="font-size: 1.5rem;">📱</span>
          <div>
            <div style="font-weight: 600; color: #000;">Hotline</div>
            <div style="color: #7D6E6E; font-size: 0.9rem;">+63 912 345 6789</div>
          </div>
        </div>
        
        <div style="display: flex; align-items: center; gap: 10px;">
          <span style="font-size: 1.5rem;">⏰</span>
          <div>
            <div style="font-weight: 600; color: #000;">Hours</div>
            <div style="color: #7D6E6E; font-size: 0.9rem;">9:00 AM - 10:00 PM</div>
          </div>
        </div>
      </div>
      
      <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #f0f0f0;">
        <p style="margin: 0 0 10px 0; font-weight: 600; color: #000;">Follow us on social media:</p>
        <div style="display: flex; gap: 15px;">
          <a href="#" style="display: inline-flex; align-items: center; gap: 8px; padding: 8px 16px; background: #1877f2; color: #fff; text-decoration: none; border-radius: 8px; font-weight: 600; transition: all 0.3s;">
            <span>📘</span> Facebook
          </a>
          <a href="#" style="display: inline-flex; align-items: center; gap: 8px; padding: 8px 16px; background: linear-gradient(45deg, #f09433, #e6683c, #dc2743, #cc2366, #bc1888); color: #fff; text-decoration: none; border-radius: 8px; font-weight: 600; transition: all 0.3s;">
            <span>📷</span> Instagram
          </a>
        </div>
      </div>
    </div>

    <!-- Contact Rider (only if active order exists) -->
    <?php if ($active_order): ?>
      <div class="contact-section" style="background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.08); margin-bottom: 25px;">
        <h4 style="margin: 0 0 15px; font-size: 1.1rem; color: #000;">🛵 Contact Your Rider</h4>
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 20px;">
          <div style="display: flex; align-items: center; gap: 15px;">
            <img src="https://cdn-icons-png.flaticon.com/512/219/219970.png" alt="Rider" 
                 style="width: 60px; height: 60px; border-radius: 50%; object-fit: cover; border: 2px solid #B76E09;">
            <div>
              <div style="font-weight: 600; font-size: 1rem; color: #000;">Delivery Rider</div>
              <div style="color: #7D6E6E; font-size: 0.9rem;">
                <?php 
                if ($active_order['status'] == 'Out for Delivery') {
                    echo 'Currently delivering your order';
                } elseif ($active_order['status'] == 'Preparing') {
                    echo 'Preparing your order';
                } else {
                    echo 'Order confirmed - awaiting pickup';
                }
                ?>
              </div>
            </div>
          </div>
          <div style="display: flex; gap: 12px;">
            <a href="orders.php" style="display: inline-block; padding: 10px 18px; background: #B76E09; color: #fff; text-decoration: none; border-radius: 8px; font-weight: 600;">
              📦 View Order
            </a>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <!-- FAQs Section -->
    <div class="contact-section" style="background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.08);">
      <h4 style="margin: 0 0 15px; font-size: 1.1rem; color: #000;">❓ Frequently Asked Questions</h4>
      
      <div style="display: flex; flex-direction: column; gap: 15px;">
        <div style="padding: 15px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #B76E09;">
          <div style="font-weight: 600; color: #000; margin-bottom: 5px;">How do I track my order?</div>
          <div style="color: #7D6E6E; font-size: 0.9rem;">Go to "My Orders" section to see real-time updates on your order status.</div>
        </div>
        
        <div style="padding: 15px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #B76E09;">
          <div style="font-weight: 600; color: #000; margin-bottom: 5px;">What payment methods do you accept?</div>
          <div style="color: #7D6E6E; font-size: 0.9rem;">We accept Cash on Delivery, GCash, PayMaya, and Bank Transfer.</div>
        </div>
        
        <div style="padding: 15px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #B76E09;">
          <div style="font-weight: 600; color: #000; margin-bottom: 5px;">How long does delivery take?</div>
          <div style="color: #7D6E6E; font-size: 0.9rem;">Standard delivery time is 30-45 minutes depending on your location.</div>
        </div>
        
        <div style="padding: 15px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #B76E09;">
          <div style="font-weight: 600; color: #000; margin-bottom: 5px;">Can I cancel my order?</div>
          <div style="color: #7D6E6E; font-size: 0.9rem;">Yes, you can cancel pending orders from the "My Orders" page before they are confirmed.</div>
        </div>
      </div>
    </div>
  </div>

<?php include 'includes/footer.php'; ?>

<style>
a[style*="background: #1877f2"]:hover {
  opacity: 0.9;
  transform: translateY(-2px);
}
</style>
