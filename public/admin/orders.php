<?php
$pageTitle = 'Manage Orders';
$basePath = '../..';
require_once __DIR__ . '/../../backend/db.php';
require_once __DIR__ . '/../../backend/auth.php';
require_once __DIR__ . '/../../backend/currency.php';

requireAdmin();

$pdo = getPDO();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['status'])) {
    $id = (int)$_POST['order_id'];
    $status = trim($_POST['status']);
    $stmt = $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?');
    $stmt->execute([$status, $id]);
    $message = 'Order status updated successfully!';
}

$stmt = $pdo->query('SELECT o.*, u.name AS user_name, u.email AS user_email FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC');
$orders = $stmt->fetchAll();

// Get order statistics
$orderStats = [
    'total' => count($orders),
    'pending' => count(array_filter($orders, fn($o) => $o['status'] === 'pending')),
    'paid' => count(array_filter($orders, fn($o) => $o['status'] === 'paid')),
    'shipped' => count(array_filter($orders, fn($o) => $o['status'] === 'shipped')),
    'completed' => count(array_filter($orders, fn($o) => $o['status'] === 'completed')),
    'cancelled' => count(array_filter($orders, fn($o) => $o['status'] === 'cancelled')),
    'revenue' => array_sum(array_column(array_filter($orders, fn($o) => $o['status'] === 'completed'), 'total_amount')),
];

// Get recent orders (last 7 days)
$recentOrders = array_filter($orders, fn($o) => strtotime($o['created_at']) > strtotime('-7 days'));

include __DIR__ . '/../partials/admin_header.php';
?>
<?php if ($message): ?>
    <div class="admin-alert admin-alert-success mb-4">
        <i class="bi bi-check-circle me-2"></i><?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<!-- Order Statistics -->
<div class="row g-4 mb-4">
    <div class="col-lg-3 col-md-6">
        <div class="stat-card info admin-card">
            <div class="stat-icon">
                <i class="bi bi-cart-check"></i>
            </div>
            <div class="stat-value"><?php echo $orderStats['total']; ?></div>
            <div class="stat-label">Total Orders</div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="stat-card warning admin-card">
            <div class="stat-icon">
                <i class="bi bi-clock-history"></i>
            </div>
            <div class="stat-value"><?php echo $orderStats['pending']; ?></div>
            <div class="stat-label">Pending</div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="stat-card success admin-card">
            <div class="stat-icon">
                <i class="bi bi-truck"></i>
            </div>
            <div class="stat-value"><?php echo $orderStats['completed']; ?></div>
            <div class="stat-label">Completed</div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="stat-card primary admin-card">
            <div class="stat-icon">
                <i class="bi bi-currency-dollar"></i>
            </div>
            <div class="stat-value"><?php echo CurrencyManager::formatPrice($orderStats['revenue']); ?></div>
            <div class="stat-label">Revenue</div>
        </div>
    </div>
</div>

<!-- Order Status Chart -->
<div class="admin-card mb-4">
    <div class="card-body">
        <h5 class="card-title mb-3">Order Status Overview</h5>
        <div class="row">
            <div class="col-md-8">
                <div class="progress" style="height: 30px;">
                    <?php 
                    $statuses = ['pending', 'paid', 'shipped', 'completed', 'cancelled'];
                    $colors = ['warning', 'info', 'primary', 'success', 'danger'];
                    $total = max($orderStats['total'], 1);
                    
                    foreach ($statuses as $i => $status): 
                        $count = $orderStats[$status];
                        $percentage = ($count / $total) * 100;
                        if ($count > 0): ?>
                            <div class="progress-bar bg-<?php echo $colors[$i]; ?>" 
                                 style="width: <?php echo $percentage; ?>%"
                                 title="<?php echo ucfirst($status); ?>: <?php echo $count; ?> orders">
                                <?php echo $count; ?>
                            </div>
                        <?php endif; 
                    endforeach; ?>
                </div>
            </div>
            <div class="col-md-4">
                <div class="d-flex flex-wrap gap-2">
                    <?php foreach ($statuses as $i => $status): 
                        $count = $orderStats[$status]; 
                        if ($count > 0): ?>
                            <div class="d-flex align-items-center">
                                <div class="badge bg-<?php echo $colors[$i]; ?> me-2" style="width: 12px; height: 12px;"></div>
                                <small><?php echo ucfirst($status); ?> (<?php echo $count; ?>)</small>
                            </div>
                        <?php endif; 
                    endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Order Management -->
<div class="admin-card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="card-title mb-0">
                <i class="bi bi-cart-check me-2"></i>Order Management
                <span class="badge bg-secondary ms-2"><?php echo count($orders); ?></span>
            </h5>
            <div class="d-flex gap-2">
                <input type="text" class="form-control form-control-sm" placeholder="Search orders..." id="orderSearch">
                <select class="form-select form-select-sm" id="statusFilter">
                    <option value="">All Status</option>
                    <?php foreach ($statuses as $status): ?>
                        <option value="<?php echo $status; ?>"><?php echo ucfirst($status); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <?php if (empty($orders)): ?>
            <div class="text-center py-5">
                <i class="bi bi-cart-check fs-1 text-muted mb-3"></i>
                <h6 class="text-muted">No orders yet</h6>
                <p class="text-muted small">Orders will appear here when customers make purchases</p>
            </div>
        <?php else: ?>
            <div class="table-responsive admin-table">
                <table class="table" id="ordersTable">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Total</th>
                            <th>Payment</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold">#<?php echo (int)$order['id']; ?></div>
                                    <?php 
                                    $isRecent = strtotime($order['created_at']) > strtotime('-7 days');
                                    if ($isRecent): ?>
                                        <span class="badge bg-success">New</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="user-avatar me-2" style="width: 32px; height: 32px;">
                                            <i class="bi bi-person-circle text-muted"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold"><?php echo htmlspecialchars($order['user_name']); ?></div>
                                            <div class="text-muted small"><?php echo htmlspecialchars($order['user_email']); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="text-muted small">
                                        <?php echo date('M j, Y', strtotime($order['created_at'])); ?><br>
                                        <?php echo date('h:i A', strtotime($order['created_at'])); ?>
                                    </div>
                                </td>
                                <td>
                                    <form method="post" class="d-flex align-items-center gap-1">
                                        <input type="hidden" name="order_id" value="<?php echo (int)$order['id']; ?>">
                                        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                            <?php foreach (['pending','paid','shipped','completed','cancelled'] as $status): ?>
                                                <option value="<?php echo $status; ?>" <?php if ($order['status'] === $status) echo 'selected'; ?>>
                                                    <?php echo ucfirst($status); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </form>
                                </td>
                                <td class="fw-semibold"><?php echo CurrencyManager::formatPrice($order['total_amount']); ?></td>
                                <td>
                                    <?php if (!empty($order['payment_reference'])): ?>
                                        <div class="text-muted small" title="<?php echo htmlspecialchars($order['payment_reference']); ?>">
                                            <i class="bi bi-credit-card"></i> Paid
                                        </div>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="table-actions">
                                        <button class="btn btn-sm btn-admin-secondary" onclick="viewOrderDetails(<?php echo (int)$order['id']; ?>)" title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php include __DIR__ . '/../partials/admin_footer.php'; ?>

<script>
// Search functionality
document.getElementById('orderSearch').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('#ordersTable tbody tr');
    
    rows.forEach(row => {
        const orderId = row.querySelector('td:nth-child(1)').textContent.toLowerCase();
        const customer = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
        
        if (orderId.includes(searchTerm) || customer.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});

// Status filter
document.getElementById('statusFilter').addEventListener('change', function(e) {
    const statusFilter = e.target.value;
    const rows = document.querySelectorAll('#ordersTable tbody tr');
    
    rows.forEach(row => {
        if (!statusFilter) {
            row.style.display = '';
        } else {
            const statusSelect = row.querySelector('select[name="status"]');
            const currentStatus = statusSelect.value;
            
            row.style.display = currentStatus === statusFilter ? '' : 'none';
        }
    });
});

function viewOrderDetails(orderId) {
    // Implementation for viewing order details
    console.info('Order details view coming soon. Order ID:', orderId);
}
</script>
