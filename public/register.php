<?php
$pageTitle = 'Register';
$basePath = '..';
require_once __DIR__ . '/../backend/auth.php';

if (getCurrentUser()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';

    if ($password !== $passwordConfirm) {
        $error = 'Passwords do not match.';
    } else {
        $result = registerUser($name, $email, $password);
        if ($result['success']) {
            $success = $result['message'];
        } else {
            $error = $result['message'];
        }
    }
}

include __DIR__ . '/partials/header.php';
?>
<div class="auth-page my-4 my-md-5">
    <div class="row justify-content-center py-1 py-md-2">
        <div class="col-12 col-sm-10 col-md-8 col-lg-6">
        <h1 class="h3 mb-3">Create your DripYard account</h1>
        <p class="text-muted mb-4">Save your details, track orders, and get your SunnyDripBox faster.</p>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        <form method="post" class="card shadow-sm p-3">
            <div class="mb-3">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Confirm password</label>
                <input type="password" name="password_confirm" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-sunny-primary w-100">Sign up</button>
        </form>
        <p class="mt-3 small">Already have an account? <a href="login.php">Log in</a>.</p>
        </div>
    </div>
</div>
<?php include __DIR__ . '/partials/footer.php'; ?>
