<?php
$pageTitle = 'Login';
$basePath = '..';
require_once __DIR__ . '/../backend/auth.php';

// If user is already logged in, redirect based on role
$existingUser = getCurrentUser();
if ($existingUser) {
	if ($existingUser['role'] === 'admin' || $existingUser['role'] === 'super_admin') {
		header('Location: admin/dashboard.php');
	} else {
		header('Location: dashboard.php');
	}
	exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$email = trim($_POST['email'] ?? '');
	$password = $_POST['password'] ?? '';

	$result = loginUser($email, $password);
	if ($result['success']) {
		// After successful login, redirect based on role
		$loggedInUser = getCurrentUser();
		if ($loggedInUser && ($loggedInUser['role'] === 'admin' || $loggedInUser['role'] === 'super_admin')) {
			header('Location: admin/dashboard.php');
		} else {
			header('Location: dashboard.php');
		}
		exit;
	} else {
		$error = $result['message'];
	}
}

include __DIR__ . '/partials/header.php';
?>
<div class="auth-page my-4 my-md-5">
    <div class="row justify-content-center py-1 py-md-2">
        <div class="col-12 col-sm-10 col-md-7 col-lg-5">
        <h1 class="h3 mb-3">Welcome back</h1>
        <p class="text-muted mb-4">Log in to view your orders and manage your DripYard drip.</p>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="post" class="card shadow-sm p-3">
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-sunny-primary w-100">Log in</button>
            <p class="small text-muted mt-3 mb-0">Forgot password? For this MVP, please contact support to reset.</p>
        </form>
        <p class="mt-3 small">New to DripYard? <a href="register.php">Create an account</a>.</p>
        </div>
    </div>
</div>
<?php include __DIR__ . '/partials/footer.php'; ?>
