<?php
$pageTitle = 'Dashboard';
$basePath = '..';
require_once __DIR__ . '/../backend/db.php';
require_once __DIR__ . '/../backend/auth.php';
require_once __DIR__ . '/../backend/currency.php';

requireLogin();

$pdo = getPDO();
$user = getCurrentUser();

// Calculate user statistics
$stmt = $pdo->prepare('SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC');
$stmt->execute([$user['id']]);
$orders = $stmt->fetchAll();

$userStats = [
    'total_orders' => count($orders),
    'total_spent' => array_sum(array_column($orders, 'total_amount')),
    'pending_orders' => count(array_filter($orders, fn($o) => $o['status'] === 'pending')),
    'completed_orders' => count(array_filter($orders, fn($o) => $o['status'] === 'completed')),
    'last_order' => !empty($orders) ? $orders[0] : null
];

$profileMessage = '';
$profileError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $newPassword = $_POST['new_password'] ?? '';
    if ($newPassword === '') {
        $newPassword = null;
    }
    
    // Handle profile image upload
    $profileImage = $user['profile_image'] ?? null; // Keep existing image by default
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 2 * 1024 * 1024; // 2MB
        
        if (!in_array($_FILES['profile_image']['type'], $allowedTypes)) {
            $profileError = 'Only JPG, PNG, GIF, and WebP images are allowed.';
        } elseif ($_FILES['profile_image']['size'] > $maxSize) {
            $profileError = 'Image size must be less than 2MB.';
        } else {
            $uploadDir = __DIR__ . '/../assets/images/profiles/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Delete old profile image if exists
            if ($user['profile_image']) {
                $oldImagePath = $uploadDir . $user['profile_image'];
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }
            
            $fileName = 'profile_' . $user['id'] . '_' . time() . '_' . basename($_FILES['profile_image']['name']);
            $fileName = preg_replace('/[^A-Za-z0-9_\-.]/', '', $fileName);
            $uploadPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadPath)) {
                $profileImage = $fileName;
            } else {
                $profileError = 'Failed to upload profile image. Please try again.';
            }
        }
    }
    
    if (!$profileError) {
        $result = updateProfile($name, $email, $newPassword, $profileImage);
        $profileMessage = $result['message'];
        $user = getCurrentUser(); // Refresh user data
    }
}

include __DIR__ . '/partials/header.php';
?>

<!-- Welcome Section -->
<div class="dashboard-welcome mb-4">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h1 class="dashboard-title">Welcome back, <?php echo htmlspecialchars($user['name']); ?>! 👋</h1>
            <p class="dashboard-subtitle">Manage your profile and track your DripYard orders in style</p>
        </div>
        <div class="col-md-4 text-md-end">
            <div class="user-avatar">
                <div class="avatar-circle">
                    <?php if ($user['profile_image']): ?>
                        <img src="<?php echo $basePath; ?>/assets/images/profiles/<?php echo htmlspecialchars($user['profile_image']); ?>" 
                             alt="Profile" class="avatar-img">
                    <?php else: ?>
                        <?php echo strtoupper(substr($user['name'], 0, 2)); ?>
                    <?php endif; ?>
                </div>
                <div class="user-info">
                    <div class="user-name"><?php echo htmlspecialchars($user['name']); ?></div>
                    <div class="user-email"><?php echo htmlspecialchars($user['email']); ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($profileMessage): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i><?php echo htmlspecialchars($profileMessage); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (isset($_GET['order']) && $_GET['order'] === 'success'): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>Your order was placed successfully! Thanks for staying sunny with DripYard! 🌟
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Statistics Cards -->
<div class="row g-4 mb-4">
    <div class="col-lg-3 col-md-6">
        <div class="stat-card stat-card-primary">
            <div class="stat-icon">
                <i class="bi bi-box-seam"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number"><?php echo $userStats['total_orders']; ?></div>
                <div class="stat-label">Total Orders</div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="stat-card stat-card-success">
            <div class="stat-icon">
                <i class="bi bi-currency-dollar"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number"><?php echo CurrencyManager::formatPrice($userStats['total_spent']); ?></div>
                <div class="stat-label">Total Spent</div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="stat-card stat-card-warning">
            <div class="stat-icon">
                <i class="bi bi-clock-history"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number"><?php echo $userStats['pending_orders']; ?></div>
                <div class="stat-label">Pending Orders</div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="stat-card stat-card-info">
            <div class="stat-icon">
                <i class="bi bi-check-circle"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number"><?php echo $userStats['completed_orders']; ?></div>
                <div class="stat-label">Completed</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <?php if ($profileError): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i><?php echo htmlspecialchars($profileError); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Profile Section -->
<div class="col-lg-4">
    <div class="dashboard-card">
        <div class="card-header">
            <h5 class="card-title">
                <i class="bi bi-person-circle me-2"></i>Profile Settings
            </h5>
        </div>
        <div class="card-body">
            <form method="post" id="profileForm" enctype="multipart/form-data">
                <input type="hidden" name="update_profile" value="1">
                
                <!-- Profile Image Upload -->
                <div class="profile-image-section mb-4">
                    <label class="form-label">Profile Picture</label>
                    <div class="profile-image-upload">
                        <div class="current-avatar">
                            <?php if ($user['profile_image']): ?>
                                <img src="<?php echo $basePath; ?>/assets/images/profiles/<?php echo htmlspecialchars($user['profile_image']); ?>" 
                                     alt="Current Profile" class="current-profile-img">
                            <?php else: ?>
                                <div class="avatar-placeholder">
                                    <i class="bi bi-person"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="upload-controls">
                            <input type="file" name="profile_image" id="profile_image" class="form-control" accept="image/*" onchange="previewProfileImage(event)">
                            <div class="form-text">JPG, PNG, GIF, WebP (Max 2MB)</div>
                            <div id="imagePreview" class="image-preview"></div>
                        </div>
                    </div>
                </div>
                
                <!-- Basic Information -->
                <div class="form-section">
                    <h6 class="section-title">Basic Information</h6>
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                            <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                    </div>
                </div>
                
                <!-- Security Section -->
                <div class="form-section">
                    <h6 class="section-title">Security</h6>
                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" name="new_password" id="new_password" class="form-control" placeholder="Leave blank to keep current">
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword()">
                                <i class="bi bi-eye" id="passwordToggle"></i>
                            </button>
                        </div>
                        <div class="form-text">Optional: Enter new password to update</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirm New Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                            <input type="password" id="confirm_password" class="form-control" placeholder="Confirm new password">
                        </div>
                        <div class="invalid-feedback" id="passwordMismatch">Passwords do not match</div>
                    </div>
                </div>
                
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary" id="updateBtn">
                        <i class="bi bi-check-circle me-2"></i>Update Profile
                    </button>
                    <button type="button" class="btn btn-outline-secondary" onclick="resetForm()">
                        <i class="bi bi-arrow-clockwise me-2"></i>Reset Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

    <!-- Orders Section -->
    <div class="col-lg-8">
        <div class="dashboard-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title">
                    <i class="bi bi-bag-check me-2"></i>Recent Orders
                </h5>
                <?php if (!empty($orders)): ?>
                    <span class="badge bg-primary"><?php echo $userStats['total_orders']; ?> Orders</span>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if (empty($orders)): ?>
                    <div class="empty-state text-center py-5">
                        <div class="empty-icon">
                            <i class="bi bi-bag"></i>
                        </div>
                        <h5 class="empty-title">No orders yet</h5>
                        <p class="empty-text">Your first DripYard fit is just a few clicks away!</p>
                        <a href="shop.php" class="btn btn-primary">
                            <i class="bi bi-bag-plus me-2"></i>Start Shopping
                        </a>
                    </div>
                <?php else: ?>
                    <div class="orders-table">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Total</th>
                                        <th>Payment</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $index => $order): ?>
                                        <tr class="<?php echo $index === 0 ? 'table-active' : ''; ?>">
                                            <td>
                                                <div class="order-id">
                                                    <strong>#<?php echo (int)$order['id']; ?></strong>
                                                    <?php if ($index === 0): ?>
                                                        <span class="badge bg-success ms-1">Latest</span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="order-date">
                                                    <i class="bi bi-calendar3 me-1"></i>
                                                    <?php echo date('M j, Y', strtotime($order['created_at'])); ?>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="status-badge status-<?php echo $order['status']; ?>">
                                                    <?php 
                                                    $statusIcons = [
                                                        'pending' => 'clock',
                                                        'paid' => 'credit-card',
                                                        'shipped' => 'truck',
                                                        'completed' => 'check-circle',
                                                        'cancelled' => 'x-circle'
                                                    ];
                                                    $icon = $statusIcons[$order['status']] ?? 'question-circle';
                                                    ?>
                                                    <i class="bi bi-<?php echo $icon; ?> me-1"></i>
                                                    <?php echo ucfirst($order['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="order-total">
                                                    <strong><?php echo CurrencyManager::formatPrice($order['total_amount']); ?></strong>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="payment-info">
                                                    <?php if (!empty($order['payment_reference'])): ?>
                                                        <span class="payment-success">
                                                            <i class="bi bi-check-circle"></i> Paid
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="payment-pending">
                                                            <i class="bi bi-clock"></i> Pending
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
/* Dashboard Styles */
.dashboard-welcome {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 2rem;
    border-radius: 1rem;
    margin-bottom: 2rem;
}

.dashboard-title {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.dashboard-subtitle {
    font-size: 1.1rem;
    opacity: 0.9;
    margin-bottom: 0;
}

.user-avatar {
    display: flex;
    align-items: center;
    gap: 1rem;
    justify-content: flex-end;
}

.avatar-circle {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1.2rem;
    color: white;
    border: 3px solid rgba(255, 255, 255, 0.3);
    overflow: hidden;
    position: relative;
}

.avatar-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    position: absolute;
    top: 0;
    left: 0;
}

.user-info {
    text-align: right;
}

.user-name {
    font-weight: 600;
    color: white;
}

.user-email {
    font-size: 0.9rem;
    opacity: 0.8;
    color: white;
}

/* Statistics Cards */
.stat-card {
    background: white;
    border-radius: 1rem;
    padding: 1.5rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
    border: 1px solid #e5e7eb;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--stat-color) 0%, var(--stat-color-light) 100%);
}

.stat-card-primary { --stat-color: #3b82f6; --stat-color-light: #60a5fa; }
.stat-card-success { --stat-color: #10b981; --stat-color-light: #34d399; }
.stat-card-warning { --stat-color: #f59e0b; --stat-color-light: #fbbf24; }
.stat-card-info { --stat-color: #06b6d4; --stat-color-light: #22d3ee; }

.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 0.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
    margin-bottom: 1rem;
}

.stat-card-primary .stat-icon { background: linear-gradient(135deg, #3b82f6, #60a5fa); }
.stat-card-success .stat-icon { background: linear-gradient(135deg, #10b981, #34d399); }
.stat-card-warning .stat-icon { background: linear-gradient(135deg, #f59e0b, #fbbf24); }
.stat-card-info .stat-icon { background: linear-gradient(135deg, #06b6d4, #22d3ee); }

.stat-number {
    font-size: 1.8rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 0.25rem;
}

.stat-label {
    color: #6b7280;
    font-size: 0.9rem;
    font-weight: 500;
}

/* Dashboard Cards */
.dashboard-card {
    background: white;
    border-radius: 1rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
    border: 1px solid #e5e7eb;
    overflow: hidden;
}

.dashboard-card .card-header {
    background: #f8fafc;
    border-bottom: 1px solid #e5e7eb;
    padding: 1.25rem;
}

.dashboard-card .card-body {
    padding: 1.5rem;
}

.dashboard-card .card-title {
    margin: 0;
    font-weight: 600;
    color: #1f2937;
}

/* Profile Image Upload */
.profile-image-section {
    text-align: center;
    padding: 1.5rem;
    background: #f8fafc;
    border-radius: 0.75rem;
    border: 2px dashed #e5e7eb;
    transition: all 0.3s ease;
}

.profile-image-section:hover {
    border-color: #3b82f6;
    background: #eff6ff;
}

.profile-image-upload {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
}

.current-avatar {
    position: relative;
    width: 120px;
    height: 120px;
    border-radius: 50%;
    overflow: hidden;
    border: 4px solid white;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    background: #f3f4f6;
    display: flex;
    align-items: center;
    justify-content: center;
}

.current-profile-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar-placeholder {
    font-size: 3rem;
    color: #9ca3af;
}

.upload-controls {
    width: 100%;
    max-width: 250px;
}

.image-preview {
    margin-top: 0.5rem;
    border-radius: 0.5rem;
    overflow: hidden;
    display: none;
}

.image-preview img {
    width: 100%;
    height: auto;
    border-radius: 0.5rem;
}

/* Form Sections */
.form-section {
    margin-bottom: 2rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid #e5e7eb;
}

.form-section:last-child {
    border-bottom: none;
    margin-bottom: 0;
}

.section-title {
    color: #374151;
    font-weight: 600;
    margin-bottom: 1rem;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

/* Form Styles */
.input-group {
    margin-bottom: 1rem;
}

.input-group-text {
    background: #f8fafc;
    border: 1px solid #e5e7eb;
    color: #6b7280;
}

.form-control:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.25);
}

.form-control.is-invalid {
    border-color: #ef4444;
}

.invalid-feedback {
    display: none;
    color: #ef4444;
    font-size: 0.875rem;
    margin-top: 0.25rem;
}

.invalid-feedback.show {
    display: block;
}

/* Empty State */
.empty-state {
    padding: 3rem 1rem;
}

.empty-icon {
    font-size: 4rem;
    color: #d1d5db;
    margin-bottom: 1rem;
}

.empty-title {
    color: #6b7280;
    margin-bottom: 0.5rem;
}

.empty-text {
    color: #9ca3af;
    margin-bottom: 1.5rem;
}

/* Status Badges */
.status-badge {
    padding: 0.35rem 0.75rem;
    border-radius: 0.5rem;
    font-size: 0.85rem;
    font-weight: 500;
}

.status-pending { background: #fef3c7; color: #92400e; }
.status-paid { background: #dbeafe; color: #1e40af; }
.status-shipped { background: #e0e7ff; color: #3730a3; }
.status-completed { background: #d1fae5; color: #065f46; }
.status-cancelled { background: #fee2e2; color: #991b1b; }

.payment-success {
    color: #059669;
    font-size: 0.85rem;
}

.payment-pending {
    color: #d97706;
    font-size: 0.85rem;
}

.order-id strong {
    color: #1f2937;
}

.order-date {
    color: #6b7280;
    font-size: 0.9rem;
}

.order-total {
    color: #1f2937;
}

/* Table Styles */
.table-responsive {
    border-radius: 0.5rem;
    overflow: hidden;
}

.table {
    margin-bottom: 0;
}

.table th {
    background: #f8fafc;
    border-bottom: 2px solid #e5e7eb;
    color: #374151;
    font-weight: 600;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.table td {
    vertical-align: middle;
    border-bottom: 1px solid #f3f4f6;
}

.table-hover tbody tr:hover {
    background-color: #f8fafc;
}

.table-active {
    background-color: #eff6ff !important;
}

/* Responsive */
@media (max-width: 768px) {
    .dashboard-welcome {
        padding: 1.5rem;
    }
    
    .dashboard-title {
        font-size: 1.5rem;
    }
    
    .user-avatar {
        justify-content: flex-start;
        margin-top: 1rem;
    }
    
    .user-info {
        text-align: left;
    }
    
    .stat-card {
        margin-bottom: 1rem;
    }
    
    .profile-image-upload {
        flex-direction: column;
        align-items: center;
    }
    
    .current-avatar {
        width: 100px;
        height: 100px;
    }
}
</style>

<script>
// Profile Image Preview
function previewProfileImage(event) {
    const file = event.target.files[0];
    const preview = document.getElementById('imagePreview');
    
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = `<img src="${e.target.result}" alt="Preview" style="max-width: 200px; border-radius: 0.5rem;">`;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    } else {
        preview.style.display = 'none';
        preview.innerHTML = '';
    }
}

// Password Toggle
function togglePassword() {
    const passwordField = document.getElementById('new_password');
    const toggleIcon = document.getElementById('passwordToggle');
    
    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        toggleIcon.className = 'bi bi-eye-slash';
    } else {
        passwordField.type = 'password';
        toggleIcon.className = 'bi bi-eye';
    }
}

// Password Confirmation
document.getElementById('confirm_password').addEventListener('input', function() {
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = this.value;
    const feedback = document.getElementById('passwordMismatch');
    
    if (confirmPassword && newPassword !== confirmPassword) {
        this.classList.add('is-invalid');
        feedback.classList.add('show');
        document.getElementById('updateBtn').disabled = true;
    } else {
        this.classList.remove('is-invalid');
        feedback.classList.remove('show');
        document.getElementById('updateBtn').disabled = false;
    }
});

// Reset Form
function resetForm() {
    if (confirm('Are you sure you want to reset all changes?')) {
        document.getElementById('profileForm').reset();
        document.getElementById('imagePreview').style.display = 'none';
        document.getElementById('imagePreview').innerHTML = '';
        document.getElementById('confirm_password').classList.remove('is-invalid');
        document.getElementById('passwordMismatch').classList.remove('show');
        document.getElementById('updateBtn').disabled = false;
    }
}

// Form Validation
document.getElementById('profileForm').addEventListener('submit', function(e) {
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    if (newPassword && newPassword !== confirmPassword) {
        e.preventDefault();
        document.getElementById('confirm_password').classList.add('is-invalid');
        document.getElementById('passwordMismatch').classList.add('show');
    }
});
</script>
