<?php
$pageTitle = 'Add Product';
$basePath = '../..';
require_once __DIR__ . '/../../backend/db.php';
require_once __DIR__ . '/../../backend/auth.php';
require_once __DIR__ . '/../../backend/currency.php';

requireAdmin();

$pdo = getPDO();
$message = '';
$error = '';

$stmtCats = $pdo->query('SELECT * FROM categories ORDER BY name ASC');
$categories = $stmtCats->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $categoryId = (int)($_POST['category_id'] ?? 0);
    $price = (float)($_POST['price'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $stock = (int)($_POST['stock'] ?? 0);
    
    // Handle image upload
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        
        if (!in_array($_FILES['image']['type'], $allowedTypes)) {
            $error = 'Only JPG, PNG, GIF, and WebP images are allowed.';
        } elseif ($_FILES['image']['size'] > $maxSize) {
            $error = 'Image size must be less than 5MB.';
        } else {
            $uploadDir = __DIR__ . '/../../assets/images/products/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileName = time() . '_' . basename($_FILES['image']['name']);
            $fileName = preg_replace('/[^A-Za-z0-9_\-.]/', '', $fileName);
            $uploadPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                $image = $fileName;
            } else {
                $error = 'Failed to upload image. Please try again.';
            }
        }
    }
    
    if (!$error && $name !== '' && $categoryId > 0 && $price > 0) {
        $stmt = $pdo->prepare('INSERT INTO products (name, category_id, price, description, image, stock, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())');
        $stmt->execute([$name, $categoryId, $price, $description, $image, $stock]);
        header('Location: products.php?success=product_added');
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
        <h5 class="card-title mb-4">Add New Product</h5>
        
        <form method="post" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Product Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Category <span class="text-danger">*</span></label>
                        <select name="category_id" class="form-select" required>
                            <option value="">Select category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo (int)$cat['id']; ?>" <?php echo (($_POST['category_id'] ?? '') == $cat['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Price (GH₵) <span class="text-danger">*</span></label>
                        <input type="number" name="price" class="form-control" min="0" step="0.01" value="<?php echo htmlspecialchars($_POST['price'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Stock Quantity <span class="text-danger">*</span></label>
                        <input type="number" name="stock" class="form-control" min="0" value="<?php echo htmlspecialchars($_POST['stock'] ?? '0'); ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Product Image</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                        <div class="form-text">Allowed formats: JPG, PNG, GIF, WebP (Max 5MB)</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" rows="4" class="form-control"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                    </div>
                </div>
            </div>
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-admin-primary">
                    <i class="bi bi-plus-circle me-2"></i>Add Product
                </button>
                <a href="products.php" class="btn btn-admin-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Back to Products
                </a>
            </div>
        </form>
    </div>
</div>
<?php include __DIR__ . '/../partials/admin_footer.php'; ?>
