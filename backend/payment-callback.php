<?php
// Paystack payment verification endpoint

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

header('Content-Type: application/json');

$user = getCurrentUser();
if (!$user) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'You must be logged in to complete payment.']);
    exit;
}

$reference = $_POST['reference'] ?? '';
if (!$reference) {
    echo json_encode(['success' => false, 'message' => 'Missing payment reference.']);
    exit;
}

if (PAYSTACK_SECRET_KEY === 'YOUR_PAYSTACK_SECRET_KEY') {
    echo json_encode(['success' => false, 'message' => 'Paystack keys are not configured.']);
    exit;
}

$verifyUrl = 'https://api.paystack.co/transaction/verify/' . urlencode($reference);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $verifyUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . PAYSTACK_SECRET_KEY,
    'Cache-Control: no-cache',
]);

// Avoid hanging requests
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
curl_setopt($ch, CURLOPT_TIMEOUT, 25);

$response = curl_exec($ch);
if ($response === false) {
    $err = curl_error($ch);
    curl_close($ch);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Could not contact Paystack. ' . ($err ? ('(' . $err . ')') : '')]);
    exit;
}

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$data = json_decode($response, true);
if ($httpCode !== 200 || !$data || !($data['status'] ?? false)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Payment verification failed.']);
    exit;
}

$transaction = $data['data'] ?? null;
if (!$transaction || ($transaction['status'] ?? '') !== 'success') {
    echo json_encode(['success' => false, 'message' => 'Payment not successful.']);
    exit;
}

$amountPaid = ((int)$transaction['amount']) / 100; // Paystack amounts are in kobo

$pdo = getPDO();
$cart = getCart();

if (empty($cart)) {
    echo json_encode(['success' => false, 'message' => 'Your cart is empty.']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Recalculate cart total from database for safety
    $totalAmount = 0.0;
    $itemsForInsert = [];

    foreach ($cart as $row) {
        $type = (string)($row['item_type'] ?? 'product');
        $quantity = (int)($row['quantity'] ?? 0);
        if ($quantity <= 0) {
            continue;
        }

        if ($type === 'box') {
            $boxId = (int)($row['box_id'] ?? 0);
            $stmt = $pdo->prepare('SELECT id, name, price FROM sunnydripboxes WHERE id = ? LIMIT 1');
            $stmt->execute([$boxId]);
            $box = $stmt->fetch();
            if (!$box) {
                throw new RuntimeException('One of the DripBoxes in your cart no longer exists.');
            }

            $lineTotal = $box['price'] * $quantity;
            $totalAmount += $lineTotal;

            $itemsForInsert[] = [
                'item_type' => 'box',
                'box_id' => $boxId,
                'product_id' => null,
                'quantity' => $quantity,
                'price' => $box['price'],
                'name' => $box['name'],
            ];
        } else {
            $productId = (int)($row['product_id'] ?? 0);
            $stmt = $pdo->prepare('SELECT id, name, price, stock FROM products WHERE id = ? LIMIT 1');
            $stmt->execute([$productId]);
            $product = $stmt->fetch();

            if (!$product) {
                throw new RuntimeException('One of the products in your cart no longer exists.');
            }

            if ($quantity > (int)$product['stock']) {
                throw new RuntimeException('Not enough stock for one of the products in your cart.');
            }

            $lineTotal = $product['price'] * $quantity;
            $totalAmount += $lineTotal;

            $itemsForInsert[] = [
                'item_type' => 'product',
                'box_id' => null,
                'product_id' => $productId,
                'quantity' => $quantity,
                'price' => $product['price'],
                'name' => $product['name'],
            ];
        }
    }

    // Simple validation to ensure the amount paid matches the cart total
    if (abs($amountPaid - $totalAmount) > 1) {
        throw new RuntimeException('Paid amount does not match order total.');
    }

    $orderStmt = $pdo->prepare('INSERT INTO orders (user_id, total_amount, status, payment_reference, created_at) VALUES (?, ?, ?, ?, NOW())');
    $orderStmt->execute([
        $user['id'],
        $totalAmount,
        'paid',
        $reference,
    ]);

    $orderId = (int)$pdo->lastInsertId();

    $itemStmt = $pdo->prepare('INSERT INTO order_items (order_id, item_type, product_id, box_id, quantity, price, name) VALUES (?, ?, ?, ?, ?, ?, ?)');
    $stockStmt = $pdo->prepare('UPDATE products SET stock = stock - ? WHERE id = ?');

    foreach ($itemsForInsert as $item) {
        $itemStmt->execute([
            $orderId,
            $item['item_type'],
            $item['product_id'],
            $item['box_id'],
            $item['quantity'],
            $item['price'],
            $item['name'],
        ]);
        if ($item['item_type'] === 'product') {
            $stockStmt->execute([$item['quantity'], $item['product_id']]);
        }
    }

    $pdo->commit();

    // Clear cart after successful order
    saveCart([]);

    echo json_encode(['success' => true, 'message' => 'Order placed successfully.', 'order_id' => $orderId]);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Could not complete order: ' . $e->getMessage()]);
}
