<?php
$pageTitle = 'Contact';
$basePath = '..';
require_once __DIR__ . '/../backend/db.php';
require_once __DIR__ . '/../backend/auth.php';

$sent = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sent = true; // In a real app you would send an email or store the message
}

include __DIR__ . '/partials/header.php';
?>
<div class="row g-4">
    <div class="col-md-6">
        <div class="section-title">Contact</div>
        <h1 class="h3 mb-3">Let9s talk DripYard fits</h1>
        <p class="text-muted mb-3">Questions about sizing, bulk orders, collaborations, or your SunnyDripBox? Hit us up and we9ll get back to you.</p>
        <ul class="list-unstyled small text-muted mb-0">
            <li><strong>Email:</strong> hello@dripyard.local</li>
            <li><strong>Instagram:</strong> @dripyard.clo</li>
        </ul>
    </div>
    <div class="col-md-6">
        <?php if ($sent): ?>
            <div class="alert alert-success">Thanks for reaching out! We9ll get back to you soon.</div>
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
                <label class="form-label">Message</label>
                <textarea name="message" rows="4" class="form-control" required></textarea>
            </div>
            <button type="submit" class="btn btn-sunny-primary">Send message</button>
        </form>
    </div>
</div>
<?php include __DIR__ . '/partials/footer.php'; ?>
