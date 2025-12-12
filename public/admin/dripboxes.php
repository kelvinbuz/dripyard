<?php
$pageTitle = 'DripBoxes';
$basePath = '../..';
require_once __DIR__ . '/../../backend/db.php';
require_once __DIR__ . '/../../backend/auth.php';
require_once __DIR__ . '/../../backend/currency.php';

requireAdmin();

$pdo = getPDO();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_box'])) {
    $boxId = (int)($_POST['delete_box'] ?? 0);

    try {
        $stmt = $pdo->prepare('SELECT image FROM sunnydripboxes WHERE id = ?');
        $stmt->execute([$boxId]);
        $box = $stmt->fetch();

        if ($box) {
            if (!empty($box['image'])) {
                $imagePath = __DIR__ . '/../../assets/images/dripboxes/' . $box['image'];
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }

            $stmt = $pdo->prepare('DELETE FROM sunnydripboxes WHERE id = ?');
            $stmt->execute([$boxId]);
        }

        header('Location: dripboxes.php?success=dripbox_deleted');
        exit;
    } catch (Throwable $e) {
        $error = 'Failed to delete DripBox: ' . $e->getMessage();
    }
}

$stmtBoxes = $pdo->query('SELECT * FROM sunnydripboxes ORDER BY created_at DESC');
$boxes = $stmtBoxes->fetchAll();

include __DIR__ . '/../partials/admin_header.php';
?>

<?php
$success = '';
if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'dripbox_added':
            $success = 'DripBox bundle added successfully!';
            break;
        case 'dripbox_updated':
            $success = 'DripBox bundle updated successfully!';
            break;
        case 'dripbox_deleted':
            $success = 'DripBox bundle deleted successfully!';
            break;
    }
}
?>

<?php if ($success): ?>
    <div class="admin-alert admin-alert-success mb-4">
        <i class="bi bi-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="admin-alert admin-alert-danger mb-4">
        <i class="bi bi-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?>
    </div>
<?php endif; ?>

<div class="admin-card mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h5 class="card-title mb-0">DripBox Management</h5>
                <p class="text-muted small mb-0">Create and manage curated bundles</p>
            </div>
            <div class="col-md-6 text-md-end mt-3 mt-md-0">
                <a href="new-dripbox.php" class="btn btn-admin-primary">
                    <i class="bi bi-plus-circle me-2"></i>Add DripBox
                </a>
            </div>
        </div>
    </div>
</div>

<div class="admin-card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="card-title mb-0">
                <i class="bi bi-box me-2"></i>All DripBoxes
                <span class="badge bg-secondary ms-2"><?php echo count($boxes); ?></span>
            </h5>
        </div>

        <?php if (empty($boxes)): ?>
            <div class="text-center py-5">
                <i class="bi bi-box fs-1 text-muted mb-3"></i>
                <h6 class="text-muted">No DripBox bundles yet</h6>
                <p class="text-muted small">Create curated outfit bundles for your customers</p>
                <a href="new-dripbox.php" class="btn btn-admin-primary">Create DripBox</a>
            </div>
        <?php else: ?>
            <div class="table-responsive admin-table">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Theme</th>
                            <th>Price</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($boxes as $box): ?>
                            <tr>
                                <td>
                                    <div class="product-image">
                                        <?php if (!empty($box['image'])): ?>
                                            <img src="<?php echo $basePath; ?>/assets/images/dripboxes/<?php echo htmlspecialchars($box['image']); ?>"
                                                 alt="<?php echo htmlspecialchars($box['name']); ?>"
                                                 style="width: 40px; height: 40px; object-fit: cover; border: 1px solid #e2e8f0; border-radius: 0.5rem;">
                                        <?php else: ?>
                                            <div class="product-image-placeholder"></div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="fw-semibold"><?php echo htmlspecialchars($box['name']); ?></td>
                                <td><span class="badge bg-info text-dark"><?php echo htmlspecialchars($box['theme']); ?></span></td>
                                <td class="fw-semibold"><?php echo CurrencyManager::formatPrice($box['price']); ?></td>
                                <td class="text-muted small"><?php echo htmlspecialchars(substr($box['description'], 0, 60) . '...'); ?></td>
                                <td>
                                    <div class="table-actions">
                                        <a class="btn btn-sm btn-admin-secondary" href="edit-dripbox.php?id=<?php echo (int)$box['id']; ?>">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form method="post" class="d-inline" onsubmit="return confirm('Delete this DripBox?');">
                                            <input type="hidden" name="delete_box" value="<?php echo (int)$box['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-admin-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../partials/admin_footer.php'; ?>
