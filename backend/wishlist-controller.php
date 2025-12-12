<?php
// Session-based wishlist controller with JSON responses

require_once __DIR__ . '/db.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_POST['action'] ?? $_GET['action'] ?? null;

$wishlist = getWishlist();

if ($method === 'GET' && $action === 'summary') {
    echo json_encode([
        'success' => true,
        'items' => array_values($wishlist),
    ]);
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
        case 'toggle':
            $type = $_POST['type'] ?? 'product';
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid item.']);
                break;
            }

            $key = $type . ':' . $id;
            if (isset($wishlist[$key])) {
                unset($wishlist[$key]);
                saveWishlist($wishlist);
                echo json_encode(['success' => true, 'inWishlist' => false]);
            } else {
                $wishlist[$key] = [
                    'type' => $type,
                    'id'   => $id,
                ];
                saveWishlist($wishlist);
                echo json_encode(['success' => true, 'inWishlist' => true]);
            }
            break;

        case 'clear':
            $wishlist = [];
            saveWishlist($wishlist);
            echo json_encode(['success' => true, 'inWishlist' => false]);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Unknown action.']);
            break;
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error while processing wishlist.']);
}
