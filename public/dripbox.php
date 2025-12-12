<?php
$pageTitle = 'DripBox';
$basePath = '..';
require_once __DIR__ . '/../backend/db.php';
require_once __DIR__ . '/../backend/auth.php';
require_once __DIR__ . '/../backend/currency.php';

$pdo = getPDO();

// Get all DripBoxes
$stmt = $pdo->query('SELECT * FROM sunnydripboxes ORDER BY created_at DESC');
$boxes = $stmt->fetchAll();

// Get featured box
$featuredBox = !empty($boxes) ? $boxes[0] : null;

include __DIR__ . '/partials/header.php';
?>

<!-- DripBox Hero Section -->
<section class="dripbox-hero">
    <div class="container">
        <div class="dripbox-hero-content">
            <div class="hero-badges">
                <span class="hero-badge">EXCLUSIVE</span>
                <span class="hero-badge hero-badge-accent">LIMITED EDITION</span>
            </div>
            <h1 class="dripbox-title">
                <span class="text-gradient">DripBox</span> Collection
            </h1>
            <p class="dripbox-subtitle">
                Curated streetwear bundles delivered to your doorstep. Each box is carefully crafted by our stylists to give you the perfect drip for any occasion.
            </p>
            <div class="dripbox-stats">
                <div class="dripbox-stat">
                    <span class="stat-number"><?php echo count($boxes); ?></span>
                    <span class="stat-label">Available Boxes</span>
                </div>
                <div class="dripbox-stat">
                    <span class="stat-number">5+</span>
                    <span class="stat-label">Items per Box</span>
                </div>
                <div class="dripbox-stat">
                    <span class="stat-number">30%</span>
                    <span class="stat-label">Savings</span>
                </div>
            </div>
            <div class="dripbox-actions">
                <a href="#featured-box" class="btn btn-hero-primary">
                    <span>Explore Boxes</span>
                    <i class="bi bi-arrow-down"></i>
                </a>
                <button class="btn btn-hero-secondary" onclick="showHowItWorks()">
                    <i class="bi bi-play-circle me-2"></i>How It Works
                </button>
            </div>
        </div>
        <div class="dripbox-hero-visual">
            <div class="floating-boxes">
                <div class="floating-box floating-box-1">
                    <i class="bi bi-box-seam"></i>
                </div>
                <div class="floating-box floating-box-2">
                    <i class="bi bi-gift"></i>
                </div>
                <div class="floating-box floating-box-3">
                    <i class="bi bi-star"></i>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- How It Works -->
<section class="how-it-works" id="how-it-works">
    <div class="container">
        <div class="section-header text-center">
            <span class="section-subtitle">PROCESS</span>
            <h2 class="section-title">How DripBox Works</h2>
            <p class="section-description">Get your perfect drip in three simple steps</p>
        </div>
        <div class="process-steps">
            <div class="process-step">
                <div class="step-number">1</div>
                <div class="step-icon">
                    <i class="bi bi-search"></i>
                </div>
                <h3>Choose Your Style</h3>
                <p>Browse our curated collection of DripBoxes, each designed for different vibes and occasions</p>
            </div>
            <div class="process-step">
                <div class="step-number">2</div>
                <div class="step-icon">
                    <i class="bi bi-credit-card"></i>
                </div>
                <h3>Secure Checkout</h3>
                <p>Complete your purchase with our secure payment system and get instant order confirmation</p>
            </div>
            <div class="process-step">
                <div class="step-number">3</div>
                <div class="step-icon">
                    <i class="bi bi-truck"></i>
                </div>
                <h3>Fast Delivery</h3>
                <p>Receive your carefully curated DripBox at your doorstep within 3-5 business days</p>
            </div>
        </div>
    </div>
</section>

<!-- Featured DripBox -->
<section class="featured-dripbox" id="featured-box">
    <div class="container">
        <?php if ($featuredBox): ?>
            <div class="featured-box-card">
                <div class="featured-box-content">
                    <div class="featured-badges">
                        <span class="featured-badge">MOST POPULAR</span>
                        <span class="featured-badge featured-badge-accent">LIMITED STOCK</span>
                    </div>
                    <h2 class="featured-title"><?php echo htmlspecialchars($featuredBox['name']); ?></h2>
                    <p class="featured-theme"><?php echo htmlspecialchars($featuredBox['theme'] ?? 'Premium Collection'); ?></p>
                    <div class="featured-description">
                        <?php echo nl2br(htmlspecialchars($featuredBox['description'])); ?>
                    </div>
                    <div class="featured-highlights">
                        <div class="highlight-item">
                            <i class="bi bi-check-circle"></i>
                            <span>5+ Premium Items</span>
                        </div>
                        <div class="highlight-item">
                            <i class="bi bi-check-circle"></i>
                            <span>Exclusive Designs</span>
                        </div>
                        <div class="highlight-item">
                            <i class="bi bi-check-circle"></i>
                            <span>30% Savings</span>
                        </div>
                    </div>
                    <div class="featured-price">
                        <span class="price-label">Total Value:</span>
                        <span class="original-price"><?php echo CurrencyManager::formatPrice($featuredBox['price'] * 1.3); ?></span>
                        <span class="current-price"><?php echo CurrencyManager::formatPrice($featuredBox['price']); ?></span>
                    </div>
                    <div class="featured-actions">
                        <a href="checkout.php?box=<?php echo (int)$featuredBox['id']; ?>" class="btn btn-primary btn-lg">
                            <i class="bi bi-bag-plus me-2"></i>Get This DripBox
                        </a>
                        <button class="btn btn-outline-secondary" onclick="viewDetails(<?php echo (int)$featuredBox['id']; ?>)">
                            <i class="bi bi-eye me-2"></i>View Details
                        </button>
                    </div>
                </div>
                <div class="featured-box-visual">
                    <?php if ($featuredBox['image']): ?>
                        <img src="<?php echo $basePath; ?>/assets/images/dripboxes/<?php echo htmlspecialchars($featuredBox['image']); ?>" 
                             alt="<?php echo htmlspecialchars($featuredBox['name']); ?>" 
                             class="featured-image">
                    <?php else: ?>
                        <div class="featured-placeholder">
                            <i class="bi bi-box-seam"></i>
                        </div>
                    <?php endif; ?>
                    <div class="floating-elements">
                        <div class="floating-element element-1">
                            <i class="bi bi-tshirt"></i>
                        </div>
                        <div class="floating-element element-2">
                            <i class="bi bi-cap"></i>
                        </div>
                        <div class="floating-element element-3">
                            <i class="bi bi-gem"></i>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- All DripBoxes -->
<section class="all-dripboxes">
    <div class="container">
        <div class="section-header">
            <div class="section-header-content">
                <span class="section-subtitle">COMPLETE COLLECTION</span>
                <h2 class="section-title">All DripBoxes</h2>
                <p class="section-description">Explore our full range of curated streetwear bundles</p>
            </div>
        </div>
        <?php if (!empty($boxes)): ?>
            <div class="dripboxes-grid">
                <?php foreach ($boxes as $index => $box): ?>
                    <div class="dripbox-card" style="--delay: <?php echo $index * 0.1; ?>s">
                        <div class="dripbox-badges">
                            <?php if ($index === 0): ?>
                                <span class="badge badge-featured">Featured</span>
                            <?php endif; ?>
                            <span class="badge badge-new">New</span>
                        </div>
                        <div class="dripbox-image-container">
                            <?php if ($box['image']): ?>
                                <img src="<?php echo $basePath; ?>/assets/images/dripboxes/<?php echo htmlspecialchars($box['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($box['name']); ?>" 
                                     class="dripbox-image">
                            <?php else: ?>
                                <div class="dripbox-placeholder">
                                    <i class="bi bi-box-seam"></i>
                                </div>
                            <?php endif; ?>
                            <div class="dripbox-overlay">
                                <div class="dripbox-actions">
                                    <button class="btn btn-icon btn-wishlist" data-box-id="<?php echo (int)$box['id']; ?>">
                                        <i class="bi bi-heart"></i>
                                    </button>
                                    <button class="btn btn-icon btn-quickview" onclick="quickView(<?php echo (int)$box['id']; ?>)">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                                <button class="btn btn-get-box" onclick="getBox(<?php echo (int)$box['id']; ?>)">
                                    <i class="bi bi-bag-plus me-2"></i>Get This Box
                                </button>
                            </div>
                        </div>
                        <div class="dripbox-info">
                            <div class="dripbox-theme"><?php echo htmlspecialchars($box['theme'] ?? 'Premium Collection'); ?></div>
                            <h3 class="dripbox-name"><?php echo htmlspecialchars($box['name']); ?></h3>
                            <div class="dripbox-description">
                                <?php echo substr(htmlspecialchars($box['description']), 0, 100) . '...'; ?>
                            </div>
                            <div class="dripbox-price">
                                <span class="price-label">Starting from</span>
                                <span class="current-price"><?php echo CurrencyManager::formatPrice($box['price']); ?></span>
                                <span class="savings-badge">Save 30%</span>
                            </div>
                            <div class="dripbox-features">
                                <div class="feature-tag">
                                    <i class="bi bi-check-circle"></i>
                                    <span>5+ Items</span>
                                </div>
                                <div class="feature-tag">
                                    <i class="bi bi-truck"></i>
                                    <span>Free Shipping</span>
                                </div>
                                <div class="feature-tag">
                                    <i class="bi bi-shield-check"></i>
                                    <span>Quality Guaranteed</span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="bi bi-box-seam"></i>
                </div>
                <h3>No DripBoxes Available</h3>
                <p>Our stylists are working on new amazing collections. Check back soon!</p>
                <a href="shop.php" class="btn btn-primary">Shop Individual Items</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Benefits Section -->
<section class="dripbox-benefits">
    <div class="container">
        <div class="benefits-grid">
            <div class="benefit-item">
                <div class="benefit-icon">
                    <i class="bi bi-piggy-bank"></i>
                </div>
                <h3>Great Value</h3>
                <p>Save up to 30% compared to buying items individually</p>
            </div>
            <div class="benefit-item">
                <div class="benefit-icon">
                    <i class="bi bi-palette"></i>
                </div>
                <h3>Expert Curation</h3>
                <p>Styled by professionals who know streetwear trends</p>
            </div>
            <div class="benefit-item">
                <div class="benefit-icon">
                    <i class="bi bi-lightning"></i>
                </div>
                <h3>Exclusive Items</h3>
                <p>Access to limited edition pieces not sold separately</p>
            </div>
            <div class="benefit-item">
                <div class="benefit-icon">
                    <i class="bi bi-box-seam"></i>
                </div>
                <h3>Premium Packaging</h3>
                <p>Beautiful unboxing experience with premium materials</p>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="dripbox-faq">
    <div class="container">
        <div class="section-header text-center">
            <span class="section-subtitle">QUESTIONS</span>
            <h2 class="section-title">Frequently Asked Questions</h2>
            <p class="section-description">Everything you need to know about DripBox</p>
        </div>
        <div class="faq-grid">
            <div class="faq-item">
                <div class="faq-question" onclick="toggleFAQ(this)">
                    <h4>What's included in a DripBox?</h4>
                    <i class="bi bi-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    <p>Each DripBox contains 5+ carefully curated items including tops, bottoms, accessories, and exclusive pieces designed to create a complete outfit.</p>
                </div>
            </div>
            <div class="faq-item">
                <div class="faq-question" onclick="toggleFAQ(this)">
                    <h4>Can I customize my DripBox?</h4>
                    <i class="bi bi-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    <p>Currently, our DripBoxes are pre-curated by our stylists to ensure the perfect combination. However, we're working on customizable options for the future!</p>
                </div>
            </div>
            <div class="faq-item">
                <div class="faq-question" onclick="toggleFAQ(this)">
                    <h4>How long does delivery take?</h4>
                    <i class="bi bi-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    <p>Standard delivery takes 3-5 business days. Express delivery options are available at checkout for urgent orders.</p>
                </div>
            </div>
            <div class="faq-item">
                <div class="faq-question" onclick="toggleFAQ(this)">
                    <h4>What's your return policy?</h4>
                    <i class="bi bi-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    <p>We offer a 30-day return policy for unused items in original packaging. Please see our full return policy for more details.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* DripBox Hero */
.dripbox-hero {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 4rem 0;
    color: white;
    position: relative;
    overflow: hidden;
}

.dripbox-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%23ffffff" fill-opacity="0.05" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,122.7C672,117,768,139,864,154.7C960,171,1056,181,1152,165.3C1248,149,1344,107,1392,85.3L1440,64L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>') no-repeat bottom;
    background-size: cover;
}

.dripbox-hero-content {
    max-width: 600px;
    margin: 0 auto;
    text-align: center;
    position: relative;
    z-index: 2;
}

.hero-badges {
    display: flex;
    gap: 1rem;
    justify-content: center;
    margin-bottom: 2rem;
}

.hero-badge {
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.3);
    padding: 0.5rem 1rem;
    border-radius: 2rem;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.1em;
}

.hero-badge-accent {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    border: none;
}

.dripbox-title {
    font-size: 3.5rem;
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

.dripbox-subtitle {
    font-size: 1.25rem;
    opacity: 0.9;
    margin-bottom: 2rem;
    max-width: 500px;
    margin-left: auto;
    margin-right: auto;
}

.dripbox-stats {
    display: flex;
    justify-content: center;
    gap: 3rem;
    margin-bottom: 2rem;
}

.dripbox-stat {
    text-align: center;
}

.dripbox-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}

.dripbox-hero-visual {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    pointer-events: none;
}

.floating-boxes {
    position: relative;
    height: 100%;
}

.floating-box {
    position: absolute;
    width: 80px;
    height: 80px;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    color: white;
}

.floating-box-1 {
    top: 20%;
    left: 10%;
    animation: float 8s ease-in-out infinite;
}

.floating-box-2 {
    top: 60%;
    right: 15%;
    animation: float 10s ease-in-out infinite reverse;
}

.floating-box-3 {
    bottom: 20%;
    left: 20%;
    animation: float 12s ease-in-out infinite;
}

/* How It Works */
.how-it-works {
    padding: 4rem 0;
    background: #f8fafc;
}

.process-steps {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 3rem;
    margin-top: 3rem;
}

.process-step {
    text-align: center;
    position: relative;
}

.step-number {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0 auto 1.5rem;
}

.step-icon {
    width: 80px;
    height: 80px;
    background: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
    font-size: 2rem;
    color: #667eea;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.2);
}

.process-step h3 {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 1rem;
    color: #1f2937;
}

.process-step p {
    color: #6b7280;
    line-height: 1.6;
}

/* Featured DripBox */
.featured-dripbox {
    padding: 4rem 0;
    background: white;
}

.featured-box-card {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 4rem;
    align-items: center;
    background: linear-gradient(135deg, #f8fafc 0%, #e5e7eb 100%);
    border-radius: 2rem;
    padding: 3rem;
    position: relative;
    overflow: hidden;
}

.featured-box-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
    pointer-events: none;
}

.featured-box-content {
    position: relative;
    z-index: 2;
}

.featured-badges {
    display: flex;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.featured-badge {
    background: #667eea;
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 2rem;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.featured-badge-accent {
    background: #ef4444;
}

.featured-title {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    color: #1f2937;
}

.featured-theme {
    font-size: 1.125rem;
    color: #667eea;
    font-weight: 500;
    margin-bottom: 1.5rem;
}

.featured-description {
    color: #6b7280;
    line-height: 1.6;
    margin-bottom: 2rem;
}

.featured-highlights {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    margin-bottom: 2rem;
}

.highlight-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    color: #374151;
}

.highlight-item i {
    color: #10b981;
    font-size: 1.125rem;
}

.featured-price {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 2rem;
}

.price-label {
    color: #6b7280;
    font-size: 0.875rem;
}

.original-price {
    color: #9ca3af;
    text-decoration: line-through;
    font-size: 1.125rem;
}

.current-price {
    font-size: 2rem;
    font-weight: 700;
    color: #1f2937;
}

.featured-actions {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.featured-box-visual {
    position: relative;
    height: 400px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.featured-image {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    border-radius: 1rem;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
}

.featured-placeholder {
    width: 200px;
    height: 200px;
    background: rgba(102, 126, 234, 0.1);
    border-radius: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 4rem;
    color: #667eea;
}

.floating-elements {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    pointer-events: none;
}

.floating-element {
    position: absolute;
    width: 50px;
    height: 50px;
    background: rgba(255, 255, 255, 0.9);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    color: #667eea;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.element-1 {
    top: 10%;
    right: 10%;
    animation: float 6s ease-in-out infinite;
}

.element-2 {
    bottom: 20%;
    right: 20%;
    animation: float 8s ease-in-out infinite reverse;
}

.element-3 {
    top: 50%;
    left: 5%;
    animation: float 10s ease-in-out infinite;
}

/* All DripBoxes */
.all-dripboxes {
    padding: 4rem 0;
    background: #f8fafc;
}

.dripboxes-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 2rem;
    margin-top: 3rem;
}

.dripbox-card {
    background: white;
    border-radius: 1rem;
    overflow: hidden;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    position: relative;
    opacity: 0;
    animation: fadeInUp 0.5s ease forwards;
    animation-delay: var(--delay);
}

.dripbox-card:hover {
    transform: translateY(-10px);
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

.dripbox-badges {
    position: absolute;
    top: 1rem;
    left: 1rem;
    z-index: 3;
    display: flex;
    gap: 0.5rem;
}

.badge-featured {
    background: #f59e0b;
    color: white;
}

.dripbox-image-container {
    position: relative;
    height: 300px;
    overflow: hidden;
}

.dripbox-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.dripbox-card:hover .dripbox-image {
    transform: scale(1.1);
}

.dripbox-placeholder {
    width: 100%;
    height: 100%;
    background: #f3f4f6;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
    color: #9ca3af;
}

.dripbox-overlay {
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

.dripbox-card:hover .dripbox-overlay {
    opacity: 1;
}

.dripbox-actions {
    display: flex;
    gap: 1rem;
    margin-bottom: 1rem;
}

.btn-get-box {
    background: white;
    color: #1f2937;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 2rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-get-box:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
}

.dripbox-info {
    padding: 1.5rem;
}

.dripbox-theme {
    color: #667eea;
    font-size: 0.875rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 0.5rem;
}

.dripbox-name {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 0.75rem;
    color: #1f2937;
}

.dripbox-description {
    color: #6b7280;
    line-height: 1.5;
    margin-bottom: 1rem;
}

.dripbox-price {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1rem;
}

.savings-badge {
    background: #10b981;
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 1rem;
    font-size: 0.75rem;
    font-weight: 600;
}

.dripbox-features {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.feature-tag {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    font-size: 0.875rem;
    color: #6b7280;
}

.feature-tag i {
    color: #10b981;
}

/* Benefits */
.dripbox-benefits {
    padding: 4rem 0;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.benefits-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 3rem;
}

.benefit-item {
    text-align: center;
}

.benefit-icon {
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

.benefit-item h3 {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.benefit-item p {
    opacity: 0.9;
}

/* FAQ */
.dripbox-faq {
    padding: 4rem 0;
    background: #f8fafc;
}

.faq-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
    margin-top: 3rem;
}

.faq-item {
    background: white;
    border-radius: 1rem;
    overflow: hidden;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.faq-question {
    padding: 1.5rem;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: background-color 0.2s ease;
}

.faq-question:hover {
    background: #f8fafc;
}

.faq-question h4 {
    margin: 0;
    font-weight: 600;
    color: #1f2937;
}

.faq-question i {
    color: #667eea;
    transition: transform 0.2s ease;
}

.faq-item.active .faq-question i {
    transform: rotate(180deg);
}

.faq-answer {
    padding: 0 1.5rem;
    max-height: 0;
    overflow: hidden;
    transition: all 0.3s ease;
}

.faq-item.active .faq-answer {
    padding: 0 1.5rem 1.5rem;
    max-height: 200px;
}

.faq-answer p {
    color: #6b7280;
    line-height: 1.6;
    margin: 0;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    background: white;
    border-radius: 1rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.empty-icon {
    font-size: 4rem;
    color: #d1d5db;
    margin-bottom: 1rem;
}

.empty-state h3 {
    color: #374151;
    margin-bottom: 0.5rem;
}

.empty-state p {
    color: #6b7280;
    margin-bottom: 2rem;
}

/* Responsive */
@media (max-width: 768px) {
    .dripbox-title {
        font-size: 2.5rem;
    }
    
    .dripbox-stats {
        flex-direction: column;
        gap: 1rem;
    }
    
    .dripbox-actions {
        flex-direction: column;
        align-items: stretch;
    }
    
    .featured-box-card {
        grid-template-columns: 1fr;
        gap: 2rem;
        padding: 2rem;
    }
    
    .featured-title {
        font-size: 2rem;
    }
    
    .dripboxes-grid {
        grid-template-columns: 1fr;
    }
    
    .benefits-grid {
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 2rem;
    }
    
    .faq-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
// Smooth scroll
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });
});

// How it works modal
function showHowItWorks() {
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.innerHTML = `
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">How DripBox Works</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="process-steps">
                        <div class="process-step">
                            <div class="step-number">1</div>
                            <div class="step-icon">
                                <i class="bi bi-search"></i>
                            </div>
                            <h3>Choose Your Style</h3>
                            <p>Browse our curated collection of DripBoxes</p>
                        </div>
                        <div class="process-step">
                            <div class="step-number">2</div>
                            <div class="step-icon">
                                <i class="bi bi-credit-card"></i>
                            </div>
                            <h3>Secure Checkout</h3>
                            <p>Complete your purchase securely</p>
                        </div>
                        <div class="process-step">
                            <div class="step-number">3</div>
                            <div class="step-icon">
                                <i class="bi bi-truck"></i>
                            </div>
                            <h3>Fast Delivery</h3>
                            <p>Receive your DripBox in 3-5 days</p>
                        </div>
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

// View details
function viewDetails(boxId) {
    // For now, just scroll to the box or show a simple modal
    const boxElement = document.querySelector(`[data-box-id="${boxId}"]`);
    if (boxElement) {
        boxElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
        // Highlight the box temporarily
        boxElement.style.transform = 'scale(1.05)';
        boxElement.style.boxShadow = '0 20px 40px rgba(102, 126, 234, 0.3)';
        setTimeout(() => {
            boxElement.style.transform = '';
            boxElement.style.boxShadow = '';
        }, 1000);
    }
}

// Quick view
function quickView(boxId) {
    // Create a simple quick view modal
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.innerHTML = `
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">DripBox Quick View</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3">Loading DripBox details...</p>
                </div>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
    
    // Simulate loading and then show content
    setTimeout(() => {
        modal.querySelector('.modal-body').innerHTML = `
            <div class="text-center">
                <i class="bi bi-box-seam display-1 text-primary mb-3"></i>
                <h4>DripBox Details</h4>
                <p>This DripBox contains 5+ carefully curated items including tops, bottoms, and accessories.</p>
                <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                    <button class="btn btn-primary" onclick="getBox(${boxId})">
                        <i class="bi bi-bag-plus me-2"></i>Get This Box
                    </button>
                    <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        `;
    }, 1000);
    
    modal.addEventListener('hidden.bs.modal', () => {
        document.body.removeChild(modal);
    });
}

// Get box
function getBox(boxId) {
    // Add to cart as a special box item
    const basePath = window.DRIPYARD && window.DRIPYARD.basePath ? window.DRIPYARD.basePath : '..';
    
    // For DripBoxes, we'll redirect to checkout with the box ID
    window.location.href = `${basePath}/checkout.php?box=${boxId}`;
}

// FAQ toggle
function toggleFAQ(element) {
    const faqItem = element.parentElement;
    const allItems = document.querySelectorAll('.faq-item');
    
    allItems.forEach(item => {
        if (item !== faqItem) {
            item.classList.remove('active');
        }
    });
    
    faqItem.classList.toggle('active');
}


// Toast notification helper
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3`;
    toast.style.zIndex = '9999';
    toast.innerHTML = `
        <i class="bi bi-${type === 'success' ? 'check-circle' : 'info-circle'} me-2"></i>${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

// Set base path
document.addEventListener('DOMContentLoaded', function() {
    if (!window.DRIPYARD) {
        window.DRIPYARD = {};
    }
    window.DRIPYARD.basePath = '..';
});
</script>

<?php include __DIR__ . '/partials/footer.php'; ?>
