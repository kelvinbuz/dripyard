<?php
$pageTitle = 'About';
$basePath = '..';
require_once __DIR__ . '/../backend/db.php';
require_once __DIR__ . '/../backend/auth.php';

include __DIR__ . '/partials/header.php';
?>
<div class="row g-4 align-items-center">
    <div class="col-md-6">
        <div class="section-title">About DripYard</div>
        <h1 class="h3 mb-3">Streetwear built for golden hours</h1>
        <p class="text-muted mb-3">DripYard is a youth-driven clothing line inspired by late-afternoon sun, city rooftops, and everyday moments with your crew. We blend bold colors with clean silhouettes for fits that feel effortless, not overdone.</p>
        <p class="text-muted mb-3">From tees and hoodies to curated SunnyDripBox drops, every piece is designed to keep you feeling fresh, confident, and ready for whatever the day brings.</p>
        <p class="fw-semibold mb-0">Stay Fresh. Stay Sunny.</p>
    </div>
    <div class="col-md-6">
        <div class="ratio ratio-4x3 bg-light rounded-4"></div>
    </div>
</div>
<?php include __DIR__ . '/partials/footer.php'; ?>
