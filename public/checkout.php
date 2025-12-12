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
$total = 0.0;
$discount = 0.0;
$shipping = 0.0;

if (!empty($cart)) {
    $ids = array_map('intval', array_column($cart, 'product_id'));
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT id, name, price, image FROM products WHERE id IN ($placeholders)");
    $stmt->execute($ids);
    $rows = $stmt->fetchAll();
    foreach ($rows as $row) {
        $products[$row['id']] = $row;
    }
}

// Calculate totals
$subtotal = 0.0;
foreach ($cart as $item) {
    $pid = (int)$item['product_id'];
    $qty = (int)$item['quantity'];
    if (isset($products[$pid])) {
        $subtotal += $products[$pid]['price'] * $qty;
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
        <?php if (empty($cart) || empty($products)): ?>
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
                                           placeholder="+233 XXX XXX XXX" required>
                                </div>
                                <div class="form-group">
                                    <label for="address">Delivery Address</label>
                                    <input type="text" id="address" name="address" class="form-control" 
                                           placeholder="Enter your full delivery address" required>
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="city">City</label>
                                        <input type="text" id="city" name="city" class="form-control" 
                                               placeholder="Accra" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="region">Region</label>
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
                                </div>
                                <div class="form-group">
                                    <label for="notes">Delivery Notes (Optional)</label>
                                    <textarea id="notes" name="notes" class="form-control" rows="3" 
                                              placeholder="Any special delivery instructions?"></textarea>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div class="checkout-card">
                        <div class="card-header">
                            <h3>
                                <i class="bi bi-credit-card me-2"></i>
                                Payment Method
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="payment-methods">
                                <div class="payment-method active">
                                    <div class="payment-radio">
                                        <input type="radio" name="paymentMethod" id="paystack" value="paystack" checked>
                                        <label for="paystack">
                                            <div class="payment-icon">
                                                <i class="bi bi-credit-card-fill"></i>
                                            </div>
                                            <div class="payment-info">
                                                <div class="payment-name">Paystack</div>
                                                <div class="payment-description">Secure payment with card, mobile money, or bank transfer</div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="payment-security">
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
                                    $pid = (int)$item['product_id'];
                                    $qty = (int)$item['quantity'];
                                    if (!isset($products[$pid])) continue;
                                    $prod = $products[$pid];
                                    $lineTotal = $prod['price'] * $qty;
                                    ?>
                                    <div class="order-item">
                                        <div class="order-item-image">
                                            <?php if ($prod['image']): ?>
                                                <img src="<?php echo $basePath; ?>/assets/images/products/<?php echo htmlspecialchars($prod['image']); ?>" 
                                                     alt="<?php echo htmlspecialchars($prod['name']); ?>">
                                            <?php else: ?>
                                                <div class="order-item-placeholder">
                                                    <i class="bi bi-image"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="order-item-details">
                                            <div class="order-item-name"><?php echo htmlspecialchars($prod['name']); ?></div>
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
    flex: 1;
}

.payment-name {
    font-weight: 600;
    margin-bottom: 0.25rem;
    color: #1f2937;
}

.payment-description {
    font-size: 0.875rem;
    color: #6b7280;
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
    const address = document.getElementById('address').value;
    const city = document.getElementById('city').value;
    const region = document.getElementById('region').value;
    
    if (!firstName || !lastName || !phone || !address || !city || !region) {
        console.warn('Missing required shipping information fields.');
        return;
    }
    
    // Save shipping info to localStorage for potential use
    const shippingInfo = {
        firstName, lastName, phone, address, city, region,
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
    
    // Check if Paystack is available and configured
    if (typeof PaystackPop !== 'undefined' && window.DRIPYARD.paystackPublicKey) {
        // Use existing Paystack implementation
        if (typeof paystackHandler === 'function') {
            paystackHandler();
        } else {
            initPaystackPayment();
        }
    } else {
        // Fallback for development or when Paystack is not configured
        showPaymentFallback();
    }
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
    let value = e.target.value.replace(/\D/g, '');
    if (value.length > 0 && !value.startsWith('233')) {
        value = '233' + value;
    }
    if (value.length > 12) {
        value = value.substring(0, 12);
    }
    e.target.value = value;
});

function initPaystackPayment() {
    const btn = document.getElementById('pay-now-btn');
    const details = window.DRIPYARD.checkout || {};
    
    if (!details.totalAmount || !details.email) {
        console.error('Missing checkout details for Paystack initialisation.');
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
            
            // Send payment verification
            fetch(`${basePath}/backend/payment-callback.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    reference: response.reference,
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    window.location.href = `${basePath}/public/dashboard.php?order=success`;
                } else {
                    console.error('Payment verification failed:', data.message);
                }
            })
            .catch((err) => {
                console.error('Could not verify payment. Please contact support.', err);
            });
        },
        onClose: function () {
            // Reset button state
            btn.innerHTML = '<i class="bi bi-lock me-2"></i>Complete Payment - <?php echo CurrencyManager::formatPrice($total); ?>';
            btn.disabled = false;
        },
        onError: function (error) {
            console.error('Paystack error:', error);
            // Reset button state
            btn.innerHTML = '<i class="bi bi-lock me-2"></i>Complete Payment - <?php echo CurrencyManager::formatPrice($total); ?>';
            btn.disabled = false;
        }
    });
    
    handler.openIframe();
}

// Phone number formatting
document.getElementById('phone').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length > 0 && !value.startsWith('233')) {
        value = '233' + value;
    }
    if (value.length > 12) {
        value = value.substring(0, 12);
    }
    e.target.value = value;
});

// Auto-save form data
const formFields = ['firstName', 'lastName', 'phone', 'address', 'city', 'region', 'notes'];
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

// Payment method selection
document.querySelectorAll('input[name="paymentMethod"]').forEach(radio => {
    radio.addEventListener('change', function() {
        // Remove active class from all payment methods
        document.querySelectorAll('.payment-method').forEach(method => {
            method.classList.remove('active');
        });
        
        // Add active class to selected payment method
        this.closest('.payment-method').classList.add('active');
    });
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
