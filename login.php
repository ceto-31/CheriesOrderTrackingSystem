<?php
require_once 'config/config.php';

// Redirect if already logged in
if (is_logged_in()) {
    if (is_admin()) {
        redirect(SITE_URL . 'admin/dashboard.php');
    } elseif (is_cashier()) {
        redirect(SITE_URL . 'cashier/order_entry.php');
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
        
        $query = "SELECT id, first_name, last_name, email, password, role, is_admin FROM users WHERE email = :email LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch();
            
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                $_SESSION['user_email']= $user['email'];
                $_SESSION['role']      = (int)$user['role'];
                $_SESSION['is_admin']  = $user['is_admin'];
                
                // Regenerate session ID for security
                session_regenerate_id(true);
                
                // Redirect based on role
                if ($user['is_admin'] == 1) {
                    redirect(SITE_URL . 'admin/dashboard.php');
                } elseif ((int)$user['role'] === 2) {
                    redirect(SITE_URL . 'cashier/order_entry.php');
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
  <title>Cheries — Staff Login</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #3b1f0a 0%, #7a3e10 50%, #a66b27 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .card {
      width: 100%;
      max-width: 390px;
      background: #fff;
      border-radius: 16px;
      box-shadow: 0 20px 60px rgba(0,0,0,.35);
      overflow: hidden;
    }

    .card-header {
      background: #a66b27;
      padding: 28px 32px 24px;
      text-align: center;
      color: #fff;
    }

    .card-header .logo-icon {
      font-size: 2.4rem;
      line-height: 1;
      margin-bottom: 8px;
    }

    .card-header h1 {
      font-size: 1.4rem;
      font-weight: 700;
      letter-spacing: .5px;
    }

    .card-header p {
      font-size: .8rem;
      opacity: .8;
      margin-top: 3px;
    }

    .card-body {
      padding: 28px 32px 32px;
    }

    .form-group {
      margin-bottom: 18px;
    }

    .form-group label {
      display: block;
      font-size: .78rem;
      font-weight: 600;
      color: #5a3e1b;
      margin-bottom: 6px;
      text-transform: uppercase;
      letter-spacing: .4px;
    }

    .form-group input {
      width: 100%;
      padding: 12px 16px;
      border: 1.5px solid #e0d4c4;
      border-radius: 9px;
      font-size: .92rem;
      font-family: inherit;
      color: #2d1a09;
      outline: none;
      transition: border-color .2s, box-shadow .2s;
      background: #fdfaf7;
    }

    .form-group input:focus {
      border-color: #a66b27;
      box-shadow: 0 0 0 3px rgba(166,107,39,.12);
    }

    .login-btn {
      width: 100%;
      padding: 13px;
      background: #a66b27;
      color: #fff;
      border: none;
      border-radius: 9px;
      font-size: .97rem;
      font-weight: 700;
      font-family: inherit;
      cursor: pointer;
      letter-spacing: .3px;
      transition: background .2s, transform .1s;
      margin-top: 4px;
    }

    .login-btn:hover  { background: #8c5620; }
    .login-btn:active { transform: scale(.98); }

    .alert {
      padding: 11px 15px;
      border-radius: 8px;
      font-size: .85rem;
      margin-bottom: 18px;
      font-weight: 500;
    }

    .alert-error {
      background: #fdf0f0;
      color: #891c1c;
      border: 1px solid #f5c6c6;
    }

    .powered {
      text-align: center;
      font-size: .72rem;
      color: #b0956d;
      margin-top: 22px;
    }
  </style>
</head>
<body>

<div class="card">
  <div class="card-header">
    <div class="logo-icon">🍽️</div>
    <h1>Cheries</h1>
    <p>Order Tracking System — Staff Portal</p>
  </div>

  <div class="card-body">

    <?php if (!empty($error)): ?>
      <div class="alert alert-error">
        <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="" novalidate>
      <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email"
               placeholder="staff@cheries.com" required autocomplete="username"
               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8') : ''; ?>">
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password"
               placeholder="••••••••" required autocomplete="current-password">
      </div>

      <button type="submit" class="login-btn">Sign In</button>
    </form>

    <p class="powered">Access is restricted to authorised staff only.</p>

  </div>
</div>

</body>
</html>
