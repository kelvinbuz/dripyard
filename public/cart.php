<?php
$pageTitle = 'Shopping Cart';
$basePath = '..';
require_once __DIR__ . '/../backend/db.php';
require_once __DIR__ . '/../backend/auth.php';
require_once __DIR__ . '/../backend/currency.php';

$pdo = getPDO();
$cart = getCart();
$products = [];
$boxes = [];
$total = 0.0;
$discount = 0.0;

if (!empty($cart)) {
    $productIds = [];
    $boxIds = [];
    foreach ($cart as $item) {
        $type = (string)($item['item_type'] ?? 'product');
        if ($type === 'box') {
            $boxIds[] = (int)($item['box_id'] ?? 0);
        } else {
            $productIds[] = (int)($item['product_id'] ?? 0);
        }
    }
    $productIds = array_values(array_unique(array_filter($productIds)));
    $boxIds = array_values(array_unique(array_filter($boxIds)));

    if (!empty($productIds)) {
        $placeholders = implode(',', array_fill(0, count($productIds), '?'));
        $stmt = $pdo->prepare("SELECT id, name, price, stock, image FROM products WHERE id IN ($placeholders)");
        $stmt->execute($productIds);
        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {
            $products[$row['id']] = $row;
        }
    }

    if (!empty($boxIds)) {
        $placeholders = implode(',', array_fill(0, count($boxIds), '?'));
        $stmt = $pdo->prepare("SELECT id, name, price, image FROM sunnydripboxes WHERE id IN ($placeholders)");
        $stmt->execute($boxIds);
        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {
            $boxes[$row['id']] = $row;
        }
    }
}

// Calculate totals
$subtotal = 0.0;
foreach ($cart as $item) {
    $type = (string)($item['item_type'] ?? 'product');
    $qty = (int)($item['quantity'] ?? 0);
    if ($qty <= 0) {
        continue;
    }
    if ($type === 'box') {
        $bid = (int)($item['box_id'] ?? 0);
        if (isset($boxes[$bid])) {
            $subtotal += $boxes[$bid]['price'] * $qty;
        }
    } else {
        $pid = (int)($item['product_id'] ?? 0);
        if (isset($products[$pid])) {
            $subtotal += $products[$pid]['price'] * $qty;
        }
    }
}

// Apply discount if applicable
if ($subtotal > 500) {
    $discount = $subtotal * 0.1; // 10% discount for orders over 500
}
$total = $subtotal - $discount;

include __DIR__ . '/partials/header.php';
?>

<!-- Cart Header -->
<section class="cart-header">
    <div class="container">
        <div class="cart-header-content">
            <div class="cart-breadcrumb">
                <a href="index.php">Home</a>
                <i class="bi bi-chevron-right"></i>
                <a href="shop.php">Shop</a>
                <i class="bi bi-chevron-right"></i>
                <span>Cart</span>
            </div>
            <h1 class="cart-title">Shopping Cart</h1>
            <p class="cart-subtitle">
                <?php if (!empty($cart)): ?>
                    You have <span class="item-count"><?php echo count($cart); ?></span> item<?php echo count($cart) > 1 ? 's' : ''; ?> in your cart
                <?php else: ?>
                    Your cart is empty - let's change that!
                <?php endif; ?>
            </p>
        </div>
    </div>
</section>

<!-- Cart Content -->
<section class="cart-content">
    <div class="container">
        <?php if (empty($cart)): ?>
            <!-- Empty Cart -->
            <div class="empty-cart">
                <div class="empty-cart-visual">
                    <div class="empty-cart-icon">
                        <i class="bi bi-cart-x"></i>
                    </div>
                    <div class="floating-items">
                        <div class="floating-item item-1">
                            <i class="bi bi-tshirt"></i>
                        </div>
                        <div class="floating-item item-2">
                            <i class="bi bi-bag"></i>
                        </div>
                        <div class="floating-item item-3">
                            <i class="bi bi-gem"></i>
                        </div>
                    </div>
                </div>
                <div class="empty-cart-content">
                    <h2>Your cart is empty</h2>
                    <p>Looks like you haven't added any items to your cart yet. Start shopping and fill it with amazing streetwear!</p>
                    <div class="empty-cart-actions">
                        <a href="shop.php" class="btn btn-primary btn-lg">
                            <i class="bi bi-bag-plus me-2"></i>Start Shopping
                        </a>
                        <a href="dripbox.php" class="btn btn-outline-secondary btn-lg">
                            <i class="bi bi-box-seam me-2"></i>View DripBoxes
                        </a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="cart-layout">
                <!-- Cart Items -->
                <div class="cart-items-section">
                    <div class="section-header">
                        <h2>Cart Items</h2>
                        <button class="btn btn-outline-danger btn-sm" onclick="clearCart()">
                            <i class="bi bi-trash me-1"></i>Clear Cart
                        </button>
                    </div>
                    
                    <div class="cart-items">
                        <?php foreach ($cart as $index => $item): ?>
                            <?php
                            $type = (string)($item['item_type'] ?? 'product');
                            $qty = (int)($item['quantity'] ?? 0);
                            if ($qty <= 0) continue;

                            $pid = (int)($item['product_id'] ?? 0);
                            $bid = (int)($item['box_id'] ?? 0);

                            if ($type === 'box') {
                                if (!isset($boxes[$bid])) continue;
                                $box = $boxes[$bid];
                                $lineTotal = $box['price'] * $qty;
                            } else {
                                if (!isset($products[$pid])) continue;
                                $prod = $products[$pid];
                                $lineTotal = $prod['price'] * $qty;
                            }
                            ?>
                            <div class="cart-item" data-index="<?php echo $index; ?>">
                                <div class="cart-item-image">
                                    <?php if ($type === 'box'): ?>
                                        <?php if (!empty($box['image'])): ?>
                                            <img src="<?php echo $basePath; ?>/assets/images/dripboxes/<?php echo htmlspecialchars($box['image']); ?>" 
                                                 alt="<?php echo htmlspecialchars($box['name']); ?>">
                                        <?php else: ?>
                                            <div class="cart-item-placeholder">
                                                <i class="bi bi-box-seam"></i>
                                            </div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <?php if (!empty($prod['image'])): ?>
                                            <img src="<?php echo $basePath; ?>/assets/images/products/<?php echo htmlspecialchars($prod['image']); ?>" 
                                                 alt="<?php echo htmlspecialchars($prod['name']); ?>">
                                        <?php else: ?>
                                            <div class="cart-item-placeholder">
                                                <i class="bi bi-image"></i>
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="cart-item-details">
                                    <h3 class="cart-item-name"><?php echo htmlspecialchars($type === 'box' ? $box['name'] : $prod['name']); ?></h3>
                                    <div class="cart-item-meta">
                                        <?php if ($type === 'box'): ?>
                                            <span class="stock-info">
                                                <i class="bi bi-box-seam"></i>
                                                DripBox
                                            </span>
                                            <span class="item-id">BOX: #<?php echo str_pad($bid, 6, '0', STR_PAD_LEFT); ?></span>
                                        <?php else: ?>
                                            <span class="stock-info">
                                                <i class="bi bi-check-circle"></i>
                                                <?php echo (int)$prod['stock']; ?> in stock
                                            </span>
                                            <span class="item-id">SKU: #<?php echo str_pad($pid, 6, '0', STR_PAD_LEFT); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="cart-item-price">
                                        <?php echo CurrencyManager::formatPrice($type === 'box' ? $box['price'] : $prod['price']); ?>
                                    </div>
                                </div>
                                
                                <div class="cart-item-quantity">
                                    <div class="quantity-controls">
                                        <button class="btn-quantity btn-decrease" onclick="updateQuantity(<?php echo json_encode($type); ?>, <?php echo (int)($type === 'box' ? $bid : $pid); ?>, <?php echo $qty - 1; ?>)">
                                            <i class="bi bi-dash"></i>
                                        </button>
                                        <input type="number" class="quantity-input" value="<?php echo $qty; ?>" 
                                               min="1" <?php echo $type === 'box' ? '' : 'max="' . (int)$prod['stock'] . '"'; ?>
                                               onchange="updateQuantity(<?php echo json_encode($type); ?>, <?php echo (int)($type === 'box' ? $bid : $pid); ?>, this.value)">
                                        <button class="btn-quantity btn-increase" onclick="updateQuantity(<?php echo json_encode($type); ?>, <?php echo (int)($type === 'box' ? $bid : $pid); ?>, <?php echo $qty + 1; ?>)">
                                            <i class="bi bi-plus"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="cart-item-subtotal">
                                    <span class="subtotal-amount"><?php echo CurrencyManager::formatPrice($lineTotal); ?></span>
                                </div>
                                
                                <div class="cart-item-actions">
                                    <?php if ($type !== 'box'): ?>
                                        <button class="btn btn-icon btn-wishlist" title="Save for later" data-product-id="<?php echo $pid; ?>">
                                            <i class="bi bi-heart"></i>
                                        </button>
                                    <?php endif; ?>
                                    <button class="btn btn-icon btn-remove" title="Remove item" onclick="removeItem(<?php echo json_encode($type); ?>, <?php echo (int)($type === 'box' ? $bid : $pid); ?>)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Order Summary -->
                <div class="cart-summary-section">
                    <div class="summary-card">
                        <div class="summary-header">
                            <h3>Order Summary</h3>
                        </div>
                        
                        <div class="summary-content">
                            <div class="summary-row">
                                <span class="summary-label">Subtotal</span>
                                <span class="summary-value"><?php echo CurrencyManager::formatPrice($subtotal); ?></span>
                            </div>
                            
                            <div class="summary-row">
                                <span class="summary-label">Shipping</span>
                                <span class="summary-value">Free</span>
                            </div>
                            
                            <?php if ($discount > 0): ?>
                                <div class="summary-row discount-row">
                                    <span class="summary-label">Discount (10%)</span>
                                    <span class="summary-value text-success">-<?php echo CurrencyManager::formatPrice($discount); ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <div class="summary-divider"></div>
                            
                            <div class="summary-row total-row">
                                <span class="summary-label">Total</span>
                                <span class="summary-value"><?php echo CurrencyManager::formatPrice($total); ?></span>
                            </div>
                            
                            <?php if ($discount > 0): ?>
                                <div class="discount-notice">
                                    <i class="bi bi-gift"></i>
                                    You saved <?php echo CurrencyManager::formatPrice($discount); ?> on this order!
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="summary-actions">
                            <a href="checkout.php" class="btn btn-primary btn-lg w-100">
                                <i class="bi bi-lock me-2"></i>Secure Checkout
                            </a>
                            <a href="shop.php" class="btn btn-outline-secondary w-100">
                                <i class="bi bi-arrow-left me-2"></i>Continue Shopping
                            </a>
                        </div>
                        
                        <div class="security-badges">
                            <div class="security-badge">
                                <i class="bi bi-shield-check"></i>
                                <span>Secure Payment</span>
                            </div>
                            <div class="security-badge">
                                <i class="bi bi-truck"></i>
                                <span>Fast Delivery</span>
                            </div>
                            <div class="security-badge">
                                <i class="bi bi-arrow-repeat"></i>
                                <span>Easy Returns</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Promo Code -->
                    <div class="promo-card">
                        <h4>Have a promo code?</h4>
                        <div class="promo-form">
                            <input type="text" class="form-control" placeholder="Enter promo code" id="promoCode">
                            <button class="btn btn-outline-primary" onclick="applyPromo()">Apply</button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Recommended Products -->
<?php if (!empty($cart)): ?>
    <section class="recommended-products">
        <div class="container">
            <div class="section-header text-center">
                <h2>You might also like</h2>
                <p>Complete your look with these amazing pieces</p>
            </div>
            <div class="recommended-grid">
                <?php
                // Get some random products as recommendations
                $recStmt = $pdo->query("SELECT id, name, price, image FROM products WHERE id NOT IN (" . implode(',', array_column($cart, 'product_id')) . ") ORDER BY RAND() LIMIT 4");
                $recommended = $recStmt->fetchAll();
                ?>
                <?php foreach ($recommended as $product): ?>
                    <div class="recommended-card">
                        <div class="recommended-image">
                            <?php if ($product['image']): ?>
                                <img src="<?php echo $basePath; ?>/assets/images/products/<?php echo htmlspecialchars($product['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <?php else: ?>
                                <div class="recommended-placeholder">
                                    <i class="bi bi-image"></i>
                                </div>
                            <?php endif; ?>
                            <button class="btn btn-icon btn-add-recommended" onclick="addToCart(<?php echo (int)$product['id']; ?>)">
                                <i class="bi bi-bag-plus"></i>
                            </button>
                        </div>
                        <div class="recommended-info">
                            <h4><?php echo htmlspecialchars($product['name']); ?></h4>
                            <div class="recommended-price"><?php echo CurrencyManager::formatPrice($product['price']); ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<style>
/* Cart Header */
.cart-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 3rem 0;
    color: white;
}

.cart-breadcrumb {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 1rem;
    font-size: 0.875rem;
    opacity: 0.8;
}

.cart-breadcrumb a {
    color: white;
    text-decoration: none;
}

.cart-breadcrumb a:hover {
    text-decoration: underline;
}

.cart-title {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.cart-subtitle {
    font-size: 1.125rem;
    opacity: 0.9;
}

.item-count {
    font-weight: 600;
    color: #fbbf24;
}

/* Cart Content */
.cart-content {
    padding: 3rem 0;
    background: #f8fafc;
    min-height: 60vh;
}

/* Empty Cart */
.empty-cart {
    text-align: center;
    padding: 4rem 2rem;
}

.empty-cart-visual {
    position: relative;
    height: 200px;
    margin-bottom: 3rem;
}

.empty-cart-icon {
    font-size: 6rem;
    color: #d1d5db;
    margin-bottom: 1rem;
}

.floating-items {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
}

.floating-item {
    position: absolute;
    width: 50px;
    height: 50px;
    background: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    color: #667eea;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.item-1 {
    top: 20%;
    left: 20%;
    animation: float 6s ease-in-out infinite;
}

.item-2 {
    top: 50%;
    right: 30%;
    animation: float 8s ease-in-out infinite reverse;
}

.item-3 {
    bottom: 20%;
    left: 40%;
    animation: float 10s ease-in-out infinite;
}

.empty-cart-content h2 {
    font-size: 2rem;
    font-weight: 600;
    margin-bottom: 1rem;
    color: #374151;
}

.empty-cart-content p {
    color: #6b7280;
    margin-bottom: 2rem;
    max-width: 500px;
    margin-left: auto;
    margin-right: auto;
}

.empty-cart-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}

/* Cart Layout */
.cart-layout {
    display: grid;
    grid-template-columns: 1fr 400px;
    gap: 2rem;
}

/* Cart Items Section */
.cart-items-section {
    background: white;
    border-radius: 1rem;
    padding: 2rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #e5e7eb;
}

.section-header h2 {
    font-size: 1.5rem;
    font-weight: 600;
    color: #1f2937;
}

/* Cart Items */
.cart-items {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.cart-item {
    display: grid;
    grid-template-columns: 120px 1fr auto auto auto;
    gap: 1.5rem;
    align-items: center;
    padding: 1.5rem;
    background: #f8fafc;
    border-radius: 1rem;
    transition: all 0.3s ease;
}

.cart-item:hover {
    background: #f1f5f9;
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.cart-item-image {
    width: 120px;
    height: 120px;
    border-radius: 0.5rem;
    overflow: hidden;
}

.cart-item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.cart-item-placeholder {
    width: 100%;
    height: 100%;
    background: #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    color: #9ca3af;
}

.cart-item-details h3 {
    font-size: 1.125rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: #1f2937;
}

.cart-item-meta {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    margin-bottom: 0.5rem;
}

.stock-info {
    color: #10b981;
    font-size: 0.875rem;
    font-weight: 500;
}

.item-id {
    color: #6b7280;
    font-size: 0.75rem;
}

.cart-item-price {
    font-size: 1.125rem;
    font-weight: 600;
    color: #667eea;
}

/* Quantity Controls */
.quantity-controls {
    display: flex;
    align-items: center;
    gap: 0;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    overflow: hidden;
}

.btn-quantity {
    background: white;
    border: none;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.btn-quantity:hover {
    background: #f3f4f6;
}

.quantity-input {
    width: 60px;
    height: 40px;
    border: none;
    text-align: center;
    font-weight: 600;
    background: white;
}

.cart-item-subtotal {
    text-align: right;
}

.subtotal-amount {
    font-size: 1.25rem;
    font-weight: 700;
    color: #1f2937;
}

.cart-item-actions {
    display: flex;
    gap: 0.5rem;
}

.btn-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: none;
    background: white;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.btn-icon:hover {
    transform: scale(1.1);
}

.btn-wishlist:hover {
    background: #fef2f2;
    color: #ef4444;
}

.btn-remove:hover {
    background: #fef2f2;
    color: #ef4444;
}

/* Summary Section */
.cart-summary-section {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.summary-card {
    background: white;
    border-radius: 1rem;
    padding: 2rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.summary-header h3 {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 1.5rem;
    color: #1f2937;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.summary-label {
    color: #6b7280;
    font-weight: 500;
}

.summary-value {
    font-weight: 600;
    color: #1f2937;
}

.discount-row .summary-value {
    color: #10b981;
}

.summary-divider {
    height: 1px;
    background: #e5e7eb;
    margin: 1rem 0;
}

.total-row {
    margin-top: 1rem;
}

.total-row .summary-label {
    font-size: 1.125rem;
    font-weight: 600;
    color: #1f2937;
}

.total-row .summary-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: #667eea;
}

.discount-notice {
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    border-radius: 0.5rem;
    padding: 0.75rem;
    margin-top: 1rem;
    color: #166534;
    font-size: 0.875rem;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.summary-actions {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    margin-top: 2rem;
}

.security-badges {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 1px solid #e5e7eb;
}

.security-badge {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.25rem;
    font-size: 0.75rem;
    color: #6b7280;
}

.security-badge i {
    font-size: 1.25rem;
    color: #10b981;
}

/* Promo Code */
.promo-card {
    background: white;
    border-radius: 1rem;
    padding: 1.5rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.promo-card h4 {
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 1rem;
    color: #1f2937;
}

.promo-form {
    display: flex;
    gap: 0.5rem;
}

.promo-form input {
    flex: 1;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    padding: 0.75rem;
}

/* Recommended Products */
.recommended-products {
    padding: 4rem 0;
    background: white;
}

.recommended-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
    margin-top: 3rem;
}

.recommended-card {
    background: #f8fafc;
    border-radius: 1rem;
    overflow: hidden;
    transition: all 0.3s ease;
    position: relative;
}

.recommended-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
}

.recommended-image {
    position: relative;
    height: 200px;
    overflow: hidden;
}

.recommended-image img,
.recommended-placeholder {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.recommended-placeholder {
    background: #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    color: #9ca3af;
}

.btn-add-recommended {
    position: absolute;
    top: 1rem;
    right: 1rem;
    background: white;
    border: none;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    opacity: 0;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}

.recommended-card:hover .btn-add-recommended {
    opacity: 1;
}

.btn-add-recommended:hover {
    background: #667eea;
    color: white;
    transform: scale(1.1);
}

.recommended-info {
    padding: 1rem;
}

.recommended-info h4 {
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: #1f2937;
}

.recommended-price {
    font-size: 1.125rem;
    font-weight: 700;
    color: #667eea;
}

/* Responsive */
@media (max-width: 1024px) {
    .cart-layout {
        grid-template-columns: 1fr;
    }
    
    .cart-summary-section {
        order: -1;
    }
}

@media (max-width: 768px) {
    .cart-title {
        font-size: 2rem;
    }
    
    .cart-item {
        grid-template-columns: 80px 1fr;
        gap: 1rem;
    }
    
    .cart-item-image {
        width: 80px;
        height: 80px;
        grid-column: 1;
        grid-row: 1 / 3;
    }
    
    .cart-item-details {
        grid-column: 2;
        grid-row: 1;
    }
    
    .cart-item-quantity {
        grid-column: 2;
        grid-row: 2;
    }
    
    .cart-item-subtotal {
        display: none;
    }
    
    .cart-item-actions {
        grid-column: 2;
        grid-row: 3;
        justify-content: flex-start;
    }
    
    .recommended-grid {
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
    }
    
    .security-badges {
        grid-template-columns: 1fr;
        gap: 0.5rem;
    }
}

/* Animations */
@keyframes float {
    0%, 100% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(-20px);
    }
}
</style>

<script>
// Cart functionality
function updateQuantity(itemType, id, newQuantity) {
    if (newQuantity < 1) return;
    
    const basePath = window.DRIPYARD && window.DRIPYARD.basePath ? window.DRIPYARD.basePath : '..';
    
    fetch(`${basePath}/backend/cart-controller.php`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams(Object.assign({
            action: 'update',
            item_type: itemType,
            quantity: newQuantity
        }, itemType === 'box' ? { box_id: id } : { product_id: id }))
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            console.warn('Could not update cart:', data.message);
        }
    })
    .catch(error => {
        console.error('Could not update cart.', error);
    });
}

function removeItem(itemType, id) {
    if (confirm('Are you sure you want to remove this item?')) {
        const basePath = window.DRIPYARD && window.DRIPYARD.basePath ? window.DRIPYARD.basePath : '..';
        
        fetch(`${basePath}/backend/cart-controller.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams(Object.assign({
                action: 'remove',
                item_type: itemType
            }, itemType === 'box' ? { box_id: id } : { product_id: id }))
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                console.warn('Could not remove item:', data.message);
            }
        })
        .catch(error => {
            console.error('Could not remove item.', error);
        });
    }
}

function clearCart() {
    if (confirm('Are you sure you want to clear your entire cart?')) {
        const basePath = window.DRIPYARD && window.DRIPYARD.basePath ? window.DRIPYARD.basePath : '..';
        
        fetch(`${basePath}/backend/cart-controller.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'clear'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                console.warn('Could not clear cart:', data.message);
            }
        })
        .catch(error => {
            console.error('Could not clear cart.', error);
        });
    }
}

function addToCart(productId) {
    const basePath = window.DRIPYARD && window.DRIPYARD.basePath ? window.DRIPYARD.basePath : '..';
    
    fetch(`${basePath}/backend/cart-controller.php`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            action: 'add',
            product_id: productId,
            quantity: 1
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update cart count
            const cartCount = document.getElementById('cart-count');
            if (cartCount) {
                cartCount.textContent = data.count || 0;
            }
            // Show success message
            const successMsg = document.createElement('div');
            successMsg.className = 'alert alert-success alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3';
            successMsg.style.zIndex = '9999';
            successMsg.innerHTML = `
                <i class="bi bi-check-circle me-2"></i>Added to cart!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(successMsg);
            setTimeout(() => successMsg.remove(), 3000);
        } else {
            console.warn('Could not add to cart:', data.message);
        }
    })
    .catch(error => {
        console.error('Could not add to cart.', error);
    });
}

function applyPromo() {
    const code = document.getElementById('promoCode').value;
    if (!code) return;
    
    // Implement promo code logic (placeholder without alert)
    console.info('Promo code entered (no logic implemented yet):', code);
}

// Add animation to cart items on load
document.addEventListener('DOMContentLoaded', function() {
    const items = document.querySelectorAll('.cart-item');
    items.forEach((item, index) => {
        item.style.opacity = '0';
        item.style.transform = 'translateY(20px)';
        setTimeout(() => {
            item.style.transition = 'all 0.3s ease';
            item.style.opacity = '1';
            item.style.transform = 'translateY(0)';
        }, index * 100);
    });
    
    // Set base path for cart operations
    if (!window.DRIPYARD) {
        window.DRIPYARD = {};
    }
    window.DRIPYARD.basePath = '..';
});
</script>

<?php include __DIR__ . '/partials/footer.php'; ?>
