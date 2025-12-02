// Import the functions you need from the SDKs you need
import { initializeApp } from "firebase/app";
import { getAnalytics } from "firebase/analytics";
// TODO: Add SDKs for Firebase products that you want to use
// https://firebase.google.com/docs/web/setup#available-libraries

// Your web app's Firebase configuration
// For Firebase JS SDK v7.20.0 and later, measurementId is optional
const firebaseConfig = {
    apiKey: "AIzaSyAEjWKDKLlOpGcP_pugwygaB5Hk-7sYRZ8",
    authDomain: "cafein-419ea.firebaseapp.com",
    projectId: "cafein-419ea",
    storageBucket: "cafein-419ea.firebasestorage.app",
    messagingSenderId: "911038080398",
    appId: "1:911038080398:web:806d9d527473eb1d2630df",
    measurementId: "G-MZ6X3NHWNE"
};

// Initialize Firebase
const app = initializeApp(firebaseConfig);
const analytics = getAnalytics(app);

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
