<?php
$pageTitle = 'My Wishlist';
$basePath = '..';
require_once __DIR__ . '/../backend/db.php';
require_once __DIR__ . '/../backend/auth.php';
require_once __DIR__ . '/../backend/currency.php';

$pdo = getPDO();
$wishlist = getWishlist();

$productIds = [];
$boxIds = [];
foreach ($wishlist as $item) {
    if (($item['type'] ?? '') === 'product' && !empty($item['id'])) {
        $productIds[] = (int)$item['id'];
    } elseif (($item['type'] ?? '') === 'box' && !empty($item['id'])) {
        $boxIds[] = (int)$item['id'];
    }
}

$products = [];
$boxes = [];

if ($productIds) {
    $placeholders = implode(',', array_fill(0, count($productIds), '?'));
    $stmt = $pdo->prepare("SELECT p.*, c.name AS category_name
                            FROM products p
                            JOIN categories c ON p.category_id = c.id
                            WHERE p.id IN ($placeholders)");
    $stmt->execute($productIds);
    $products = $stmt->fetchAll();
}

if ($boxIds) {
    $placeholders = implode(',', array_fill(0, count($boxIds), '?'));
    $stmt = $pdo->prepare('SELECT * FROM sunnydripboxes WHERE id IN (' . $placeholders . ')');
    $stmt->execute($boxIds);
    $boxes = $stmt->fetchAll();
}

include __DIR__ . '/partials/header.php';
?>

<div class="row mb-4">
    <div class="col-12">
        <h1 class="h3 mb-1">My Wishlist</h1>
        <p class="text-muted mb-0">Save your favourite fits and DripBoxes to come back to later.</p>
    </div>
</div>

<?php if (empty($products) && empty($boxes)): ?>
    <div class="text-center py-5">
        <i class="bi bi-heart fs-1 text-muted mb-3"></i>
        <h5 class="text-muted">Your wishlist is empty</h5>
        <p class="text-muted small mb-4">Tap the heart icon on any product or DripBox to add it here.</p>
        <a href="shop.php" class="btn btn-sunny-primary me-2">
            <i class="bi bi-bag-plus me-1"></i>Browse Products
        </a>
        <a href="dripbox.php" class="btn btn-sunny-ghost">
            <i class="bi bi-box-seam me-1"></i>Explore DripBox
        </a>
    </div>
<?php else: ?>
    <div class="row g-4">
        <?php foreach ($products as $product): ?>
            <div class="col-6 col-md-4 col-lg-3">
                <div class="product-card">
                    <div class="product-badges">
                        <?php if ($product['stock'] < 10): ?>
                            <span class="badge badge-low-stock">Low Stock</span>
                        <?php endif; ?>
                        <span class="badge badge-new">Saved</span>
                    </div>
                    <div class="product-image-container">
                        <?php if ($product['image']): ?>
                            <img src="<?php echo $basePath; ?>/assets/images/products/<?php echo htmlspecialchars($product['image']); ?>"
                                 alt="<?php echo htmlspecialchars($product['name']); ?>"
                                 class="product-image">
                        <?php else: ?>
                            <div class="product-placeholder">
                                <i class="bi bi-image"></i>
                            </div>
                        <?php endif; ?>
                        <div class="product-overlay">
                            <div class="product-actions">
                                <button class="btn btn-icon btn-wishlist" data-product-id="<?php echo (int)$product['id']; ?>">
                                    <i class="bi bi-heart-fill"></i>
                                </button>
                                <a href="shop.php" class="btn btn-icon btn-quickview" title="View in shop">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </div>
                            <button class="btn btn-add-to-cart add-to-cart-btn" data-product-id="<?php echo (int)$product['id']; ?>">
                                <i class="bi bi-bag-plus me-2"></i>Add to Cart
                            </button>
                        </div>
                    </div>
                    <div class="product-body">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <h3 class="product-title mb-0"><?php echo htmlspecialchars($product['name']); ?></h3>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="price-tag"><?php echo CurrencyManager::formatPrice($product['price']); ?></span>
                            <span class="badge badge-category"><?php echo htmlspecialchars($product['category_name']); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <?php foreach ($boxes as $box): ?>
            <div class="col-6 col-md-4 col-lg-3">
                <div class="product-card">
                    <div class="product-badges">
                        <span class="badge badge-new">DripBox</span>
                    </div>
                    <div class="product-image-container">
                        <?php if ($box['image']): ?>
                            <img src="<?php echo $basePath; ?>/assets/images/dripboxes/<?php echo htmlspecialchars($box['image']); ?>"
                                 alt="<?php echo htmlspecialchars($box['name']); ?>"
                                 class="product-image">
                        <?php else: ?>
                            <div class="product-placeholder">
                                <i class="bi bi-box-seam"></i>
                            </div>
                        <?php endif; ?>
                        <div class="product-overlay">
                            <div class="product-actions">
                                <button class="btn btn-icon btn-wishlist" data-box-id="<?php echo (int)$box['id']; ?>">
                                    <i class="bi bi-heart-fill"></i>
                                </button>
                                <button class="btn btn-icon btn-quickview" onclick="quickView(<?php echo (int)$box['id']; ?>)">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <button class="btn btn-get-box" onclick="getBox(<?php echo (int)$box['id']; ?>)">
                                <i class="bi bi-lightning-charge me-2"></i>Get This Box
                            </button>
                        </div>
                    </div>
                    <div class="product-body">
                        <h3 class="product-title mb-1"><?php echo htmlspecialchars($box['name']); ?></h3>
                        <p class="text-muted xsmall mb-1"><?php echo htmlspecialchars($box['theme'] ?? 'Curated drip'); ?></p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="price-tag"><?php echo CurrencyManager::formatPrice($box['price']); ?></span>
                            <span class="badge badge-category">DripBox</span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/partials/footer.php'; ?>
