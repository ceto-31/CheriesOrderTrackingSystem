<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo isset($page_title) ? $page_title . ' - ' . SITE_NAME : SITE_NAME; ?></title>
  
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  
  <!-- jQuery (must load first) -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  
  <!-- SweetAlert2 -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  
  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      margin: 0;
      font-family: 'Poppins', sans-serif;
      background-color: #F2F2F2;
      color: #000;
      display: flex;
      min-height: 100vh;
    }

    /* Sidebar */
    aside {
      width: 240px;
      background-color: #fff;
      padding: 20px;
      border-right: 1px solid #ddd;
      display: flex;
      flex-direction: column;
      position: sticky;
      top: 0;
      height: 100vh;
      overflow-y: auto;
    }

    .logo {
      font-size: 1.5rem;
      font-weight: 700;
      color: #B76E09;
      margin-bottom: 35px;
      text-align: center;
      letter-spacing: 0.5px;
    }

    .sidebar-menu a {
      display: block;
      padding: 12px 15px;
      color: #7D6E6E;
      text-decoration: none;
      font-weight: 600;
      border-radius: 8px;
      margin-bottom: 6px;
      transition: all 0.3s ease;
    }

    .sidebar-menu a:hover {
      background-color: #B76E09;
      color: #fff;
      transform: translateX(4px);
    }

    .sidebar-menu a.active {
      background-color: #B76E09;
      color: #fff;
    }

    /* Main area */
    main {
      flex: 1;
      display: flex;
      flex-direction: column;
      min-height: 100vh;
    }

    /* Header */
    header {
      background: #fff;
      padding: 15px 25px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
      position: sticky;
      top: 0;
      z-index: 100;
    }

    header h3 {
      color: #000;
      font-weight: 600;
    }

    header nav a {
      margin-left: 20px;
      text-decoration: none;
      color: #7D6E6E;
      font-weight: 600;
      padding: 8px 14px;
      border-radius: 8px;
      transition: all 0.3s ease;
    }

    header nav a:hover,
    header nav a.active {
      background-color: #B76E09;
      color: #fff;
    }

    /* Footer */
    footer {
      text-align: center;
      padding: 12px;
      font-size: 0.85rem;
      color: #7D6E6E;
      border-top: 1px solid #ddd;
      background: #fff;
      margin-top: auto;
    }

    /* Mobile Menu Toggle */
    .mobile-menu-toggle {
      display: none;
      position: fixed;
      top: 15px;
      left: 15px;
      z-index: 1000;
      background: #B76E09;
      color: #fff;
      border: none;
      border-radius: 8px;
      padding: 10px 15px;
      font-size: 1.2rem;
      cursor: pointer;
      box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    }

    .mobile-menu-overlay {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0,0,0,0.5);
      z-index: 998;
    }

    aside.mobile-open {
      display: flex !important;
      position: fixed;
      z-index: 999;
      left: 0;
      top: 0;
      width: 260px;
      box-shadow: 2px 0 12px rgba(0,0,0,0.2);
    }

    /* Responsive */
    @media (max-width: 968px) {
      .mobile-menu-toggle {
        display: block;
      }

      aside {
        display: none;
      }

      main {
        margin-left: 0;
      }

      header {
        flex-wrap: wrap;
        padding-left: 60px;
      }

      header nav {
        width: 100%;
        text-align: center;
        margin-top: 10px;
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 8px;
      }

      header nav a {
        margin: 0;
        font-size: 0.85rem;
      }
    }

    @media (max-width: 576px) {
      header h3 {
        font-size: 1rem;
      }

      header nav a {
        padding: 6px 10px;
        font-size: 0.8rem;
      }
    }

    /* Alert messages */
    .alert {
      padding: 12px 20px;
      margin: 15px 0;
      border-radius: 8px;
      font-weight: 500;
    }
    .alert-success {
      background-color: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
    }
    .alert-error {
      background-color: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
    }
    .alert-info {
      background-color: #d1ecf1;
      color: #0c5460;
      border: 1px solid #bee5eb;
    }
  </style>
  
  <?php if (isset($additional_css)) echo $additional_css; ?>
</head>
<body>
