<?php
// Database connection and shared helpers for DripYard Clothing Line

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Update these credentials for your local environment
define('DB_HOST', 'localhost');
define('DB_NAME', 'dripyard_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// Paystack API keys (replace with your real keys before going live). 
// Note: Test keys may not support GHS currency. For production, get live keys that support Ghana Cedis.
define('PAYSTACK_PUBLIC_KEY', 'pk_test_3e9ee8394f7e8065a370b319c98045332cb2c423');
define('PAYSTACK_SECRET_KEY', 'sk_test_c7ec025474ef8afb1b4f89d7a5e2fbc374bbb40b');

/**
 * Returns a shared PDO instance using prepared statements by default.
 */
function getPDO(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            // In production you may want to log this instead of displaying it
            die('Database connection failed: ' . $e->getMessage());
        }
    }

    return $pdo;
}

/**
 * Returns the current cart from session.
 */
function getCart(): array
{
    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    return $_SESSION['cart'];
}

/**
 * Saves the cart back to the session.
 */
function saveCart(array $cart): void
{
    $_SESSION['cart'] = $cart;
}

/**
 * Returns the total quantity of items in the cart.
 */
function getCartItemCount(): int
{
    $cart = getCart();
    $count = 0;
    foreach ($cart as $item) {
        $count += (int)($item['quantity'] ?? 0);
    }
    return $count;
}

/**
 * Returns the current wishlist from session.
 */
function getWishlist(): array
{
    if (!isset($_SESSION['wishlist']) || !is_array($_SESSION['wishlist'])) {
        $_SESSION['wishlist'] = [];
    }

    return $_SESSION['wishlist'];
}

/**
 * Saves the wishlist back to the session.
 */
function saveWishlist(array $wishlist): void
{
    $_SESSION['wishlist'] = $wishlist;
}
