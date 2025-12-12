<?php
$pageTitle = 'Secure Checkout';
$basePath = '..';
require_once __DIR__ . '/../backend/db.php';
require_once __DIR__ . '/../backend/auth.php';
require_once __DIR__ . '/../backend/currency.php';

requireLogin();

$pdo = getPDO();
$user = getCurrentUser();
$cart = getCart();
$products = [];
$boxes = [];
$total = 0.0;
$discount = 0.0;
$shipping = 0.0;

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
        $stmt = $pdo->prepare("SELECT id, name, price, image FROM products WHERE id IN ($placeholders)");
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

// Free shipping for orders over 300
if ($subtotal < 300) {
    $shipping = 25.0;
}

$total = $subtotal - $discount + $shipping;

include __DIR__ . '/partials/header.php';
?>

<!-- Checkout Header -->
<section class="checkout-header">
    <div class="container">
        <div class="checkout-progress">
            <div class="progress-step active">
                <div class="step-number">1</div>
                <div class="step-label">Cart</div>
            </div>
            <div class="progress-line"></div>
            <div class="progress-step active">
                <div class="step-number">2</div>
                <div class="step-label">Checkout</div>
            </div>
            <div class="progress-line"></div>
            <div class="progress-step">
                <div class="step-number">3</div>
                <div class="step-label">Payment</div>
            </div>
            <div class="progress-line"></div>
            <div class="progress-step">
                <div class="step-number">4</div>
                <div class="step-label">Complete</div>
            </div>
        </div>
        <h1 class="checkout-title">Secure Checkout</h1>
        <p class="checkout-subtitle">Complete your order in just a few steps</p>
    </div>
</section>

<!-- Checkout Content -->
<section class="checkout-content">
    <div class="container">
        <?php if (empty($cart) || (empty($products) && empty($boxes))): ?>
            <!-- Empty Checkout -->
            <div class="empty-checkout">
                <div class="empty-checkout-icon">
                    <i class="bi bi-cart-x"></i>
                </div>
                <h2>Your cart is empty</h2>
                <p>Add some amazing streetwear pieces to your cart before proceeding to checkout.</p>
                <div class="empty-checkout-actions">
                    <a href="shop.php" class="btn btn-primary btn-lg">
                        <i class="bi bi-bag-plus me-2"></i>Continue Shopping
                    </a>
                    <a href="dripbox.php" class="btn btn-outline-secondary btn-lg">
                        <i class="bi bi-box-seam me-2"></i>View DripBoxes
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="checkout-layout">
                <!-- Checkout Form -->
                <div class="checkout-form-section">
                    <!-- Contact Information -->
                    <div class="checkout-card">
                        <div class="card-header">
                            <h3>
                                <i class="bi bi-person me-2"></i>
                                Contact Information
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="contact-info">
                                <div class="contact-avatar">
                                    <?php if ($user['profile_image']): ?>
                                        <img src="<?php echo $basePath; ?>/assets/images/profiles/<?php echo htmlspecialchars($user['profile_image']); ?>" 
                                             alt="<?php echo htmlspecialchars($user['name']); ?>">
                                    <?php else: ?>
                                        <div class="avatar-placeholder">
                                            <?php echo strtoupper(substr($user['name'], 0, 2)); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="contact-details">
                                    <div class="contact-name"><?php echo htmlspecialchars($user['name']); ?></div>
                                    <div class="contact-email"><?php echo htmlspecialchars($user['email']); ?></div>
                                    <a href="dashboard.php" class="edit-profile-link">
                                        <i class="bi bi-pencil me-1"></i>Edit Profile
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Shipping Information -->
                    <div class="checkout-card">
                        <div class="card-header">
                            <h3>
                                <i class="bi bi-truck me-2"></i>
                                Shipping Information
                            </h3>
                        </div>
                        <div class="card-body">
                            <form id="shippingForm">
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="firstName">First Name</label>
                                        <input type="text" id="firstName" name="firstName" class="form-control" 
                                               value="<?php echo explode(' ', $user['name'])[0]; ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="lastName">Last Name</label>
                                        <input type="text" id="lastName" name="lastName" class="form-control" 
                                               value="<?php echo explode(' ', $user['name'])[1] ?? ''; ?>" required>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="phone">Phone Number</label>
                                    <input type="tel" id="phone" name="phone" class="form-control" 
                                           placeholder="+1 555 123 4567" required>
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="country">Country</label>
                                        <input type="text" id="country" name="country" class="form-control"
                                               placeholder="e.g. Ghana" value="Ghana" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="postalCode">Postal / ZIP Code</label>
                                        <input type="text" id="postalCode" name="postalCode" class="form-control"
                                               placeholder="e.g. 00233" required>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="digitalAddress">GhanaPost Digital Address Code</label>
                                    <input type="text" id="digitalAddress" name="digitalAddress" class="form-control"
                                           placeholder="e.g. GA-123-4567" required>
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="city">City</label>
                                        <input type="text" id="city" name="city" class="form-control" 
                                               placeholder="Accra" required>
                                    </div>
                                    <div class="form-group" id="ghana-region-group">
                                        <label for="region">Region (Ghana)</label>
                                        <select id="region" name="region" class="form-control" required>
                                            <option value="">Select Region</option>
                                            <option value="Greater Accra">Greater Accra</option>
                                            <option value="Ashanti">Ashanti</option>
                                            <option value="Western">Western</option>
                                            <option value="Central">Central</option>
                                            <option value="Eastern">Eastern</option>
                                            <option value="Northern">Northern</option>
                                        </select>
                                    </div>
                                    <div class="form-group" id="state-province-group" style="display:none;">
                                        <label for="stateProvince">State / Province / Region</label>
                                        <input type="text" id="stateProvince" name="stateProvince" class="form-control"
                                               placeholder="e.g. California" value="">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="notes">Delivery Notes (Optional)</label>
                                    <textarea id="notes" name="notes" class="form-control" rows="3" 
                                              placeholder="Any special delivery instructions?"></textarea>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Payment -->
                    <div class="checkout-card">
                        <div class="card-header">
                            <h3>
                                <i class="bi bi-credit-card me-2"></i>
                                Payment
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="payment-security" style="border-top: none; padding-top: 0;">
                                <div class="security-item">
                                    <i class="bi bi-shield-check"></i>
                                    <span>SSL Encrypted</span>
                                </div>
                                <div class="security-item">
                                    <i class="bi bi-lock"></i>
                                    <span>Secure Payment</span>
                                </div>
                                <div class="security-item">
                                    <i class="bi bi-patch-check"></i>
                                    <span>PCI Compliant</span>
                                </div>
                            </div>
                            <div class="text-muted small mt-3">
                                You’ll be redirected to Paystack to complete your payment.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="checkout-summary-section">
                    <div class="summary-card">
                        <div class="card-header">
                            <h3>
                                <i class="bi bi-bag me-2"></i>
                                Order Summary
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="order-items">
                                <?php foreach ($cart as $item): ?>
                                    <?php
                                    $type = (string)($item['item_type'] ?? 'product');
                                    $qty = (int)($item['quantity'] ?? 0);
                                    if ($qty <= 0) continue;

                                    $pid = (int)($item['product_id'] ?? 0);
                                    $bid = (int)($item['box_id'] ?? 0);

                                    if ($type === 'box') {
                                        if (!isset($boxes[$bid])) continue;
                                        $box = $boxes[$bid];
                                        $name = $box['name'];
                                        $image = $box['image'] ?? '';
                                        $imageFolder = 'dripboxes';
                                        $lineTotal = $box['price'] * $qty;
                                    } else {
                                        if (!isset($products[$pid])) continue;
                                        $prod = $products[$pid];
                                        $name = $prod['name'];
                                        $image = $prod['image'] ?? '';
                                        $imageFolder = 'products';
                                        $lineTotal = $prod['price'] * $qty;
                                    }
                                    ?>
                                    <div class="order-item">
                                        <div class="order-item-image">
                                            <?php if (!empty($image)): ?>
                                                <img src="<?php echo $basePath; ?>/assets/images/<?php echo $imageFolder; ?>/<?php echo htmlspecialchars($image); ?>" 
                                                     alt="<?php echo htmlspecialchars($name); ?>">
                                            <?php else: ?>
                                                <div class="order-item-placeholder">
                                                    <i class="bi <?php echo $type === 'box' ? 'bi-box-seam' : 'bi-image'; ?>"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="order-item-details">
                                            <div class="order-item-name"><?php echo htmlspecialchars($name); ?></div>
                                            <div class="order-item-quantity">Quantity: <?php echo $qty; ?></div>
                                        </div>
                                        <div class="order-item-price">
                                            <?php echo CurrencyManager::formatPrice($lineTotal); ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="order-totals">
                                <div class="total-row">
                                    <span class="total-label">Subtotal</span>
                                    <span class="total-value"><?php echo CurrencyManager::formatPrice($subtotal); ?></span>
                                </div>
                                <div class="total-row">
                                    <span class="total-label">Shipping</span>
                                    <span class="total-value">
                                        <?php if ($shipping === 0): ?>
                                            <span class="free-shipping">FREE</span>
                                        <?php else: ?>
                                            <?php echo CurrencyManager::formatPrice($shipping); ?>
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <?php if ($discount > 0): ?>
                                    <div class="total-row discount-row">
                                        <span class="total-label">Discount (10%)</span>
                                        <span class="total-value text-success">-<?php echo CurrencyManager::formatPrice($discount); ?></span>
                                    </div>
                                <?php endif; ?>
                                <div class="total-divider"></div>
                                <div class="total-row final-total">
                                    <span class="total-label">Total</span>
                                    <span class="total-value"><?php echo CurrencyManager::formatPrice($total); ?></span>
                                </div>
                            </div>

                            <?php if ($shipping > 0): ?>
                                <div class="shipping-notice">
                                    <i class="bi bi-info-circle"></i>
                                    <span>Add <?php echo CurrencyManager::formatPrice(300 - $subtotal); ?> more for free shipping!</span>
                                </div>
                            <?php endif; ?>

                            <div class="checkout-actions">
                                <button id="pay-now-btn" class="btn btn-primary btn-lg w-100">
                                    <i class="bi bi-lock me-2"></i>
                                    Complete Payment - <?php echo CurrencyManager::formatPrice($total); ?>
                                </button>
                                <div class="back-to-cart">
                                    <a href="cart.php">
                                        <i class="bi bi-arrow-left me-1"></i>
                                        Back to Cart
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Trust Badges -->
                    <div class="trust-badges">
                        <div class="trust-badge">
                            <i class="bi bi-truck"></i>
                            <span>Fast Delivery</span>
                        </div>
                        <div class="trust-badge">
                            <i class="bi bi-shield-check"></i>
                            <span>Secure Payment</span>
                        </div>
                        <div class="trust-badge">
                            <i class="bi bi-arrow-repeat"></i>
                            <span>Easy Returns</span>
                        </div>
                        <div class="trust-badge">
                            <i class="bi bi-headset"></i>
                            <span>24/7 Support</span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<style>
/* Checkout Header */
.checkout-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 3rem 0;
    color: white;
}

.checkout-progress {
    display: flex;
    align-items: center;
    justify-content: center;
    max-width: 600px;
    margin: 0 auto 3rem;
}

.progress-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    position: relative;
}

.step-number {
    width: 40px;
    height: 40px;
    background: rgba(255, 255, 255, 0.2);
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    transition: all 0.3s ease;
}

.progress-step.active .step-number {
    background: white;
    color: #667eea;
    border-color: white;
}

.step-label {
    font-size: 0.875rem;
    opacity: 0.8;
}

.progress-step.active .step-label {
    opacity: 1;
    font-weight: 600;
}

.progress-line {
    flex: 1;
    height: 2px;
    background: rgba(255, 255, 255, 0.3);
    margin: 0 1rem;
    position: relative;
    top: -20px;
}

.checkout-title {
    font-size: 2.5rem;
    font-weight: 700;
    text-align: center;
    margin-bottom: 0.5rem;
}

.checkout-subtitle {
    text-align: center;
    opacity: 0.9;
}

/* Checkout Content */
.checkout-content {
    padding: 3rem 0;
    background: #f8fafc;
    min-height: 60vh;
}

/* Empty Checkout */
.empty-checkout {
    text-align: center;
    padding: 4rem 2rem;
    background: white;
    border-radius: 1rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    max-width: 600px;
    margin: 0 auto;
}

.empty-checkout-icon {
    font-size: 5rem;
    color: #d1d5db;
    margin-bottom: 2rem;
}

.empty-checkout h2 {
    font-size: 1.875rem;
    font-weight: 600;
    margin-bottom: 1rem;
    color: #374151;
}

.empty-checkout p {
    color: #6b7280;
    margin-bottom: 2rem;
}

.empty-checkout-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}

/* Checkout Layout */
.checkout-layout {
    display: grid;
    grid-template-columns: 1fr 400px;
    gap: 2rem;
}

/* Checkout Cards */
.checkout-card {
    background: white;
    border-radius: 1rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    margin-bottom: 2rem;
    overflow: hidden;
}

.card-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 1.5rem;
}

.card-header h3 {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 600;
    display: flex;
    align-items: center;
}

.card-body {
    padding: 2rem;
}

/* Contact Information */
.contact-info {
    display: flex;
    align-items: center;
    gap: 1.5rem;
}

.contact-avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    overflow: hidden;
}

.contact-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar-placeholder {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    font-weight: 700;
}

.contact-details {
    flex: 1;
}

.contact-name {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 0.25rem;
    color: #1f2937;
}

.contact-email {
    color: #6b7280;
    margin-bottom: 0.5rem;
}

.edit-profile-link {
    color: #667eea;
    text-decoration: none;
    font-size: 0.875rem;
    font-weight: 500;
}

.edit-profile-link:hover {
    text-decoration: underline;
}

/* Form Styles */
.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: #374151;
}

.form-control {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    font-size: 1rem;
    transition: all 0.2s ease;
}

.form-control:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

/* Payment Methods */
.payment-methods {
    margin-bottom: 2rem;
}

.payment-method {
    border: 2px solid #e5e7eb;
    border-radius: 0.75rem;
    padding: 1rem;
    margin-bottom: 1rem;
    transition: all 0.2s ease;
    cursor: pointer;
}

.payment-method.active {
    border-color: #667eea;
    background: #f0f4ff;
}

.payment-radio {
    display: flex;
    align-items: center;
}

.payment-radio input[type="radio"] {
    display: none;
}

.payment-radio label {
    display: flex;
    align-items: center;
    gap: 1rem;
    cursor: pointer;
    margin: 0;
    width: 100%;
}

.payment-icon {
    flex: 0 0 50px;
    width: 50px;
    height: 50px;
    background: white;
    border-radius: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: #667eea;
    border: 1px solid #e5e7eb;
}

.payment-info {
    flex: 1 1 auto;
    min-width: 220px;
}

.payment-name {
    font-weight: 600;
    margin-bottom: 0.25rem;
    color: #1f2937;
}

.payment-description {
    font-size: 0.875rem;
    color: #6b7280;
    white-space: normal;
}

/* Payment Security */
.payment-security {
    display: flex;
    gap: 2rem;
    padding-top: 1rem;
    border-top: 1px solid #e5e7eb;
}

.security-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    color: #6b7280;
}

.security-item i {
    color: #10b981;
}

/* Order Summary */
.order-items {
    margin-bottom: 2rem;
}

.order-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem 0;
    border-bottom: 1px solid #f3f4f6;
}

.order-item:last-child {
    border-bottom: none;
}

.order-item-image {
    width: 60px;
    height: 60px;
    border-radius: 0.5rem;
    overflow: hidden;
}

.order-item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.order-item-placeholder {
    width: 100%;
    height: 100%;
    background: #f3f4f6;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #9ca3af;
}

.order-item-details {
    flex: 1;
}

.order-item-name {
    font-weight: 600;
    margin-bottom: 0.25rem;
    color: #1f2937;
}

.order-item-quantity {
    font-size: 0.875rem;
    color: #6b7280;
}

.order-item-price {
    font-weight: 600;
    color: #1f2937;
}

/* Order Totals */
.order-totals {
    margin-bottom: 2rem;
}

.total-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.total-label {
    color: #6b7280;
    font-weight: 500;
}

.total-value {
    font-weight: 600;
    color: #1f2937;
}

.free-shipping {
    color: #10b981;
    font-weight: 600;
}

.discount-row .total-value {
    color: #10b981;
}

.total-divider {
    height: 1px;
    background: #e5e7eb;
    margin: 1rem 0;
}

.final-total {
    margin-top: 1rem;
}

.final-total .total-label {
    font-size: 1.125rem;
    font-weight: 600;
    color: #1f2937;
}

.final-total .total-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: #667eea;
}

/* Shipping Notice */
.shipping-notice {
    background: #f0f4ff;
    border: 1px solid #c7d2fe;
    border-radius: 0.5rem;
    padding: 0.75rem;
    margin-bottom: 2rem;
    color: #4338ca;
    font-size: 0.875rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

/* Checkout Actions */
.checkout-actions {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.back-to-cart {
    text-align: center;
}

.back-to-cart a {
    color: #667eea;
    text-decoration: none;
    font-size: 0.875rem;
    font-weight: 500;
}

.back-to-cart a:hover {
    text-decoration: underline;
}

/* Trust Badges */
.trust-badges {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
    margin-top: 2rem;
}

.trust-badge {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    padding: 1rem;
    text-align: center;
    font-size: 0.875rem;
    color: #6b7280;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
}

.trust-badge i {
    font-size: 1.5rem;
    color: #10b981;
}

/* Responsive */
@media (max-width: 1024px) {
    .checkout-layout {
        grid-template-columns: 1fr;
    }
    
    .checkout-summary-section {
        order: -1;
    }
}

@media (max-width: 768px) {
    .checkout-progress {
        flex-direction: column;
        align-items: stretch;
        gap: 1rem;
    }
    
    .progress-line {
        display: none;
    }
    
    .checkout-title {
        font-size: 2rem;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .payment-security {
        flex-direction: column;
        gap: 1rem;
    }
    
    .trust-badges {
        grid-template-columns: 1fr;
    }
    
    .contact-info {
        flex-direction: column;
        text-align: center;
    }
}
</style>

<script>
// Form validation and payment processing
document.getElementById('pay-now-btn').addEventListener('click', function() {
    const form = document.getElementById('shippingForm');
    const firstName = document.getElementById('firstName').value;
    const lastName = document.getElementById('lastName').value;
    const phone = document.getElementById('phone').value;
    const country = document.getElementById('country').value;
    const postalCode = document.getElementById('postalCode').value;
    const digitalAddress = document.getElementById('digitalAddress').value;
    const city = document.getElementById('city').value;
    const region = document.getElementById('region').value;
    const stateProvince = document.getElementById('stateProvince').value;

    const isGhana = (country || '').trim().toLowerCase() === 'ghana';
    
    if (!firstName || !lastName || !phone || !country || !postalCode || !city) {
        console.warn('Missing required shipping information fields.');
        return;
    }

    if (isGhana) {
        if (!digitalAddress || !region) {
            console.warn('Missing required Ghana shipping information fields.');
            return;
        }
    } else {
        if (!stateProvince) {
            console.warn('Missing required global shipping information fields.');
            return;
        }
    }
    
    // Save shipping info to localStorage for potential use
    const shippingInfo = {
        firstName, lastName, phone, country, postalCode,
        digitalAddress: isGhana ? digitalAddress : '',
        city,
        region: isGhana ? region : '',
        stateProvince: !isGhana ? stateProvince : '',
        notes: document.getElementById('notes').value
    };
    localStorage.setItem('checkout_shipping', JSON.stringify(shippingInfo));
    
    // Proceed with payment
    proceedWithPayment();
});

function proceedWithPayment() {
    // Show loading state
    const payBtn = document.getElementById('pay-now-btn');
    const originalText = payBtn.innerHTML;
    payBtn.dataset.originalHtml = originalText;
    payBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Processing...';
    payBtn.disabled = true;
    
    // Initialize Paystack payment
    if (!window.DRIPYARD) {
        window.DRIPYARD = {};
    }
    
    window.DRIPYARD.checkout = {
        totalAmount: <?php echo json_encode((float)$total); ?>,
        email: <?php echo json_encode($user['email']); ?>
    };
    
    window.DRIPYARD.basePath = '..';

    if (!window.DRIPYARD.paystackPublicKey) {
        // Fallback when Paystack is not configured
        showPaymentFallback();
        return;
    }

    ensurePaystackLoaded()
        .then(function () {
            initPaystackPayment();
        })
        .catch(function (err) {
            console.error('Could not load Paystack.', err);
            showPaymentFallback();
        });
}

function ensurePaystackLoaded() {
    return new Promise(function (resolve, reject) {
        if (typeof PaystackPop !== 'undefined') {
            resolve();
            return;
        }

        var existing = document.querySelector('script[src="https://js.paystack.co/v1/inline.js"]');
        if (existing) {
            existing.addEventListener('load', function () { resolve(); });
            existing.addEventListener('error', function () { reject(new Error('Paystack script failed to load')); });
            return;
        }

        var script = document.createElement('script');
        script.src = 'https://js.paystack.co/v1/inline.js';
        script.async = true;
        script.onload = function () { resolve(); };
        script.onerror = function () { reject(new Error('Paystack script failed to load')); };
        document.head.appendChild(script);
    });
}

function showPaymentFallback() {
    const payBtn = document.getElementById('pay-now-btn');
    const originalText = payBtn.innerHTML;
    
    // Reset button state
    payBtn.innerHTML = originalText;
    payBtn.disabled = false;
    
    // Create a more detailed fallback modal
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.innerHTML = `
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Payment Processing
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Payment Gateway Status:</strong> Our payment gateway is currently being configured for Ghana Cedis (GHS).
                    </div>
                    
                    <h6>Order Summary:</h6>
                    <ul class="list-unstyled">
                        <li><strong>Total Amount:</strong> <?php echo CurrencyManager::formatPrice($total); ?></li>
                        <li><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></li>
                        <li><strong>Order ID:</strong> #DRIP${rand(10000, 99999)}</li>
                    </ul>
                    
                    <div class="d-grid gap-2">
                        <button class="btn btn-success" onclick="completeDemoOrder()">
                            <i class="bi bi-check-circle me-2"></i>Complete Demo Order
                        </button>
                        <button class="btn btn-outline-secondary" onclick="contactSupport()">
                            <i class="bi bi-telephone me-2"></i>Contact Support
                        </button>
                    </div>
                    
                    <div class="mt-3">
                        <small class="text-muted">
                            <i class="bi bi-lock me-1"></i>
                            Your payment information is secure. This is a demo mode for testing purposes.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
    
    modal.addEventListener('hidden.bs.modal', () => {
        document.body.removeChild(modal);
    });
}

function completeDemoOrder() {
    // Close any open modals
    const modals = document.querySelectorAll('.modal.show');
    modals.forEach(modal => {
        const bsModal = bootstrap.Modal.getInstance(modal);
        if (bsModal) bsModal.hide();
    });
    
    // Show processing message
    const payBtn = document.getElementById('pay-now-btn');
    payBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Processing...';
    payBtn.disabled = true;
    
    // Simulate order processing
    setTimeout(() => {
        // Show success message
        const successMsg = document.createElement('div');
        successMsg.className = 'alert alert-success alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3';
        successMsg.style.zIndex = '9999';
        successMsg.innerHTML = `
            <i class="bi bi-check-circle me-2"></i>
            <strong>Order Placed Successfully!</strong><br>
            <small>Your order has been received. You will be contacted for payment arrangements.</small>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(successMsg);
        setTimeout(() => successMsg.remove(), 5000);
        
        // Redirect to dashboard after delay
        setTimeout(() => {
            window.location.href = 'dashboard.php?order=success';
        }, 2000);
    }, 2000);
}

function contactSupport() {
    // Close modal and show contact info
    const modals = document.querySelectorAll('.modal.show');
    modals.forEach(modal => {
        const bsModal = bootstrap.Modal.getInstance(modal);
        if (bsModal) bsModal.hide();
    });
    
    const contactMsg = document.createElement('div');
    contactMsg.className = 'alert alert-info alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3';
    contactMsg.style.zIndex = '9999';
    contactMsg.innerHTML = `
        <i class="bi bi-telephone me-2"></i>
        <strong>Contact Support:</strong><br>
        📧 Email: support@dripyard.com<br>
        📱 Phone/WhatsApp: +233 20 123 4567<br>
        <small>We'll help you complete your order manually!</small>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(contactMsg);
    setTimeout(() => contactMsg.remove(), 8000);
}

// Phone number formatting
document.getElementById('phone').addEventListener('input', function(e) {
    var raw = (e.target.value || '').trim();
    // Allow optional leading +, then digits only
    var hasPlus = raw.startsWith('+');
    var digits = raw.replace(/\D/g, '');
    // E.164 max is 15 digits
    if (digits.length > 15) {
        digits = digits.substring(0, 15);
    }
    e.target.value = (hasPlus ? '+' : (digits.length ? '+' : '')) + digits;
});

function updateShippingFieldsVisibility() {
    var country = (document.getElementById('country').value || '').trim().toLowerCase();
    var isGhana = country === 'ghana';

    var digitalAddress = document.getElementById('digitalAddress');
    var ghRegionGroup = document.getElementById('ghana-region-group');
    var regionSelect = document.getElementById('region');
    var stateGroup = document.getElementById('state-province-group');
    var stateInput = document.getElementById('stateProvince');

    if (isGhana) {
        if (digitalAddress) digitalAddress.required = true;
        if (regionSelect) regionSelect.required = true;
        if (ghRegionGroup) ghRegionGroup.style.display = '';
        if (stateGroup) stateGroup.style.display = 'none';
        if (stateInput) stateInput.required = false;
    } else {
        if (digitalAddress) {
            digitalAddress.required = false;
            digitalAddress.value = '';
        }
        if (regionSelect) {
            regionSelect.required = false;
            regionSelect.value = '';
        }
        if (ghRegionGroup) ghRegionGroup.style.display = 'none';
        if (stateGroup) stateGroup.style.display = '';
        if (stateInput) stateInput.required = true;
    }
}

document.getElementById('country').addEventListener('input', updateShippingFieldsVisibility);
document.addEventListener('DOMContentLoaded', updateShippingFieldsVisibility);

function initPaystackPayment() {
    const btn = document.getElementById('pay-now-btn');
    const details = window.DRIPYARD.checkout || {};

    function resetBtn() {
        const original = btn?.dataset?.originalHtml;
        btn.innerHTML = original || '<i class="bi bi-lock me-2"></i>Complete Payment - <?php echo CurrencyManager::formatPrice($total); ?>';
        btn.disabled = false;
    }
    
    if (!details.totalAmount || !details.email) {
        console.error('Missing checkout details for Paystack initialisation.');
        resetBtn();
        return;
    }

    if (typeof PaystackPop === 'undefined') {
        console.error('Paystack SDK not available.');
        resetBtn();
        return;
    }
    
    const ref = 'DRIP-' + Date.now();
    
    const handler = PaystackPop.setup({
        key: window.DRIPYARD.paystackPublicKey,
        email: details.email,
        amount: Math.round(details.totalAmount * 100),
        currency: 'GHS',
        ref: ref,
        callback: function (response) {
            const basePath = window.DRIPYARD.basePath || '..';

            const controller = new AbortController();
            const timeout = setTimeout(() => controller.abort(), 25000);

            fetch(`${basePath}/backend/payment-callback.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    reference: response.reference,
                }),
                signal: controller.signal,
            })
            .then(async (res) => {
                const text = await res.text();
                let data;
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    throw new Error('Invalid response from server: ' + text.slice(0, 200));
                }

                if (!res.ok) {
                    throw new Error(data?.message || ('Request failed with status ' + res.status));
                }

                return data;
            })
            .then((data) => {
                if (data && data.success) {
                    window.location.href = `${basePath}/public/dashboard.php?order=success`;
                    return;
                }
                throw new Error((data && data.message) ? data.message : 'Payment verification failed.');
            })
            .catch((err) => {
                console.error('Could not verify payment.', err);
                resetBtn();
                alert('Payment completed, but we could not verify your order yet. Please try again or contact support.\n\n' + (err?.message || ''));
            })
            .finally(() => {
                clearTimeout(timeout);
            });
        },
        onClose: function () {
            resetBtn();
        },
        onError: function (error) {
            console.error('Paystack error:', error);
            resetBtn();
        }
    });
    
    handler.openIframe();
}

// Auto-save form data
const formFields = ['firstName', 'lastName', 'phone', 'country', 'postalCode', 'digitalAddress', 'city', 'region', 'stateProvince', 'notes'];
formFields.forEach(fieldId => {
    const field = document.getElementById(fieldId);
    if (field) {
        // Load saved data
        const savedValue = localStorage.getItem(`checkout_${fieldId}`);
        if (savedValue) {
            field.value = savedValue;
        }
        
        // Save on change
        field.addEventListener('input', function() {
            localStorage.setItem(`checkout_${fieldId}`, this.value);
        });
    }
});

// Set base path and initialize
document.addEventListener('DOMContentLoaded', function() {
    if (!window.DRIPYARD) {
        window.DRIPYARD = {};
    }
    window.DRIPYARD.basePath = '..';
    
    // Check for DripBox in URL
    const urlParams = new URLSearchParams(window.location.search);
    const boxId = urlParams.get('box');
    if (boxId) {
        // Show a notification that a DripBox is being processed
        const boxMsg = document.createElement('div');
        boxMsg.className = 'alert alert-info alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3';
        boxMsg.style.zIndex = '9999';
        boxMsg.innerHTML = `
            <i class="bi bi-box-seam me-2"></i>DripBox #${boxId} added to checkout
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(boxMsg);
        setTimeout(() => boxMsg.remove(), 5000);
    }
});
</script>

<?php include __DIR__ . '/partials/footer.php'; ?>
