<?php
$pageTitle = 'Edit DripBox';
$basePath = '../..';
require_once __DIR__ . '/../../backend/db.php';
require_once __DIR__ . '/../../backend/auth.php';
require_once __DIR__ . '/../../backend/currency.php';

requireAdmin();

$pdo = getPDO();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare('SELECT * FROM sunnydripboxes WHERE id = ? LIMIT 1');
$stmt->execute([$id]);
$box = $stmt->fetch();

if (!$box) {
    header('Location: products.php');
    exit;
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['box_name'] ?? '');
    $theme = trim($_POST['box_theme'] ?? '');
    $price = (float)($_POST['box_price'] ?? 0);
    $description = trim($_POST['box_description'] ?? '');
    
    // Handle image upload
    $image = $box['image']; // Keep existing image by default
    if (isset($_FILES['box_image']) && $_FILES['box_image']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        
        if (!in_array($_FILES['box_image']['type'], $allowedTypes)) {
            $error = 'Only JPG, PNG, GIF, and WebP images are allowed.';
        } elseif ($_FILES['box_image']['size'] > $maxSize) {
            $error = 'Image size must be less than 5MB.';
        } else {
            $uploadDir = __DIR__ . '/../../assets/images/dripboxes/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Delete old image if exists
            if ($box['image'] && file_exists($uploadDir . $box['image'])) {
                unlink($uploadDir . $box['image']);
            }
            
            $fileName = time() . '_' . basename($_FILES['box_image']['name']);
            $fileName = preg_replace('/[^A-Za-z0-9_\-.]/', '', $fileName);
            $uploadPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['box_image']['tmp_name'], $uploadPath)) {
                $image = $fileName;
            } else {
                $error = 'Failed to upload image. Please try again.';
            }
        }
    }
    
    if (!$error && $name !== '' && $price > 0) {
        $stmt = $pdo->prepare('UPDATE sunnydripboxes SET name = ?, theme = ?, description = ?, price = ?, image = ? WHERE id = ?');
        $stmt->execute([$name, $theme, $description, $price, $image, $id]);
        header('Location: products.php?success=dripbox_updated');
        exit;
    } elseif (!$error) {
        $error = 'Please fill in all required fields.';
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

<div class="admin-card">
    <div class="card-body">
        <h5 class="card-title mb-4">Edit DripBox Bundle</h5>
        
        <form method="post" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Bundle Name <span class="text-danger">*</span></label>
                        <input type="text" name="box_name" class="form-control" value="<?php echo htmlspecialchars($box['name']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Theme</label>
                        <input type="text" name="box_theme" class="form-control" value="<?php echo htmlspecialchars($box['theme']); ?>" placeholder="e.g., Casual Weekend">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Price (GH₵) <span class="text-danger">*</span></label>
                        <input type="number" name="box_price" class="form-control" min="0" step="0.01" value="<?php echo htmlspecialchars($box['price']); ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Bundle Image</label>
                        <input type="file" name="box_image" class="form-control" accept="image/*">
                        <div class="form-text">Leave empty to keep current image. Allowed formats: JPG, PNG, GIF, WebP (Max 5MB)</div>
                        <?php if ($box['image']): ?>
                            <div class="mt-2">
                                <small class="text-muted">Current image:</small><br>
                                <img src="<?php echo $basePath; ?>/assets/images/dripboxes/<?php echo htmlspecialchars($box['image']); ?>" 
                                     alt="Current DripBox image" style="max-width: 100px; max-height: 100px; border: 1px solid #ddd; border-radius: 4px;">
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="box_description" rows="4" class="form-control" placeholder="Describe what's included in this bundle..."><?php echo htmlspecialchars($box['description']); ?></textarea>
                    </div>
                </div>
            </div>
            
            <div class="d-flex gap-2">
                <button type="submit" name="save_box" value="1" class="btn btn-admin-primary">
                    <i class="bi bi-check-circle me-2"></i>Update DripBox
                </button>
                <a href="products.php" class="btn btn-admin-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Back to Products
                </a>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../partials/admin_footer.php'; ?>
