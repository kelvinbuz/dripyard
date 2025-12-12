<?php
$pageTitle = 'Add DripBox';
$basePath = '../..';
require_once __DIR__ . '/../../backend/db.php';
require_once __DIR__ . '/../../backend/auth.php';
require_once __DIR__ . '/../../backend/currency.php';

requireAdmin();

$pdo = getPDO();

$stmtProducts = $pdo->query('SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON p.category_id = c.id ORDER BY p.created_at DESC');
$products = $stmtProducts->fetchAll();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $boxName = trim($_POST['box_name'] ?? '');
    $boxTheme = trim($_POST['box_theme'] ?? '');
    $boxPrice = (float)($_POST['box_price'] ?? 0);
    $boxDescription = trim($_POST['box_description'] ?? '');

    $boxImage = '';
    if (isset($_FILES['box_image']) && $_FILES['box_image']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 5 * 1024 * 1024;

        if (!in_array($_FILES['box_image']['type'], $allowedTypes)) {
            $error = 'Only JPG, PNG, GIF, and WebP images are allowed.';
        } elseif ($_FILES['box_image']['size'] > $maxSize) {
            $error = 'Image size must be less than 5MB.';
        } else {
            $uploadDir = __DIR__ . '/../../assets/images/dripboxes/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $fileName = time() . '_' . basename($_FILES['box_image']['name']);
            $fileName = preg_replace('/[^A-Za-z0-9_\-.]/', '', $fileName);
            $uploadPath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['box_image']['tmp_name'], $uploadPath)) {
                $boxImage = $fileName;
            } else {
                $error = 'Failed to upload image. Please try again.';
            }
        }
    }

    if (!$error && $boxName !== '' && $boxPrice > 0) {
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('INSERT INTO sunnydripboxes (name, theme, description, price, image, created_at) VALUES (?, ?, ?, ?, ?, NOW())');
            $stmt->execute([$boxName, $boxTheme, $boxDescription, $boxPrice, $boxImage]);
            $boxId = (int)$pdo->lastInsertId();

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
                    $ins->execute([$boxId, $productId, $qty]);
                }
            }

            $pdo->commit();
            header('Location: dripboxes.php?success=dripbox_added');
            exit;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = 'Failed to create DripBox: ' . $e->getMessage();
        }
    } elseif (!$error) {
        $error = 'Please fill in all required fields.';
    }
}

include __DIR__ . '/../partials/admin_header.php';
?>

<?php if ($error): ?>
    <div class="admin-alert admin-alert-danger mb-4">
        <i class="bi bi-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?>
    </div>
<?php endif; ?>

<div class="admin-card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="card-title mb-0">Create DripBox Bundle</h5>
            <a href="dripboxes.php" class="btn btn-admin-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back
            </a>
        </div>

        <form method="post" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Bundle Name <span class="text-danger">*</span></label>
                        <input type="text" name="box_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Theme</label>
                        <input type="text" name="box_theme" class="form-control" placeholder="e.g., Casual Weekend">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Price (GH₵) <span class="text-danger">*</span></label>
                        <input type="number" name="box_price" class="form-control" min="0" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Bundle Image</label>
                        <input type="file" name="box_image" class="form-control" accept="image/*">
                        <div class="form-text">Allowed formats: JPG, PNG, GIF, WebP (Max 5MB)</div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="box_description" rows="6" class="form-control" placeholder="Describe what's included in this bundle..."></textarea>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Select Products in this DripBox</label>
                <div class="border rounded p-2" style="max-height: 360px; overflow: auto;">
                    <?php if (empty($products)): ?>
                        <div class="text-muted small">No products found. Add products first, then create a DripBox.</div>
                    <?php else: ?>
                        <?php foreach ($products as $p): ?>
                            <div class="d-flex align-items-center gap-2 py-1">
                                <input class="form-check-input dripbox-product-check" type="checkbox" id="box-prod-<?php echo (int)$p['id']; ?>" data-product-id="<?php echo (int)$p['id']; ?>">
                                <label class="form-check-label flex-grow-1" for="box-prod-<?php echo (int)$p['id']; ?>">
                                    <?php echo htmlspecialchars($p['name']); ?>
                                    <span class="text-muted small">(<?php echo htmlspecialchars($p['category_name']); ?>)</span>
                                </label>
                                <input type="number" min="1" value="1" class="form-control form-control-sm" style="width: 90px;" name="box_products[<?php echo (int)$p['id']; ?>]" id="box-qty-<?php echo (int)$p['id']; ?>" disabled>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <div class="form-text">Tick products to include in the DripBox, then set quantity for each.</div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" name="save_box" value="1" class="btn btn-admin-primary">
                    <i class="bi bi-check-circle me-2"></i>Create DripBox
                </button>
                <a href="dripboxes.php" class="btn btn-admin-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

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

<?php include __DIR__ . '/../partials/admin_footer.php'; ?>
