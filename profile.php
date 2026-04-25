<?php
require_once 'includes/session_check.php';

$page_title = "My Profile";
$database = new Database();
$db = $database->getConnection();

$user_id = $_SESSION['user_id'];

// Get user details
$query = "SELECT * FROM users WHERE id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $user_id);
$stmt->execute();
$user = $stmt->fetch();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = clean_input($_POST['first_name']);
    $last_name = clean_input($_POST['last_name']);
    $email = clean_input($_POST['email']);
    $phone = clean_input($_POST['phone']);
    $address = clean_input($_POST['address']);
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($first_name) || empty($last_name) || empty($email)) {
        $error = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        // Check if email is already taken by another user
        $check_query = "SELECT id FROM users WHERE email = :email AND id != :id LIMIT 1";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->bindParam(':email', $email);
        $check_stmt->bindParam(':id', $user_id);
        $check_stmt->execute();
        
        if ($check_stmt->rowCount() > 0) {
            $error = "Email already in use by another account.";
        } else {
            // Update profile
            $update_query = "UPDATE users SET first_name = :first_name, last_name = :last_name, 
                            email = :email, phone = :phone, address = :address WHERE id = :id";
            $update_stmt = $db->prepare($update_query);
            $update_stmt->bindParam(':first_name', $first_name);
            $update_stmt->bindParam(':last_name', $last_name);
            $update_stmt->bindParam(':email', $email);
            $update_stmt->bindParam(':phone', $phone);
            $update_stmt->bindParam(':address', $address);
            $update_stmt->bindParam(':id', $user_id);
            
            if ($update_stmt->execute()) {
                // Update session name
                $_SESSION['user_name'] = $first_name . ' ' . $last_name;
                $_SESSION['user_email'] = $email;
                
                // Check if password change requested
                if (!empty($current_password) && !empty($new_password)) {
                    if (password_verify($current_password, $user['password'])) {
                        if (strlen($new_password) < 8) {
                            $error = "New password must be at least 8 characters long.";
                        } elseif ($new_password !== $confirm_password) {
                            $error = "New passwords do not match.";
                        } else {
                            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                            $pwd_query = "UPDATE users SET password = :password WHERE id = :id";
                            $pwd_stmt = $db->prepare($pwd_query);
                            $pwd_stmt->bindParam(':password', $hashed_password);
                            $pwd_stmt->bindParam(':id', $user_id);
                            $pwd_stmt->execute();
                            
                            $success = "Profile and password updated successfully!";
                        }
                    } else {
                        $error = "Current password is incorrect.";
                    }
                } else {
                    $success = "Profile updated successfully!";
                }
                
                // Refresh user data
                $stmt->execute();
                $user = $stmt->fetch();
            } else {
                $error = "Failed to update profile.";
            }
        }
    }
}

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<!-- Main Content -->
<main>
  <header>
    <h3>My Profile</h3>
    <nav>
      <a href="index.php">Home</a>
      <a href="menu.php">Menu</a>
      <a href="orders.php">My Orders</a>
      <a href="profile.php" class="active">Profile</a>
    </nav>
  </header>

  <div style="padding: 30px; overflow-y: auto;">
    <?php if (!empty($error)): ?>
      <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if (!empty($success)): ?>
      <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <div style="max-width: 800px; margin: 0 auto;">
      <div style="background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.08);">
        <h2 style="margin-bottom: 25px; font-weight: 600;">Profile Settings</h2>
        
        <form method="POST" action="">
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <div>
              <label style="display: block; margin-bottom: 8px; font-weight: 600;">First Name *</label>
              <input type="text" name="first_name" required 
                     value="<?php echo htmlspecialchars($user['first_name']); ?>"
                     style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px;">
            </div>
            <div>
              <label style="display: block; margin-bottom: 8px; font-weight: 600;">Last Name *</label>
              <input type="text" name="last_name" required 
                     value="<?php echo htmlspecialchars($user['last_name']); ?>"
                     style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px;">
            </div>
          </div>
          
          <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600;">Email Address *</label>
            <input type="email" name="email" required 
                   value="<?php echo htmlspecialchars($user['email']); ?>"
                   style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px;">
          </div>
          
          <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600;">Phone Number</label>
            <input type="tel" name="phone" 
                   value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                   style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px;">
          </div>
          
          <div style="margin-bottom: 30px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600;">Address</label>
            <textarea name="address" rows="3" 
                      style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-family: 'Poppins', sans-serif; resize: vertical;"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
          </div>
          
          <hr style="margin: 30px 0; border: none; border-top: 1px solid #ddd;">
          
          <h3 style="margin-bottom: 20px; font-weight: 600; color: #B76E09;">Change Password</h3>
          
          <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600;">Current Password</label>
            <input type="password" name="current_password" 
                   style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px;">
          </div>
          
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <div>
              <label style="display: block; margin-bottom: 8px; font-weight: 600;">New Password</label>
              <input type="password" name="new_password" 
                     style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px;">
            </div>
            <div>
              <label style="display: block; margin-bottom: 8px; font-weight: 600;">Confirm New Password</label>
              <input type="password" name="confirm_password" 
                     style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px;">
            </div>
          </div>
          
          <button type="submit" 
                  style="width: 100%; padding: 14px; background: #B76E09; color: #fff; border: none; border-radius: 8px; font-weight: 600; font-size: 1.1rem; cursor: pointer; margin-top: 20px;">
            Update Profile
          </button>
        </form>
      </div>
    </div>
  </div>

<?php include 'includes/footer.php'; ?>

<style>
@media (max-width: 768px) {
  div[style*="grid-template-columns: 1fr 1fr"] {
    grid-template-columns: 1fr !important;
  }
}
</style>
