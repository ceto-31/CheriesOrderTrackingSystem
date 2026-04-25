# 🍴 DineClick - Food Ordering System

A complete PHP-based food ordering system with MySQL database integration, featuring separate user and admin dashboards with modern UI and real-time functionality.

## ✨ Features

### User Features
- **Secure Authentication**: Registration and login with password hashing (password_hash())
- **Browse Menu**: Search and filter products by category
- **Shopping Cart**: Session-based cart with AJAX add-to-cart (no page reload)
- **Checkout System**: Order placement with delivery information
- **Order Tracking**: View active orders with real-time status updates
- **Order History**: Complete order history with details
- **Profile Management**: Update personal information and password
- **Responsive Design**: Works on desktop and mobile devices

### Admin Features
- **Dashboard**: Overview with statistics and charts
- **Product Management**: Full CRUD operations (Create, Read, Update, Delete)
- **Image Upload**: Product images stored in `/assets/images/products/`
- **Category Management**: Organize products into categories
- **Order Management**: View all orders, update status
- **Sales Reports**: Daily/monthly revenue with Chart.js visualization
- **Inventory Control**: Real-time stock tracking and updates

### Security Features
- ✅ Password hashing with `password_hash()` and `password_verify()`
- ✅ SQL injection prevention with PDO prepared statements
- ✅ XSS protection with input sanitization
- ✅ Session management with regeneration
- ✅ CSRF protection ready
- ✅ Password strength validation (min 8 chars, uppercase, lowercase, number)
- ✅ Email validation

### Modern Enhancements
- ✅ **AJAX**: Add to cart without page reload
- ✅ **SweetAlert2**: Beautiful confirmation dialogs
- ✅ **Chart.js**: Sales analytics and revenue charts
- ✅ **Real-time Stock**: Automatic stock decrease after checkout
- ✅ **Responsive Layout**: Bootstrap-inspired design
- ✅ **Notifications System**: Order status updates

## 📁 Project Structure

```
DineClick/
├── admin/
│   ├── dashboard.php          # Admin dashboard with statistics
│   ├── products.php            # Product CRUD management
│   ├── product_save.php        # Product add/edit handler
│   ├── product_delete.php      # Product deletion handler
│   ├── orders_manage.php       # Order management
│   ├── order_view.php          # Order details view
│   ├── order_update_status.php # Order status update
│   ├── categories.php          # Category management
│   ├── category_save.php       # Category save handler
│   ├── category_delete.php     # Category delete handler
│   └── reports.php             # Sales reports with charts
├── ajax/
│   ├── add_to_cart.php         # AJAX add to cart
│   ├── update_cart.php         # AJAX cart operations
│   └── cancel_order.php        # AJAX order cancellation
├── assets/
│   └── images/
│       └── products/           # Product images directory
├── config/
│   ├── database.php            # Database connection class
│   └── config.php              # Application configuration
├── database/
│   └── schema.sql              # Database schema and sample data
├── includes/
│   ├── header.php              # Common header template
│   ├── footer.php              # Common footer template
│   ├── session_check.php       # User authentication check
│   └── admin_check.php         # Admin authentication check
├── index.php                   # User dashboard/home
├── login.php                   # Login page
├── register.php                # Registration page
├── logout.php                  # Logout handler
├── menu.php                    # Product listing/menu
├── cart.php                    # Shopping cart
├── checkout.php                # Checkout page
├── orders.php                  # Active orders
├── order_details.php           # Order details view
├── order_history.php           # Past orders
├── profile.php                 # User profile settings
└── README.md                   # This file
```

## 🚀 Installation Instructions

### Prerequisites
- WAMP64 Server installed
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Modern web browser

### Step 1: Database Setup

1. Start WAMP64 server
2. Open phpMyAdmin (http://localhost/phpmyadmin)
3. Import the database schema:
   - Click "Import" tab
   - Choose file: `database/schema.sql`
   - Click "Go"
   
   **OR** Run the SQL manually:
   - Create database: `CREATE DATABASE dineclick_db;`
   - Copy and paste the contents of `database/schema.sql` into SQL tab

### Step 2: Configuration

1. The project is already configured for WAMP64 with default settings:
   - Host: `localhost`
   - Database: `dineclick_db`
   - Username: `root`
   - Password: (empty)

2. If your WAMP has different credentials, edit `config/database.php`:
   ```php
   private $host = "localhost";
   private $db_name = "dineclick_db";
   private $username = "root";
   private $password = ""; // Add your password if needed
   ```

### Step 3: File Permissions

1. Create the upload directory for product images:
   - Navigate to: `c:\wamp64\www\DineClick\assets\images\products\`
   - Ensure this folder exists and is writable

### Step 4: Access the Application

1. **User Access**:
   - URL: http://localhost/DineClick/login.php
   - Create a new account via registration

2. **Admin Access**:
   - URL: http://localhost/DineClick/login.php
   - Email: `admin@dineclick.com`
   - Password: `admin123`

## 👤 Default Admin Account

After running the schema.sql file, you'll have:
- **Email**: admin@dineclick.com
- **Password**: admin123
- **Role**: Administrator

**Important**: Change the admin password after first login!

## 📊 Database Schema

### Tables Created:
1. **users** - User accounts (customers and admin)
2. **categories** - Product categories
3. **products** - Product catalog with images and stock
4. **orders** - Customer orders
5. **order_items** - Order line items
6. **cart** - Shopping cart items
7. **notifications** - User notifications

### Sample Data Included:
- 1 Admin account
- 6 Categories (Burgers, Pasta, Pizza, Salads, Beverages, Desserts)
- 6 Sample products with stock

## 🎨 Design & UI

The system maintains your original HTML design with:
- **Color Scheme**: Primary color #B76E09 (orange/brown)
- **Font**: Poppins (Google Fonts)
- **Layout**: Sidebar navigation + main content area
- **Responsive**: Mobile-friendly design
- **Modern**: Smooth transitions and hover effects

## 🔧 Usage Guide

### For Customers:

1. **Register/Login**: Create account or login
2. **Browse Menu**: Navigate to Menu page, search/filter products
3. **Add to Cart**: Click "Add to Cart" on products (AJAX - no page reload)
4. **View Cart**: Check cart, adjust quantities, or remove items
5. **Checkout**: Fill delivery info, select payment method
6. **Track Orders**: View order status in "My Orders"
7. **Order History**: Check past orders in "Order History"

### For Administrators:

1. **Dashboard**: View statistics and recent orders
2. **Manage Products**:
   - Click "Products" in sidebar
   - Add new products with images
   - Edit or delete existing products
   - Update stock levels
3. **Manage Orders**:
   - View all orders
   - Update order status (Pending → Confirmed → Preparing → Out for Delivery → Delivered)
   - View detailed order information
4. **Categories**: Add/edit/delete product categories
5. **Reports**: View sales analytics with charts

## 🔐 Security Features Implemented

1. **Password Security**:
   - Passwords hashed with `password_hash()` (bcrypt algorithm)
   - Minimum 8 characters, requires uppercase, lowercase, and number
   
2. **SQL Injection Prevention**:
   - All queries use PDO prepared statements
   - Parameter binding for all user inputs
   
3. **XSS Protection**:
   - `htmlspecialchars()` on all output
   - Input sanitization with `clean_input()` function
   
4. **Session Security**:
   - Session regeneration after login
   - Session-based authentication
   - Automatic logout on session expiry
   
5. **Input Validation**:
   - Server-side validation for all forms
   - Email format validation
   - File type validation for image uploads

## 📱 Technologies Used

### Backend:
- PHP 7.4+
- MySQL (PDO)
- Session Management

### Frontend:
- HTML5
- CSS3 (Custom styling)
- JavaScript/jQuery
- AJAX for dynamic interactions

### Libraries:
- **SweetAlert2**: Modern alert dialogs
- **Chart.js**: Data visualization
- **jQuery**: AJAX and DOM manipulation
- **Google Fonts**: Poppins font family

## 🐛 Troubleshooting

### Common Issues:

1. **Database Connection Error**:
   - Verify WAMP is running (icon should be green)
   - Check database credentials in `config/database.php`
   - Ensure database `dineclick_db` exists

2. **Image Upload Not Working**:
   - Check folder exists: `assets/images/products/`
   - Verify folder has write permissions
   - Check PHP upload settings in php.ini

3. **Session/Login Issues**:
   - Clear browser cookies
   - Check if sessions are enabled in PHP
   - Verify session path is writable

4. **Page Not Found**:
   - Ensure URL is: `http://localhost/DineClick/`
   - Check .htaccess if using custom URLs

## 📈 Future Enhancements (Optional)

- Payment gateway integration (PayPal, Stripe)
- Email notifications for orders
- SMS notifications
- Customer reviews and ratings
- Loyalty points system
- Coupon/discount codes
- Multiple delivery addresses
- Order scheduling

## 🆘 Support

For issues or questions:
1. Check the troubleshooting section
2. Verify all installation steps were followed
3. Check PHP error logs in WAMP

## 📄 License

This project is created for educational and commercial purposes.

---

**Created with ❤️ for DineClick Food Ordering System**

Enjoy your modern, secure, and feature-rich ordering system! 🍕🍔🍰
