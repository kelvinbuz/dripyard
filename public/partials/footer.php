    </div>
</main>

<style>
/* Enhanced Footer Styles */
.footer-dripyard {
    background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
    color: rgba(255, 255, 255, 0.9);
    padding: 4rem 0 2rem;
    position: relative;
    overflow: hidden;
}

.footer-dripyard::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 1px;
    background: linear-gradient(90deg, transparent, rgba(102, 126, 234, 0.5), transparent);
}

.footer-dripyard::after {
    content: '';
    position: absolute;
    top: -50%;
    right: -10%;
    width: 300px;
    height: 300px;
    background: radial-gradient(circle, rgba(102, 126, 234, 0.1) 0%, transparent 70%);
    border-radius: 50%;
    animation: float 20s infinite ease-in-out;
}

@keyframes float {
    0%, 100% {
        transform: translateY(0) rotate(0deg);
    }
    50% {
        transform: translateY(-20px) rotate(180deg);
    }
}

.footer-content {
    position: relative;
    z-index: 1;
}

.footer-brand {
    text-align: center;
    margin-bottom: 3rem;
}

.footer-logo {
    font-size: 2.5rem;
    font-weight: 800;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 1rem;
    display: inline-block;
    animation: glow 3s ease-in-out infinite;
}

@keyframes glow {
    0%, 100% {
        filter: brightness(1);
    }
    50% {
        filter: brightness(1.2);
    }
}

.footer-tagline {
    font-size: 1.1rem;
    color: rgba(255, 255, 255, 0.7);
    margin-bottom: 1.5rem;
}

.footer-description {
    max-width: 600px;
    margin: 0 auto 2rem;
    color: rgba(255, 255, 255, 0.6);
    line-height: 1.6;
}

.footer-social {
    display: flex;
    justify-content: center;
    gap: 1rem;
    margin-bottom: 2rem;
}

.social-link {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    color: rgba(255, 255, 255, 0.8);
    text-decoration: none;
    transition: all 0.3s ease;
    font-size: 1.2rem;
}

.social-link:hover {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-color: transparent;
    color: white;
    transform: translateY(-5px) scale(1.1);
    box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
}

.footer-sections {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 3rem;
    margin-bottom: 3rem;
}

.footer-section h3 {
    font-size: 1.2rem;
    font-weight: 600;
    color: white;
    margin-bottom: 1.5rem;
    position: relative;
    padding-bottom: 0.5rem;
}

.footer-section h3::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 50px;
    height: 2px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.footer-links {
    list-style: none;
    padding: 0;
    margin: 0;
}

.footer-links li {
    margin-bottom: 0.75rem;
}

.footer-links a {
    color: rgba(255, 255, 255, 0.7);
    text-decoration: none;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.footer-links a:hover {
    color: #667eea;
    transform: translateX(5px);
}

.footer-links a i {
    font-size: 0.9rem;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.footer-links a:hover i {
    opacity: 1;
}

.footer-contact-info {
    list-style: none;
    padding: 0;
    margin: 0;
}

.footer-contact-info li {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1rem;
    color: rgba(255, 255, 255, 0.7);
}

.footer-contact-info i {
    width: 20px;
    color: #667eea;
    font-size: 1.1rem;
}

.footer-newsletter {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 1rem;
    padding: 1.5rem;
    margin-top: 1rem;
}

.newsletter-form {
    display: flex;
    gap: 0.5rem;
    margin-top: 1rem;
}

.newsletter-input {
    flex: 1;
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 0.5rem;
    padding: 0.75rem 1rem;
    color: white;
    font-size: 0.9rem;
    transition: all 0.3s ease;
}

.newsletter-input::placeholder {
    color: rgba(255, 255, 255, 0.5);
}

.newsletter-input:focus {
    outline: none;
    background: rgba(255, 255, 255, 0.15);
    border-color: #667eea;
}

.newsletter-btn {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    border-radius: 0.5rem;
    padding: 0.75rem 1.5rem;
    color: white;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    white-space: nowrap;
}

.newsletter-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
}

.footer-bottom {
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    padding-top: 2rem;
    margin-top: 3rem;
    text-align: center;
}

.footer-bottom-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
}

.footer-copyright {
    color: rgba(255, 255, 255, 0.6);
    font-size: 0.9rem;
}

.footer-payment-methods {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
    justify-content: center;
}

.payment-method {
    width: 40px;
    height: 25px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 0.25rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.7rem;
    color: rgba(255, 255, 255, 0.7);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.footer-legal-links {
    display: flex;
    gap: 2rem;
    flex-wrap: wrap;
    justify-content: center;
}

.footer-legal-links a {
    color: rgba(255, 255, 255, 0.6);
    text-decoration: none;
    font-size: 0.9rem;
    transition: color 0.3s ease;
}

.footer-legal-links a:hover {
    color: #667eea;
}

/* Mobile responsive */
@media (max-width: 768px) {
    .footer-dripyard {
        padding: 3rem 0 1.5rem;
    }
    
    .footer-logo {
        font-size: 2rem;
    }
    
    .footer-sections {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
    
    .newsletter-form {
        flex-direction: column;
    }
    
    .footer-bottom-content {
        gap: 1.5rem;
    }
    
    .footer-payment-methods {
        gap: 0.5rem;
    }
}
</style>

<footer class="footer-dripyard">
    <div class="container footer-content">
        <!-- Brand Section -->
        <div class="footer-brand">
            <div class="footer-logo">DripYard</div>
            <div class="footer-tagline">Stay Fresh. Stay Sunny.</div>
            <p class="footer-description">
                Your premier destination for trendy fashion and curated DripBoxes. 
                We bring you the latest styles with quality you can trust.
            </p>
            
            <!-- Social Media Links -->
            <div class="footer-social">
                <a href="https://facebook.com/dripyard" class="social-link" aria-label="Facebook">
                    <i class="bi bi-facebook"></i>
                </a>
                <a href="https://instagram.com/dripyard" class="social-link" aria-label="Instagram">
                    <i class="bi bi-instagram"></i>
                </a>
                <a href="https://twitter.com/dripyard" class="social-link" aria-label="Twitter">
                    <i class="bi bi-twitter"></i>
                </a>
                <a href="https://wa.me/233201234567" class="social-link" aria-label="WhatsApp">
                    <i class="bi bi-whatsapp"></i>
                </a>
                <a href="https://tiktok.com/@dripyard" class="social-link" aria-label="TikTok">
                    <i class="bi bi-tiktok"></i>
                </a>
            </div>
        </div>

        <!-- Footer Sections -->
        <div class="footer-sections">
            <!-- Quick Links -->
            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul class="footer-links">
                    <li><a href="<?php echo $basePath; ?>/public/index.php"><i class="bi bi-chevron-right"></i>Home</a></li>
                    <li><a href="<?php echo $basePath; ?>/public/shop.php"><i class="bi bi-chevron-right"></i>Shop</a></li>
                    <li><a href="<?php echo $basePath; ?>/public/dripbox.php"><i class="bi bi-chevron-right"></i>DripBox</a></li>
                    <li><a href="<?php echo $basePath; ?>/public/cart.php"><i class="bi bi-chevron-right"></i>Cart</a></li>
                    <li><a href="<?php echo $basePath; ?>/public/dashboard.php"><i class="bi bi-chevron-right"></i>My Account</a></li>
                </ul>
            </div>

            <!-- Categories -->
            <div class="footer-section">
                <h3>Categories</h3>
                <ul class="footer-links">
                    <li><a href="<?php echo $basePath; ?>/public/shop.php?category=1"><i class="bi bi-chevron-right"></i>T-Shirts</a></li>
                    <li><a href="<?php echo $basePath; ?>/public/shop.php?category=2"><i class="bi bi-chevron-right"></i>Jeans</a></li>
                    <li><a href="<?php echo $basePath; ?>/public/shop.php?category=3"><i class="bi bi-chevron-right"></i>Accessories</a></li>
                    <li><a href="<?php echo $basePath; ?>/public/shop.php?category=4"><i class="bi bi-chevron-right"></i>Shoes</a></li>
                    <li><a href="<?php echo $basePath; ?>/public/shop.php?category=5"><i class="bi bi-chevron-right"></i>Special Collections</a></li>
                </ul>
            </div>

            <!-- Customer Service -->
            <div class="footer-section">
                <h3>Customer Service</h3>
                <ul class="footer-links">
                    <li><a href="<?php echo $basePath; ?>/public/help.php"><i class="bi bi-chevron-right"></i>Help Center</a></li>
                    <li><a href="<?php echo $basePath; ?>/public/shipping.php"><i class="bi bi-chevron-right"></i>Shipping Info</a></li>
                    <li><a href="<?php echo $basePath; ?>/public/returns.php"><i class="bi bi-chevron-right"></i>Returns & Exchanges</a></li>
                    <li><a href="<?php echo $basePath; ?>/public/size-guide.php"><i class="bi bi-chevron-right"></i>Size Guide</a></li>
                    <li><a href="<?php echo $basePath; ?>/public/contact.php"><i class="bi bi-chevron-right"></i>Contact Us</a></li>
                </ul>
            </div>

            <!-- Contact & Newsletter -->
            <div class="footer-section">
                <h3>Get in Touch</h3>
                <ul class="footer-contact-info">
                    <li>
                        <i class="bi bi-geo-alt"></i>
                        <span>Accra, Ghana<br>West Africa</span>
                    </li>
                    <li>
                        <i class="bi bi-telephone"></i>
                        <span>+233 20 123 4567</span>
                    </li>
                    <li>
                        <i class="bi bi-envelope"></i>
                        <span>support@dripyard.com</span>
                    </li>
                </ul>

                <!-- Newsletter -->
                <div class="footer-newsletter">
                    <h4 style="color: white; margin-bottom: 0.5rem;">Stay Updated</h4>
                    <p style="color: rgba(255, 255, 255, 0.7); font-size: 0.9rem; margin-bottom: 0;">
                        Get exclusive offers and new arrivals
                    </p>
                    <form class="newsletter-form" onsubmit="subscribeNewsletter(event)">
                        <input type="email" class="newsletter-input" placeholder="Enter your email" required>
                        <button type="submit" class="newsletter-btn">Subscribe</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Footer Bottom -->
        <div class="footer-bottom">
            <div class="footer-bottom-content">
                <div class="footer-copyright">
                    &copy; <?php echo date('Y'); ?> DripYard. All rights reserved. Made with ❤️ in Ghana
                </div>
                
                <!-- Payment Methods -->
                <div class="footer-payment-methods">
                    <div class="payment-method" title="Visa">VISA</div>
                    <div class="payment-method" title="Mastercard">MC</div>
                    <div class="payment-method" title="Paystack">PS</div>
                    <div class="payment-method" title="Mobile Money">MM</div>
                </div>
                
                <!-- Legal Links -->
                <div class="footer-legal-links">
                    <a href="<?php echo $basePath; ?>/public/privacy.php">Privacy Policy</a>
                    <a href="<?php echo $basePath; ?>/public/terms.php">Terms of Service</a>
                    <a href="<?php echo $basePath; ?>/public/cookie-policy.php">Cookie Policy</a>
                </div>
            </div>
        </div>
    </div>
</footer>

<script>
// Newsletter subscription
function subscribeNewsletter(event) {
    event.preventDefault();
    const email = event.target.querySelector('.newsletter-input').value;
    
    // Show success message
    const successMsg = document.createElement('div');
    successMsg.className = 'alert alert-success alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3';
    successMsg.style.zIndex = '9999';
    successMsg.innerHTML = `
        <i class="bi bi-check-circle me-2"></i>
        Successfully subscribed to newsletter!
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(successMsg);
    setTimeout(() => successMsg.remove(), 5000);
    
    // Clear form
    event.target.reset();
}

// Smooth scroll for footer links
document.querySelectorAll('.footer-links a').forEach(link => {
    link.addEventListener('click', function(e) {
        // Add smooth scroll effect for same-page links
        if (this.getAttribute('href').startsWith('#')) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({ behavior: 'smooth' });
            }
        }
    });
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
window.DRIPYARD = window.DRIPYARD || {};
window.DRIPYARD.basePath = '<?php echo $basePath; ?>';
window.DRIPYARD.paystackPublicKey = '<?php echo PAYSTACK_PUBLIC_KEY; ?>';
</script>
<script src="<?php echo $basePath; ?>/assets/js/main.js"></script>
<script src="https://js.paystack.co/v1/inline.js"></script>
</body>
</html>
