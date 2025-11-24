document.addEventListener('DOMContentLoaded', () => {
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

    // Add to cart button click handler
    document.body.addEventListener('click', (event) => {
        if (event.target.classList.contains('btn-add')) {
            const button = event.target;
            const menuId = button.getAttribute('data-menu-id');
            if (!menuId) return;

            // Check if user is logged in by checking a meta tag or a global JS variable
            const isLoggedInMeta = document.querySelector('meta[name="user-logged-in"]');
            const isLoggedIn = isLoggedInMeta && isLoggedInMeta.getAttribute('content') === 'true';

            if (!isLoggedIn) {
                window.location.href = '/login';
                return;
            }

            fetch(`/cart/add/${menuId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ quantity: 1 }),
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateCartCount();
                        alert('Item added to cart');
                    } else {
                        alert('Failed to add item to cart');
                    }
                })
                .catch(error => {
                    console.error('Error adding to cart:', error);
                });
        }
    });

    // Update cart count on page load
    updateCartCount();
});