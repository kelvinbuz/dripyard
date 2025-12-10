# 🧵 DripYard Clothing Line – E-Commerce Platform
A modern PHP-based e-commerce solution for the **DripYard streetwear brand** with navy & white themed UI.

## 🚀 Features
- 🛍 Product Catalog with categories & filters  
- 🛒 Shopping Cart (add/update/remove)  
- 👤 User Authentication (Admin/Customer)  
- 🛠 Admin Panel (Products, Categories, Users, Orders)  
- 📦 DripBox Bundles (curated outfits)  
- 💳 Paystack Payment Integration  
- 🔐 Security (PDO, hashed passwords, sanitized inputs)  
- 📱 Bootstrap 5 responsive design  

## 🏗️ Tech Stack
| Component | Technology |
|----------|-------------|
| Backend  | PHP (PDO)   |
| Database | MySQL       |
| Frontend | Bootstrap 5 |
| Payments | Paystack API |
| Auth     | Sessions     |

## 📁 Project Structure
```
Dripyard/
├── backend/
│   ├── auth.php
│   ├── cart-controller.php
│   ├── db.php
│   ├── migrations.sql
│   └── ...
├── public/
│   ├── admin/
│   ├── partials/
│   ├── index.php
│   └── ...
├── assets/
└── ...
```

# 🛠️ Installation Guide  
Supports both **Windows** and **Linux**.

# 🪟 Installation on Windows (XAMPP)

### 1️⃣ Requirements
- XAMPP (Apache + MySQL)
- PHP 7.4+ recommended

### 2️⃣ Setup Directory
Place the project in:
```
C:\xampp\htdocs\Dripyard
```

Start `Apache` and `MySQL` in XAMPP.

### 3️⃣ Create Database
```bash
cd "C:\xampp\mysql\bin"
mysql -u root -e "CREATE DATABASE dripyard_db;"
mysql -u root dripyard_db < C:\xampp\htdocs\Dripyard\backend\migrations.sql
```

### 4️⃣ Admin Account
- Email: `admin@dripyard.com`
- Password: `admin123`

### 5️⃣ Configure Paystack Keys
Edit:
```
Dripyard/backend/db.php
```

### 6️⃣ Access Application
- Storefront: http://localhost/Dripyard/
- Admin: http://localhost/Dripyard/public/admin/login.php

# 🐧 Installation on Linux (Ubuntu LAMP)

### 1️⃣ Install Dependencies
```bash
sudo apt update
sudo apt install apache2 mysql-server php php-mysql php-xml php-curl unzip
```

### 2️⃣ Move Project to Web Root
```bash
sudo cp -r Dripyard /var/www/html/
```

### 3️⃣ Set Permissions
```bash
sudo chown -R www-data:www-data /var/www/html/Dripyard
sudo chmod -R 755 /var/www/html/Dripyard
```

### 4️⃣ Create Database & Import SQL
```bash
sudo mysql -e "CREATE DATABASE dripyard_db;"
sudo mysql dripyard_db < /var/www/html/Dripyard/backend/migrations.sql
```

### 5️⃣ Restart Apache
```bash
sudo systemctl restart apache2
```

### 6️⃣ Access Application
- Storefront: http://localhost/Dripyard/
- Admin: http://localhost/Dripyard/public/admin/login.php

## 🔐 Security Notes
- `password_hash()`  
- Prepared statements (PDO)  
- Sanitized inputs (`htmlspecialchars()`)  
- Role-based access

## 🧪 Troubleshooting
| Problem | Fix |
|--------|-----|
MySQL fails | Ensure service is running  
Paystack issues | Recheck keys  
Permission denied (Linux) | Use chmod/chown  

## 🌱 Future Enhancements
- Email notifications  
- Discount coupons  
- Product reviews  
- Mobile admin dashboard  

## 🤝 Contributing
Feel free to submit PRs and issues!

## 📄 License
MIT License
