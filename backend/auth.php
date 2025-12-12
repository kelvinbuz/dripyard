<?php
// Authentication helpers for DripYard Clothing Line

require_once __DIR__ . '/db.php';

/**
 * Registers a new user account.
 */
function registerUser(string $name, string $email, string $password): array
{
    $pdo = getPDO();

    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'Email is already registered.'];
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
 
    $countStmt = $pdo->query('SELECT COUNT(*) AS c FROM users');
    $row = $countStmt->fetch();
    $isFirstUser = ((int)($row['c'] ?? 0) === 0);
    $role = $isFirstUser ? 'admin' : 'customer';

    $stmt = $pdo->prepare('INSERT INTO users (name, email, password, role, created_at) VALUES (?, ?, ?, ?, NOW())');
    $stmt->execute([$name, $email, $hash, $role]);

    return ['success' => true, 'message' => 'Registration successful. You can now log in.'];
}

/**
 * Attempts to log a user in by email and password.
 */
function loginUser(string $email, string $password): array
{
    $pdo = getPDO();

    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        return ['success' => false, 'message' => 'Invalid email or password.'];
    }

    $_SESSION['user_id'] = (int)$user['id'];
    $_SESSION['user_role'] = $user['role'];

    return ['success' => true, 'message' => 'Login successful.'];
}

/**
 * Logs the current user out.
 */
function logoutUser(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    session_destroy();
}

/**
 * Returns the currently authenticated user as an array, or null if not logged in.
 */
function getCurrentUser(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }

    static $cachedUser = null;
    if ($cachedUser !== null) {
        return $cachedUser;
    }

    $pdo = getPDO();
    $stmt = $pdo->prepare('SELECT id, name, email, role, profile_image, created_at FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!$user) {
        unset($_SESSION['user_id'], $_SESSION['user_role']);
        return null;
    }

    $cachedUser = $user;
    return $cachedUser;
}

/**
 * Ensures that a user is logged in before accessing a page.
 * Redirects to the login page for public routes.
 */
function requireLogin(): void
{
    if (!getCurrentUser()) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Ensures that the current user is an admin before accessing admin routes.
 */
function requireAdmin() {
    $user = getCurrentUser();
    if (!$user || ($user['role'] !== 'admin' && $user['role'] !== 'super_admin')) {
        header('Location: ../login.php');
        exit;
    }
}

function requireSuperAdmin() {
    $user = getCurrentUser();
    if (!$user || $user['role'] !== 'super_admin') {
        header('Location: ../admin/dashboard.php');
        exit;
    }
}

/**
 * Updates the profile for the currently logged-in user.
 */
function updateProfile(string $name, string $email, ?string $newPassword = null, ?string $profileImage = null): array
{
    $user = getCurrentUser();
    if (!$user) {
        return ['success' => false, 'message' => 'You must be logged in.'];
    }

    $pdo = getPDO();

    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1');
    $stmt->execute([$email, $user['id']]);
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'Email is already used by another account.'];
    }

    if ($newPassword && $profileImage) {
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('UPDATE users SET name = ?, email = ?, password = ?, profile_image = ? WHERE id = ?');
        $stmt->execute([$name, $email, $hash, $profileImage, $user['id']]);
    } elseif ($newPassword) {
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('UPDATE users SET name = ?, email = ?, password = ? WHERE id = ?');
        $stmt->execute([$name, $email, $hash, $user['id']]);
    } elseif ($profileImage) {
        $stmt = $pdo->prepare('UPDATE users SET name = ?, email = ?, profile_image = ? WHERE id = ?');
        $stmt->execute([$name, $email, $profileImage, $user['id']]);
    } else {
        $stmt = $pdo->prepare('UPDATE users SET name = ?, email = ? WHERE id = ?');
        $stmt->execute([$name, $email, $user['id']]);
    }

    return ['success' => true, 'message' => 'Profile updated successfully.'];
}

/**
 * Updates only the profile image for the current user.
 */
function updateProfileImage(string $profileImage): array
{
    $user = getCurrentUser();
    if (!$user) {
        return ['success' => false, 'message' => 'You must be logged in.'];
    }

    $pdo = getPDO();
    $stmt = $pdo->prepare('UPDATE users SET profile_image = ? WHERE id = ?');
    $stmt->execute([$profileImage, $user['id']]);

    return ['success' => true, 'message' => 'Profile image updated successfully.'];
}
