<?php
// Professional admin header template
if (!isset($basePath)) {
    $basePath = '../..';
}

require_once __DIR__ . '/../../backend/db.php';
require_once __DIR__ . '/../../backend/auth.php';

$currentUser = getCurrentUser();
$pageTitleFull = 'DripYard Admin Panel';
if (!empty($pageTitle)) {
    $pageTitleFull = htmlspecialchars($pageTitle) . ' | DripYard Admin';
}

// Get admin stats for header
$pdo = getPDO();
$adminStats = [
    'products' => (int)$pdo->query('SELECT COUNT(*) AS c FROM products')->fetch()['c'],
    'categories' => (int)$pdo->query('SELECT COUNT(*) AS c FROM categories')->fetch()['c'],
    'orders' => (int)$pdo->query('SELECT COUNT(*) AS c FROM orders')->fetch()['c'],
    'users' => (int)$pdo->query('SELECT COUNT(*) AS c FROM users')->fetch()['c'],
    'pending_orders' => (int)$pdo->query('SELECT COUNT(*) AS c FROM orders WHERE status = "pending"')->fetch()['c'],
];
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $pageTitleFull; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?php echo $basePath; ?>/assets/css/admin.css" rel="stylesheet">
    <style>
        .admin-wrapper { display: flex; min-height: 100vh; }
        .admin-sidebar { width: 250px; background: #1e293b; border-right: 1px solid #e2e8f0; }
        .admin-main { flex: 1; }
        .admin-header { background: white; border-bottom: 1px solid #e2e8f0; padding: 1rem 2rem; }
        .admin-content { padding: 2rem; }
        @media (max-width: 768px) {
            .admin-sidebar { position: fixed; left: -250px; top: 0; height: 100vh; z-index: 1000; }
            .admin-sidebar.show { left: 0; }
            .admin-main { margin-left: 0; }
            .admin-content { padding: 1rem; }
        }
    </style>
</head>
<body>
<div class="admin-wrapper">
    <!-- Admin Sidebar -->
    <div class="admin-sidebar" id="adminSidebar">
        <div class="navbar-brand">
            <i class="bi bi-shop me-2"></i>
            DripYard Admin
        </div>
        <ul class="admin-nav">
            <li class="admin-nav-item">
                <a href="<?php echo $basePath; ?>/public/admin/dashboard.php" class="admin-nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?>">
                    <i class="bi bi-speedometer2"></i>
                    Dashboard
                </a>
            </li>
            <li class="admin-nav-item">
                <a href="<?php echo $basePath; ?>/public/admin/products.php" class="admin-nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'products.php' || basename($_SERVER['PHP_SELF']) === 'new-product.php' || basename($_SERVER['PHP_SELF']) === 'edit-product.php' ? 'active' : ''; ?>">
                    <i class="bi bi-box-seam"></i>
                    Products
                    <?php if ($adminStats['products'] > 0): ?>
                        <span class="badge bg-secondary ms-auto"><?php echo $adminStats['products']; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="admin-nav-item">
                <a href="<?php echo $basePath; ?>/public/admin/dripboxes.php" class="admin-nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'dripboxes.php' || basename($_SERVER['PHP_SELF']) === 'new-dripbox.php' || basename($_SERVER['PHP_SELF']) === 'edit-dripbox.php' ? 'active' : ''; ?>">
                    <i class="bi bi-box"></i>
                    DripBoxes
                </a>
            </li>
            <li class="admin-nav-item">
                <a href="<?php echo $basePath; ?>/public/admin/categories.php" class="admin-nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'categories.php' ? 'active' : ''; ?>">
                    <i class="bi bi-tags"></i>
                    Categories
                    <?php if ($adminStats['categories'] > 0): ?>
                        <span class="badge bg-secondary ms-auto"><?php echo $adminStats['categories']; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="admin-nav-item">
                <a href="<?php echo $basePath; ?>/public/admin/orders.php" class="admin-nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'orders.php' ? 'active' : ''; ?>">
                    <i class="bi bi-cart-check"></i>
                    Orders
                    <?php if ($adminStats['pending_orders'] > 0): ?>
                        <span class="badge bg-warning ms-auto"><?php echo $adminStats['pending_orders']; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <?php if ($currentUser['role'] === 'super_admin'): ?>
            <li class="admin-nav-item">
                <a href="<?php echo $basePath; ?>/public/admin/users.php" class="admin-nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'users.php' ? 'active' : ''; ?>">
                    <i class="bi bi-people"></i>
                    Admin Management
                    <?php if ($adminStats['users'] > 0): ?>
                        <span class="badge bg-secondary ms-auto"><?php echo $adminStats['users']; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <?php endif; ?>
            <li class="admin-nav-item mt-4">
                <a href="<?php echo $basePath; ?>/public/admin/profile.php" class="admin-nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'profile.php' ? 'active' : ''; ?>">
                    <i class="bi bi-person-circle"></i>
                    My Profile
                </a>
            </li>
            <li class="admin-nav-item">
                <a href="<?php echo $basePath; ?>/public/logout.php" class="admin-nav-link">
                    <i class="bi bi-box-arrow-right"></i>
                    Logout
                </a>
            </li>
        </ul>
    </div>

    <!-- Admin Main Content -->
    <div class="admin-main">
        <!-- Admin Header -->
        <div class="admin-header">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <button class="mobile-menu-toggle me-3" onclick="toggleSidebar()">
                        <i class="bi bi-list"></i>
                    </button>
                    <div>
                        <h1 class="page-title mb-1"><?php echo htmlspecialchars($pageTitle ?? 'Dashboard'); ?></h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="<?php echo $basePath; ?>/public/admin/dashboard.php">Admin</a></li>
                                <li class="breadcrumb-item active"><?php echo htmlspecialchars($pageTitle ?? 'Dashboard'); ?></li>
                            </ol>
                        </nav>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <div class="text-end">
                        <div class="fw-semibold"><?php echo htmlspecialchars($currentUser['name']); ?></div>
                        <div class="text-muted small"><?php echo $currentUser['role'] === 'super_admin' ? 'Super Administrator' : 'Administrator'; ?></div>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-light rounded-circle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle fs-4"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?php echo $basePath; ?>/public/admin/profile.php">
                                <i class="bi bi-person me-2"></i>My Profile
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?php echo $basePath; ?>/public/logout.php">
                                <i class="bi bi-box-arrow-right me-2"></i>Logout
                            </a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Admin Content -->
        <div class="admin-content">
