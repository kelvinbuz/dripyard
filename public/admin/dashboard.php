<?php
$pageTitle = 'Admin Dashboard';
$basePath = '../..';
require_once __DIR__ . '/../../backend/db.php';
require_once __DIR__ . '/../../backend/auth.php';
require_once __DIR__ . '/../../backend/currency.php';

requireAdmin();

$pdo = getPDO();

// Get detailed stats
$stats = [
    'products' => (int)$pdo->query('SELECT COUNT(*) AS c FROM products')->fetch()['c'],
    'orders' => (int)$pdo->query('SELECT COUNT(*) AS c FROM orders')->fetch()['c'],
    'users' => (int)$pdo->query('SELECT COUNT(*) AS c FROM users')->fetch()['c'],
    'pending_orders' => (int)$pdo->query('SELECT COUNT(*) AS c FROM orders WHERE status = "pending"')->fetch()['c'],
    'total_revenue' => (float)$pdo->query('SELECT SUM(total_amount) AS total FROM orders WHERE status = "completed"')->fetch()['total'] ?? 0,
    'low_stock' => (int)$pdo->query('SELECT COUNT(*) AS c FROM products WHERE stock < 10')->fetch()['c'],
];

// Get recent orders
$recentOrders = $pdo->query('
    SELECT o.*, u.name as customer_name 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC 
    LIMIT 5
')->fetchAll();

// Get top products
$topProducts = $pdo->query('
    SELECT p.name, SUM(oi.quantity) as total_sold 
    FROM products p 
    JOIN order_items oi ON p.id = oi.product_id 
    JOIN orders o ON oi.order_id = o.id 
    WHERE o.status = "completed" 
    GROUP BY p.id, p.name 
    ORDER BY total_sold DESC 
    LIMIT 5
')->fetchAll();

include __DIR__ . '/../partials/admin_header.php';
?>
<!-- Stats Cards -->
<div class="row g-4 mb-4">
    <div class="col-lg-3 col-md-6">
        <div class="stat-card primary admin-card">
            <div class="stat-icon">
                <i class="bi bi-box-seam"></i>
            </div>
            <div class="stat-value"><?php echo $stats['products']; ?></div>
            <div class="stat-label">Total Products</div>
            <?php if ($stats['low_stock'] > 0): ?>
                <div class="text-warning small mt-2">
                    <i class="bi bi-exclamation-triangle"></i> <?php echo $stats['low_stock']; ?> low stock
                </div>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="stat-card success admin-card">
            <div class="stat-icon">
                <i class="bi bi-cart-check"></i>
            </div>
            <div class="stat-value"><?php echo $stats['orders']; ?></div>
            <div class="stat-label">Total Orders</div>
            <?php if ($stats['pending_orders'] > 0): ?>
                <div class="text-warning small mt-2">
                    <i class="bi bi-clock"></i> <?php echo $stats['pending_orders']; ?> pending
                </div>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="stat-card info admin-card">
            <div class="stat-icon">
                <i class="bi bi-people"></i>
            </div>
            <div class="stat-value"><?php echo $stats['users']; ?></div>
            <div class="stat-label">Total Users</div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="stat-card warning admin-card">
            <div class="stat-icon">
                <i class="bi bi-currency-dollar"></i>
            </div>
            <div class="stat-value"><?php echo CurrencyManager::formatPrice($stats['total_revenue']); ?></div>
            <div class="stat-label">Total Revenue</div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="admin-card mb-4">
    <div class="card-body">
        <h5 class="card-title mb-3">Quick Actions</h5>
        <div class="quick-actions">
            <a href="products.php" class="quick-action-btn">
                <i class="bi bi-plus-circle"></i>
                Add Product
            </a>
            <a href="orders.php" class="quick-action-btn">
                <i class="bi bi-list-check"></i>
                View Orders
            </a>
            <a href="users.php" class="quick-action-btn">
                <i class="bi bi-person-plus"></i>
                Manage Users
            </a>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Recent Orders -->
    <div class="col-lg-6">
        <div class="admin-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-0">Recent Orders</h5>
                    <a href="orders.php" class="btn btn-sm btn-admin-secondary">View All</a>
                </div>
                <?php if (empty($recentOrders)): ?>
                    <p class="text-muted text-center py-3">No orders yet</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentOrders as $order): ?>
                                    <tr>
                                        <td>#<?php echo (int)$order['id']; ?></td>
                                        <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                        <td><?php echo CurrencyManager::formatPrice($order['total_amount']); ?></td>
                                        <td>
                                            <span class="status-badge status-badge-<?php echo $order['status'] === 'completed' ? 'active' : 'pending'; ?>">
                                                <?php echo htmlspecialchars($order['status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Top Products -->
    <div class="col-lg-6">
        <div class="admin-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-0">Top Products</h5>
                    <a href="products.php" class="btn btn-sm btn-admin-secondary">Manage</a>
                </div>
                <?php if (empty($topProducts)): ?>
                    <p class="text-muted text-center py-3">No sales data yet</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Sold</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($topProducts as $product): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                                        <td><?php echo (int)$product['total_sold']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../partials/admin_footer.php'; ?>
