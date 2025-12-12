<?php
// Order endpoints for user dashboards and admin panel

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

header('Content-Type: application/json');

$pdo = getPDO();
$user = getCurrentUser();
$action = $_GET['action'] ?? 'list_user';

if (!$user) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Authentication required.']);
    exit;
}

try {
    switch ($action) {
        case 'list_user':
            $stmt = $pdo->prepare('SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC');
            $stmt->execute([$user['id']]);
            $orders = $stmt->fetchAll();
            echo json_encode(['success' => true, 'orders' => $orders]);
            break;

        case 'get_user_order':
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            $stmt = $pdo->prepare('SELECT * FROM orders WHERE id = ? AND user_id = ? LIMIT 1');
            $stmt->execute([$id, $user['id']]);
            $order = $stmt->fetch();
            if (!$order) {
                echo json_encode(['success' => false, 'message' => 'Order not found.']);
                break;
            }
            $itemsStmt = $pdo->prepare('SELECT oi.*, p.name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?');
            $itemsStmt->execute([$order['id']]);
            $items = $itemsStmt->fetchAll();
            echo json_encode(['success' => true, 'order' => $order, 'items' => $items]);
            break;

        case 'list_all':
            if ($user['role'] !== 'admin') {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Admin access required.']);
                break;
            }
            $stmt = $pdo->query('SELECT o.*, u.name AS user_name, u.email AS user_email FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC');
            $orders = $stmt->fetchAll();
            echo json_encode(['success' => true, 'orders' => $orders]);
            break;

        case 'get':
            if ($user['role'] !== 'admin') {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Admin access required.']);
                break;
            }
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            $stmt = $pdo->prepare('SELECT o.*, u.name AS user_name, u.email AS user_email FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ? LIMIT 1');
            $stmt->execute([$id]);
            $order = $stmt->fetch();
            if (!$order) {
                echo json_encode(['success' => false, 'message' => 'Order not found.']);
                break;
            }
            $itemsStmt = $pdo->prepare('SELECT oi.*, p.name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?');
            $itemsStmt->execute([$order['id']]);
            $items = $itemsStmt->fetchAll();
            echo json_encode(['success' => true, 'order' => $order, 'items' => $items]);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Unknown action.']);
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error while processing orders.']);
}
