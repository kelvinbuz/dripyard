<?php
// Session-based cart controller with JSON responses

require_once __DIR__ . '/db.php';

header('Content-Type: application/json');

$pdo = getPDO();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_POST['action'] ?? $_GET['action'] ?? null;

/**
 * Fetches a single product row by ID.
 */
function findProduct(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare('SELECT id, name, price, stock, image FROM products WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $product = $stmt->fetch();
    return $product ?: null;
}

$cart = getCart();

if ($method === 'GET' && $action === 'summary') {
    // Lightweight summary for navbar/cart icon
    $totalItems = getCartItemCount();
    echo json_encode(['success' => true, 'count' => $totalItems]);
    exit;
}

if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

if (!$action) {
    echo json_encode(['success' => false, 'message' => 'No action specified.']);
    exit;
}

try {
    switch ($action) {
        case 'add':
            $productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
            $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
            $quantity = max(1, $quantity);

            $product = findProduct($pdo, $productId);
            if (!$product) {
                echo json_encode(['success' => false, 'message' => 'Product not found.']);
                break;
            }

            $currentQty = isset($cart[$productId]) ? (int)$cart[$productId]['quantity'] : 0;
            $newQty = $currentQty + $quantity;
            if ($newQty > (int)$product['stock']) {
                echo json_encode(['success' => false, 'message' => 'Not enough stock for this product.']);
                break;
            }

            $cart[$productId] = [
                'product_id' => $productId,
                'quantity' => $newQty,
            ];
            saveCart($cart);

            echo json_encode([
                'success' => true,
                'message' => 'Added to cart.',
                'count' => getCartItemCount(),
            ]);
            break;

        case 'update':
            $productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
            $quantity = max(0, (int)($_POST['quantity'] ?? 0));

            if (!isset($cart[$productId])) {
                echo json_encode(['success' => false, 'message' => 'Item not found in cart.']);
                break;
            }

            if ($quantity === 0) {
                unset($cart[$productId]);
            } else {
                $product = findProduct($pdo, $productId);
                if (!$product) {
                    echo json_encode(['success' => false, 'message' => 'Product not found.']);
                    break;
                }
                if ($quantity > (int)$product['stock']) {
                    echo json_encode(['success' => false, 'message' => 'Not enough stock for this product.']);
                    break;
                }
                $cart[$productId]['quantity'] = $quantity;
            }

            saveCart($cart);
            echo json_encode([
                'success' => true,
                'message' => 'Cart updated.',
                'count' => getCartItemCount(),
            ]);
            break;

        case 'remove':
            $productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
            if (isset($cart[$productId])) {
                unset($cart[$productId]);
                saveCart($cart);
            }
            echo json_encode([
                'success' => true,
                'message' => 'Item removed from cart.',
                'count' => getCartItemCount(),
            ]);
            break;

        case 'clear':
            $cart = [];
            saveCart($cart);
            echo json_encode([
                'success' => true,
                'message' => 'Cart cleared.',
                'count' => 0,
            ]);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Unknown action.']);
            break;
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error while processing cart.']);
}
