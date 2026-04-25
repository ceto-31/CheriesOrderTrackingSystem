<?php
require_once 'config/config.php';

// Redirect if already logged in
if (is_logged_in()) {
    redirect(SITE_URL . 'index.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = clean_input($_POST['first_name']);
    $last_name = clean_input($_POST['last_name']);
    $email = clean_input($_POST['email']);
    $phone = clean_input($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    if (empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
        $error = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)) {
        $error = "Password must contain at least one uppercase letter, one lowercase letter, and one number.";
    } else {
        $database = new Database();
        $db = $database->getConnection();
        
        // Check if email already exists
        $check_query = "SELECT id FROM users WHERE email = :email LIMIT 1";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->bindParam(':email', $email);
        $check_stmt->execute();
        
        if ($check_stmt->rowCount() > 0) {
            $error = "Email already registered. Please use a different email.";
        } else {
            // Hash password securely
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new user
            $query = "INSERT INTO users (first_name, last_name, email, phone, password) 
                      VALUES (:first_name, :last_name, :email, :phone, :password)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':first_name', $first_name);
            $stmt->bindParam(':last_name', $last_name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':password', $hashed_password);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = "Registration successful! Please log in.";
                redirect(SITE_URL . 'login.php');
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Create Account - DineClick</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #F2F2F2;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      margin: 0;
      padding: 20px 0;
    }

    .register-container {
      width: 90%;
      max-width: 450px;
      background: #fff;
      padding: 40px 30px;
      border-radius: 15px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }

    h2 {
      font-size: 24px;
      font-weight: 700;
      color: #B76E09;
      margin-bottom: 10px;
      text-align: center;
    }

    p {
      text-align: center;
      color: #7D6E6E;
      font-size: 14px;
      margin-bottom: 25px;
    }

    .form-group {
      margin-bottom: 15px;
    }

    label {
      display: block;
      margin-bottom: 5px;
      color: #7D6E6E;
      font-size: 14px;
      font-weight: 500;
    }

    input {
      width: 100%;
      padding: 12px;
      border: none;
      border-radius: 25px;
      background-color: #D9D9D9;
      font-size: 14px;
      outline: none;
      color: #000;
    }

    input::placeholder {
      color: #C4AFAF;
    }

    .register-btn {
      width: 100%;
      padding: 12px;
      background-color: #B76E09;
      color: #fff;
      border: none;
      border-radius: 8px;
      font-weight: 600;
      font-size: 15px;
      cursor: pointer;
      margin-top: 10px;
      transition: 0.3s;
    }

    .register-btn:hover {
      background-color: #8C5608;
    }

    .login-link {
      text-align: center;
      margin-top: 20px;
      color: #7D6E6E;
      font-size: 14px;
    }

    .login-link a {
      color: #B76E09;
      text-decoration: none;
      font-weight: 600;
    }

    .login-link a:hover {
      text-decoration: underline;
    }

    .alert {
      padding: 12px;
      border-radius: 8px;
      margin-bottom: 15px;
      font-size: 14px;
    }

    .alert-error {
      background-color: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
    }

    .password-requirements {
      font-size: 12px;
      color: #7D6E6E;
      margin-top: 5px;
      line-height: 1.6;
    }
  </style>
</head>
<body>
  <div class="register-container">
    <h2>Create Your Account</h2>
    <p>Join DineClick and start ordering!</p>
    
    <?php if (!empty($error)): ?>
      <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <form method="POST" action="">
      <div class="form-group">
        <label>First Name *</label>
        <input type="text" name="first_name" placeholder="Enter your first name" required 
               value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>">
      </div>
      
      <div class="form-group">
        <label>Last Name *</label>
        <input type="text" name="last_name" placeholder="Enter your last name" required
               value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>">
      </div>
      
      <div class="form-group">
        <label>Email Address *</label>
        <input type="email" name="email" placeholder="Enter your email" required
               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
      </div>
      
      <div class="form-group">
        <label>Phone Number</label>
        <input type="tel" name="phone" placeholder="09XX XXX XXXX"
               value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
      </div>
      
      <div class="form-group">
        <label>Password *</label>
        <input type="password" name="password" placeholder="Create a strong password" required>
        <div class="password-requirements">
          • At least 8 characters<br>
          • One uppercase & lowercase letter<br>
          • At least one number
        </div>
      </div>
      
      <div class="form-group">
        <label>Confirm Password *</label>
        <input type="password" name="confirm_password" placeholder="Re-enter your password" required>
      </div>
      
      <button type="submit" class="register-btn">Create Account</button>
    </form>
    
    <div class="login-link">
      Already have an account? <a href="login.php">Log in here</a>
    </div>
  </div>
</body>
</html>
