<?php
// Shared header for DripYard public and admin pages

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($basePath)) {
    $basePath = '..';
}

require_once __DIR__ . '/../../backend/db.php';
require_once __DIR__ . '/../../backend/auth.php';

$currentUser = getCurrentUser();
$cartCount = getCartItemCount();

$pageTitleFull = 'DripYard | Stay Fresh. Stay Sunny.';
if (!empty($pageTitle)) {
    $pageTitleFull = htmlspecialchars($pageTitle) . ' | DripYard';
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $pageTitleFull; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?php echo $basePath; ?>/assets/css/styles.css" rel="stylesheet">
</head>
<body>
<?php include __DIR__ . '/navbar.php'; ?>
<main class="py-4">
    <div class="container">
