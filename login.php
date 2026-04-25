<?php
require_once 'config/config.php';

// Redirect if already logged in
if (is_logged_in()) {
    if (is_admin()) {
        redirect(SITE_URL . 'admin/dashboard.php');
    } else {
        redirect(SITE_URL . 'index.php');
    }
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = clean_input($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "SELECT id, first_name, last_name, email, password, is_admin FROM users WHERE email = :email LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch();
            
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['is_admin'] = $user['is_admin'];
                
                // Regenerate session ID for security
                session_regenerate_id(true);
                
                // Redirect based on role
                if ($user['is_admin'] == 1) {
                    redirect(SITE_URL . 'admin/dashboard.php');
                } else {
                    redirect(SITE_URL . 'index.php');
                }
            } else {
                $error = "Invalid email or password.";
            }
        } else {
            $error = "Invalid email or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>DineClick Login</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #F2F2F2;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      margin: 0;
    }

    .login-container {
      text-align: center;
      width: 90%;
      max-width: 380px;
      background: #fff;
      padding: 40px 30px;
      border-radius: 15px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }

    h2 {
      font-size: 22px;
      font-weight: 700;
      color: #000000;
      margin-bottom: 10px;
    }

    h3 {
      color: #B76E09;
      text-align: center;
      font-size: 16px;
      font-weight: 700;
      margin-top: 15px;
      margin-bottom: 20px;
    }

    .form-group {
      margin-bottom: 15px;
      text-align: left;
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

    .login-btn {
      display: inline-block;
      background-color: #B76E09;
      color: #fff;
      border: none;
      padding: 12px 30px;
      border-radius: 8px;
      text-decoration: none;
      font-weight: 600;
      transition: 0.3s;
      cursor: pointer;
      width: 100%;
      margin-top: 10px;
      font-size: 15px;
    }

    .login-btn:hover {
      background-color: #8C5608;
    }

    .forgot {
      display: block;
      margin-top: 15px;
      color: #7D6E6E;
      font-size: 13px;
      text-decoration: none;
    }

    .forgot:hover {
      color: #B76E09;
    }

    .question {
      margin-top: 20px;
      color: #7D6E6E;
      font-size: 14px;
    }

    .create {
      color: #B76E09;
      font-size: 14px;
      text-decoration: none;
      font-weight: 600;
    }

    .create:hover {
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

    .alert-success {
      background-color: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
    }
  </style>
</head>
<body>
  <div class="login-container">
    <h2>Welcome to DineClick</h2>
    <h3>LOGIN</h3>
    
    <?php if (!empty($error)): ?>
      <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['success'])): ?>
      <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    
    <form method="POST" action="">
      <div class="form-group">
        <input type="email" name="email" placeholder="Email Address" required>
      </div>
      <div class="form-group">
        <input type="password" name="password" placeholder="Password" required>
      </div>
      <button type="submit" class="login-btn">Log In</button>
    </form>

    <p class="question">Don't have an account?</p>
    <a href="register.php" class="create">
      <b>Create your DineClick Account!</b>
    </a>
  </div>
</body>
</html>
