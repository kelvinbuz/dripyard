<?php
$pageTitle = 'Product Management';
$basePath = '../..';
require_once __DIR__ . '/../../backend/db.php';
require_once __DIR__ . '/../../backend/auth.php';
require_once __DIR__ . '/../../backend/currency.php';

requireAdmin();

$pdo = getPDO();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_category_name'])) {
    $name = trim($_POST['new_category_name']);
    if ($name !== '') {
        $stmt = $pdo->prepare('INSERT INTO categories (name) VALUES (?) ON DUPLICATE KEY UPDATE name = VALUES(name)');
        $stmt->execute([$name]);
        $message = 'Category saved successfully!';
    }
}

if (isset($_GET['delete']) && $_GET['delete'] !== '') {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare('DELETE FROM products WHERE id = ?');
    $stmt->execute([$id]);
    $message = 'Product deleted successfully!';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle DripBox operations
    if (isset($_POST['save_box'])) {
        $boxName = trim($_POST['box_name'] ?? '');
        $boxTheme = trim($_POST['box_theme'] ?? '');
        $boxPrice = (float)($_POST['box_price'] ?? 0);
        $boxDescription = trim($_POST['box_description'] ?? '');
        
        // Handle DripBox image upload
        $boxImage = '';
        if (isset($_FILES['box_image']) && $_FILES['box_image']['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $maxSize = 5 * 1024 * 1024; // 5MB
            
            if (!in_array($_FILES['box_image']['type'], $allowedTypes)) {
                $message = 'Only JPG, PNG, GIF, and WebP images are allowed.';
            } elseif ($_FILES['box_image']['size'] > $maxSize) {
                $message = 'Image size must be less than 5MB.';
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
                }
            }
        }
        
        if ($boxName !== '' && $boxPrice > 0) {
            $stmt = $pdo->prepare('INSERT INTO sunnydripboxes (name, theme, description, price, image, created_at) VALUES (?, ?, ?, ?, ?, NOW())');
            $stmt->execute([$boxName, $boxTheme, $boxDescription, $boxPrice, $boxImage]);
            header('Location: products.php?success=dripbox_added');
            exit;
        }
    }
    
    // Handle DripBox deletion
    if (isset($_POST['delete_box'])) {
        $boxId = (int)$_POST['delete_box'];
        
        // Get DripBox info to delete image
        $stmt = $pdo->prepare('SELECT image FROM sunnydripboxes WHERE id = ?');
        $stmt->execute([$boxId]);
        $box = $stmt->fetch();
        
        if ($box) {
            // Delete image file
            if ($box['image']) {
                $imagePath = __DIR__ . '/../../assets/images/dripboxes/' . $box['image'];
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
            
            // Delete from database
            $stmt = $pdo->prepare('DELETE FROM sunnydripboxes WHERE id = ?');
            $stmt->execute([$boxId]);
            header('Location: products.php?success=dripbox_deleted');
            exit;
        }
    }
    
    // Handle category operations
    if (isset($_POST['new_category'])) {
        $catName = trim($_POST['new_category']);
        if ($catName !== '') {
            $stmt = $pdo->prepare('INSERT INTO categories (name) VALUES (?)');
            $stmt->execute([$catName]);
            header('Location: products.php?success=category_added');
            exit;
        }
    }
    
    if (isset($_POST['delete_category'])) {
        $catId = (int)$_POST['delete_category'];
        $stmt = $pdo->prepare('DELETE FROM categories WHERE id = ?');
        $stmt->execute([$catId]);
        header('Location: products.php?success=category_deleted');
        exit;
    }
}

$stmtCats = $pdo->query('SELECT * FROM categories ORDER BY name ASC');
$categories = $stmtCats->fetchAll();

$stmt = $pdo->query('SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON p.category_id = c.id ORDER BY p.created_at DESC');
$products = $stmt->fetchAll();

$stmtBoxes = $pdo->query('SELECT * FROM sunnydripboxes ORDER BY created_at DESC');
$boxes = $stmtBoxes->fetchAll();

include __DIR__ . '/../partials/admin_header.php';
?>
<?php 
// Handle success messages from redirects
$success = '';
if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'product_added':
            $success = 'Product added successfully!';
            break;
        case 'product_updated':
            $success = 'Product updated successfully!';
            break;
        case 'dripbox_added':
            $success = 'DripBox bundle added successfully!';
            break;
        case 'dripbox_updated':
            $success = 'DripBox bundle updated successfully!';
            break;
        case 'dripbox_deleted':
            $success = 'DripBox bundle deleted successfully!';
            break;
        case 'category_added':
            $success = 'Category added successfully!';
            break;
        case 'category_deleted':
            $success = 'Category deleted successfully!';
            break;
    }
}
?>

<?php if ($success): ?>
    <div class="admin-alert admin-alert-success mb-4">
        <i class="bi bi-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
    </div>
<?php endif; ?>

<?php if ($message): ?>
    <div class="admin-alert admin-alert-success mb-4">
        <i class="bi bi-check-circle me-2"></i><?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<!-- Action Bar -->
<div class="admin-card mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h5 class="card-title mb-0">Product Management</h5>
                <p class="text-muted small mb-0">Manage your inventory and DripBox bundles</p>
            </div>
            <div class="col-md-6 text-md-end mt-3 mt-md-0">
                <a href="new-product.php" class="btn btn-admin-primary">
                    <i class="bi bi-plus-circle me-2"></i>Add Product
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Products Section -->
<div class="admin-card mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="card-title mb-0">
                <i class="bi bi-box-seam me-2"></i>Products
                <span class="badge bg-secondary ms-2"><?php echo count($products); ?></span>
            </h5>
            <div class="d-flex gap-2">
                <input type="text" class="form-control form-control-sm" placeholder="Search products..." id="productSearch">
                <select class="form-select form-select-sm" id="categoryFilter">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo (int)$cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <?php if (empty($products)): ?>
            <div class="text-center py-5">
                <i class="bi bi-box-seam fs-1 text-muted mb-3"></i>
                <h6 class="text-muted">No products yet</h6>
                <p class="text-muted small">Start by adding your first product</p>
                <a href="new-product.php" class="btn btn-admin-primary">Add Product</a>
            </div>
        <?php else: ?>
            <div class="table-responsive admin-table">
                <table class="table" id="productsTable">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $p): ?>
                            <tr>
                                <td>
                                    <div class="product-image">
                                        <?php if ($p['image']): ?>
                                            <img src="<?php echo $basePath; ?>/assets/images/products/<?php echo htmlspecialchars($p['image']); ?>" 
                                                 alt="<?php echo htmlspecialchars($p['name']); ?>" 
                                                 style="width: 40px; height: 40px; object-fit: cover; border: 1px solid #e2e8f0; border-radius: 0.5rem;">
                                        <?php else: ?>
                                            <div class="product-image-placeholder"></div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-semibold"><?php echo htmlspecialchars($p['name']); ?></div>
                                    <div class="text-muted small">ID: #<?php echo (int)$p['id']; ?></div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark"><?php echo htmlspecialchars($p['category_name']); ?></span>
                                </td>
                                <td class="fw-semibold"><?php echo CurrencyManager::formatPrice($p['price']); ?></td>
                                <td>
                                    <?php if ($p['stock'] < 10): ?>
                                        <span class="text-warning fw-semibold">
                                            <i class="bi bi-exclamation-triangle"></i> <?php echo (int)$p['stock']; ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-success fw-semibold"><?php echo (int)$p['stock']; ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="status-badge status-badge-<?php echo $p['stock'] > 0 ? 'active' : 'inactive'; ?>">
                                        <?php echo $p['stock'] > 0 ? 'In Stock' : 'Out of Stock'; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="table-actions">
                                        <a href="edit-product.php?id=<?php echo (int)$p['id']; ?>" class="btn btn-sm btn-admin-secondary">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="?delete=<?php echo (int)$p['id']; ?>" class="btn btn-sm btn-admin-danger">
                                            <i class="bi bi-trash"></i>
                                        </a>
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

<!-- Category Management Section -->
<div class="admin-card mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
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
                <p class="text-muted small">Create categories to organize your products</p>
                <button class="btn btn-admin-primary" data-bs-toggle="modal" data-bs-target="#categoryModal">Create Category</button>
            </div>
        <?php else: ?>
            <div class="table-responsive admin-table">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Category Name</th>
                            <th>Products Count</th>
                            <th>Created Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $category): ?>
                            <?php 
                            // Count products in this category
                            $stmtCount = $pdo->prepare('SELECT COUNT(*) as count FROM products WHERE category_id = ?');
                            $stmtCount->execute([$category['id']]);
                            $productCount = $stmtCount->fetch()['count'];
                            ?>
                            <tr>
                                <td class="fw-semibold"><?php echo htmlspecialchars($category['name']); ?></td>
                                <td>
                                    <span class="badge bg-info"><?php echo (int)$productCount; ?> products</span>
                                </td>
                                <td class="text-muted small">
                                    <?php echo $category['created_at'] ? date('M j, Y', strtotime($category['created_at'])) : 'N/A'; ?>
                                </td>
                                <td>
                                    <div class="table-actions">
                                        <?php if ($productCount == 0): ?>
                                            <form method="post" onsubmit="return confirm('Are you sure you want to delete this category?');" class="d-inline">
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

<!-- DripBox Section -->
<div class="admin-card mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="card-title mb-0">
                <i class="bi bi-box me-2"></i>DripBox Bundles
                <span class="badge bg-secondary ms-2"><?php echo count($boxes); ?></span>
            </h5>
            <button class="btn btn-admin-primary" data-bs-toggle="modal" data-bs-target="#dripboxModal">
                <i class="bi bi-plus-circle me-2"></i>Add DripBox
            </button>
        </div>
        
        <?php if (empty($boxes)): ?>
            <div class="text-center py-5">
                <i class="bi bi-box fs-1 text-muted mb-3"></i>
                <h6 class="text-muted">No DripBox bundles yet</h6>
                <p class="text-muted small">Create curated outfit bundles for your customers</p>
                <button class="btn btn-admin-primary" data-bs-toggle="modal" data-bs-target="#dripboxModal">Create DripBox</button>
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
                                        <?php if ($box['image']): ?>
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
                                <td class="text-muted small"><?php echo htmlspecialchars(substr($box['description'], 0, 50) . '...'); ?></td>
                                <td>
                                    <div class="table-actions">
                                        <button class="btn btn-sm btn-admin-secondary" onclick="editDripBox(<?php echo (int)$box['id']; ?>)">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-admin-danger" onclick="deleteDripBox(<?php echo (int)$box['id']; ?>)">
                                            <i class="bi bi-trash"></i>
                                        </button>
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

<!-- Category Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Category Name <span class="text-danger">*</span></label>
                        <input type="text" name="new_category_name" class="form-control" required placeholder="e.g., T-Shirts, Hoodies, Accessories">
                        <div class="form-text">Create a new category to organize your products</div>
                    </div>
                    <button type="submit" class="btn btn-admin-primary">Create Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- DripBox Modal -->
<div class="modal fade" id="dripboxModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New DripBox Bundle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Bundle Name</label>
                        <input type="text" name="box_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Theme</label>
                        <input type="text" name="box_theme" class="form-control" placeholder="e.g., Casual Weekend">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Price (GH₵)</label>
                        <input type="number" name="box_price" class="form-control" min="0" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Bundle Image</label>
                        <input type="file" name="box_image" class="form-control" accept="image/*">
                        <div class="form-text">Allowed formats: JPG, PNG, GIF, WebP (Max 5MB)</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="box_description" rows="3" class="form-control" placeholder="Describe what's included in this bundle..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="save_box" value="1" class="btn btn-admin-primary">Create DripBox</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Search functionality
document.getElementById('productSearch').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('#productsTable tbody tr');
    
    rows.forEach(row => {
        const productName = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
        const category = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
        
        if (productName.includes(searchTerm) || category.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});

// Category filter
document.getElementById('categoryFilter').addEventListener('change', function(e) {
    const categoryId = e.target.value;
    const rows = document.querySelectorAll('#productsTable tbody tr');
    
    rows.forEach(row => {
        if (!categoryId) {
            row.style.display = '';
        } else {
            const categoryCell = row.querySelector('td:nth-child(3)');
            const categoryText = categoryCell.textContent.trim();
            
            // Simple category matching
            const matches = categoryText.includes(categoryId) || 
                           (categoryId === '1' && categoryText.includes('T-Shirts')) ||
                           (categoryId === '2' && categoryText.includes('Hoodies')) ||
                           (categoryId === '3' && categoryText.includes('Accessories'));
            
            row.style.display = matches ? '' : 'none';
        }
    });
});

function editDripBox(id) {
    window.location.href = 'edit-dripbox.php?id=' + id;
}

function deleteDripBox(id) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.innerHTML = '<input type="hidden" name="delete_box" value="' + id + '">';
    document.body.appendChild(form);
    form.submit();
}
</script>

<style>
.product-image-placeholder {
    width: 40px;
    height: 40px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #94a3b8;
}
.product-image-placeholder::before {
    content: "📦";
    font-size: 1.2rem;
}
</style>
