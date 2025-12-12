// Core JS for DripYard Clothing Line

(function () {
  function postForm(url, data) {
    return fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams(data),
    }).then(function (res) { return res.json(); });
  }

  function updateCartCount(count) {
    var el = document.getElementById('cart-count');
    if (el) {
      el.textContent = count;
    }
  }

  // Handle Add to Cart buttons (data-product-id, optional data-quantity)
  document.addEventListener('click', function (e) {
    var btn = e.target.closest('.add-to-cart-btn');
    if (!btn) return;

    e.preventDefault();

    var productId = btn.getAttribute('data-product-id');
    var qty = btn.getAttribute('data-quantity') || '1';
    var basePath = (window.DRIPYARD && window.DRIPYARD.basePath) || '..';

    postForm(basePath + '/backend/cart-controller.php', {
      action: 'add',
      product_id: productId,
      quantity: qty,
    })
      .then(function (data) {
        if (data.success) {
          updateCartCount(data.count || 0);
        }
        if (data.message) {
          console.info('Cart update:', data.message);
        }
      })
      .catch(function (err) {
        console.error('Could not update cart.', err);
      });
  });

  // Quick View handler for product cards
  document.addEventListener('click', function (e) {
    var quickBtn = e.target.closest('.btn-quickview');
    if (!quickBtn) return;

    e.preventDefault();

    var card = quickBtn.closest('.product-card');
    if (!card) return;

    var imgEl = card.querySelector('.product-image-container img');
    var nameEl = card.querySelector('.product-name, .product-title, .product-info h3');
    var catEl = card.querySelector('.product-category, .badge-category');
    var priceEl = card.querySelector('.current-price, .price-tag');

    var imgSrc = imgEl ? imgEl.getAttribute('src') : '';
    var imgAlt = imgEl ? imgEl.getAttribute('alt') : '';
    var name = nameEl ? nameEl.textContent.trim() : 'Product';
    var category = catEl ? catEl.textContent.trim() : '';
    var price = priceEl ? priceEl.textContent.trim() : '';

    // Build modal if not exists
    var existing = document.getElementById('quickViewModal');
    if (!existing) {
      var modal = document.createElement('div');
      modal.id = 'quickViewModal';
      modal.className = 'modal fade';
      modal.innerHTML = '<div class="modal-dialog modal-dialog-centered modal-lg">'
        + '<div class="modal-content">'
        + '<div class="modal-header">'
        + '<h5 class="modal-title">Quick View</h5>'
        + '<button type="button" class="btn-close" data-bs-dismiss="modal"></button>'
        + '</div>'
        + '<div class="modal-body">'
        + '<div class="row g-3 align-items-center">'
        + '<div class="col-md-5">'
        + '<div class="ratio ratio-4x3 bg-light rounded-3 d-flex align-items-center justify-content-center overflow-hidden" id="qvImageWrap">'
        + '<span class="text-muted"><i class="bi bi-image"></i></span>'
        + '</div>'
        + '</div>'
        + '<div class="col-md-7">'
        + '<div class="text-muted small mb-1" id="qvCategory"></div>'
        + '<h3 class="h5 mb-2" id="qvName"></h3>'
        + '<div class="mb-3 fw-semibold" id="qvPrice"></div>'
        + '<button type="button" class="btn btn-sunny-primary w-100" id="qvAddToCartBtn">'
        + '<i class="bi bi-bag-plus me-2"></i>Add to Cart'
        + '</button>'
        + '</div>'
        + '</div>'
        + '</div>'
        + '</div>'
        + '</div>';
      document.body.appendChild(modal);
    }

    var modalEl = document.getElementById('quickViewModal');
    var imgWrap = modalEl.querySelector('#qvImageWrap');
    var nameOut = modalEl.querySelector('#qvName');
    var catOut = modalEl.querySelector('#qvCategory');
    var priceOut = modalEl.querySelector('#qvPrice');
    var addBtn = modalEl.querySelector('#qvAddToCartBtn');

    // Populate
    nameOut.textContent = name;
    catOut.textContent = category;
    priceOut.textContent = price;

    imgWrap.innerHTML = '';
    if (imgSrc) {
      var img = document.createElement('img');
      img.src = imgSrc;
      img.alt = imgAlt || name;
      img.className = 'w-100 h-100 object-fit-cover';
      imgWrap.appendChild(img);
    } else {
      imgWrap.innerHTML = '<span class="text-muted"><i class="bi bi-image"></i></span>';
    }

    // Wire Add to Cart from quick view (re-use global handler via data attributes)
    var prodId = quickBtn.getAttribute('data-product-id');
    if (prodId) {
      addBtn.dataset.productId = prodId;
      addBtn.classList.add('add-to-cart-btn');
    }

    var bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);
    bsModal.show();
  });

  // Wishlist toggle handler for any .btn-wishlist button
  document.addEventListener('click', function (e) {
    var wishBtn = e.target.closest('.btn-wishlist');
    if (!wishBtn) return;

    e.preventDefault();

    var productId = wishBtn.getAttribute('data-product-id');
    var boxId = wishBtn.getAttribute('data-box-id');
    var type = productId ? 'product' : 'box';
    var id = productId || boxId;
    if (!id) {
      return;
    }

    var basePath = (window.DRIPYARD && window.DRIPYARD.basePath) || '..';

    postForm(basePath + '/backend/wishlist-controller.php', {
      action: 'toggle',
      type: type,
      id: id,
    })
      .then(function (data) {
        if (!data.success) {
          console.warn('Wishlist toggle failed');
          return;
        }

        var icon = wishBtn.querySelector('i');
        var inWishlist = !!data.inWishlist;

        if (icon) {
          if (inWishlist) {
            icon.classList.remove('bi-heart');
            icon.classList.add('bi-heart-fill');
            wishBtn.style.color = '#ef4444';
          } else {
            icon.classList.remove('bi-heart-fill');
            icon.classList.add('bi-heart');
            wishBtn.style.color = '';
          }
        }
      })
      .catch(function (err) {
        console.error('Error toggling wishlist.', err);
      });
  });

  // Handle cart quantity updates and removals on the cart page
  document.addEventListener('change', function (e) {
    var input = e.target.closest('.cart-qty-input');
    if (!input) return;

    var productId = input.getAttribute('data-product-id');
    var qty = input.value;
    var basePath = (window.DRIPYARD && window.DRIPYARD.basePath) || '..';

    postForm(basePath + '/backend/cart-controller.php', {
      action: 'update',
      product_id: productId,
      quantity: qty,
    })
      .then(function (data) {
        if (data.success) {
          updateCartCount(data.count || 0);
          window.location.reload();
        } else if (data.message) {
          console.warn('Cart update failed:', data.message);
        }
      })
      .catch(function (err) {
        console.error('Could not update cart.', err);
      });
  });

  document.addEventListener('click', function (e) {
    var btnRemove = e.target.closest('.cart-remove-btn');
    if (!btnRemove) return;

    e.preventDefault();

    if (!confirm('Remove this item from your cart?')) return;

    var productId = btnRemove.getAttribute('data-product-id');
    var basePath = (window.DRIPYARD && window.DRIPYARD.basePath) || '..';

    postForm(basePath + '/backend/cart-controller.php', {
      action: 'remove',
      product_id: productId,
    })
      .then(function (data) {
        if (data.success) {
          updateCartCount(data.count || 0);
          window.location.reload();
        } else if (data.message) {
          console.error('Cart update failed:', data.message);
        }
      })
      .catch(function (err) {
        console.error('Could not update cart.', err);
      });
  });

  // Paystack inline payment on checkout page
  function handlePaystackCheckout() {
    var btn = document.getElementById('pay-now-btn');
    if (!btn || typeof PaystackPop === 'undefined') return;

    btn.addEventListener('click', function (e) {
      e.preventDefault();

      if (!window.DRIPYARD || !window.DRIPYARD.paystackPublicKey) {
        console.error('Payment is not configured.');
        return;
      }

      var details = window.DRIPYARD.checkout || {};
      if (!details.totalAmount || !details.email) {
        console.error('Missing checkout details for payment.');
        return;
      }

      var ref = 'DRIP-' + Date.now();

      var handler = PaystackPop.setup({
        key: window.DRIPYARD.paystackPublicKey,
        email: details.email,
        amount: Math.round(details.totalAmount * 100),
        currency: 'GHS',
        ref: ref,
        callback: function (response) {
          var basePath = window.DRIPYARD.basePath || '..';
          postForm(basePath + '/backend/payment-callback.php', {
            reference: response.reference,
          })
            .then(function (data) {
              if (data.success) {
                window.location.href = basePath + '/public/dashboard.php?order=success';
              } else if (data.message) {
                console.error('Payment verification failed:', data.message);
              }
            })
            .catch(function (err) {
              console.error('Could not verify payment. Please contact support.', err);
            });
        },
        onClose: function () {
          console.info('Payment window closed by user.');
        },
        onError: function (error) {
          console.error('Paystack error:', error);
          var errorMsg = 'Payment processing failed. ';
          if (error.message && error.message.includes('Currency not supported')) {
            errorMsg = 'Ghana Cedis (GHS) is not supported by this payment gateway. Please contact support for alternative payment methods.';
          } else if (error.message) {
            errorMsg += error.message;
          } else {
            errorMsg += 'Please try again or use a different payment method.';
          }
          console.error(errorMsg);
        },
      });

      handler.openIframe();
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    handlePaystackCheckout();
  });
})();
