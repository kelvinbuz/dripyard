<?php
$pageTitle = 'Product';
$basePath = '..';
require_once __DIR__ . '/../backend/db.php';
require_once __DIR__ . '/../backend/auth.php';
require_once __DIR__ . '/../backend/currency.php';

$pdo = getPDO();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare('SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.id = ? LIMIT 1');
$stmt->execute([$id]);
$product = $stmt->fetch();

include __DIR__ . '/partials/header.php';
?>
<?php if (!$product): ?>
    <p class="text-muted">Product not found.</p>
<?php else: ?>
    <div class="row g-4">
        <div class="col-md-6">
            <?php if ($product['image']): ?>
                <img src="<?php echo $basePath; ?>/assets/images/products/<?php echo htmlspecialchars($product['image']); ?>" 
                     alt="<?php echo htmlspecialchars($product['name']); ?>" 
                     class="img-fluid rounded-4 mb-3">
            <?php else: ?>
                <div class="ratio ratio-4x3 bg-light rounded-4 mb-3 d-flex align-items-center justify-content-center">
                    <i class="bi bi-image text-muted fs-1"></i>
                </div>
            <?php endif; ?>
        </div>
        <div class="col-md-6">
            <span class="badge badge-category mb-2"><?php echo htmlspecialchars($product['category_name']); ?></span>
            <h1 class="h3 mb-2"><?php echo htmlspecialchars($product['name']); ?></h1>
            <p class="price-tag fs-4 mb-3"><?php echo CurrencyManager::formatPrice($product['price']); ?></p>
            <p class="mb-3 text-muted"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
            <p class="mb-3 small">Stock: <?php echo (int)$product['stock']; ?> available</p>
            <div class="d-flex align-items-center gap-2">
                <input type="number" min="1" value="1" class="form-control w-auto" id="product-qty">
                <button class="btn btn-sunny-primary add-to-cart-btn" data-product-id="<?php echo (int)$product['id']; ?>" onclick="this.setAttribute('data-quantity', document.getElementById('product-qty').value);">
                    Add to cart
                </button>
            </div>
        </div>
    </div>
<?php endif; ?>
<?php include __DIR__ . '/partials/footer.php'; ?>
