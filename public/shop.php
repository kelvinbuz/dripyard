<?php
$pageTitle = 'Shop';
$basePath = '..';
require_once __DIR__ . '/../backend/db.php';
require_once __DIR__ . '/../backend/auth.php';
require_once __DIR__ . '/../backend/currency.php';

$pdo = getPDO();

// Get categories
$stmtCats = $pdo->query('SELECT * FROM categories ORDER BY name ASC');
$categories = $stmtCats->fetchAll();

// Get selected category and sort options
$selectedCategory = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$sortBy = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// Build query based on filters
$whereClause = $selectedCategory ? 'WHERE c.id = ?' : '';
$orderBy = match($sortBy) {
    'price-low' => 'p.price ASC',
    'price-high' => 'p.price DESC',
    'name' => 'p.name ASC',
    'popular' => 'RAND()',
    default => 'p.created_at DESC'
};

if ($selectedCategory) {
    $stmt = $pdo->prepare("SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON p.category_id = c.id $whereClause ORDER BY $orderBy");
    $stmt->execute([$selectedCategory]);
} else {
    $stmt = $pdo->query("SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON p.category_id = c.id ORDER BY $orderBy");
}
$products = $stmt->fetchAll();

// Get product count for each category
$categoryCounts = [];
foreach ($categories as $cat) {
    $countStmt = $pdo->prepare('SELECT COUNT(*) FROM products WHERE category_id = ?');
    $countStmt->execute([$cat['id']]);
    $categoryCounts[$cat['id']] = $countStmt->fetchColumn();
}

include __DIR__ . '/partials/header.php';
?>

<!-- Shop Header -->
<section class="shop-header">
    <div class="container">
        <div class="shop-hero">
            <div class="shop-hero-content">
                <div class="shop-badges">
                    <span class="shop-badge">NEW ARRIVALS</span>
                    <span class="shop-badge shop-badge-accent">FALL 2024</span>
                </div>
                <h1 class="shop-title">Shop Collection</h1>
                <p class="shop-subtitle">Discover premium streetwear designed for the modern urban lifestyle</p>
                <div class="shop-stats">
                    <div class="shop-stat">
                        <span class="shop-stat-number"><?php echo count($products); ?></span>
                        <span class="shop-stat-label">Products</span>
                    </div>
                    <div class="shop-stat">
                        <span class="shop-stat-number"><?php echo count($categories); ?></span>
                        <span class="shop-stat-label">Categories</span>
                    </div>
                    <div class="shop-stat">
                        <span class="shop-stat-number">100%</span>
                        <span class="shop-stat-label">Quality</span>
                    </div>
                </div>
            </div>
            <div class="shop-hero-visual">
                <div class="floating-elements">
                    <div class="floating-element floating-element-1">
                        <i class="bi bi-tshirt"></i>
                    </div>
                    <div class="floating-element floating-element-2">
                        <i class="bi bi-lightning"></i>
                    </div>
                    <div class="floating-element floating-element-3">
                        <i class="bi bi-gem"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Shop Filters and Products -->
<section class="shop-content">
    <div class="container">
        <div class="shop-layout">
            <!-- Sidebar Filters -->
            <div class="shop-sidebar">
                <div class="filter-card">
                    <div class="filter-header">
                        <h3>Filters</h3>
                        <button class="btn btn-clear-filters" onclick="clearFilters()">Clear All</button>
                    </div>
                    
                    <!-- Categories -->
                    <div class="filter-section">
                        <h4 class="filter-title">Categories</h4>
                        <div class="category-filters">
                            <label class="category-filter">
                                <input type="radio" name="category" value="0" <?php if ($selectedCategory === 0) echo 'checked'; ?> onchange="filterByCategory(0)">
                                <span class="category-name">All Products</span>
                                <span class="category-count"><?php echo array_sum($categoryCounts); ?></span>
                            </label>
                            <?php foreach ($categories as $cat): ?>
                                <label class="category-filter">
                                    <input type="radio" name="category" value="<?php echo (int)$cat['id']; ?>" <?php if ($selectedCategory === (int)$cat['id']) echo 'checked'; ?> onchange="filterByCategory(<?php echo (int)$cat['id']; ?>)">
                                    <span class="category-name"><?php echo htmlspecialchars($cat['name']); ?></span>
                                    <span class="category-count"><?php echo $categoryCounts[$cat['id']] ?? 0; ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Price Range -->
                    <div class="filter-section">
                        <h4 class="filter-title">Price Range</h4>
                        <div class="price-range">
                            <div class="price-inputs">
                                <input type="number" placeholder="Min" class="form-control" id="minPrice">
                                <span>-</span>
                                <input type="number" placeholder="Max" class="form-control" id="maxPrice">
                            </div>
                            <button class="btn btn-apply-price" onclick="filterByPrice()">Apply</button>
                        </div>
                    </div>
                    
                    <!-- Quick Links -->
                    <div class="filter-section">
                        <h4 class="filter-title">Quick Links</h4>
                        <div class="quick-links">
                            <a href="shop.php?sort=popular" class="quick-link">Most Popular</a>
                            <a href="shop.php?sort=newest" class="quick-link">New Arrivals</a>
                            <a href="shop.php?sort=price-low" class="quick-link">Best Value</a>
                            <a href="dripbox.php" class="quick-link">DripBox Bundles</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="shop-main">
                <!-- Toolbar -->
                <div class="shop-toolbar">
                    <div class="toolbar-left">
                        <div class="results-count">
                            Showing <span><?php echo count($products); ?></span> products
                        </div>
                    </div>
                    <div class="toolbar-right">
                        <div class="view-options">
                            <button class="view-btn view-btn-grid active" onclick="setView('grid')">
                                <i class="bi bi-grid-3x3-gap"></i>
                            </button>
                            <button class="view-btn view-btn-list" onclick="setView('list')">
                                <i class="bi bi-list-ul"></i>
                            </button>
                        </div>
                        <div class="sort-dropdown">
                            <select class="form-select" onchange="sortProducts(this.value)">
                                <option value="newest" <?php if ($sortBy === 'newest') echo 'selected'; ?>>Newest First</option>
                                <option value="price-low" <?php if ($sortBy === 'price-low') echo 'selected'; ?>>Price: Low to High</option>
                                <option value="price-high" <?php if ($sortBy === 'price-high') echo 'selected'; ?>>Price: High to Low</option>
                                <option value="name" <?php if ($sortBy === 'name') echo 'selected'; ?>>Name: A to Z</option>
                                <option value="popular" <?php if ($sortBy === 'popular') echo 'selected'; ?>>Most Popular</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- Products Grid -->
                <div class="products-container">
                    <?php if (!empty($products)): ?>
                        <div class="products-grid" id="productsGrid">
                            <?php foreach ($products as $product): ?>
                                <div class="product-card">
                                    <div class="product-badges">
                                        <?php if ($product['stock'] < 10): ?>
                                            <span class="badge badge-low-stock">Low Stock</span>
                                        <?php endif; ?>
                                        <?php if (strtotime($product['created_at']) > strtotime('-7 days')): ?>
                                            <span class="badge badge-new">New</span>
                                        <?php endif; ?>
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
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-icon">
                                <i class="bi bi-search"></i>
                            </div>
                            <h3>No products found</h3>
                            <p>Try adjusting your filters or browse all categories</p>
                            <button class="btn btn-primary" onclick="clearFilters()">Clear Filters</button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* Shop Header */
.shop-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 3rem 0;
    color: white;
    position: relative;
    overflow: hidden;
}

.shop-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%23ffffff" fill-opacity="0.05" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,122.7C672,117,768,139,864,154.7C960,171,1056,181,1152,165.3C1248,149,1344,107,1392,85.3L1440,64L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>') no-repeat bottom;
    background-size: cover;
}

.shop-hero {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 3rem;
    align-items: center;
    position: relative;
    z-index: 2;
}

.shop-badges {
    display: flex;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.shop-badge {
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

.shop-badge-accent {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    border: none;
}

.shop-title {
    font-size: 3rem;
    font-weight: 800;
    line-height: 1.1;
    margin-bottom: 1rem;
}

.shop-subtitle {
    font-size: 1.125rem;
    opacity: 0.9;
    margin-bottom: 2rem;
    max-width: 500px;
}

.shop-stats {
    display: flex;
    gap: 2rem;
}

.shop-stat {
    text-align: center;
}

.shop-stat-number {
    display: block;
    font-size: 1.5rem;
    font-weight: 700;
    color: white;
}

.shop-stat-label {
    font-size: 0.875rem;
    opacity: 0.8;
}

.shop-hero-visual {
    position: relative;
    height: 300px;
}

.floating-elements {
    position: relative;
    height: 100%;
}

.floating-element {
    position: absolute;
    width: 60px;
    height: 60px;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
}

.floating-element-1 {
    top: 20%;
    left: 20%;
    animation: float 6s ease-in-out infinite;
}

.floating-element-2 {
    top: 50%;
    right: 30%;
    animation: float 8s ease-in-out infinite reverse;
}

.floating-element-3 {
    bottom: 20%;
    left: 40%;
    animation: float 10s ease-in-out infinite;
}

@keyframes float {
    0%, 100% { transform: translateY(0px) rotate(0deg); }
    50% { transform: translateY(-20px) rotate(10deg); }
}

/* Shop Content */
.shop-content {
    padding: 3rem 0;
    background: #f8fafc;
}

.shop-layout {
    display: grid;
    grid-template-columns: 280px 1fr;
    gap: 2rem;
}

/* Sidebar Filters */
.shop-sidebar {
    position: sticky;
    top: 2rem;
    height: fit-content;
}

.filter-card {
    background: white;
    border-radius: 1rem;
    padding: 1.5rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.filter-header {
    display: flex;
    justify-content: between;
    align-items: center;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #e5e7eb;
}

.filter-header h3 {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 600;
    color: #1f2937;
}

.btn-clear-filters {
    background: none;
    border: none;
    color: #667eea;
    font-weight: 500;
    cursor: pointer;
    font-size: 0.875rem;
}

.btn-clear-filters:hover {
    text-decoration: underline;
}

.filter-section {
    margin-bottom: 2rem;
}

.filter-section:last-child {
    margin-bottom: 0;
}

.filter-title {
    font-size: 0.875rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #6b7280;
    margin-bottom: 1rem;
}

.category-filters {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.category-filter {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.75rem;
    border-radius: 0.5rem;
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.category-filter:hover {
    background: #f3f4f6;
}

.category-filter input[type="radio"] {
    margin-right: 0.75rem;
}

.category-name {
    flex: 1;
    font-weight: 500;
    color: #374151;
}

.category-count {
    font-size: 0.875rem;
    color: #6b7280;
    background: #f3f4f6;
    padding: 0.25rem 0.5rem;
    border-radius: 1rem;
    min-width: 2rem;
    text-align: center;
}

.price-range {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.price-inputs {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.price-inputs input {
    flex: 1;
}

.price-inputs span {
    color: #6b7280;
    font-weight: 500;
}

.btn-apply-price {
    background: #667eea;
    color: white;
    border: none;
    padding: 0.75rem;
    border-radius: 0.5rem;
    font-weight: 500;
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.btn-apply-price:hover {
    background: #5a67d8;
}

.quick-links {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.quick-link {
    color: #667eea;
    text-decoration: none;
    font-weight: 500;
    padding: 0.5rem 0;
    border-bottom: 1px solid transparent;
    transition: all 0.2s ease;
}

.quick-link:hover {
    color: #5a67d8;
    border-bottom-color: #667eea;
}

/* Main Content */
.shop-main {
    min-height: 100vh;
}

.shop-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    padding: 1rem 1.5rem;
    background: white;
    border-radius: 1rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.toolbar-left .results-count {
    color: #6b7280;
    font-size: 0.875rem;
}

.toolbar-left .results-count span {
    font-weight: 600;
    color: #1f2937;
}

.toolbar-right {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.view-options {
    display: flex;
    gap: 0.25rem;
    background: #f3f4f6;
    padding: 0.25rem;
    border-radius: 0.5rem;
}

.view-btn {
    background: none;
    border: none;
    padding: 0.5rem;
    border-radius: 0.25rem;
    color: #6b7280;
    cursor: pointer;
    transition: all 0.2s ease;
}

.view-btn.active {
    background: white;
    color: #667eea;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.sort-dropdown .form-select {
    min-width: 200px;
    border-radius: 0.5rem;
    border: 1px solid #e5e7eb;
    padding: 0.5rem 1rem;
}

/* Products Grid */
.products-container {
    min-height: 400px;
}

.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 2rem;
}

/* Product Cards (reuse from homepage styles) */
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

.product-placeholder {
    width: 100%;
    height: 100%;
    background: #f3f4f6;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
    color: #9ca3af;
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
    cursor: pointer;
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
    cursor: pointer;
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
@media (max-width: 1024px) {
    .shop-layout {
        grid-template-columns: 1fr;
    }
    
    .shop-sidebar {
        position: static;
        order: 2;
    }
    
    .shop-main {
        order: 1;
    }
}

@media (max-width: 768px) {
    .shop-hero {
        grid-template-columns: 1fr;
        gap: 2rem;
        text-align: center;
    }
    
    .shop-title {
        font-size: 2rem;
    }
    
    .shop-stats {
        justify-content: center;
    }
    
    .shop-hero-visual {
        height: 200px;
    }
    
    .shop-toolbar {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
    }
    
    .toolbar-right {
        justify-content: space-between;
    }
    
    .products-grid {
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 1rem;
    }
}
</style>

<script>
// Filter functions
function filterByCategory(categoryId) {
    const url = new URL(window.location);
    if (categoryId == 0) {
        url.searchParams.delete('category');
    } else {
        url.searchParams.set('category', categoryId);
    }
    window.location.href = url.toString();
}

function filterByPrice() {
    const minPrice = document.getElementById('minPrice').value;
    const maxPrice = document.getElementById('maxPrice').value;
    const url = new URL(window.location);
    
    if (minPrice) url.searchParams.set('min', minPrice);
    if (maxPrice) url.searchParams.set('max', maxPrice);
    
    window.location.href = url.toString();
}

function sortProducts(sortBy) {
    const url = new URL(window.location);
    url.searchParams.set('sort', sortBy);
    window.location.href = url.toString();
}

function clearFilters() {
    window.location.href = 'shop.php';
}

function setView(viewType) {
    const grid = document.getElementById('productsGrid');
    const buttons = document.querySelectorAll('.view-btn');
    
    buttons.forEach(btn => btn.classList.remove('active'));
    
    if (viewType === 'list') {
        grid.classList.add('list-view');
        document.querySelector('.view-btn-list').classList.add('active');
    } else {
        grid.classList.remove('list-view');
        document.querySelector('.view-btn-grid').classList.add('active');
    }
}

// Add to cart functionality
document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const productId = this.dataset.productId;
        // Add to cart logic here
        this.innerHTML = '<i class="bi bi-check-circle me-2"></i>Added!';
        this.classList.add('added');
        setTimeout(() => {
            this.innerHTML = '<i class="bi bi-bag-plus me-2"></i>Add to Cart';
            this.classList.remove('added');
        }, 2000);
    });
});

</script>

<?php include __DIR__ . '/partials/footer.php'; ?>
