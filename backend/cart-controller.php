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

function findDripBox(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare('SELECT id, name, price, image FROM sunnydripboxes WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $box = $stmt->fetch();
    return $box ?: null;
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

            $key = 'product:' . $productId;
            $currentQty = isset($cart[$key]) ? (int)$cart[$key]['quantity'] : 0;
            $newQty = $currentQty + $quantity;
            if ($newQty > (int)$product['stock']) {
                echo json_encode(['success' => false, 'message' => 'Not enough stock for this product.']);
                break;
            }

            $cart[$key] = [
                'item_type' => 'product',
                'product_id' => $productId,
                'box_id' => 0,
                'quantity' => $newQty,
            ];
            saveCart($cart);

            echo json_encode([
                'success' => true,
                'message' => 'Added to cart.',
                'count' => getCartItemCount(),
            ]);
            break;

        case 'add_box':
            $boxId = isset($_POST['box_id']) ? (int)$_POST['box_id'] : 0;
            if ($boxId <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid DripBox.']);
                break;
            }

            $box = findDripBox($pdo, $boxId);
            if (!$box) {
                echo json_encode(['success' => false, 'message' => 'DripBox not found.']);
                break;
            }

            // Add DripBox as a single cart item (do not expand into its products)
            $key = 'box:' . $boxId;
            $currentQty = isset($cart[$key]) ? (int)$cart[$key]['quantity'] : 0;
            $cart[$key] = [
                'item_type' => 'box',
                'product_id' => 0,
                'box_id' => $boxId,
                'quantity' => $currentQty + 1,
            ];
            saveCart($cart);

            echo json_encode([
                'success' => true,
                'message' => 'DripBox added to cart.',
                'count' => getCartItemCount(),
            ]);
            break;

        case 'update':
            $itemType = (string)($_POST['item_type'] ?? 'product');
            $quantity = max(0, (int)($_POST['quantity'] ?? 0));
            $productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
            $boxId = isset($_POST['box_id']) ? (int)$_POST['box_id'] : 0;
            $key = $itemType === 'box' ? ('box:' . $boxId) : ('product:' . $productId);

            if (!isset($cart[$key])) {
                echo json_encode(['success' => false, 'message' => 'Item not found in cart.']);
                break;
            }

            if ($quantity === 0) {
                unset($cart[$key]);
            } else {
                if ($itemType === 'box') {
                    $box = findDripBox($pdo, $boxId);
                    if (!$box) {
                        echo json_encode(['success' => false, 'message' => 'DripBox not found.']);
                        break;
                    }
                    $cart[$key]['quantity'] = $quantity;
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
                    $cart[$key]['quantity'] = $quantity;
                }
            }

            saveCart($cart);
            echo json_encode([
                'success' => true,
                'message' => 'Cart updated.',
                'count' => getCartItemCount(),
            ]);
            break;

        case 'remove':
            $itemType = (string)($_POST['item_type'] ?? 'product');
            $productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
            $boxId = isset($_POST['box_id']) ? (int)$_POST['box_id'] : 0;
            $key = $itemType === 'box' ? ('box:' . $boxId) : ('product:' . $productId);

            if (isset($cart[$key])) {
                unset($cart[$key]);
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
