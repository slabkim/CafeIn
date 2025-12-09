// CafeIn - Main JavaScript File
// Firebase analytics disabled in this build (browser module imports removed)
// Wait for DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {

            // ========== Cart Functionality (server-side via AJAX) ==========
            // Update cart count by calling server endpoint (already present in layout script too)
            function updateCartCount() {
                fetch('/cart/count')
                    .then(response => response.json())
                    .then(data => {
                        const cartCountElements = document.querySelectorAll('.cart-count');
                        cartCountElements.forEach(el => {
                            el.textContent = data.count;
                        });
                    })
                    .catch(error => {
                        console.error('Error fetching cart count:', error);
                    });
            }

            const cartConfig = window.CafeInCart || {};
            const summaryEls = {
                total: document.querySelector('[data-cart-total]'),
                service: document.querySelector('[data-cart-service]'),
                grand: document.querySelector('[data-cart-grand]'),
            };

            function updateCartSummary(cartTotal) {
                if (!summaryEls.total) {
                    return;
                }

                const totalValue = typeof cartTotal === 'number' ?
                    cartTotal :
                    parseFloat(cartTotal || cartConfig.baseTotal || 0) || 0;
                const rate = parseFloat(cartConfig.serviceFeeRate || 0);
                const serviceFee = totalValue * rate;
                const grandTotal = totalValue + serviceFee;

                summaryEls.total.textContent = formatCurrency(totalValue);
                if (summaryEls.service) {
                    summaryEls.service.textContent = formatCurrency(serviceFee);
                }
                if (summaryEls.grand) {
                    summaryEls.grand.textContent = formatCurrency(grandTotal);
                }

                cartConfig.baseTotal = totalValue;
            }

            function renderEmptyCart() {
                const container = document.querySelector('.cart-section .container');
                if (!container) {
                    return;
                }
                container.innerHTML = `
            <div class="empty-cart">
                <p>Your cart is empty.</p>
                <a href="${cartConfig.menuUrl || '/menus'}" class="btn-primary">Browse Menu</a>
            </div>
        `;
            }

            // Delegate add-to-cart, quantity and remove button clicks
            document.body.addEventListener('click', function(e) {
                const target = e.target;

                // Add to cart buttons (menu pages)
                const addBtn = target.closest('.btn-add');
                if (addBtn) {
                    e.preventDefault();
                    const menuId = addBtn.getAttribute('data-menu-id');
                    if (!menuId) return;

                    // Check if user logged in via meta
                    const isLoggedInMeta = document.querySelector('meta[name="user-logged-in"]');
                    const isLoggedIn = isLoggedInMeta && isLoggedInMeta.getAttribute('content') === 'true';
                    if (!isLoggedIn) {
                        window.location.href = '/login';
                        return;
                    }

                    // determine quantity: if from modal, read modal qty; else default 1
                    let qty = 1;
                    const modalQty = document.getElementById('md-qty-input');
                    if (addBtn.id === 'md-add' && modalQty) {
                        qty = parseInt(modalQty.value, 10) || 1;
                    }

                    // prevent double-click
                    addBtn.disabled = true;
                    addBtn.classList.add('is-loading');

                    fetch(`/cart/add/${menuId}`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({ quantity: qty }),
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                updateCartCount();
                                if (typeof data.cartTotal !== 'undefined') {
                                    updateCartSummary(parseFloat(data.cartTotal));
                                }
                                showNotification('Item added to cart', 'success');
                            } else {
                                showNotification('Failed to add item to cart', 'error');
                            }
                        })
                        .catch(err => {
                            console.error('Error adding to cart:', err);
                            showNotification('Gagal menambahkan ke keranjang.', 'error');
                        })
                        .finally(() => {
                            addBtn.disabled = false;
                            addBtn.classList.remove('is-loading');
                        });

                    return; // avoid falling through
                }

                // Quantity buttons
                const qtyButton = target.closest('.qty-btn');
                if (qtyButton) {
                    e.preventDefault();
                    const action = qtyButton.getAttribute('data-action');
                    const itemElem = qtyButton.closest('.cart-item');
                    if (!itemElem || !action) return;
                    const cartItemId = itemElem.getAttribute('data-cart-item-id');
                    const qtyInput = itemElem.querySelector('.quantity-input');
                    if (!qtyInput || !cartItemId) return;
                    const currentQuantity = parseInt(qtyInput.value, 10) || 1;
                    let quantity = currentQuantity;

                    if (action === 'increase') {
                        quantity = currentQuantity + 1;
                    } else if (action === 'decrease') {
                        quantity = Math.max(1, currentQuantity - 1);
                    }

                    // Nothing to update when already at minimum
                    if (quantity === currentQuantity) {
                        return;
                    }

                    qtyInput.value = quantity;

                    // PATCH to update quantity
                    fetch(`/cart/item/${cartItemId}`, {
                            method: 'PATCH',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({ quantity }),
                        })
                        .then(res => {
                            if (!res.ok) {
                                throw new Error(`Failed to update cart item: ${res.status}`);
                            }
                            return res.json();
                        })
                        .then(data => {
                            if (data.success) {
                                updateCartCount();
                                const subtotalElem = itemElem.querySelector('[data-item-subtotal]');
                                if (subtotalElem && typeof data.itemSubtotal !== 'undefined') {
                                    subtotalElem.textContent = formatCurrency(parseFloat(data.itemSubtotal));
                                } else if (subtotalElem) {
                                    const unitPrice = parseFloat(itemElem.getAttribute('data-unit-price'));
                                    const quantity = parseInt(qtyInput.value, 10);
                                    const subtotal = unitPrice * quantity;
                                    subtotalElem.textContent = formatCurrency(subtotal);
                                    // Recalculate cart total
                                    let newTotal = 0;
                                    document.querySelectorAll('.cart-item').forEach(item => {
                                        const price = parseFloat(item.getAttribute('data-unit-price'));
                                        const qty = parseInt(item.querySelector('.quantity-input').value, 10);
                                        newTotal += price * qty;
                                    });
                                    updateCartSummary(newTotal);
                                }
                                if (typeof data.cartTotal !== 'undefined') {
                                    updateCartSummary(parseFloat(data.cartTotal));
                                }
                                showNotification('Quantity updated', 'success');
                            } else {
                                showNotification('Failed to update quantity', 'error');
                            }
                        })
                        .catch(err => {
                            console.error('Error updating quantity:', err);
                            showNotification('Failed to update quantity', 'error');
                        });
                }

                // Quantity input change
                const qtyInput = target.closest('.quantity-input');
                if (qtyInput && target === qtyInput) {
                    const itemElem = qtyInput.closest('.cart-item');
                    if (!itemElem) return;
                    const cartItemId = itemElem.getAttribute('data-cart-item-id');
                    const quantity = parseInt(qtyInput.value, 10) || 1;

                    if (quantity < 1) {
                        qtyInput.value = 1;
                        return;
                    }

                    // PATCH to update quantity
                    fetch(`/cart/item/${cartItemId}`, {
                            method: 'PATCH',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({ quantity }),
                        })
                        .then(res => {
                            if (!res.ok) {
                                throw new Error(`Failed to update cart item: ${res.status}`);
                            }
                            return res.json();
                        })
                        .then(data => {
                            if (data.success) {
                                updateCartCount();
                                const subtotalElem = itemElem.querySelector('[data-item-subtotal]');
                                if (subtotalElem && typeof data.itemSubtotal !== 'undefined') {
                                    subtotalElem.textContent = formatCurrency(parseFloat(data.itemSubtotal));
                                } else if (subtotalElem) {
                                    const unitPrice = parseFloat(itemElem.getAttribute('data-unit-price'));
                                    const quantity = parseInt(qtyInput.value, 10);
                                    const subtotal = unitPrice * quantity;
                                    subtotalElem.textContent = formatCurrency(subtotal);
                                    // Recalculate cart total
                                    let newTotal = 0;
                                    document.querySelectorAll('.cart-item').forEach(item => {
                                        const price = parseFloat(item.getAttribute('data-unit-price'));
                                        const qty = parseInt(item.querySelector('.quantity-input').value, 10);
                                        newTotal += price * qty;
                                    });
                                    updateCartSummary(newTotal);
                                }
                                if (typeof data.cartTotal !== 'undefined') {
                                    updateCartSummary(parseFloat(data.cartTotal));
                                }
                                showNotification('Quantity updated', 'success');
                            } else {
                                showNotification('Failed to update quantity', 'error');
                            }
                        })
                        .catch(err => {
                            console.error('Error updating quantity:', err);
                            showNotification('Failed to update quantity', 'error');
                        });
                }

                // Remove button
                const removeButton = target.closest('.cart-remove-btn, .remove-btn, [data-action="remove"]');
                if (removeButton) {
                    e.preventDefault();
                    const itemElem = removeButton.closest('.cart-item');
                    if (!itemElem) return;
                    const cartItemId = itemElem.getAttribute('data-cart-item-id');
                    if (!cartItemId) return;

                    fetch(`/cart/item/${cartItemId}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Content-Type': 'application/json',
                            },
                        })
                        .then(res => {
                            if (!res.ok) {
                                throw new Error(`Failed to remove cart item: ${res.status}`);
                            }
                            return res.json();
                        })
                        .then(data => {
                            if (data.success) {
                                itemElem.remove();
                                updateCartCount();
                                if (typeof data.cartTotal !== 'undefined') {
                                    updateCartSummary(parseFloat(data.cartTotal));
                                }
                                showNotification('Item removed', 'success');
                                if (!document.querySelector('.cart-item')) {
                                    renderEmptyCart();
                                }
                            } else {
                                showNotification('Failed to remove item', 'error');
                            }
                        })
                        .catch(err => {
                            console.error('Error removing item:', err);
                            showNotification('Failed to remove item', 'error');
                        });
                }
            });

            const checkoutButton = document.querySelector('.checkout-btn');
            if (checkoutButton && cartConfig.checkoutUrl) {
                checkoutButton.addEventListener('click', () => {
                    if ((cartConfig.baseTotal || 0) <= 0) {
                        showNotification('Tambahkan item ke keranjang terlebih dahulu.', 'error');
                        return;
                    }

                    checkoutButton.disabled = true;
                    checkoutButton.classList.add('is-loading');

                    fetch(cartConfig.checkoutUrl, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({}),
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                showNotification(data.message || 'Pesanan berhasil dibuat.', 'success');
                                cartConfig.baseTotal = 0;
                                updateCartSummary(0);
                                renderEmptyCart();
                                updateCartCount();
                                const redirectUrl = data.redirect || cartConfig.paymentsUrl || '/payments';
                                setTimeout(() => {
                                    window.location.href = redirectUrl;
                                }, 700);
                            } else {
                                showNotification(data.message || 'Gagal membuat pesanan.', 'error');
                            }
                        })
                        .catch(err => {
                            console.error('Error during checkout:', err);
                            showNotification('Terjadi kesalahan saat membuat pesanan.', 'error');
                        })
                        .finally(() => {
                            checkoutButton.disabled = false;
                            checkoutButton.classList.remove('is-loading');
                        });
                });
            }

            if (summaryEls.total) {
                updateCartSummary(cartConfig.baseTotal || 0);
            }

            const paymentConfig = window.CafeInPayment || null;
            const cancelPendingPayment = (reason) => {
                if (!paymentConfig || !paymentConfig.cancelUrl || !paymentConfig.orderId) {
                    return Promise.resolve(false);
                }

                const payload = {
                    order_id: paymentConfig.orderId,
                };
                if (reason) {
                    payload.reason = reason;
                }

                return fetch(paymentConfig.cancelUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify(payload),
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data && data.success) {
                            showNotification(data.message || 'Pembayaran dibatalkan.', 'info');
                            return true;
                        }
                        return false;
                    })
                    .catch(err => {
                        console.error('Error cancelling pending payment', err);
                        return false;
                    });
            };
            if (paymentConfig && paymentConfig.completeUrl) {
                const paymentButton = document.querySelector('.btn-payment');
                if (paymentButton) {
                    paymentButton.addEventListener('click', () => {
                        const selectedMethod = document.querySelector('input[name="payment"]:checked');
                        if (!selectedMethod) {
                            showNotification('Pilih metode pembayaran terlebih dahulu.', 'error');
                            return;
                        }

                        const method = selectedMethod.value;

                        // Alur Midtrans (Snap) untuk pembayaran online
                        if (method === 'midtrans') {
                            if (!paymentConfig.snapUrl) {
                                showNotification('Link pembayaran Midtrans tidak tersedia. Coba ulangi.', 'error');
                                return;
                            }
                            if (typeof snap === 'undefined') {
                                showNotification('Pembayaran Midtrans belum siap. Coba reload halaman.', 'error');
                                return;
                            }

                            paymentButton.disabled = true;
                            paymentButton.classList.add('is-loading');

                            fetch(paymentConfig.snapUrl, {
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json',
                                },
                            })
                                .then(res => res.json())
                                .then(data => {
                                    if (!data.success || !data.token) {
                                        throw new Error(data.message || 'Gagal mendapatkan token pembayaran.');
                                    }
                                    snap.pay(data.token, {
                                        onSuccess: function (result) {
                                            // Setelah Midtrans sukses, catat pembayaran di server
                                            fetch(paymentConfig.completeUrl, {
                                                method: 'POST',
                                                headers: {
                                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                                    'Content-Type': 'application/json',
                                                    'Accept': 'application/json',
                                                },
                                                body: JSON.stringify((() => {
                                                    const payload = {
                                                        order_id: paymentConfig.orderId,
                                                        method: method,
                                                    };
                                                    if (result) {
                                                        payload.transaction_id = result.transaction_id;
                                                        payload.payment_type = result.payment_type;
                                                        payload.transaction_status = result.transaction_status;
                                                        payload.gross_amount = result.gross_amount;
                                                    }
                                                    const nameEl = document.getElementById('cf-customer-name');
                                                    const notesEl = document.getElementById('cf-notes');
                                                    if (nameEl) payload.customer_name = nameEl.value.trim();
                                                    if (notesEl) payload.notes = notesEl.value.trim();
                                                    return payload;
                                                })()),
                                            })
                                                .then(r => r.json())
                                                .then(result => {
                                                    if (result.success) {
                                                        showNotification(result.message || 'Pembayaran berhasil.', 'success');
                                                        setTimeout(() => window.location.reload(), 600);
                                                    } else {
                                                        showNotification(result.message || 'Gagal mencatat pembayaran.', 'error');
                                                    }
                                                })
                                                .catch(err => {
                                                    console.error('Error completing payment after Midtrans:', err);
                                                    showNotification('Terjadi kesalahan saat mencatat pembayaran.', 'error');
                                                })
                                                .finally(() => {
                                                    paymentButton.disabled = false;
                                                    paymentButton.classList.remove('is-loading');
                                                });
                                        },
                                        onPending: function () {
                                            showNotification('Pembayaran masih pending di Midtrans.', 'info');
                                            paymentButton.disabled = false;
                                            paymentButton.classList.remove('is-loading');
                                        },
                                        onError: function () {
                                            cancelPendingPayment('midtrans-error')
                                                .then(cancelled => {
                                                    showNotification('Pembayaran melalui Midtrans gagal.', 'error');
                                                    if (cancelled) {
                                                        setTimeout(() => window.location.reload(), 600);
                                                    }
                                                })
                                                .finally(() => {
                                                    paymentButton.disabled = false;
                                                    paymentButton.classList.remove('is-loading');
                                                });
                                        },
                                        onClose: function () {
                                            cancelPendingPayment('midtrans-cancelled')
                                                .then(cancelled => {
                                                    if (cancelled) {
                                                        setTimeout(() => window.location.reload(), 600);
                                                    } else {
                                                        showNotification('Pembayaran dibatalkan.', 'info');
                                                    }
                                                })
                                                .finally(() => {
                                                    paymentButton.disabled = false;
                                                    paymentButton.classList.remove('is-loading');
                                                });
                                        },
                                    });
                                })
                                .catch(err => {
                                    console.error('Error getting Midtrans Snap token:', err);
                                    showNotification('Gagal memulai pembayaran Midtrans.', 'error');
                                    paymentButton.disabled = false;
                                    paymentButton.classList.remove('is-loading');
                                });

                            return; // jangan lanjut ke alur manual
                        }

                        // Alur pembayaran internal (kasir/admin)
                        paymentButton.disabled = true;
                        paymentButton.classList.add('is-loading');

                        fetch(paymentConfig.completeUrl, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify((() => {
                                const payload = {
                                    order_id: paymentConfig.orderId,
                                    method: method,
                                };
                                const nameEl = document.getElementById('cf-customer-name');
                                const notesEl = document.getElementById('cf-notes');
                                if (nameEl) payload.customer_name = nameEl.value.trim();
                                if (notesEl) payload.notes = notesEl.value.trim();
                                return payload;
                            })()),
                        })
                            .then(res => res.json())
                            .then(data => {
                                if (data.success) {
                                    showNotification(data.message || 'Pembayaran berhasil.', 'success');
                                    setTimeout(() => {
                                        window.location.reload();
                                    }, 600);
                                } else {
                                    showNotification(data.message || 'Gagal menyelesaikan pembayaran.', 'error');
                                }
                            })
                            .catch(err => {
                                console.error('Error completing payment:', err);
                                showNotification('Terjadi kesalahan saat memproses pembayaran.', 'error');
                            })
                            .finally(() => {
                                paymentButton.disabled = false;
                                paymentButton.classList.remove('is-loading');
                            });
                    });
                }
            }

            // Save order info (cashier/admin)
            if (paymentConfig && paymentConfig.saveUrl) {
                const saveBtn = document.getElementById('btn-save-order-info');
                if (saveBtn) {
                    saveBtn.addEventListener('click', () => {
                        const nameEl = document.getElementById('cf-customer-name');
                        const notesEl = document.getElementById('cf-notes');
                        const payload = {
                            order_id: paymentConfig.orderId,
                            customer_name: nameEl ? (nameEl.value || '').trim() : undefined,
                            notes: notesEl ? (notesEl.value || '').trim() : undefined,
                        };
                        saveBtn.disabled = true;
                        fetch(paymentConfig.saveUrl, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                },
                                body: JSON.stringify(payload),
                            })
                            .then(res => res.json())
                            .then(data => {
                                if (data && data.success) {
                                    showNotification(data.message || 'Informasi order disimpan.', 'success');
                                    const now = new Date();
                                    const stamp = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                                    const cs = document.getElementById('cf-customer-status');
                                    const ns = document.getElementById('cf-notes-status');
                                    if (cs) cs.textContent = `Tersimpan ${stamp}`;
                                    if (ns) ns.textContent = `Tersimpan ${stamp}`;
                                } else {
                                    showNotification('Gagal menyimpan informasi order.', 'error');
                                }
                            })
                            .catch(err => {
                                console.error('Save order info error', err);
                                showNotification('Terjadi kesalahan saat menyimpan.', 'error');
                            })
                            .finally(() => {
                                saveBtn.disabled = false;
                            });
                    });
                }

                // Autosave with debounce on inputs
                const nameEl = document.getElementById('cf-customer-name');
                const notesEl = document.getElementById('cf-notes');
                const cs = document.getElementById('cf-customer-status');
                const ns = document.getElementById('cf-notes-status');
                let t;
                let lastSaved = {
                    name: nameEl ? nameEl.value : undefined,
                    notes: notesEl ? notesEl.value : undefined,
                };

                function setSaving(el) { if (el) el.textContent = 'Menyimpan…'; }

                function setSaved(el) {
                    if (!el) return;
                    const now = new Date();
                    const stamp = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                    el.textContent = `Tersimpan ${stamp}`;
                }

                function autosave() {
                    const nameVal = nameEl ? nameEl.value.trim() : undefined;
                    const notesVal = notesEl ? notesEl.value.trim() : undefined;
                    const changed = (nameVal !== lastSaved.name) || (notesVal !== lastSaved.notes);
                    if (!changed) return;
                    if (cs) setSaving(cs);
                    if (ns) setSaving(ns);
                    fetch(paymentConfig.saveUrl, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({
                                order_id: paymentConfig.orderId,
                                customer_name: nameVal,
                                notes: notesVal,
                            }),
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data && data.success) {
                                lastSaved = { name: nameVal, notes: notesVal };
                                setSaved(cs);
                                setSaved(ns);
                            }
                        })
                        .catch(err => console.error('Autosave error', err));
                }

                [nameEl, notesEl].forEach(el => {
                    if (!el) return;
                    el.addEventListener('input', () => {
                        clearTimeout(t);
                        t = setTimeout(autosave, 600);
                    });
                    el.addEventListener('blur', () => {
                        clearTimeout(t);
                        autosave();
                    });
                });
            }

            // ========== Menu Filter Functionality ==========
            const filterButtons = document.querySelectorAll('.filter-btn');
            const menuCategories = document.querySelectorAll('.menu-category');

            filterButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const category = this.getAttribute('data-category');

                    // Update active button
                    filterButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');

                    // Filter categories
                    if (category === 'all') {
                        menuCategories.forEach(cat => {
                            cat.style.display = 'block';
                        });
                    } else {
                        menuCategories.forEach(cat => {
                            if (cat.id === category) {
                                cat.style.display = 'block';
                            } else {
                                cat.style.display = 'none';
                            }
                        });
                    }
                });
            });

            // ========== Live Menu Search (AJAX) ==========
            const searchForm = document.getElementById('menu-search-form');
            const searchInput = document.getElementById('q');
            const liveWrap = document.getElementById('live-search-results');
            const liveGrid = document.getElementById('live-results-grid');
            const liveEmpty = document.getElementById('live-results-empty');
            const filterWrap = document.querySelector('.menu-filter');
            const categoryBlocks = document.querySelectorAll('.menu-category');

            function escapeHtml(s) {
                if (!s) return '';
                return String(s)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/\"/g, '&quot;')
                    .replace(/'/g, '&#39;');
            }

            function highlight(text, q) {
                if (!q) return escapeHtml(text || '');
                const safe = escapeHtml(text || '');
                const re = new RegExp(q.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'ig');
                return safe.replace(re, m => `<mark>${m}</mark>`);
            }

            async function performLiveSearch(q) {
                if (!searchForm || !searchInput || !liveWrap || !liveGrid) return;
                if (!q) {
                    // restore normal view
                    liveWrap.style.display = 'none';
                    if (filterWrap) filterWrap.style.display = '';
                    categoryBlocks.forEach(el => el.style.display = '');
                    return;
                }
                try {
                    const params = new URLSearchParams();
                    params.set('q', q);
                    params.set('ajax', '1');
                    const catSel = document.getElementById('category');
                    const minEl = document.getElementById('min_price');
                    const maxEl = document.getElementById('max_price');
                    if (catSel && catSel.value) params.set('category', catSel.value);
                    if (minEl && minEl.value) params.set('min_price', minEl.value);
                    if (maxEl && maxEl.value) params.set('max_price', maxEl.value);
                    const res = await fetch(`/menus?${params.toString()}`, {
                        headers: { 'Accept': 'application/json' }
                    });
                    const data = await res.json();
                    const items = Array.isArray(data.results) ? data.results : [];
                    // show live section, hide categories
                    liveWrap.style.display = '';
                    if (filterWrap) filterWrap.style.display = 'none';
                    categoryBlocks.forEach(el => el.style.display = 'none');
                    liveGrid.innerHTML = '';
                    if (items.length === 0) {
                        liveEmpty.style.display = '';
                        return;
                    }
                    liveEmpty.style.display = 'none';
                    const html = items.map(menu => `
                <div class="menu-item" data-menu-id="${menu.id}" data-menu-name="${escapeHtml(menu.name)}"
                    data-menu-desc="${escapeHtml(menu.description || 'Menu favorit kami.')}"
                    data-menu-price="${menu.price}" data-menu-image="${menu.image_url || ''}">
                    <div class="menu-item-image">
                        ${menu.image_url ? `<img src="${menu.image_url}" alt="${escapeHtml(menu.name)}" loading="lazy">` : `<span class="avatar-fallback">${escapeHtml((menu.name||' ').charAt(0).toUpperCase())}</span>`}
                    </div>
                    <div class="menu-item-content" role="button" tabindex="0" aria-label="Detail ${escapeHtml(menu.name)}">
                        <h3>${highlight(menu.name, q)}</h3>
                        <p>${highlight(menu.description || 'Menu favorit kami.', q)}</p>
                        <div class="menu-item-footer">
                            <span class="price">${formatCurrency(parseFloat(menu.price||0))}</span>
                            <button class="btn-add" data-menu-id="${menu.id}" ${menu.stock < 1 ? 'disabled' : ''}>${menu.stock < 1 ? 'Out of Stock' : 'Add to Cart'}</button>
                        </div>
                    </div>
                </div>`).join('');
            liveGrid.innerHTML = html;
        } catch (e) {
            console.error('Live search failed', e);
        }
    }

    if (searchInput) {
        let t;
        searchInput.addEventListener('input', function() {
            clearTimeout(t);
            const q = this.value.trim();
            t = setTimeout(() => performLiveSearch(q), 380);
        });
    }

    // ========== Order Tabs Functionality ==========
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');

    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const tabName = this.getAttribute('data-tab');

            // Update active tab button
            tabButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');

            // Show corresponding tab content
            tabContents.forEach(content => {
                if (content.id === tabName) {
                    content.classList.add('active');
                } else {
                    content.classList.remove('active');
                }
            });
        });
    });

    // ========== Payment Method Selection ==========
    const paymentMethods = document.querySelectorAll('.payment-method');

    paymentMethods.forEach(method => {
        method.addEventListener('click', function() {
            // Remove active class from all
            paymentMethods.forEach(m => m.classList.remove('active'));

            // Add active class to clicked method
            this.classList.add('active');

            // Check the radio button
            const radio = this.querySelector('input[type="radio"]');
            if (radio) {
                radio.checked = true;
            }
        });
    });

    // ========== Promo Code Functionality ==========
    const applyPromoBtn = document.querySelector('.btn-apply');
    if (applyPromoBtn) {
        applyPromoBtn.addEventListener('click', function() {
            const promoInput = document.querySelector('.promo-input');
            const promoCode = promoInput.value.trim().toUpperCase();

            if (promoCode === '') {
                showNotification('Please enter a promo code', 'warning');
                return;
            }

            // Example promo codes
            const validPromoCodes = {
                'CAFEIN10': 10,
                'WELCOME20': 20,
                'FIRST50': 50
            };

            if (validPromoCodes[promoCode]) {
                const discount = validPromoCodes[promoCode];
                showNotification(`Promo code applied! ${discount}% discount`, 'success');
                promoInput.value = '';
                // Here you would update the total calculation
            } else {
                showNotification('Invalid promo code', 'error');
            }
        });
    }

    // ========== Payment Button ==========
    const paymentBtn = document.querySelector('.btn-payment');
    if (paymentBtn) {
        paymentBtn.addEventListener('click', function() {
            // Validate delivery form
            const deliveryForm = document.querySelector('.delivery-form');
            if (deliveryForm) {
                const inputs = deliveryForm.querySelectorAll('input[required], textarea[required]');
                let isValid = true;

                inputs.forEach(input => {
                    if (input.value.trim() === '') {
                        isValid = false;
                        input.style.borderColor = '#EF4444';
                    } else {
                        input.style.borderColor = '#E5E7EB';
                    }
                });

                if (!isValid) {
                    showNotification('Please fill in all required fields', 'error');
                    return;
                }
            }

            // Check if payment method is selected
            const selectedPayment = document.querySelector('.payment-method.active');
            if (!selectedPayment) {
                showNotification('Please select a payment method', 'warning');
                return;
            }

            showNotification('Processing payment...', 'success');

            // Simulate payment processing
            setTimeout(() => {
                showNotification('Payment successful! Order confirmed.', 'success');
                // Here you would redirect to order confirmation page
                // window.location.href = '/order-confirmation';
            }, 2000);
        });
    }

    // ========== Track Order Buttons ==========
    const trackOrderButtons = document.querySelectorAll('.btn-outline');
    trackOrderButtons.forEach(button => {
        if (button.textContent.includes('Track')) {
            button.addEventListener('click', function() {
                const orderCard = this.closest('.order-card');
                const orderNumber = orderCard.querySelector('.order-number').textContent;
                showNotification(`Tracking order ${orderNumber}...`, 'success');
                // Here you would show order tracking modal or redirect
            });
        }

        if (button.textContent.includes('Reorder')) {
            button.addEventListener('click', function() {
                showNotification('Items added to cart!', 'success');
                // Here you would add items back to cart
            });
        }
    });

    // ========== Notification System ==========
    function showNotification(message, type = 'success') {
        // Remove existing notification if any
        const existingNotif = document.querySelector('.notification');
        if (existingNotif) {
            existingNotif.remove();
        }

        // Create notification element
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.textContent = message;

        // Add to body
        document.body.appendChild(notification);

        // Trigger animation
        setTimeout(() => {
            notification.classList.add('show');
        }, 10);

        // Remove after 3 seconds
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 3000);
    }

    // ========== Smooth Scroll for Navigation ==========
    const navLinks = document.querySelectorAll('.nav-menu a');
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            // Only prevent default for anchor links (starts with #)
            if (this.getAttribute('href').startsWith('#')) {
                e.preventDefault();
                const targetId = this.getAttribute('href');
                const targetSection = document.querySelector(targetId);

                if (targetSection) {
                    targetSection.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    });

    // ========== Form Validation Helper ==========
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        const inputs = form.querySelectorAll('input, textarea');
        inputs.forEach(input => {
            let isDirty = false;
            const wrapper = input.closest('.input-wrapper');
            const toggleInvalid = (state) => {
                input.classList.toggle('input-invalid', state);
                if (wrapper) {
                    wrapper.classList.toggle('input-invalid', state);
                }
                input.setAttribute('aria-invalid', state ? 'true' : 'false');
            };

            input.addEventListener('input', function() {
                isDirty = true;
                const hasError = this.hasAttribute('required') && this.value.trim() === '';
                toggleInvalid(hasError);
            });

            input.addEventListener('blur', function() {
                if (!isDirty) return;
                const hasError = this.hasAttribute('required') && this.value.trim() === '';
                toggleInvalid(hasError);
            });
        });
    });

    // ========== Admin Sidebar Toggle ==========
    const adminShell = document.querySelector('[data-admin-shell]');
    if (adminShell) {
        const toggles = adminShell.querySelectorAll('[data-admin-toggle]');
        const overlay = adminShell.querySelector('[data-admin-overlay]');
        toggles.forEach(btn => {
            btn.addEventListener('click', () => {
                adminShell.classList.toggle('sidebar-open');
            });
        });
        if (overlay) {
            overlay.addEventListener('click', () => adminShell.classList.remove('sidebar-open'));
        }
        window.addEventListener('resize', () => {
            if (window.innerWidth > 992) {
                adminShell.classList.remove('sidebar-open');
            }
        });
    }

    // ========== Initialize ==========
    console.log('CafeIn website loaded successfully! ☕');

});

// Menu Detail Modal (outside DOMContentLoaded in case of dynamic loads)
(function(){
    const menuModal = document.getElementById('menu-detail-modal');
    if (!menuModal) return;
    const imgEl = document.getElementById('md-image');
    const titleEl = document.getElementById('menu-detail-title');
    const descEl = document.getElementById('md-desc');
    const priceEl = document.getElementById('md-price');
    const addBtn = document.getElementById('md-add');
    const catEl = document.getElementById('md-category');
    const stockEl = document.getElementById('md-stock');
    const prepEl = document.getElementById('md-prep');
    const sizeEl = document.getElementById('md-size');
    const calEl = document.getElementById('md-cal');
    const qtyInput = document.getElementById('md-qty-input');
    const qtyInc = document.getElementById('md-qty-inc');
    const qtyDec = document.getElementById('md-qty-dec');

    function openMenuModal(data) {
        if (imgEl) {
            if (data.image) {
                imgEl.src = data.image;
                imgEl.alt = data.name;
                imgEl.style.display = '';
            } else {
                imgEl.removeAttribute('src');
                imgEl.alt = '';
                imgEl.style.display = 'none';
            }
        }
        if (titleEl) titleEl.textContent = data.name || '';
        if (descEl) descEl.textContent = data.desc || '';
        if (priceEl) priceEl.textContent = formatCurrency(parseFloat(data.price || 0));
        if (addBtn) addBtn.setAttribute('data-menu-id', data.id);
        if (catEl) catEl.textContent = data.category || '-';
        if (stockEl) stockEl.textContent = (data.stock !== undefined && data.stock !== null) ? data.stock : '-';
        if (prepEl) prepEl.textContent = data.prep || '-';
        if (sizeEl) sizeEl.textContent = data.size || '-';
        if (calEl) calEl.textContent = data.cal || '-';
        if (qtyInput) qtyInput.value = 1;
        menuModal.setAttribute('aria-hidden', 'false');
        menuModal.classList.add('is-open');
        document.body.style.overflow = 'hidden';
    }

    function closeMenuModal() {
        menuModal.setAttribute('aria-hidden', 'true');
        menuModal.classList.remove('is-open');
        document.body.style.overflow = '';
    }

    document.body.addEventListener('click', function(e) {
        if (e.target.closest('.btn-add')) return; // let add-to-cart handler handle it
        const trigger = e.target.closest('.menu-item-content');
        if (trigger && trigger.closest('.menu-item')) {
            const wrap = trigger.closest('.menu-item');
            const data = {
                id: wrap.getAttribute('data-menu-id'),
                name: wrap.getAttribute('data-menu-name'),
                desc: wrap.getAttribute('data-menu-desc'),
                price: wrap.getAttribute('data-menu-price'),
                image: wrap.getAttribute('data-menu-image') || '',
                category: wrap.getAttribute('data-menu-category') || '',
                stock: wrap.getAttribute('data-menu-stock') || '',
                prep: wrap.getAttribute('data-menu-meta-prep') || '',
                size: wrap.getAttribute('data-menu-meta-size') || '',
                cal: wrap.getAttribute('data-menu-meta-cal') || '',
            };
            openMenuModal(data);
            return;
        }
        if (e.target.closest('[data-close-modal]')) {
            closeMenuModal();
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && menuModal.classList.contains('is-open')) {
            closeMenuModal();
        }
    });
    if (qtyInc && qtyInput) {
        qtyInc.addEventListener('click', function(){
            const current = parseInt(qtyInput.value, 10) || 1;
            qtyInput.value = current + 1;
        });
    }
    if (qtyDec && qtyInput) {
        qtyDec.addEventListener('click', function(){
            const current = parseInt(qtyInput.value, 10) || 1;
            qtyInput.value = Math.max(1, current - 1);
        });
    }
})();

// ========== Utility Functions ==========

// Format currency
function formatCurrency(amount) {
    return 'Rp ' + amount.toLocaleString('id-ID');
}

// Get cart total
function getCartTotal() {
    // Fetch total from server or compute on server-side; here we return 0 as a fallback
    return 0;
}
