# DripYard Clothing Line - E-commerce Website

A modern PHP-based e-commerce platform for DripYard clothing brand with navy and white themed streetwear.

## Features

- **Product Catalog**: Browse and filter products by category
- **Shopping Cart**: Session-based cart with add/update/remove functionality
- **User Authentication**: Registration, login, and role-based access (admin/customer)
- **Admin Panel**: Manage products, categories, users, and orders
- **DripBox Bundles**: Curated outfit bundles
- **Payment Integration**: Paystack payment gateway (test mode)
- **Responsive Design**: Bootstrap 5 with custom navy/white theme

## Setup Instructions

### 1. Database Setup

Ensure XAMPP MySQL is running, then execute the migrations:

```bash
# Navigate to your XAMPP MySQL bin directory
cd "c:\xampp\mysql\bin"

# Import the database schema and seed data
Get-Content "c:\xampp\htdocs\Dripyard\backend\migrations.sql" | .\mysql.exe -u root dripyard_db
```

### 2. Admin Account Setup

The first user to register automatically becomes an admin. Use these credentials:

- **Email**: admin@dripyard.com
- **Password**: admin123

### 3. Paystack Configuration

Update the Paystack keys in `backend/db.php` for production:

```php
define('PAYSTACK_PUBLIC_KEY', 'your_live_public_key');
define('PAYSTACK_SECRET_KEY', 'your_live_secret_key');
```

### 4. Access the Application

- **Frontend Store**: `http://localhost/Dripyard/` (Customer interface only)
- **Admin Panel**: `http://localhost/Dripyard/public/admin/dashboard.php` (Admin interface only)
- **Admin Login**: Use credentials `admin@dripyard.com` / `admin123`

## Project Structure

```
Dripyard/
├── backend/                 # Core PHP backend
│   ├── auth.php            # Authentication functions
│   ├── cart-controller.php # Shopping cart API
│   ├── db.php              # Database connection & helpers
│   ├── migrations.sql      # Database schema & seed data
│   └── ...                 # Other controllers
├── public/                  # Public-facing pages
│   ├── admin/              # Admin panel pages
│   ├── partials/           # Shared templates (header, footer, navbar)
│   ├── index.php           # Homepage
│   ├── shop.php            # Product catalog
│   ├── cart.php            # Shopping cart
│   ├── checkout.php        # Checkout process
│   └── ...                 # Other public pages
└── assets/                  # Static assets
    ├── css/styles.css      # Custom styling
    └── js/main.js          # Frontend JavaScript
```

## Database Schema

- **users**: Customer and admin accounts
- **categories**: Product categories (T-Shirts, Hoodies, Accessories)
- **products**: Individual products with pricing and stock
- **sunnydripboxes**: Curated outfit bundles
- **orders**: Customer orders
- **order_items**: Line items for each order

## Security Features

- Prepared statements for SQL injection prevention
- Password hashing with PHP's `password_hash()`
- Session-based authentication
- Role-based access control (admin vs customer)
- Input sanitization with `htmlspecialchars()`

## Payment Integration

Currently configured with Paystack test keys. For production:

1. Update Paystack keys in `backend/db.php`
2. Configure your Paystack account with the correct callback URLs
3. Test with real Paystack credentials

## Development Notes

- Uses PDO for database operations
- Bootstrap 5 for responsive design
- Session-based shopping cart (no database persistence required)
- First registered user automatically gets admin role
- Clean, modern navy and white color scheme

## Troubleshooting

- **Database connection errors**: Ensure XAMPP MySQL is running and credentials are correct
- **Permission errors**: Check that XAMPP has write permissions for the project directory
- **Paystack errors**: Verify API keys are correctly configured for your environment
