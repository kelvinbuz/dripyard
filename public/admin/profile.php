<?php
$pageTitle = 'My Profile';
$basePath = '../..';
require_once __DIR__ . '/../../backend/db.php';
require_once __DIR__ . '/../../backend/auth.php';

requireAdmin();

$currentUser = getCurrentUser();
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Update basic info
    if ($name && $email) {
        $result = updateProfile($name, $email);
        if (!$result['success']) {
            $error = $result['message'];
        } else {
            $message = 'Profile updated successfully!';
            $currentUser = getCurrentUser(); // Refresh user data
        }
    }

    // Update password if provided
    if ($currentPassword && $newPassword && $confirmPassword) {
        if ($newPassword !== $confirmPassword) {
            $error = 'New passwords do not match!';
        } elseif (strlen($newPassword) < 6) {
            $error = 'Password must be at least 6 characters long!';
        } else {
            // Verify current password
            $pdo = getPDO();
            $stmt = $pdo->prepare('SELECT password FROM users WHERE id = ?');
            $stmt->execute([$currentUser['id']]);
            $user = $stmt->fetch();

            if (password_verify($currentPassword, $user['password'])) {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
                $stmt->execute([$hashedPassword, $currentUser['id']]);
                $message .= ' Password updated successfully!';
            } else {
                $error = 'Current password is incorrect!';
            }
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

<div class="row">
    <div class="col-lg-4">
        <div class="admin-card">
            <div class="card-body text-center">
                <div class="mb-3">
                    <i class="bi bi-person-circle" style="font-size: 4rem; color: var(--admin-secondary);"></i>
                </div>
                <h5 class="card-title mb-1"><?php echo htmlspecialchars($currentUser['name']); ?></h5>
                <p class="text-muted mb-3"><?php echo htmlspecialchars($currentUser['email']); ?></p>
                <span class="status-badge status-badge-active">Administrator</span>
                
                <div class="mt-4 text-start">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <small class="text-muted">Member Since</small>
                        <small class="fw-semibold"><?php echo date('M j, Y', strtotime($currentUser['created_at'])); ?></small>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">Account Status</small>
                        <small class="fw-semibold text-success">Active</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-8">
        <div class="admin-card">
            <div class="card-body">
                <h5 class="card-title mb-4">Profile Information</h5>
                
                <form method="post">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($currentUser['name']); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Email Address</label>
                                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($currentUser['email']); ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    
                    <h6 class="mb-3">Change Password</h6>
                    <p class="text-muted small mb-3">Leave blank if you don't want to change your password</p>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Current Password</label>
                                <input type="password" name="current_password" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">New Password</label>
                                <input type="password" name="new_password" class="form-control" minlength="6">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Confirm New Password</label>
                                <input type="password" name="confirm_password" class="form-control" minlength="6">
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-admin-primary">
                            <i class="bi bi-check-circle me-2"></i>Save Changes
                        </button>
                        <a href="dashboard.php" class="btn btn-admin-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../partials/admin_footer.php'; ?>
