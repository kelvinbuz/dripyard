<?php
$pageTitle = 'Create Admin Account';
$basePath = '../..';
require_once __DIR__ . '/../../backend/db.php';
require_once __DIR__ . '/../../backend/auth.php';

requireSuperAdmin();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? 'admin';

    // Validate input
    if (!$name || !$email || !$password) {
        $error = 'All fields are required!';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match!';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long!';
    } elseif (!in_array($role, ['admin', 'super_admin'])) {
        $error = 'Invalid role specified!';
    } else {
        $pdo = getPDO();

        // Check if email already exists
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Email is already registered!';
        } else {
            // Create admin account
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO users (name, email, password, role, created_at) VALUES (?, ?, ?, ?, NOW())');
            $stmt->execute([$name, $email, $hashedPassword, $role]);
            
            $message = 'Admin account created successfully!';
            
            // Clear form
            $_POST = [];
        }
    }
}

include __DIR__ . '/../partials/admin_header.php';
?>

<?php if ($message): ?>
    <div class="admin-alert admin-alert-success mb-4">
        <i class="bi bi-check-circle me-2"></i><?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="admin-alert admin-alert-danger mb-4">
        <i class="bi bi-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?>
    </div>
<?php endif; ?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="admin-card">
            <div class="card-body">
                <div class="text-center mb-4">
                    <i class="bi bi-person-plus fs-1 text-primary mb-3"></i>
                    <h5 class="card-title">Create New Admin Account</h5>
                    <p class="text-muted">Add a new administrator to manage the DripYard store</p>
                </div>
                
                <form method="post">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Email Address</label>
                                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
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
                    
                    <div class="mb-4">
                        <label class="form-label">Admin Role</label>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="role" id="role_admin" value="admin" <?php echo ($_POST['role'] ?? '') !== 'super_admin' ? 'checked' : ''; ?> required>
                                    <label class="form-check-label" for="role_admin">
                                        <strong>Regular Admin</strong>
                                        <div class="text-muted small">Can manage products and orders</div>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="role" id="role_super_admin" value="super_admin" <?php echo ($_POST['role'] ?? '') === 'super_admin' ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="role_super_admin">
                                        <strong>Super Admin</strong>
                                        <div class="text-muted small">Full access including admin management</div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-admin-primary">
                            <i class="bi bi-person-plus me-2"></i>Create Admin Account
                        </button>
                        <a href="users.php" class="btn btn-admin-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Back to Admin Management
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Role Information -->
        <div class="admin-card mt-4">
            <div class="card-body">
                <h6 class="card-title mb-3">Role Permissions</h6>
                <div class="row">
                    <div class="col-md-6">
                        <div class="d-flex align-items-start mb-3">
                            <i class="bi bi-person text-primary me-3 mt-1"></i>
                            <div>
                                <strong>Regular Admin</strong>
                                <ul class="text-muted small mb-0">
                                    <li>Manage products and categories</li>
                                    <li>Process and manage orders</li>
                                    <li>View order statistics</li>
                                    <li>Cannot access admin management</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-start">
                            <i class="bi bi-person-badge text-warning me-3 mt-1"></i>
                            <div>
                                <strong>Super Admin</strong>
                                <ul class="text-muted small mb-0">
                                    <li>All Regular Admin permissions</li>
                                    <li>Create and manage admin accounts</li>
                                    <li>Change admin roles</li>
                                    <li>Delete admin accounts</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../partials/admin_footer.php'; ?>
