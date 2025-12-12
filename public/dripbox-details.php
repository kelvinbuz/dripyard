<?php
$pageTitle = 'DripBox Details';
$basePath = '..';
require_once __DIR__ . '/../backend/db.php';
require_once __DIR__ . '/../backend/auth.php';
require_once __DIR__ . '/../backend/currency.php';

$pdo = getPDO();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare('SELECT * FROM sunnydripboxes WHERE id = ? LIMIT 1');
$stmt->execute([$id]);
$box = $stmt->fetch();

$items = [];
if ($box) {
    $stmtItems = $pdo->prepare('SELECT p.*, c.name AS category_name, dp.quantity
                                FROM dripbox_products dp
                                JOIN products p ON dp.product_id = p.id
                                JOIN categories c ON p.category_id = c.id
                                WHERE dp.box_id = ?
                                ORDER BY p.created_at DESC');
    $stmtItems->execute([$id]);
    $items = $stmtItems->fetchAll();
}

include __DIR__ . '/partials/header.php';
?>

<?php if (!$box): ?>
    <div class="text-center py-5">
        <i class="bi bi-box-seam fs-1 text-muted mb-3"></i>
        <h4 class="text-muted">DripBox not found</h4>
        <p class="text-muted mb-4">This DripBox may have been removed.</p>
        <a href="dripbox.php" class="btn btn-sunny-primary">
            <i class="bi bi-arrow-left me-2"></i>Back to DripBoxes
        </a>
    </div>
<?php else: ?>
    <div class="mb-4">
        <a href="dripbox.php" class="text-decoration-none">
            <i class="bi bi-arrow-left"></i> <span class="ms-1">Back to DripBoxes</span>
        </a>
    </div>

    <div class="row g-4 align-items-start">
        <div class="col-lg-5">
            <?php if (!empty($box['image'])): ?>
                <img src="<?php echo $basePath; ?>/assets/images/dripboxes/<?php echo htmlspecialchars($box['image']); ?>"
                     alt="<?php echo htmlspecialchars($box['name']); ?>"
                     class="img-fluid rounded-4 mb-3 w-100" style="border: 1px solid #e2e8f0;">
            <?php else: ?>
                <div class="ratio ratio-4x3 bg-light rounded-4 mb-3 d-flex align-items-center justify-content-center">
                    <i class="bi bi-box-seam text-muted fs-1"></i>
                </div>
            <?php endif; ?>

            <div class="p-3 rounded-4" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h1 class="h4 mb-1"><?php echo htmlspecialchars($box['name']); ?></h1>
                        <div class="text-muted small"><?php echo htmlspecialchars($box['theme'] ?? 'Curated drip'); ?></div>
                    </div>
                    <div class="text-end">
                        <div class="fw-semibold"><?php echo CurrencyManager::formatPrice($box['price']); ?></div>
                        <div class="text-muted small">Bundle price</div>
                    </div>
                </div>

                <?php if (!empty($box['description'])): ?>
                    <div class="mt-3 text-muted">
                        <?php echo nl2br(htmlspecialchars($box['description'])); ?>
                    </div>
                <?php endif; ?>

                <div class="d-grid gap-2 mt-3">
                    <button type="button" class="btn btn-sunny-primary add-box-to-cart-btn" data-box-id="<?php echo (int)$box['id']; ?>">
                        <i class="bi bi-bag-plus me-2"></i>Get This DripBox
                    </button>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h5 mb-0">What's inside this DripBox</h2>
                <span class="text-muted small"><?php echo count($items); ?> item<?php echo count($items) === 1 ? '' : 's'; ?></span>
            </div>

            <?php if (empty($items)): ?>
                <div class="text-center py-5 bg-light rounded-4" style="border: 1px solid #e2e8f0;">
                    <i class="bi bi-exclamation-circle fs-2 text-muted mb-2"></i>
                    <h6 class="text-muted">No products added yet</h6>
                    <p class="text-muted small mb-0">This DripBox is available, but the admin hasn't selected the included products yet.</p>
                </div>
            <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($items as $product): ?>
                        <div class="col-12">
                            <div class="d-flex gap-3 p-3 rounded-4" style="border: 1px solid #e2e8f0; background: white;">
                                <div style="width: 90px;">
                                    <?php if (!empty($product['image'])): ?>
                                        <img src="<?php echo $basePath; ?>/assets/images/products/<?php echo htmlspecialchars($product['image']); ?>"
                                             alt="<?php echo htmlspecialchars($product['name']); ?>"
                                             class="img-fluid rounded-3" style="width: 90px; height: 90px; object-fit: cover; border: 1px solid #e2e8f0;">
                                    <?php else: ?>
                                        <div class="ratio ratio-1x1 bg-light rounded-3 d-flex align-items-center justify-content-center" style="width: 90px; border: 1px solid #e2e8f0;">
                                            <i class="bi bi-image text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <div class="text-muted small mb-1"><?php echo htmlspecialchars($product['category_name']); ?></div>
                                            <a href="product.php?id=<?php echo (int)$product['id']; ?>" class="text-decoration-none">
                                                <div class="fw-semibold"><?php echo htmlspecialchars($product['name']); ?></div>
                                            </a>
                                        </div>
                                        <div class="text-end">
                                            <div class="fw-semibold"><?php echo CurrencyManager::formatPrice($product['price']); ?></div>
                                            <div class="text-muted small">each</div>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center mt-2">
                                        <div class="text-muted small">Qty: <span class="fw-semibold"><?php echo (int)$product['quantity']; ?></span></div>
                                        <button class="btn btn-sm btn-sunny-primary add-to-cart-btn" data-product-id="<?php echo (int)$product['id']; ?>" data-quantity="<?php echo (int)$product['quantity']; ?>">
                                            <i class="bi bi-bag-plus me-1"></i>Add
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/partials/footer.php'; ?>
