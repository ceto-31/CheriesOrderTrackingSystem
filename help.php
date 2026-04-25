<?php
require_once 'includes/session_check.php';

$page_title = "Help & Support";

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<!-- Main Content -->
<main>
  <header>
    <h3>Help & Support</h3>
    <nav>
      <a href="index.php">Home</a>
      <a href="menu.php">Menu</a>
      <a href="orders.php">My Orders</a>
      <a href="profile.php">Profile</a>
    </nav>
  </header>

  <div style="padding: 30px; overflow-y: auto;">
    <h2 style="margin-bottom: 10px; font-weight: 600;">How can we help you?</h2>
    <p style="color: #7D6E6E; margin-bottom: 30px;">Find answers to common questions or contact our support team.</p>

    <!-- Search Box -->
    <div style="background: #fff; padding: 15px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.08); margin-bottom: 30px;">
      <input type="text" placeholder="🔍 Search for help..." 
             style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 1rem; outline: none;">
    </div>

    <!-- FAQ Categories -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 30px;">
      
      <div style="background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.08); transition: all 0.3s;">
        <div style="font-size: 2.5rem; margin-bottom: 15px;">📦</div>
        <h3 style="margin: 0 0 10px 0; font-size: 1.1rem; color: #000;">Orders & Delivery</h3>
        <p style="color: #7D6E6E; font-size: 0.9rem; margin-bottom: 15px;">
          Track orders, delivery times, and order modifications
        </p>
        <a href="#orders" style="color: #B76E09; text-decoration: none; font-weight: 600; font-size: 0.9rem;">
          View FAQs →
        </a>
      </div>

      <div style="background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.08); transition: all 0.3s;">
        <div style="font-size: 2.5rem; margin-bottom: 15px;">💳</div>
        <h3 style="margin: 0 0 10px 0; font-size: 1.1rem; color: #000;">Payment & Billing</h3>
        <p style="color: #7D6E6E; font-size: 0.9rem; margin-bottom: 15px;">
          Payment methods, refunds, and transaction issues
        </p>
        <a href="#payment" style="color: #B76E09; text-decoration: none; font-weight: 600; font-size: 0.9rem;">
          View FAQs →
        </a>
      </div>

      <div style="background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.08); transition: all 0.3s;">
        <div style="font-size: 2.5rem; margin-bottom: 15px;">👤</div>
        <h3 style="margin: 0 0 10px 0; font-size: 1.1rem; color: #000;">Account & Profile</h3>
        <p style="color: #7D6E6E; font-size: 0.9rem; margin-bottom: 15px;">
          Account management, security, and preferences
        </p>
        <a href="#account" style="color: #B76E09; text-decoration: none; font-weight: 600; font-size: 0.9rem;">
          View FAQs →
        </a>
      </div>

      <div style="background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.08); transition: all 0.3s;">
        <div style="font-size: 2.5rem; margin-bottom: 15px;">🍔</div>
        <h3 style="margin: 0 0 10px 0; font-size: 1.1rem; color: #000;">Menu & Products</h3>
        <p style="color: #7D6E6E; font-size: 0.9rem; margin-bottom: 15px;">
          Product information, allergens, and availability
        </p>
        <a href="#menu" style="color: #B76E09; text-decoration: none; font-weight: 600; font-size: 0.9rem;">
          View FAQs →
        </a>
      </div>
    </div>

    <!-- Popular FAQs -->
    <div id="orders" style="background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.08); margin-bottom: 20px;">
      <h3 style="margin: 0 0 20px 0; font-size: 1.2rem; color: #000;">📦 Orders & Delivery</h3>
      
      <details style="margin-bottom: 15px; padding: 15px; background: #f8f9fa; border-radius: 8px; cursor: pointer;">
        <summary style="font-weight: 600; color: #000; outline: none;">How do I track my order?</summary>
        <p style="margin: 10px 0 0 0; color: #7D6E6E; font-size: 0.9rem;">
          Go to "My Orders" section in your dashboard. Click on any active order to see real-time tracking with delivery status updates.
        </p>
      </details>

      <details style="margin-bottom: 15px; padding: 15px; background: #f8f9fa; border-radius: 8px; cursor: pointer;">
        <summary style="font-weight: 600; color: #000; outline: none;">Can I cancel my order?</summary>
        <p style="margin: 10px 0 0 0; color: #7D6E6E; font-size: 0.9rem;">
          Yes, you can cancel orders with "Pending" status. Go to "My Orders", select the order, and click "Cancel Order" button.
        </p>
      </details>

      <details style="margin-bottom: 15px; padding: 15px; background: #f8f9fa; border-radius: 8px; cursor: pointer;">
        <summary style="font-weight: 600; color: #000; outline: none;">What is the delivery time?</summary>
        <p style="margin: 10px 0 0 0; color: #7D6E6E; font-size: 0.9rem;">
          Standard delivery takes 30-45 minutes depending on your location and order volume. You'll receive updates as your order progresses.
        </p>
      </details>
    </div>

    <div id="payment" style="background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.08); margin-bottom: 20px;">
      <h3 style="margin: 0 0 20px 0; font-size: 1.2rem; color: #000;">💳 Payment & Billing</h3>
      
      <details style="margin-bottom: 15px; padding: 15px; background: #f8f9fa; border-radius: 8px; cursor: pointer;">
        <summary style="font-weight: 600; color: #000; outline: none;">What payment methods do you accept?</summary>
        <p style="margin: 10px 0 0 0; color: #7D6E6E; font-size: 0.9rem;">
          We accept Cash on Delivery, GCash, PayMaya, and Bank Transfer. All online payments are processed securely.
        </p>
      </details>

      <details style="margin-bottom: 15px; padding: 15px; background: #f8f9fa; border-radius: 8px; cursor: pointer;">
        <summary style="font-weight: 600; color: #000; outline: none;">Is my payment information secure?</summary>
        <p style="margin: 10px 0 0 0; color: #7D6E6E; font-size: 0.9rem;">
          Yes, all transactions are encrypted with industry-standard security protocols. We never store your complete payment details.
        </p>
      </details>
    </div>

    <div id="account" style="background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.08); margin-bottom: 20px;">
      <h3 style="margin: 0 0 20px 0; font-size: 1.2rem; color: #000;">👤 Account & Profile</h3>
      
      <details style="margin-bottom: 15px; padding: 15px; background: #f8f9fa; border-radius: 8px; cursor: pointer;">
        <summary style="font-weight: 600; color: #000; outline: none;">How do I update my profile information?</summary>
        <p style="margin: 10px 0 0 0; color: #7D6E6E; font-size: 0.9rem;">
          Go to "Profile Settings" from the sidebar menu. You can update your name, email, phone number, address, and password.
        </p>
      </details>

      <details style="margin-bottom: 15px; padding: 15px; background: #f8f9fa; border-radius: 8px; cursor: pointer;">
        <summary style="font-weight: 600; color: #000; outline: none;">I forgot my password, what should I do?</summary>
        <p style="margin: 10px 0 0 0; color: #7D6E6E; font-size: 0.9rem;">
          Click "Forgot Password" on the login page. You'll receive instructions via email to reset your password securely.
        </p>
      </details>
    </div>

    <div id="menu" style="background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.08); margin-bottom: 20px;">
      <h3 style="margin: 0 0 20px 0; font-size: 1.2rem; color: #000;">🍔 Menu & Products</h3>
      
      <details style="margin-bottom: 15px; padding: 15px; background: #f8f9fa; border-radius: 8px; cursor: pointer;">
        <summary style="font-weight: 600; color: #000; outline: none;">How do I view the menu?</summary>
        <p style="margin: 10px 0 0 0; color: #7D6E6E; font-size: 0.9rem;">
          Click on "Menu" in the navigation or sidebar to browse all available dishes and products.
        </p>
      </details>

      <details style="margin-bottom: 15px; padding: 15px; background: #f8f9fa; border-radius: 8px; cursor: pointer;">
        <summary style="font-weight: 600; color: #000; outline: none;">Do you have allergen information?</summary>
        <p style="margin: 10px 0 0 0; color: #7D6E6E; font-size: 0.9rem;">
          Each product description includes ingredient details. For specific allergen concerns, please contact support.
        </p>
      </details>
    </div>

    <!-- Contact Support -->
    <div style="background: linear-gradient(135deg, #B76E09 0%, #8C5608 100%); padding: 30px; border-radius: 12px; text-align: center; color: #fff; margin-top: 30px;">
      <h3 style="margin: 0 0 10px 0; font-size: 1.3rem;">Still need help?</h3>
      <p style="margin: 0 0 20px 0; opacity: 0.9;">Our support team is here to assist you</p>
      <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
        <a href="support.php" style="display: inline-block; padding: 12px 30px; background: #fff; color: #B76E09; text-decoration: none; border-radius: 8px; font-weight: 600;">
          💬 Contact Support
        </a>
        <a href="mailto:support@dineclick.com" style="display: inline-block; padding: 12px 30px; background: transparent; color: #fff; text-decoration: none; border-radius: 8px; font-weight: 600; border: 2px solid #fff;">
          📧 Email Us
        </a>
      </div>
    </div>
  </div>

<?php include 'includes/footer.php'; ?>

<style>
div[style*="box-shadow: 0 2px 4px"]:not([style*="background: linear-gradient"]):hover {
  transform: translateY(-3px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
}

details[open] {
  background: #fffaf3 !important;
  border-left: 4px solid #B76E09;
}

summary {
  list-style: none;
}

summary::-webkit-details-marker {
  display: none;
}
</style>
