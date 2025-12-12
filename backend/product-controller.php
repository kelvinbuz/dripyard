<?php
// REST-like JSON endpoints for products and categories

require_once __DIR__ . '/db.php';

header('Content-Type: application/json');

$pdo = getPDO();
$action = $_GET['action'] ?? 'list';

try {
    switch ($action) {
        case 'list':
            $categoryId = $_GET['category_id'] ?? null;
            if ($categoryId) {
                $stmt = $pdo->prepare('SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE c.id = ? ORDER BY p.created_at DESC');
                $stmt->execute([$categoryId]);
            } else {
                $stmt = $pdo->query('SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON p.category_id = c.id ORDER BY p.created_at DESC');
            }
            $products = $stmt->fetchAll();
            echo json_encode(['success' => true, 'products' => $products]);
            break;

        case 'featured':
            $stmt = $pdo->query('SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON p.category_id = c.id ORDER BY p.created_at DESC LIMIT 8');
            $products = $stmt->fetchAll();
            echo json_encode(['success' => true, 'products' => $products]);
            break;

        case 'get':
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            $stmt = $pdo->prepare('SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.id = ? LIMIT 1');
            $stmt->execute([$id]);
            $product = $stmt->fetch();
            if (!$product) {
                echo json_encode(['success' => false, 'message' => 'Product not found.']);
                break;
            }
            echo json_encode(['success' => true, 'product' => $product]);
            break;

        case 'categories':
            $stmt = $pdo->query('SELECT * FROM categories ORDER BY name ASC');
            $categories = $stmt->fetchAll();
            echo json_encode(['success' => true, 'categories' => $categories]);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Unknown action.']);
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error while processing products.']);
}
