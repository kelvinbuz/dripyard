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
define('PAYSTACK_PUBLIC_KEY', 'pk_test_a6556ba2eb4bc12349d6dfb8f24c17a433a114e5');
define('PAYSTACK_SECRET_KEY', 'sk_test_43ab70211e6c31d9483d9a776e985ea76215c80c');

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

function cartDbAvailable(PDO $pdo): bool
{
    static $available = null;
    if ($available !== null) {
        return $available;
    }

    try {
        $pdo->query('SELECT 1 FROM cart_items LIMIT 1');
        $available = true;
    } catch (Throwable $e) {
        $available = false;
    }

    return $available;
}

function loadUserCart(int $userId): array
{
    $pdo = getPDO();
    if (!cartDbAvailable($pdo)) {
        return [];
    }

    $stmt = $pdo->prepare('SELECT item_type, product_id, box_id, quantity FROM cart_items WHERE user_id = ?');
    $stmt->execute([$userId]);
    $rows = $stmt->fetchAll();

    $cart = [];
    foreach ($rows as $row) {
        $type = (string)($row['item_type'] ?? 'product');
        $pid = isset($row['product_id']) ? (int)$row['product_id'] : 0;
        $bid = isset($row['box_id']) ? (int)$row['box_id'] : 0;
        $key = $type === 'box' ? ('box:' . $bid) : ('product:' . $pid);

        $cart[$key] = [
            'item_type' => $type,
            'product_id' => $pid,
            'box_id' => $bid,
            'quantity' => (int)$row['quantity'],
        ];
    }

    return $cart;
}

function saveUserCart(int $userId, array $cart): void
{
    $pdo = getPDO();
    if (!cartDbAvailable($pdo)) {
        return;
    }

    $pdo->beginTransaction();
    try {
        $stmtDelete = $pdo->prepare('DELETE FROM cart_items WHERE user_id = ?');
        $stmtDelete->execute([$userId]);

        if (!empty($cart)) {
            $stmtInsert = $pdo->prepare('INSERT INTO cart_items (user_id, item_type, product_id, box_id, quantity) VALUES (?, ?, ?, ?, ?)');
            foreach ($cart as $item) {
                $type = (string)($item['item_type'] ?? 'product');
                $pid = (int)($item['product_id'] ?? 0);
                $bid = (int)($item['box_id'] ?? 0);
                $qty = (int)($item['quantity'] ?? 0);

                if ($qty <= 0) {
                    continue;
                }

                if ($type === 'box') {
                    if ($bid > 0) {
                        $stmtInsert->execute([$userId, 'box', null, $bid, $qty]);
                    }
                } else {
                    if ($pid > 0) {
                        $stmtInsert->execute([$userId, 'product', $pid, null, $qty]);
                    }
                }
            }
        }

        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}

/**
 * Returns the current cart from session.
 */
function getCart(): array
{
    $userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
    if ($userId > 0) {
        try {
            return loadUserCart($userId);
        } catch (Throwable $e) {
            // fall back to session cart
        }
    }

    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Backward compatibility: old cart shape was keyed by productId and items did not include item_type.
    // Normalize to composite keys so we can store both products and boxes.
    $normalized = [];
    foreach ($_SESSION['cart'] as $key => $item) {
        if (!is_array($item)) {
            continue;
        }
        $type = (string)($item['item_type'] ?? 'product');
        $pid = (int)($item['product_id'] ?? 0);
        $bid = (int)($item['box_id'] ?? 0);
        $qty = (int)($item['quantity'] ?? 0);

        if ($qty <= 0) {
            continue;
        }

        if ($type === 'box') {
            if ($bid > 0) {
                $normalized['box:' . $bid] = ['item_type' => 'box', 'product_id' => 0, 'box_id' => $bid, 'quantity' => $qty];
            }
        } else {
            if ($pid <= 0 && is_numeric($key)) {
                $pid = (int)$key;
            }
            if ($pid > 0) {
                $normalized['product:' . $pid] = ['item_type' => 'product', 'product_id' => $pid, 'box_id' => 0, 'quantity' => $qty];
            }
        }
    }
    $_SESSION['cart'] = $normalized;

    return $_SESSION['cart'];
}

/**
 * Saves the cart back to the session.
 */
function saveCart(array $cart): void
{
    $userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
    if ($userId > 0) {
        try {
            saveUserCart($userId, $cart);
            return;
        } catch (Throwable $e) {
            // fall back to session cart
        }
    }

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
