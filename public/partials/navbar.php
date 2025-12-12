<?php
// Enhanced navigation bar with professional design
?>
<style>
/* Enhanced Navbar Styles */
.navbar-dripyard {
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.95) 0%, rgba(118, 75, 162, 0.95) 100%);
    backdrop-filter: blur(10px);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    padding: 0.75rem 0;
    transition: all 0.3s ease;
    position: relative;
    z-index: 1030; /* Ensure navbar sits above hero and page content */
}

.navbar-dripyard.scrolled {
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.98) 0%, rgba(118, 75, 162, 0.98) 100%);
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
}

.navbar-brand {
    font-size: 1.5rem;
    font-weight: 700;
    color: white !important;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.3s ease;
}

.navbar-brand:hover {
    transform: translateY(-2px);
    color: #fbbf24 !important;
}

.navbar-brand::before {
    content: '👕';
    font-size: 1.8rem;
    animation: bounce 2s infinite;
}

@keyframes bounce {
    0%, 20%, 50%, 80%, 100% {
        transform: translateY(0);
    }
    40% {
        transform: translateY(-10px);
    }
    60% {
        transform: translateY(-5px);
    }
}

.navbar-nav .nav-link {
    color: rgba(255, 255, 255, 0.9) !important;
    font-weight: 500;
    padding: 0.5rem 1rem !important;
    margin: 0 0.25rem;
    border-radius: 0.5rem;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.navbar-nav .nav-link::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s ease;
}

.navbar-nav .nav-link:hover::before {
    left: 100%;
}

.navbar-nav .nav-link:hover {
    background: rgba(255, 255, 255, 0.1);
    color: white !important;
    transform: translateY(-2px);
}

.navbar-nav .nav-link.active {
    background: rgba(255, 255, 255, 0.15);
    color: #fbbf24 !important;
}

/* Cart Icon Styles */
.nav-icon-link {
    color: rgba(255, 255, 255, 0.9) !important;
    font-size: 1.2rem;
    padding: 0.5rem !important;
    border-radius: 50%;
    width: 45px;
    height: 45px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    position: relative;
}

.nav-icon-link:hover {
    background: rgba(255, 255, 255, 0.1);
    color: white !important;
    transform: scale(1.1);
}

.badge-cart-count {
    position: absolute;
    top: 0;
    right: 0;
    background: #fbbf24;
    color: #1f2937;
    font-size: 0.75rem;
    font-weight: 700;
    min-width: 20px;
    height: 20px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid rgba(255, 255, 255, 0.9);
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(251, 191, 36, 0.7);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(251, 191, 36, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(251, 191, 36, 0);
    }
}

/* User Avatar Styles */
.navbar-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    overflow: hidden;
    border: 2px solid rgba(255, 255, 255, 0.3);
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    transition: all 0.3s ease;
}

.navbar-avatar:hover {
    border-color: #fbbf24;
    transform: scale(1.05);
}

.navbar-avatar-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.navbar-avatar-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 700;
    font-size: 0.9rem;
}

.navbar-user-name {
    color: rgba(255, 255, 255, 0.95);
    font-weight: 600;
    font-size: 0.9rem;
}

/* Dropdown Menu Styles */
.dropdown-menu {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 1rem;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    padding: 0.5rem;
    margin-top: 0.5rem;
    animation: slideDown 0.3s ease;
    z-index: 2000; /* Make sure dropdown appears above hero overlays */
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.dropdown-item {
    color: #374151;
    font-weight: 500;
    padding: 0.75rem 1rem;
    border-radius: 0.5rem;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
}

.dropdown-item:hover {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    transform: translateX(5px);
}

.dropdown-item i {
    font-size: 1.1rem;
    width: 20px;
}

.dropdown-divider {
    border-color: rgba(0, 0, 0, 0.1);
    margin: 0.5rem 0;
}

/* Mobile Toggler */
.navbar-toggler {
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-radius: 0.5rem;
    padding: 0.25rem 0.5rem;
    transition: all 0.3s ease;
}

.navbar-toggler:hover {
    border-color: rgba(255, 255, 255, 0.5);
    background: rgba(255, 255, 255, 0.1);
}

.navbar-toggler-icon {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%28255, 255, 255, 0.9%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
}

/* Mobile responsive */
@media (max-width: 768px) {
    .navbar-dripyard {
        padding: 0.5rem 0;
    }
    
    .navbar-brand {
        font-size: 1.3rem;
    }
    
    .navbar-brand::before {
        font-size: 1.5rem;
    }
    
    .navbar-avatar {
        width: 32px;
        height: 32px;
    }
    
    .navbar-user-name {
        display: none;
    }
    
    .nav-icon-link {
        width: 40px;
        height: 40px;
        font-size: 1.1rem;
    }
    
    .navbar-nav .nav-link {
        padding: 0.75rem 1rem !important;
        margin: 0.25rem 0;
    }
    
    .dropdown-menu {
        border-radius: 0.5rem;
        margin-top: 0;
    }
}

/* Search Bar (Optional Enhancement) */
.navbar-search {
    position: relative;
    margin: 0 1rem;
}

.navbar-search input {
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 2rem;
    padding: 0.5rem 2.5rem 0.5rem 1rem;
    color: white;
    font-size: 0.9rem;
    width: 200px;
    transition: all 0.3s ease;
}

.navbar-search input::placeholder {
    color: rgba(255, 255, 255, 0.6);
}

.navbar-search input:focus {
    outline: none;
    background: rgba(255, 255, 255, 0.15);
    border-color: rgba(255, 255, 255, 0.4);
    width: 250px;
}

.navbar-search button {
    position: absolute;
    right: 0.5rem;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: rgba(255, 255, 255, 0.8);
    cursor: pointer;
}

.navbar-search button:hover {
    color: white;
}
</style>

<nav class="navbar navbar-expand-lg navbar-dripyard navbar-light">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="<?php echo $basePath; ?>/public/index.php">
            DripYard
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#dripyardNav" aria-controls="dripyardNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="dripyardNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'shop.php' ? 'active' : ''; ?>" 
                       href="<?php echo $basePath; ?>/public/shop.php">
                        <i class="bi bi-bag me-1"></i>Shop
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'dripbox.php' ? 'active' : ''; ?>" 
                       href="<?php echo $basePath; ?>/public/dripbox.php">
                        <i class="bi bi-box-seam me-1"></i>DripBox
                    </a>
                </li>
            </ul>
            
            <!-- Search Bar (Optional) -->
            <div class="navbar-search d-none d-lg-block">
                <input type="text" placeholder="Search products..." id="navbarSearch">
                <button type="button" onclick="performSearch()">
                    <i class="bi bi-search"></i>
                </button>
            </div>
            
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-lg-center">
                <li class="nav-item me-2">
                    <a class="nav-link nav-icon-link position-relative" href="<?php echo $basePath; ?>/public/cart.php" aria-label="Shopping Cart">
                        <i class="bi bi-bag"></i>
                        <span class="badge rounded-pill badge-cart-count" id="cart-count"><?php echo (int)$cartCount; ?></span>
                    </a>
                </li>
                
                <?php if ($currentUser): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="navbar-avatar me-2">
                                <?php if ($currentUser['profile_image']): ?>
                                    <img src="<?php echo $basePath; ?>/assets/images/profiles/<?php echo htmlspecialchars($currentUser['profile_image']); ?>" 
                                         alt="Profile" class="navbar-avatar-img">
                                <?php else: ?>
                                    <div class="navbar-avatar-placeholder">
                                        <?php echo strtoupper(substr($currentUser['name'], 0, 1)); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <span class="navbar-user-name"><?php echo htmlspecialchars($currentUser['name']); ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="<?php echo $basePath; ?>/public/dashboard.php">
                                    <i class="bi bi-person-circle me-2"></i>My Dashboard
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="<?php echo $basePath; ?>/public/dashboard.php?section=orders">
                                    <i class="bi bi-box-seam me-2"></i>My Orders
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="<?php echo $basePath; ?>/public/dashboard.php?section=profile">
                                    <i class="bi bi-gear me-2"></i>Profile Settings
                                </a>
                            </li>
                            <?php if ($currentUser['role'] === 'admin'): ?>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="<?php echo $basePath; ?>/public/admin/dashboard.php">
                                        <i class="bi bi-shield-check me-2"></i>Admin Panel
                                    </a>
                                </li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="<?php echo $basePath; ?>/public/logout.php">
                                    <i class="bi bi-box-arrow-right me-2"></i>Logout
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link nav-icon-link" href="<?php echo $basePath; ?>/public/login.php" aria-label="Login">
                            <i class="bi bi-person"></i>
                        </a>
                    </li>
                    <!--<li class="nav-item">
                        <a class="btn btn-sm btn-outline-light ms-2" href="<?php echo $basePath; ?>/public/register.php">
                            Sign Up
                        </a>
                    </li>
                    -->
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<script>
// Navbar scroll effect
window.addEventListener('scroll', function() {
    const navbar = document.querySelector('.navbar-dripyard');
    if (window.scrollY > 50) {
        navbar.classList.add('scrolled');
    } else {
        navbar.classList.remove('scrolled');
    }
});

// Search functionality
function performSearch() {
    const searchInput = document.getElementById('navbarSearch');
    const query = searchInput.value.trim();
    
    if (query) {
        window.location.href = `${window.DRIPYARD?.basePath || '..'}/public/shop.php?search=${encodeURIComponent(query)}`;
    }
}

// Enter key search
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('navbarSearch');
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                performSearch();
            }
        });
    }
});
</script>
