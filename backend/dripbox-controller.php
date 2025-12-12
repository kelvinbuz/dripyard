<?php
// REST-like JSON endpoints for SunnyDripBox bundles

require_once __DIR__ . '/db.php';

header('Content-Type: application/json');

$pdo = getPDO();
$action = $_GET['action'] ?? 'list';

try {
    switch ($action) {
        case 'list':
            $stmt = $pdo->query('SELECT * FROM sunnydripboxes ORDER BY created_at DESC');
            $boxes = $stmt->fetchAll();
            echo json_encode(['success' => true, 'dripboxes' => $boxes]);
            break;

        case 'get':
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            $stmt = $pdo->prepare('SELECT * FROM sunnydripboxes WHERE id = ? LIMIT 1');
            $stmt->execute([$id]);
            $box = $stmt->fetch();
            if (!$box) {
                echo json_encode(['success' => false, 'message' => 'SunnyDripBox not found.']);
                break;
            }
            echo json_encode(['success' => true, 'box' => $box]);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Unknown action.']);
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error while processing SunnyDripBox data.']);
}
