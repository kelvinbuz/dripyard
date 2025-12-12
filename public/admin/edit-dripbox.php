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

$stmtProducts = $pdo->query('SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON p.category_id = c.id ORDER BY p.created_at DESC');
$products = $stmtProducts->fetchAll();

$stmtIncluded = $pdo->prepare('SELECT product_id, quantity FROM dripbox_products WHERE box_id = ?');
$stmtIncluded->execute([$id]);
$includedRows = $stmtIncluded->fetchAll();
$included = [];
foreach ($includedRows as $r) {
    $included[(int)$r['product_id']] = (int)$r['quantity'];
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
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('UPDATE sunnydripboxes SET name = ?, theme = ?, description = ?, price = ?, image = ? WHERE id = ?');
            $stmt->execute([$name, $theme, $description, $price, $image, $id]);

            $pdo->prepare('DELETE FROM dripbox_products WHERE box_id = ?')->execute([$id]);
            $selectedProducts = $_POST['box_products'] ?? [];
            if (is_array($selectedProducts) && $selectedProducts) {
                $ins = $pdo->prepare('INSERT INTO dripbox_products (box_id, product_id, quantity) VALUES (?, ?, ?)');
                foreach ($selectedProducts as $productIdRaw => $qtyRaw) {
                    $productId = (int)$productIdRaw;
                    $qty = (int)$qtyRaw;
                    if ($productId <= 0) {
                        continue;
                    }
                    if ($qty <= 0) {
                        $qty = 1;
                    }
                    $ins->execute([$id, $productId, $qty]);
                }
            }

            $pdo->commit();
            header('Location: products.php?success=dripbox_updated');
            exit;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = 'Failed to update DripBox: ' . $e->getMessage();
        }
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

                    <div class="mb-3">
                        <label class="form-label">Select Products in this DripBox</label>
                        <div class="border rounded p-2" style="max-height: 320px; overflow: auto;">
                            <?php if (empty($products)): ?>
                                <div class="text-muted small">No products found. Add products first.</div>
                            <?php else: ?>
                                <?php foreach ($products as $p): ?>
                                    <?php $pid = (int)$p['id']; ?>
                                    <?php $isIncluded = array_key_exists($pid, $included); ?>
                                    <div class="d-flex align-items-center gap-2 py-1">
                                        <input class="form-check-input dripbox-product-check" type="checkbox" id="box-prod-<?php echo $pid; ?>" data-product-id="<?php echo $pid; ?>" <?php echo $isIncluded ? 'checked' : ''; ?>>
                                        <label class="form-check-label flex-grow-1" for="box-prod-<?php echo $pid; ?>">
                                            <?php echo htmlspecialchars($p['name']); ?>
                                            <span class="text-muted small">(<?php echo htmlspecialchars($p['category_name']); ?>)</span>
                                        </label>
                                        <input type="number" min="1" value="<?php echo $isIncluded ? (int)$included[$pid] : 1; ?>" class="form-control form-control-sm" style="width: 90px;" name="box_products[<?php echo $pid; ?>]" id="box-qty-<?php echo $pid; ?>" <?php echo $isIncluded ? '' : 'disabled'; ?>>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <div class="form-text">Tick products to include in the DripBox, then set quantity for each.</div>
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

<script>
document.querySelectorAll('.dripbox-product-check').forEach(function(cb) {
    cb.addEventListener('change', function() {
        var pid = cb.getAttribute('data-product-id');
        var qty = document.getElementById('box-qty-' + pid);
        if (qty) {
            qty.disabled = !cb.checked;
            if (!cb.checked) {
                qty.value = '1';
            }
        }
    });
});
</script>
