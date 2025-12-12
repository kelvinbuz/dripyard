<?php
$pageTitle = 'Admin Management';
$basePath = '../..';
require_once __DIR__ . '/../../backend/db.php';
require_once __DIR__ . '/../../backend/auth.php';

requireSuperAdmin();

$pdo = getPDO();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'], $_POST['role'])) {
    $id = (int)$_POST['user_id'];
    $role = $_POST['role'];
    
    // Validate role
    if (!in_array($role, ['admin', 'super_admin'])) {
        $message = 'Invalid role specified!';
    } else {
        $stmt = $pdo->prepare('UPDATE users SET role = ? WHERE id = ?');
        $stmt->execute([$role, $id]);
        $message = 'Admin role updated successfully!';
    }
}

if (isset($_GET['delete']) && $_GET['delete'] !== '') {
    $id = (int)$_GET['delete'];
    // Don't allow deletion of the current super admin user
    $currentUser = getCurrentUser();
    if ($id != $currentUser['id']) {
        $stmt = $pdo->prepare('DELETE FROM users WHERE id = ?');
        $stmt->execute([$id]);
        $message = 'Admin deleted successfully!';
    } else {
        $message = 'You cannot delete your own account!';
    }
}

$stmt = $pdo->query('SELECT id, name, email, role, created_at FROM users WHERE role IN ("admin", "super_admin") ORDER BY created_at DESC');
$users = $stmt->fetchAll();

// Get admin statistics
$userStats = [
    'total' => count($users),
    'super_admins' => count(array_filter($users, fn($u) => $u['role'] === 'super_admin')),
    'admins' => count(array_filter($users, fn($u) => $u['role'] === 'admin')),
    'recent' => count(array_filter($users, fn($u) => strtotime($u['created_at']) > strtotime('-7 days'))),
];

include __DIR__ . '/../partials/admin_header.php';
?>
<?php if ($message): ?>
    <div class="admin-alert admin-alert-success mb-4">
        <i class="bi bi-check-circle me-2"></i><?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<!-- Admin Statistics -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stat-card info admin-card">
            <div class="stat-icon">
                <i class="bi bi-people"></i>
            </div>
            <div class="stat-value"><?php echo $userStats['total']; ?></div>
            <div class="stat-label">Total Admins</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card primary admin-card">
            <div class="stat-icon">
                <i class="bi bi-person-badge"></i>
            </div>
            <div class="stat-value"><?php echo $userStats['super_admins']; ?></div>
            <div class="stat-label">Super Admins</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card success admin-card">
            <div class="stat-icon">
                <i class="bi bi-person"></i>
            </div>
            <div class="stat-value"><?php echo $userStats['admins']; ?></div>
            <div class="stat-label">Regular Admins</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card warning admin-card">
            <div class="stat-icon">
                <i class="bi bi-person-plus"></i>
            </div>
            <div class="stat-value"><?php echo $userStats['recent']; ?></div>
            <div class="stat-label">New This Week</div>
        </div>
    </div>
</div>

<!-- Admin Management -->
<div class="admin-card mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="card-title mb-0">
                <i class="bi bi-people me-2"></i>Admin Management
                <span class="badge bg-secondary ms-2"><?php echo count($users); ?></span>
            </h5>
            <div class="d-flex gap-2">
                <input type="text" class="form-control form-control-sm" placeholder="Search admins..." id="userSearch">
                <select class="form-select form-select-sm" id="roleFilter">
                    <option value="">All Roles</option>
                    <option value="super_admin">Super Admins</option>
                    <option value="admin">Regular Admins</option>
                </select>
            </div>
        </div>
        
        <?php if (empty($users)): ?>
            <div class="text-center py-5">
                <i class="bi bi-people fs-1 text-muted mb-3"></i>
                <h6 class="text-muted">No admin accounts yet</h6>
                <p class="text-muted small">Create admin accounts to manage the store</p>
            </div>
        <?php else: ?>
            <div class="table-responsive admin-table">
                <table class="table" id="usersTable">
                    <thead>
                        <tr>
                            <th>Admin</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Joined</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="user-avatar me-3">
                                            <i class="bi bi-person-circle fs-4 text-muted"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold"><?php echo htmlspecialchars($u['name']); ?></div>
                                            <div class="text-muted small">ID: #<?php echo (int)$u['id']; ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-envelope me-2 text-muted small"></i>
                                        <?php echo htmlspecialchars($u['email']); ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="status-badge status-badge-<?php echo $u['role'] === 'super_admin' ? 'active' : 'pending'; ?>">
                                        <?php echo $u['role'] === 'super_admin' ? 'Super Admin' : 'Admin'; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="text-muted small">
                                        <?php echo date('M j, Y', strtotime($u['created_at'])); ?>
                                    </div>
                                </td>
                                <td>
                                    <?php 
                                    $isRecent = strtotime($u['created_at']) > strtotime('-7 days');
                                    if ($isRecent): ?>
                                        <span class="badge bg-success">New</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Active</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="table-actions">
                                        <?php if ($u['role'] === 'admin'): ?>
                                            <form method="post" class="d-inline">
                                                <input type="hidden" name="user_id" value="<?php echo (int)$u['id']; ?>">
                                                <input type="hidden" name="role" value="super_admin">
                                                <button type="submit" class="btn btn-sm btn-admin-secondary" title="Make Super Admin">
                                                    <i class="bi bi-person-badge"></i>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <form method="post" class="d-inline">
                                                <input type="hidden" name="user_id" value="<?php echo (int)$u['id']; ?>">
                                                <input type="hidden" name="role" value="admin">
                                                <button type="submit" class="btn btn-sm btn-admin-secondary" title="Make Regular Admin">
                                                    <i class="bi bi-person"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        <?php 
                                        $currentUser = getCurrentUser();
                                        if ($u['id'] != $currentUser['id']): ?>
                                            <a href="?delete=<?php echo (int)$u['id']; ?>" class="btn btn-sm btn-admin-danger" data-confirm="Are you sure you want to delete this admin account?" title="Delete Admin">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-admin-secondary" disabled title="Cannot delete yourself">
                                                <i class="bi bi-shield-check"></i>
                                            </button>
                                        <?php endif; ?>
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

<!-- Create Admin Section -->
<div class="admin-card">
    <div class="card-body">
        <h5 class="card-title mb-3">Create New Admin Account</h5>
        <form method="post" action="create-admin.php">
            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select name="role" class="form-select" required>
                            <option value="admin">Regular Admin</option>
                            <option value="super_admin">Super Admin</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-admin-primary">
                <i class="bi bi-person-plus me-2"></i>Create Admin Account
            </button>
        </form>
    </div>
</div>
<?php include __DIR__ . '/../partials/admin_footer.php'; ?>

<script>
// Search functionality
document.getElementById('userSearch').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('#usersTable tbody tr');
    
    rows.forEach(row => {
        const userName = row.querySelector('td:nth-child(1)').textContent.toLowerCase();
        const email = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
        
        if (userName.includes(searchTerm) || email.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});

// Role filter
document.getElementById('roleFilter').addEventListener('change', function(e) {
    const roleFilter = e.target.value;
    const rows = document.querySelectorAll('#usersTable tbody tr');
    
    rows.forEach(row => {
        if (!roleFilter) {
            row.style.display = '';
        } else {
            const roleCell = row.querySelector('td:nth-child(3)');
            const roleText = roleCell.textContent.trim().toLowerCase();
            
            row.style.display = roleText === roleFilter ? '' : 'none';
        }
    });
});
</script>

<style>
.user-avatar {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 50%;
}
</style>
