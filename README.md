🧵 DripYard Clothing Line – E-Commerce Platform

A modern PHP-based e-commerce solution for the DripYard streetwear brand with navy & white theme styling.

🚀 Features

🛍 Product Catalog with categories & filters

🛒 Shopping Cart (add/update/remove)

👤 User Authentication (Admin/Customer)

🛠 Admin Panel to manage:

Products

Categories

Users

Orders

📦 DripBox Bundles (curated outfits)

💳 Paystack Integration (Test Mode)

🔐 Security

Prepared statements (PDO)

Password hashing

Role-based access

📱 Responsive UI using Bootstrap 5

🏗️ Tech Stack
Component	Technology
Backend	PHP (PDO)
Database	MySQL
Frontend	Bootstrap 5
Payments	Paystack API
Auth	Sessions
🛠️ Installation Guide

This project supports Windows (XAMPP/WAMP) and Linux (LAMP).

📁 Project Structure
Dripyard/
├── backend/
├── public/
├── assets/
└── ...

🪟 Installation for Windows (XAMPP)
1️⃣ Requirements

XAMPP (PHP ≥7.4, MySQL)

Browser

2️⃣ Setup Folder

Copy the project to:

C:\xampp\htdocs\Dripyard


Start Apache and MySQL in XAMPP.

3️⃣ Create Database

Open the MySQL bin directory:

cd "C:\xampp\mysql\bin"


Import migration SQL:

mysql -u root -e "CREATE DATABASE dripyard_db;"
mysql -u root dripyard_db < C:\xampp\htdocs\Dripyard\backend\migrations.sql

4️⃣ Admin Setup

First registered user becomes admin:

Email: admin@dripyard.com

Password: admin123

5️⃣ Configure Paystack Keys

Edit:

Dripyard/backend/db.php


Add your keys:

define('PAYSTACK_PUBLIC_KEY', 'your_public_key');
define('PAYSTACK_SECRET_KEY', 'your_secret_key');

6️⃣ Run the App

Storefront:
http://localhost/Dripyard/

Admin Panel:
http://localhost/Dripyard/public/admin/login.php

🐧 Installation for Linux (Ubuntu LAMP)
1️⃣ Install Dependencies
sudo apt update
sudo apt install apache2 mysql-server php php-mysql php-xml php-curl unzip

2️⃣ Move Project to Web Root
sudo cp -r Dripyard /var/www/html/

3️⃣ Set Folder Permission
sudo chown -R www-data:www-data /var/www/html/Dripyard
sudo chmod -R 755 /var/www/html/Dripyard

4️⃣ Create Database & Import SQL
sudo mysql -e "CREATE DATABASE dripyard_db;"
sudo mysql dripyard_db < /var/www/html/Dripyard/backend/migrations.sql

5️⃣ Configure Paystack Keys

Edit:

/var/www/html/Dripyard/backend/db.php

6️⃣ Restart Server
sudo systemctl restart apache2

7️⃣ Access the Website

Frontend:
http://localhost/Dripyard/

Admin:
http://localhost/Dripyard/public/admin/login.php

🔐 Security Highlights

Password hashing (password_hash())

Prepared SQL statements (PDO)

Sanitized inputs (htmlspecialchars())

Role-based access (admin/customer)

🧪 Troubleshooting
Issue	Solution
MySQL not connecting	Ensure MySQL service is running
Paystack errors	Confirm API keys and callback URL
Permission denied (Linux)	Run chmod and chown
Blank page	Enable PHP errors in config
🌱 Future Enhancements

Email notifications

Wishlist system

Discount coupons

Mobile app moderation dashboard

🤝 Contributing

Feel free to fork, submit issues, and improve!
