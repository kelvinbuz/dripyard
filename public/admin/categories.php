<?php
$pageTitle = 'Category Management';
$basePath = '../..';
require_once __DIR__ . '/../../backend/db.php';
require_once __DIR__ . '/../../backend/auth.php';

requireAdmin();

$pdo = getPDO();
$error = '';

// Handle category creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_category'])) {
    $categoryName = trim($_POST['category_name'] ?? '');

    if ($categoryName === '') {
        $error = 'Category name is required!';
    } elseif (strlen($categoryName) < 2) {
        $error = 'Category name must be at least 2 characters long!';
    } else {
        // Check if category already exists
        $stmt = $pdo->prepare('SELECT id FROM categories WHERE name = ? LIMIT 1');
        $stmt->execute([$categoryName]);
        if ($stmt->fetch()) {
            $error = 'Category already exists!';
        } else {
            try {
                // Explicit insert including created_at to be compatible with both dumped and migrated schemas
                $stmt = $pdo->prepare('INSERT INTO categories (name, created_at) VALUES (?, NOW())');
                $stmt->execute([$categoryName]);
                header('Location: categories.php?success=category_created');
                exit;
            } catch (Throwable $e) {
                // Surface database errors in the admin UI instead of failing silently
                $error = 'Database error while creating category: ' . $e->getMessage();
            }
        }
    }
}

// Handle category update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_category'])) {
    $categoryId = (int)($_POST['category_id'] ?? 0);
    $categoryName = trim($_POST['category_name'] ?? '');

    if ($categoryId <= 0) {
        $error = 'Invalid category selected for update!';
    } elseif ($categoryName === '') {
        $error = 'Category name is required!';
    } elseif (strlen($categoryName) < 2) {
        $error = 'Category name must be at least 2 characters long!';
    } else {
        // Ensure no other category already uses this name
        $stmt = $pdo->prepare('SELECT id FROM categories WHERE name = ? AND id != ? LIMIT 1');
        $stmt->execute([$categoryName, $categoryId]);
        if ($stmt->fetch()) {
            $error = 'Another category already uses this name!';
        } else {
            $stmt = $pdo->prepare('UPDATE categories SET name = ? WHERE id = ?');
            $stmt->execute([$categoryName, $categoryId]);
            header('Location: categories.php?success=category_updated');
            exit;
        }
    }
}

// Handle category deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_category'])) {
    $categoryId = (int)$_POST['delete_category'];

    // Check if category has products
    $stmt = $pdo->prepare('SELECT COUNT(*) AS count FROM products WHERE category_id = ?');
    $stmt->execute([$categoryId]);
    $productCount = (int)$stmt->fetch()['count'];

    if ($productCount > 0) {
        $error = 'Cannot delete category with products! Move or delete products first.';
    } else {
        $stmt = $pdo->prepare('DELETE FROM categories WHERE id = ?');
        $stmt->execute([$categoryId]);
        header('Location: categories.php?success=category_deleted');
        exit;
    }
}

// Get all categories with product counts
$stmt = $pdo->query('
    SELECT c.id, c.name,
           (SELECT COUNT(*) FROM products WHERE category_id = c.id) AS product_count
    FROM categories c
    ORDER BY c.name ASC
');
$categories = $stmt->fetchAll();

include __DIR__ . '/../partials/admin_header.php';
?>

<?php
// Handle success messages
$success = '';
if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'category_created':
            $success = 'Category created successfully!';
            break;
        case 'category_deleted':
            $success = 'Category deleted successfully!';
            break;
        case 'category_updated':
            $success = 'Category updated successfully!';
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

<!-- Category Management -->
<div class="admin-card mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="card-title mb-0">
                <i class="bi bi-tags me-2"></i>Category Management
                <span class="badge bg-secondary ms-2"><?php echo count($categories); ?></span>
            </h5>
            <button class="btn btn-admin-primary" data-bs-toggle="modal" data-bs-target="#categoryModal">
                <i class="bi bi-plus-circle me-2"></i>Add Category
            </button>
        </div>

        <?php if (empty($categories)): ?>
            <div class="text-center py-5">
                <i class="bi bi-tags fs-1 text-muted mb-3"></i>
                <h6 class="text-muted">No categories yet</h6>
                <p class="text-muted small">Create categories to organize your products efficiently</p>
                <button class="btn btn-admin-primary" data-bs-toggle="modal" data-bs-target="#categoryModal">Create First Category</button>
            </div>
        <?php else: ?>
            <div class="table-responsive admin-table">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Category Name</th>
                            <th>Products Count</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $category): ?>
                            <tr>
                                <td class="fw-semibold">
                                    <i class="bi bi-tag me-2 text-muted"></i>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </td>
                                <td>
                                    <?php if ($category['product_count'] > 0): ?>
                                        <span class="badge bg-info"><?php echo (int)$category['product_count']; ?> products</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Empty</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($category['product_count'] > 0): ?>
                                        <span class="status-badge status-badge-active">Active</span>
                                    <?php else: ?>
                                        <span class="status-badge status-badge-inactive">Unused</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="table-actions">
                                        <button type="button"
                                                class="btn btn-sm btn-admin-primary me-1 btn-edit-category"
                                                data-bs-toggle="modal"
                                                data-bs-target="#categoryModal"
                                                data-category-id="<?php echo (int)$category['id']; ?>"
                                                data-category-name="<?php echo htmlspecialchars($category['name'], ENT_QUOTES); ?>"
                                                title="Edit Category">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <?php if ($category['product_count'] == 0): ?>
                                            <form method="post" class="d-inline">
                                                <input type="hidden" name="delete_category" value="<?php echo (int)$category['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-admin-danger" title="Delete Category">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-admin-secondary" disabled title="Cannot delete category with products">
                                                <i class="bi bi-shield-check"></i>
                                            </button>
                                        <?php endif; ?>
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

<!-- Category Statistics -->
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="stat-card primary admin-card">
            <div class="stat-icon">
                <i class="bi bi-tags"></i>
            </div>
            <div class="stat-value"><?php echo count($categories); ?></div>
            <div class="stat-label">Total Categories</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card success admin-card">
            <div class="stat-icon">
                <i class="bi bi-check-circle"></i>
            </div>
            <div class="stat-value"><?php echo count(array_filter($categories, fn($c) => $c['product_count'] > 0)); ?></div>
            <div class="stat-label">Active Categories</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card warning admin-card">
            <div class="stat-icon">
                <i class="bi bi-exclamation-triangle"></i>
            </div>
            <div class="stat-value"><?php echo count(array_filter($categories, fn($c) => $c['product_count'] == 0)); ?></div>
            <div class="stat-label">Unused Categories</div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="admin-card">
    <div class="card-body">
        <h5 class="card-title mb-3">Quick Actions</h5>
        <div class="row g-3">
            <div class="col-md-6">
                <a href="products.php" class="btn btn-admin-outline-primary w-100">
                    <i class="bi bi-box-seam me-2"></i>Manage Products
                </a>
            </div>
            <div class="col-md-6">
                <a href="new-product.php" class="btn btn-admin-outline-success w-100">
                    <i class="bi bi-plus-circle me-2"></i>Add New Product
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Category Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post" id="category-form">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Category Name <span class="text-danger">*</span></label>
                        <input type="text" name="category_name" id="category-name-field" class="form-control" required
                               placeholder="e.g., T-Shirts, Hoodies, Accessories, Shoes">
                        <div class="form-text">Choose a descriptive name for your product category</div>
                    </div>
                    <input type="hidden" name="category_id" id="category-id-field" value="">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="create_category" value="1" id="category-submit-button" class="btn btn-admin-primary">Create Category</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const categoryModal = document.getElementById('categoryModal');
    const nameField = document.getElementById('category-name-field');
    const idField = document.getElementById('category-id-field');
    const submitButton = document.getElementById('category-submit-button');
    const modalTitle = categoryModal.querySelector('.modal-title');

    // When opening the modal via the "Add Category" button, reset to create mode
    const addCategoryButtons = document.querySelectorAll('[data-bs-target="#categoryModal"]:not(.btn-edit-category)');
    addCategoryButtons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (!nameField || !idField || !submitButton || !modalTitle) return;
            nameField.value = '';
            idField.value = '';
            submitButton.textContent = 'Create Category';
            submitButton.name = 'create_category';
            submitButton.value = '1';
            modalTitle.textContent = 'Create New Category';
        });
    });

    // Edit buttons populate the modal with existing values and switch to update mode
    const editButtons = document.querySelectorAll('.btn-edit-category');
    editButtons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (!nameField || !idField || !submitButton || !modalTitle) return;

            const categoryId = btn.getAttribute('data-category-id');
            const categoryName = btn.getAttribute('data-category-name');

            idField.value = categoryId || '';
            nameField.value = categoryName || '';
            submitButton.textContent = 'Update Category';
            submitButton.name = 'update_category';
            submitButton.value = '1';
            modalTitle.textContent = 'Edit Category';
        });
    });
});
</script>

<?php include __DIR__ . '/../partials/admin_footer.php'; ?>
