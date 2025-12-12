<?php
$pageTitle = 'Home';
$basePath = '..';
require_once __DIR__ . '/../backend/db.php';
require_once __DIR__ . '/../backend/auth.php';
require_once __DIR__ . '/../backend/currency.php';

$pdo = getPDO();

// Get featured products with stock info
$stmt = $pdo->query('SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.stock > 0 ORDER BY p.created_at DESC LIMIT 8');
$featuredProducts = $stmt->fetchAll();

// Get featured DripBox
$stmtBoxes = $pdo->query('SELECT * FROM sunnydripboxes ORDER BY created_at DESC LIMIT 1');
$highlightBox = $stmtBoxes->fetch();

// Get categories for navigation
$stmtCategories = $pdo->query('SELECT * FROM categories ORDER BY name');
$categories = $stmtCategories->fetchAll();

// Get bestsellers (products with most orders - simplified for demo)
$stmtBestsellers = $pdo->query('SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.stock > 0 ORDER BY RAND() LIMIT 4');
$bestsellers = $stmtBestsellers->fetchAll();

include __DIR__ . '/partials/header.php';
?>

<!-- Hero Section with Video Background -->
<section class="hero-section">
    <div class="hero-background">
        <div class="hero-overlay"></div>
        <div class="hero-particles" id="heroParticles"></div>
    </div>
    <div class="container hero-container">
        <div class="row align-items-center min-vh-100">
            <div class="col-lg-6 hero-content">
                <div class="hero-badge mb-3">
                    <span class="badge-new">NEW COLLECTION</span>
                    <span class="badge-season">FALL 2024</span>
                </div>
                <h1 class="hero-title">
                    Define Your
                    <span class="text-gradient">Street Style</span>
                </h1>
                <p class="hero-subtitle">
                    Premium streetwear designed for the modern urban lifestyle. 
                    Crafted with precision, worn with confidence.
                </p>
                <div class="hero-stats mb-4">
                    <div class="stat-item">
                        <span class="stat-number">10K+</span>
                        <span class="stat-label">Happy Customers</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">500+</span>
                        <span class="stat-label">Products</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">98%</span>
                        <span class="stat-label">Satisfaction</span>
                    </div>
                </div>
                <div class="hero-actions">
                    <a href="shop.php" class="btn btn-hero-primary">
                        <span>Shop Now</span>
                        <i class="bi bi-arrow-right"></i>
                    </a>
                    <a href="dripbox.php" class="btn btn-hero-secondary">
                        <i class="bi bi-box-seam me-2"></i>Explore DripBox
                    </a>
                </div>
                <div class="hero-features">
                    <div class="feature-item">
                        <i class="bi bi-truck"></i>
                        <span>Free Shipping</span>
                    </div>
                    <div class="feature-item">
                        <i class="bi bi-shield-check"></i>
                        <span>Secure Payment</span>
                    </div>
                    <div class="feature-item">
                        <i class="bi bi-arrow-repeat"></i>
                        <span>Easy Returns</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 hero-visual">
                <?php if ($highlightBox): ?>
                    <div class="featured-product-card">
                        <div class="product-badge">Featured</div>
                        <div class="product-image">
                            <?php if ($highlightBox['image']): ?>
                                <img src="<?php echo $basePath; ?>/assets/images/dripboxes/<?php echo htmlspecialchars($highlightBox['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($highlightBox['name']); ?>">
                            <?php else: ?>
                                <div class="product-placeholder">
                                    <i class="bi bi-box-seam"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="product-info">
                            <h3><?php echo htmlspecialchars($highlightBox['name']); ?></h3>
                            <p><?php echo htmlspecialchars($highlightBox['theme'] ?? 'Premium Collection'); ?></p>
                            <div class="price-tag"><?php echo CurrencyManager::formatPrice($highlightBox['price']); ?></div>
                            <a href="dripbox.php" class="btn btn-product">View DripBox</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="scroll-indicator">
        <div class="scroll-mouse">
            <div class="scroll-wheel"></div>
        </div>
        <span>Scroll to explore</span>
    </div>
</section>

<!-- Categories Showcase -->
<section class="categories-showcase">
    <div class="container">
        <div class="section-header text-center">
            <span class="section-subtitle">EXPLORE COLLECTIONS</span>
            <h2 class="section-title">Shop by Category</h2>
            <p class="section-description">Discover our curated collections designed for every style and occasion</p>
        </div>
        <div class="categories-grid">
            <div class="category-card category-essentials" onclick="window.location.href='shop.php'">
                <div class="category-overlay"></div>
                <div class="category-content">
                    <h3>Everyday Essentials</h3>
                    <p>Clean basics for daily wear</p>
                    <div class="category-link">
                        <span>Shop Now</span>
                        <i class="bi bi-arrow-right"></i>
                    </div>
                </div>
                <div class="category-image">
                    <div class="category-placeholder">
                        <i class="bi bi-tshirt"></i>
                    </div>
                </div>
            </div>
            <div class="category-card category-statement" onclick="window.location.href='shop.php'">
                <div class="category-overlay"></div>
                <div class="category-content">
                    <h3>Statement Pieces</h3>
                    <p>Bold designs that stand out</p>
                    <div class="category-link">
                        <span>Shop Now</span>
                        <i class="bi bi-arrow-right"></i>
                    </div>
                </div>
                <div class="category-image">
                    <div class="category-placeholder">
                        <i class="bi bi-lightning"></i>
                    </div>
                </div>
            </div>
            <div class="category-card category-athletic" onclick="window.location.href='shop.php'">
                <div class="category-overlay"></div>
                <div class="category-content">
                    <h3>Athletic Wear</h3>
                    <p>Performance meets style</p>
                    <div class="category-link">
                        <span>Shop Now</span>
                        <i class="bi bi-arrow-right"></i>
                    </div>
                </div>
                <div class="category-image">
                    <div class="category-placeholder">
                        <i class="bi bi-lightning-charge"></i>
                    </div>
                </div>
            </div>
            <div class="category-card category-accessories" onclick="window.location.href='shop.php'">
                <div class="category-overlay"></div>
                <div class="category-content">
                    <h3>Accessories</h3>
                    <p>Complete your look</p>
                    <div class="category-link">
                        <span>Shop Now</span>
                        <i class="bi bi-arrow-right"></i>
                    </div>
                </div>
                <div class="category-image">
                    <div class="category-placeholder">
                        <i class="bi bi-gem"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Products -->
<section class="featured-products">
    <div class="container">
        <div class="section-header">
            <div class="section-header-content">
                <span class="section-subtitle">FEATURED PRODUCTS</span>
                <h2 class="section-title">Trending Now</h2>
                <p class="section-description">Our most popular pieces this season</p>
            </div>
            <div class="section-actions">
                <a href="shop.php" class="btn btn-outline-primary">View All Products</a>
            </div>
        </div>
        <div class="products-carousel">
            <div class="products-grid">
                <?php foreach ($featuredProducts as $product): ?>
                    <div class="product-card">
                        <div class="product-badges">
                            <?php if ($product['stock'] < 10): ?>
                                <span class="badge badge-low-stock">Low Stock</span>
                            <?php endif; ?>
                            <span class="badge badge-new">New</span>
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
                                        <i class="bi bi-heart"></i>
                                    </button>
                                    <button class="btn btn-icon btn-quickview" data-product-id="<?php echo (int)$product['id']; ?>">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                                <button class="btn btn-add-to-cart add-to-cart-btn" data-product-id="<?php echo (int)$product['id']; ?>">
                                    <i class="bi bi-bag-plus me-2"></i>Add to Cart
                                </button>
                            </div>
                        </div>
                        <div class="product-info">
                            <div class="product-category"><?php echo htmlspecialchars($product['category_name']); ?></div>
                            <h3 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
                            <div class="product-price">
                                <span class="current-price"><?php echo CurrencyManager::formatPrice($product['price']); ?></span>
                            </div>
                            <div class="product-rating">
                                <div class="stars">★★★★★</div>
                                <span class="rating-count">(<?php echo rand(10, 99); ?>)</span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<!-- Bestsellers -->
<section class="bestsellers-section">
    <div class="container">
        <div class="section-header text-center">
            <span class="section-subtitle">BESTSELLERS</span>
            <h2 class="section-title">Customer Favorites</h2>
            <p class="section-description">The pieces our customers can't get enough of</p>
        </div>
        <div class="bestsellers-grid">
            <?php foreach ($bestsellers as $index => $product): ?>
                <div class="bestseller-item" style="--delay: <?php echo $index * 0.1; ?>s">
                    <div class="bestseller-rank"><?php echo $index + 1; ?></div>
                    <div class="bestseller-image">
                        <?php if ($product['image']): ?>
                            <img src="<?php echo $basePath; ?>/assets/images/products/<?php echo htmlspecialchars($product['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <?php else: ?>
                            <div class="product-placeholder">
                                <i class="bi bi-image"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="bestseller-info">
                        <h4><?php echo htmlspecialchars($product['name']); ?></h4>
                        <p><?php echo htmlspecialchars($product['category_name']); ?></p>
                        <div class="bestseller-price"><?php echo CurrencyManager::formatPrice($product['price']); ?></div>
                    </div>
                    <button class="btn btn-add-to-cart add-to-cart-btn" data-product-id="<?php echo (int)$product['id']; ?>">
                        <i class="bi bi-bag-plus"></i>
                    </button>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features-section">
    <div class="container">
        <div class="features-grid">
            <div class="feature-item">
                <div class="feature-icon">
                    <i class="bi bi-truck"></i>
                </div>
                <h3>Free Delivery</h3>
                <p>Free shipping on orders over GH₵500</p>
            </div>
            <div class="feature-item">
                <div class="feature-icon">
                    <i class="bi bi-shield-check"></i>
                </div>
                <h3>Secure Payment</h3>
                <p>100% secure payment processing</p>
            </div>
            <div class="feature-item">
                <div class="feature-icon">
                    <i class="bi bi-arrow-repeat"></i>
                </div>
                <h3>Easy Returns</h3>
                <p>30-day return policy</p>
            </div>
            <div class="feature-item">
                <div class="feature-icon">
                    <i class="bi bi-headset"></i>
                </div>
                <h3>24/7 Support</h3>
                <p>Dedicated customer support</p>
            </div>
        </div>
    </div>
</section>

<!-- Newsletter Section -->
<section class="newsletter-section">
    <div class="container">
        <div class="newsletter-content">
            <h2>Stay in the Loop</h2>
            <p>Get exclusive offers and be the first to know about new drops</p>
            <form class="newsletter-form">
                <div class="input-group">
                    <input type="email" placeholder="Enter your email" class="form-control" required>
                    <button type="submit" class="btn btn-primary">
                        <span>Subscribe</span>
                        <i class="bi bi-send"></i>
                    </button>
                </div>
            </form>
            <div class="newsletter-benefits">
                <span><i class="bi bi-check-circle"></i> 10% off first order</span>
                <span><i class="bi bi-check-circle"></i> Early access to drops</span>
                <span><i class="bi bi-check-circle"></i> Exclusive deals</span>
            </div>
        </div>
    </div>
</section>

<style>
/* Hero Section */
.hero-section {
    position: relative;
    min-height: 100vh;
    display: flex;
    align-items: center;
    overflow: hidden;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.hero-background {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%23ffffff" fill-opacity="0.1" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,122.7C672,117,768,139,864,154.7C960,171,1056,181,1152,165.3C1248,149,1344,107,1392,85.3L1440,64L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>') no-repeat bottom;
    background-size: cover;
}

.hero-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.3);
}

.hero-particles {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    pointer-events: none;
}

.hero-container {
    position: relative;
    z-index: 2;
}

.hero-content {
    color: white;
}

.hero-badge {
    display: flex;
    gap: 1rem;
    align-items: center;
}

.badge-new, .badge-season {
    padding: 0.5rem 1rem;
    border-radius: 2rem;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.1em;
}

.badge-new {
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.3);
}

.badge-season {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.hero-title {
    font-size: 4rem;
    font-weight: 800;
    line-height: 1.1;
    margin-bottom: 1.5rem;
}

.text-gradient {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.hero-subtitle {
    font-size: 1.25rem;
    margin-bottom: 2rem;
    opacity: 0.9;
    max-width: 500px;
}

.hero-stats {
    display: flex;
    gap: 2rem;
    margin-bottom: 2rem;
}

.stat-item {
    text-align: center;
}

.stat-number {
    display: block;
    font-size: 1.5rem;
    font-weight: 700;
    color: white;
}

.stat-label {
    font-size: 0.875rem;
    opacity: 0.8;
}

.hero-actions {
    display: flex;
    gap: 1rem;
    margin-bottom: 2rem;
    flex-wrap: wrap;
}

.btn-hero-primary {
    background: white;
    color: #667eea;
    border: none;
    padding: 1rem 2rem;
    border-radius: 2rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.3s ease;
}

.btn-hero-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
}

.btn-hero-secondary {
    background: rgba(255, 255, 255, 0.1);
    color: white;
    border: 1px solid rgba(255, 255, 255, 0.3);
    padding: 1rem 2rem;
    border-radius: 2rem;
    font-weight: 600;
    backdrop-filter: blur(10px);
    transition: all 0.3s ease;
}

.btn-hero-secondary:hover {
    background: rgba(255, 255, 255, 0.2);
    transform: translateY(-2px);
}

.hero-features {
    display: flex;
    gap: 2rem;
    flex-wrap: wrap;
}

.feature-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    opacity: 0.9;
}

.feature-item i {
    font-size: 1.25rem;
}

.hero-visual {
    display: flex;
    justify-content: center;
    align-items: center;
}

.featured-product-card {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 1rem;
    padding: 2rem;
    max-width: 400px;
    transform: rotate(3deg);
    transition: all 0.3s ease;
}

.featured-product-card:hover {
    transform: rotate(0deg) scale(1.05);
}

.product-badge {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 1rem;
    font-size: 0.75rem;
    font-weight: 600;
    display: inline-block;
    margin-bottom: 1rem;
}

.product-image {
    width: 100%;
    height: 200px;
    border-radius: 0.5rem;
    overflow: hidden;
    margin-bottom: 1rem;
    background: rgba(255, 255, 255, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
}

.product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.product-placeholder {
    font-size: 3rem;
    color: rgba(255, 255, 255, 0.5);
}

.product-info h3 {
    color: white;
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
}

.product-info p {
    color: rgba(255, 255, 255, 0.8);
    margin-bottom: 1rem;
}

.price-tag {
    color: white;
    font-size: 1.25rem;
    font-weight: 700;
    margin-bottom: 1rem;
}

.btn-product {
    background: white;
    color: #667eea;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 2rem;
    font-weight: 600;
    width: 100%;
    transition: all 0.3s ease;
}

.btn-product:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
}

.scroll-indicator {
    position: absolute;
    bottom: 2rem;
    left: 50%;
    transform: translateX(-50%);
    text-align: center;
    color: white;
    opacity: 0.8;
    animation: bounce 2s infinite;
}

.scroll-mouse {
    width: 30px;
    height: 50px;
    border: 2px solid white;
    border-radius: 15px;
    margin: 0 auto 0.5rem;
    position: relative;
}

.scroll-wheel {
    width: 4px;
    height: 10px;
    background: white;
    border-radius: 2px;
    position: absolute;
    top: 8px;
    left: 50%;
    transform: translateX(-50%);
    animation: scroll 2s infinite;
}

@keyframes bounce {
    0%, 20%, 50%, 80%, 100% { transform: translateX(-50%) translateY(0); }
    40% { transform: translateX(-50%) translateY(-10px); }
    60% { transform: translateX(-50%) translateY(-5px); }
}

@keyframes scroll {
    0% { top: 8px; opacity: 1; }
    100% { top: 25px; opacity: 0; }
}

/* Categories Showcase */
.categories-showcase {
    padding: 5rem 0;
    background: #f8fafc;
}

.section-header {
    margin-bottom: 3rem;
}

.section-subtitle {
    color: #667eea;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    font-size: 0.875rem;
}

.section-title {
    font-size: 2.5rem;
    font-weight: 700;
    margin: 0.5rem 0;
    color: #1f2937;
}

.section-description {
    color: #6b7280;
    font-size: 1.125rem;
    max-width: 600px;
    margin: 0 auto;
}

.categories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
}

.category-card {
    position: relative;
    height: 400px;
    border-radius: 1rem;
    overflow: hidden;
    cursor: pointer;
    transition: all 0.3s ease;
}

.category-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
}

.category-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(0,0,0,0.7) 0%, rgba(0,0,0,0.3) 100%);
    z-index: 1;
}

.category-content {
    position: absolute;
    bottom: 2rem;
    left: 2rem;
    right: 2rem;
    z-index: 2;
    color: white;
}

.category-content h3 {
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.category-content p {
    opacity: 0.9;
    margin-bottom: 1rem;
}

.category-link {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 600;
    transition: all 0.3s ease;
}

.category-link:hover {
    transform: translateX(5px);
}

.category-image {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}

.category-placeholder {
    font-size: 4rem;
    color: rgba(255, 255, 255, 0.2);
}

.category-essentials { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
.category-statement { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
.category-athletic { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
.category-accessories { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); }

/* Featured Products */
.featured-products {
    padding: 5rem 0;
}

.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 2rem;
}

.product-card {
    background: white;
    border-radius: 1rem;
    overflow: hidden;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    position: relative;
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
}

.product-badges {
    position: absolute;
    top: 1rem;
    left: 1rem;
    z-index: 3;
    display: flex;
    gap: 0.5rem;
}

.badge {
    padding: 0.25rem 0.75rem;
    border-radius: 2rem;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.badge-low-stock {
    background: #ef4444;
    color: white;
}

.badge-new {
    background: #10b981;
    color: white;
}

.product-image-container {
    position: relative;
    height: 300px;
    overflow: hidden;
}

.product-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.product-card:hover .product-image {
    transform: scale(1.1);
}

.product-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.7);
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    opacity: 0;
    transition: all 0.3s ease;
}

.product-card:hover .product-overlay {
    opacity: 1;
}

.product-actions {
    display: flex;
    gap: 1rem;
    margin-bottom: 1rem;
}

.btn-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: 1px solid rgba(255, 255, 255, 0.3);
    background: rgba(255, 255, 255, 0.1);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.btn-icon:hover {
    background: white;
    color: #1f2937;
    transform: scale(1.1);
}

.btn-add-to-cart {
    background: white;
    color: #1f2937;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 2rem;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-add-to-cart:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
}

.product-info {
    padding: 1.5rem;
}

.product-category {
    color: #6b7280;
    font-size: 0.875rem;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 0.5rem;
}

.product-name {
    font-size: 1.125rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: #1f2937;
}

.product-price {
    margin-bottom: 0.5rem;
}

.current-price {
    font-size: 1.25rem;
    font-weight: 700;
    color: #1f2937;
}

.product-rating {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.stars {
    color: #fbbf24;
    font-size: 0.875rem;
}

.rating-count {
    color: #6b7280;
    font-size: 0.875rem;
}

/* Bestsellers */
.bestsellers-section {
    padding: 5rem 0;
    background: #f8fafc;
}

.bestsellers-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
}

.bestseller-item {
    background: white;
    border-radius: 1rem;
    padding: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    opacity: 0;
    animation: fadeInUp 0.5s ease forwards;
    animation-delay: var(--delay);
}

.bestseller-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.bestseller-rank {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1.25rem;
}

.bestseller-image {
    width: 80px;
    height: 80px;
    border-radius: 0.5rem;
    overflow: hidden;
    flex-shrink: 0;
}

.bestseller-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.bestseller-info {
    flex: 1;
}

.bestseller-info h4 {
    font-size: 1.125rem;
    font-weight: 600;
    margin-bottom: 0.25rem;
    color: #1f2937;
}

.bestseller-info p {
    color: #6b7280;
    font-size: 0.875rem;
    margin-bottom: 0.5rem;
}

.bestseller-price {
    font-size: 1.125rem;
    font-weight: 700;
    color: #1f2937;
}

/* Features Section */
.features-section {
    padding: 5rem 0;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 3rem;
}

.feature-item {
    text-align: center;
}

.feature-icon {
    width: 80px;
    height: 80px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
    font-size: 2rem;
    border: 2px solid rgba(255, 255, 255, 0.2);
}

.feature-item h3 {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.feature-item p {
    opacity: 0.9;
}

/* Newsletter Section */
.newsletter-section {
    padding: 5rem 0;
    background: #1f2937;
    color: white;
}

.newsletter-content {
    text-align: center;
    max-width: 600px;
    margin: 0 auto;
}

.newsletter-content h2 {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 1rem;
}

.newsletter-content p {
    font-size: 1.125rem;
    opacity: 0.9;
    margin-bottom: 2rem;
}

.newsletter-form {
    margin-bottom: 2rem;
}

.newsletter-form .input-group {
    max-width: 500px;
    margin: 0 auto;
}

.newsletter-form .form-control {
    border: none;
    padding: 1rem 1.5rem;
    border-radius: 2rem 0 0 2rem;
    font-size: 1rem;
}

.newsletter-form .btn {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    padding: 1rem 2rem;
    border-radius: 0 2rem 2rem 0;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.newsletter-benefits {
    display: flex;
    justify-content: center;
    gap: 2rem;
    flex-wrap: wrap;
}

.newsletter-benefits span {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    opacity: 0.9;
}

/* Responsive */
@media (max-width: 768px) {
    .hero-title {
        font-size: 2.5rem;
    }
    
    .hero-stats {
        flex-direction: column;
        gap: 1rem;
    }
    
    .hero-actions {
        flex-direction: column;
        align-items: stretch;
    }
    
    .categories-grid {
        grid-template-columns: 1fr;
    }
    
    .products-grid {
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    }
    
    .bestsellers-grid {
        grid-template-columns: 1fr;
    }
    
    .features-grid {
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 2rem;
    }
    
    .newsletter-benefits {
        flex-direction: column;
        gap: 1rem;
    }
}
</style>

<script>
// Hero particles animation
function createParticles() {
    const container = document.getElementById('heroParticles');
    if (!container) return;
    
    for (let i = 0; i < 50; i++) {
        const particle = document.createElement('div');
        particle.style.position = 'absolute';
        particle.style.width = Math.random() * 4 + 'px';
        particle.style.height = particle.style.width;
        particle.style.background = 'rgba(255, 255, 255, 0.5)';
        particle.style.borderRadius = '50%';
        particle.style.left = Math.random() * 100 + '%';
        particle.style.top = Math.random() * 100 + '%';
        particle.style.animation = `float ${Math.random() * 10 + 10}s linear infinite`;
        container.appendChild(particle);
    }
}

// Float animation
const style = document.createElement('style');
style.textContent = `
    @keyframes float {
        0% { transform: translateY(0px) translateX(0px); }
        50% { transform: translateY(-20px) translateX(10px); }
        100% { transform: translateY(0px) translateX(0px); }
    }
`;
document.head.appendChild(style);

// Initialize particles
createParticles();

// Newsletter form (no intrusive alert)
document.querySelector('.newsletter-form')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const email = this.querySelector('input[type="email"]').value;
    console.info('Newsletter subscribed:', email);
    this.reset();
});

// Smooth scroll for category cards
document.querySelectorAll('.category-card').forEach(card => {
    card.addEventListener('click', function() {
        window.location.href = 'shop.php';
    });
});
</script>

<?php include __DIR__ . '/partials/footer.php'; ?>
