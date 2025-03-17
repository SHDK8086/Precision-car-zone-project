document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', function() {
            const topRight = document.querySelector('.top-right');
            if (topRight) {
                topRight.classList.toggle('mobile-menu-open');
            }
        });
    }
});
    
    function setupResponsiveLayout() {
        const windowWidth = window.innerWidth;
        
        const circleContainer = document.querySelector('.circle-container');
        if (circleContainer && windowWidth <= 480) {
            const circleItems = circleContainer.querySelectorAll('.circle-item');
            circleItems.forEach((item, index) => {
                if (index >= 4 && !item.classList.contains('more-categories') && !item.classList.contains('less-categories')) {
                    item.classList.add('mobile-hidden');
                }
            });
        }
    }


let cart = [];

function initCart() {
    try {
        const storedCart = localStorage.getItem("cart");
        if (storedCart) {
            cart = JSON.parse(storedCart);
            console.log("Cart loaded from localStorage:", cart);
        } else {
            cart = [];
        }
    } catch (e) {
        console.error("Error loading cart from localStorage:", e);
        cart = [];
    }
}

function saveCart() {
    try {
        localStorage.setItem("cart", JSON.stringify(cart));
        console.log("Cart saved to localStorage:", cart);
    } catch (e) {
        console.error("Error saving cart to localStorage:", e);
    }
}

function searchBySerialNumber() {
    const serialNumber = document.getElementById('search-input').value.trim();

    if (!serialNumber) {
        alert("Please enter a serial number to search.");
        return;
    }

    $.ajax({
        url: 'product_details.php',
        type: 'GET',
        data: { serial: serialNumber },
        dataType: 'json',
        success: function(response) {
            console.log("AJAX Response:", response);
            
            const product = response;
            if (product.error) {
                alert(product.error);
                return;
            }

            document.getElementById('popup-image').src = product.productImage || product.ProductImage;
            document.getElementById('popup-name').innerText = product.productName;
            document.getElementById('popup-serial').innerText = "Serial: " + product.serialNumber;
            document.getElementById('popup-description').innerText = product.description;
            document.getElementById('popup-price').innerText = "Rs. " + product.price;

            const ratingsContainer = document.getElementById('popup-ratings');
            ratingsContainer.innerHTML = getStarRatingHTML(product.ratings);

            const popup = document.getElementById('product-popup');
            popup.style.display = 'block';
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', error);
            alert("Error finding product. Please try again.");
        }
    });
}

function openProductPopup(productName) {
    $.ajax({
        url: 'product_details.php',
        type: 'GET',
        data: { name: productName },
        dataType: 'json',
        success: function(response) {
            console.log("AJAX Response:", response); 
            
            const product = response;
            if (product.error) {
                alert(product.error);
                return;
            }

            document.getElementById('popup-image').src = product.productImage || product.productImage;
            document.getElementById('popup-name').innerText = product.productName;
            document.getElementById('popup-serial').innerText = "Serial: " + product.serialNumber;
            document.getElementById('popup-description').innerText = product.description;
            document.getElementById('popup-price').innerText = "Rs. " + product.price;

            const ratingsContainer = document.getElementById('popup-ratings');
            ratingsContainer.innerHTML = getStarRatingHTML(product.ratings);

            const popup = document.getElementById('product-popup');
            popup.style.display = 'block';
            popup.style.opacity = '0';
            popup.style.animation = 'none';
            requestAnimationFrame(() => {
                popup.style.animation = 'fadeIn 0.5s forwards, slideIn 0.5s forwards';
            });
        },
        error: function(xhr, status, errorThrown) {
            console.error('AJAX error status:', status);
            console.error('AJAX error details:', xhr.responseText || errorThrown || 'Unknown error');
            alert("Error finding product. Please try again.");
        }
    });
}

function closeProductPopup() {
    const popup = document.getElementById('product-popup');
    popup.style.display = 'none';
}

function getStarRatingHTML(rating) {
    const fullStars = Math.floor(rating);
    const halfStar = rating % 1 >= 0.5;
    const emptyStars = 5 - fullStars - (halfStar ? 1 : 0);

    let starHTML = '';
    for (let i = 0; i < fullStars; i++) {
        starHTML += '<i class="fas fa-star"></i>'; 
    }
    if (halfStar) {
        starHTML += '<i class="fas fa-star-half-alt"></i>'; 
    }
    for (let i = 0; i < emptyStars; i++) {
        starHTML += '<i class="far fa-star"></i>'; 
    }
    return starHTML;
}

window.scrollLeftBtn = function(button) {
    const container = button.parentElement.querySelector('.scroll-wrapper');
    const scrollAmount = window.innerWidth <= 480 ? 160 : 220; 
    container.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
};

window.scrollRightBtn = function(button) {
    const container = button.parentElement.querySelector('.scroll-wrapper');
    const scrollAmount = window.innerWidth <= 480 ? 160 : 220; 
    container.scrollBy({ left: scrollAmount, behavior: 'smooth' });
};

function addToCart(product) {
    const newProduct = {
        image: product.image,
        name: product.name,
        price: product.price,
        quantity: 1,
        serialNumber: product.serialNumber || ""
    };

    cart.push(newProduct);
    saveCart();
    updateCartDisplay();
    alert("Product added to cart!");
}

function addProductFromPopup() {
    const serialText = document.getElementById('popup-serial').innerText;
    const serialNumber = serialText.replace('Serial: ', '').trim();
    
    const product = {
        name: document.getElementById('popup-name').innerText,
        image: document.getElementById('popup-image').src,
        price: parseFloat(document.getElementById('popup-price').innerText.replace('Rs. ', '')),
        serialNumber: serialNumber
    };

    addToCart(product);
    closeProductPopup();
}

function updateQuantity(index, amount) {
    console.log("Updating quantity for index", index, "by", amount);
    
    if (index < 0 || index >= cart.length) {
        console.error("Invalid cart index:", index);
        return;
    }
    
    cart[index].quantity += amount;
    
    if (cart[index].quantity <= 0) {
        removeFromCart(index);
    } else {

        saveCart();
        updateCartDisplay();
    }
}

function removeFromCart(index) {
    console.log("Removing item at index:", index);
    
    if (index < 0 || index >= cart.length) {
        console.error("Invalid cart index:", index);
        return;
    }
    
    cart.splice(index, 1);
    saveCart();
    updateCartDisplay();
}

function updateCartBadge() {
    const badge = document.getElementById('cart-badge');
    if (!badge) {
        console.error("Cart badge element not found");
        return;
    }
    
    const cartCount = cart.length;
    
    if (cartCount > 0) {
        badge.style.display = 'block';
        badge.innerText = cartCount;
    } else {
        badge.style.display = 'none';
    }
}

function updateCartView() {
    updateCartDisplay(); 
}

function updateCartDisplay() {
    console.log("Updating cart display");
    
    const cartContainer = document.getElementById('cart-items');
    const totalContainer = document.getElementById('cart-total');
    const placeOrderBtn = document.getElementById('place-order-btn');
    
    if (!cartContainer || !totalContainer) {
        console.error("Cart elements are missing from the DOM");
        return;
    }
    
    updateCartBadge();
    
    if (cart.length === 0) {
        cartContainer.innerHTML = '<p>Your cart is empty.</p>';
        totalContainer.innerText = 'Total: Rs. 0.00';
        if (placeOrderBtn) placeOrderBtn.style.display = 'none';
        return;
    }
    
    let cartHTML = '';
    let total = 0;
    
    for (let i = 0; i < cart.length; i++) {
        const item = cart[i];
        const itemTotal = item.price * item.quantity;
        total += itemTotal;
        
        cartHTML += `
            <div class="cart-item">
                <img src="${item.image}" alt="${item.name}" class="cart-item-image">
                <div class="cart-item-details">
                    <p><strong>${item.name}</strong></p>
                    <p>Price: Rs. ${parseFloat(item.price).toFixed(2)}</p>
                    <p>Quantity: 
                        <button type="button" class="qty-btn" onclick="updateQuantity(${i}, -1)">-</button>
                        <span class="qty-value">${item.quantity}</span>
                        <button type="button" class="qty-btn" onclick="updateQuantity(${i}, 1)">+</button>
                    </p>
                    <p>Item Total: Rs. ${itemTotal.toFixed(2)}</p>
                    <button type="button" class="remove-btn" onclick="removeFromCart(${i})">Remove</button>
                </div>
            </div>
        `;
    }
    
    cartHTML += `
        <div class="cart-actions">
            <button type="button" id="empty-cart-btn" onclick="emptyCart()">Empty Cart</button>
        </div>
    `;
    
    cartContainer.innerHTML = cartHTML;
    totalContainer.innerText = `Total: Rs. ${total.toFixed(2)}`;
    
    if (placeOrderBtn) placeOrderBtn.style.display = 'block';
}

function emptyCart() {
    console.log("Emptying cart");
    cart = [];
    saveCart();
    updateCartDisplay();
    alert("Cart has been emptied.");
}

function placeOrder() {
    console.log("Placing order");
    
    if (cart.length === 0) {
        alert("Your cart is empty!");
        return;
    }
    
    const customerEmail = document.getElementById('customer-email').value;
    
    if (!customerEmail || customerEmail.trim() === '') {
        alert("Email address is required to place an order.");
        return;
    }
    
    let total = cart.reduce((sum, item) => sum + item.price * item.quantity, 0);
    const confirmOrder = confirm(`Your total is Rs. ${total.toFixed(2)}. Proceed with order?`);
    
    if (confirmOrder) {
        const orderItems = [];
        const orderDate = new Date().toISOString();
        
        cart.forEach(item => {
            const quantity = parseInt(item.quantity) || 1;
            const price = parseFloat(item.price) || 0;
            const itemTotal = price * quantity;
            
            orderItems.push({
                product_name: String(item.name || "Unknown Product"),
                quantity: quantity,
                price: price,
                item_total: itemTotal,
                order_total: parseFloat(total.toFixed(2)),
                order_date: orderDate
            });
        });
        
        fetch('save_order.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ 
                orders: orderItems,
                email: customerEmail
            })
        })
        .then(response => {
            return response.text().then(text => {
                console.log("Raw server response:", text);
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error("Failed to parse response as JSON:", text);
                    throw new Error("Invalid server response: " + text);
                }
            });
        })
        .then(data => {
            console.log("Parsed server response:", data);
            
            if (data.success) {
                alert("Thank you for your order! Your order has been placed successfully.");
                cart = [];
                saveCart();
                updateCartDisplay();
                closeCartPopup();
            } else {
                alert("Error: " + (data.message || "Unknown error"));
            }
        })
        .catch(error => {
            console.error("Order error:", error);
            alert("Error placing the order: " + error.message);
        });
    }
}

function openCartPopup() {
    const cartPopup = document.getElementById('cart-container');
    if (cartPopup) {
        cartPopup.style.display = 'block';
        updateCartDisplay();
    }
}

function closeCartPopup() {
    const cartPopup = document.getElementById('cart-container');
    if (cartPopup) {
        cartPopup.style.display = 'none';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    console.log("DOM loaded, initializing cart");
    initCart();
    updateCartDisplay();
});

function getCurrentUserId() {

    return sessionStorage.getItem('loggedInUserId') || localStorage.getItem('loggedInUserId');
}

function replaceOrderSuccessAlert() {
    const originalAlert = window.alert;
    
    window.alert = function(message) {
        if (message && message.includes("Thank you for your order") && message.includes("successfully")) {
            showOrderSuccessAlert();
        } else {
            originalAlert(message);
        }
    };
    
    function showOrderSuccessAlert() {
        if (!document.getElementById('order-success-alert')) {
            createOrderSuccessAlert();
        }
        
        const alertContainer = document.getElementById('order-success-alert');
        
        alertContainer.style.display = 'flex';
        
        setTimeout(() => {
            alertContainer.classList.add('show');
            playSuccessSound();
            animateConfetti();
        }, 10);
        
        setTimeout(() => {
            closeOrderSuccessAlert();
        }, 5000);
    }
    
    function closeOrderSuccessAlert() {
        const alertContainer = document.getElementById('order-success-alert');
        if (alertContainer) {
            alertContainer.classList.remove('show');
            
            setTimeout(() => {
                alertContainer.style.display = 'none';
            }, 500);
        }
    }
    
    function createOrderSuccessAlert() {
        const alertContainer = document.createElement('div');
        alertContainer.id = 'order-success-alert';
        alertContainer.className = 'order-success-alert';
        
        alertContainer.innerHTML = `
            <div class="success-content">
                <div class="success-icon-container">
                    <svg class="success-checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                        <circle class="success-checkmark-circle" cx="26" cy="26" r="25" fill="none"/>
                        <path class="success-checkmark-check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
                    </svg>
                </div>
                <h2>Thank You!</h2>
                <p>Your order has been placed successfully.</p>
                <div id="confetti-container"></div>
                <button class="success-button" onclick="document.getElementById('order-success-alert').querySelector('.success-button').blur(); document.getElementById('order-success-alert').classList.remove('show'); setTimeout(() => { document.getElementById('order-success-alert').style.display = 'none'; }, 500);">Continue Shopping</button>
            </div>
        `;
        
        const styles = document.createElement('style');
        styles.textContent = `
            .order-success-alert {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.7);
                display: none;
                align-items: center;
                justify-content: center;
                z-index: 9999;
                opacity: 0;
                transition: opacity 0.5s ease;
            }
            
            .order-success-alert.show {
                opacity: 1;
            }
            
            .success-content {
                background-color: white;
                border-radius: 15px;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
                padding: 40px;
                text-align: center;
                max-width: 450px;
                width: 85%;
                transform: translateY(30px);
                transition: transform 0.5s cubic-bezier(0.18, 0.89, 0.32, 1.28);
                position: relative;
                overflow: hidden;
            }
            
            .order-success-alert.show .success-content {
                transform: translateY(0);
            }
            
            .success-icon-container {
                width: 80px;
                height: 80px;
                margin: 0 auto 20px;
                border-radius: 50%;
                background-color: rgba(76, 175, 80, 0.1);
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            .success-checkmark {
                width: 56px;
                height: 56px;
            }
            
            .success-checkmark-circle {
                stroke-dasharray: 166;
                stroke-dashoffset: 166;
                stroke-width: 2;
                stroke-miterlimit: 10;
                stroke: #4CAF50;
                fill: none;
                animation: stroke 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;
            }
            
            .success-checkmark-check {
                transform-origin: 50% 50%;
                stroke-dasharray: 48;
                stroke-dashoffset: 48;
                stroke-width: 3;
                stroke: #4CAF50;
                animation: stroke 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.8s forwards;
            }
            
            @keyframes stroke {
                100% {
                    stroke-dashoffset: 0;
                }
            }
            
            .success-content h2 {
                color: #333;
                font-size: 28px;
                margin: 10px 0;
            }
            
            .success-content p {
                color: #666;
                font-size: 18px;
                margin-bottom: 25px;
            }
            
            .success-button {
                background-color: #4CAF50;
                color: white;
                border: none;
                padding: 12px 30px;
                border-radius: 50px;
                font-size: 16px;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.3s;
                box-shadow: 0 3px 10px rgba(76, 175, 80, 0.3);
            }
            
            .success-button:hover {
                background-color: #45a049;
                transform: translateY(-2px);
                box-shadow: 0 6px 15px rgba(76, 175, 80, 0.4);
            }
            
            .success-button:active,
            .success-button:focus {
                outline: none;
                transform: translateY(0);
                box-shadow: 0 3px 5px rgba(76, 175, 80, 0.2);
            }
            
            #confetti-container {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                overflow: hidden;
                pointer-events: none;
            }
            
            .confetti {
                position: absolute;
                z-index: 1;
                width: 10px;
                height: 10px;
                background-color: #FFC107;
                opacity: 0.7;
            }
        `;
        
        document.body.appendChild(alertContainer);
        document.head.appendChild(styles);
        
        const closeButton = alertContainer.querySelector('.success-button');
        closeButton.addEventListener('click', closeOrderSuccessAlert);
    }
    
    function animateConfetti() {
        const container = document.getElementById('confetti-container');
        if (!container) return;
        
        container.innerHTML = '';
        
        const colors = ['#f44336', '#e91e63', '#9c27b0', '#673ab7', '#3f51b5', '#2196f3', '#03a9f4', '#00bcd4', '#009688', '#4CAF50', '#8BC34A', '#CDDC39', '#FFEB3B', '#FFC107', '#FF9800', '#FF5722'];
        
        for (let i = 0; i < 150; i++) {
            const confetti = document.createElement('div');
            confetti.className = 'confetti';
            
            const size = Math.random() * 10 + 5;
            const shape = Math.random() > 0.5 ? '50%' : '0%';
            const color = colors[Math.floor(Math.random() * colors.length)];
            
            confetti.style.width = `${size}px`;
            confetti.style.height = `${size}px`;
            confetti.style.backgroundColor = color;
            confetti.style.borderRadius = shape;
            confetti.style.left = `${Math.random() * 100}%`;
            confetti.style.top = `${Math.random() * 100 - 100}%`;
            
            const duration = Math.random() * 3 + 2;
            const delay = Math.random() * 2;
            
            confetti.style.animation = `fall ${duration}s ease-in ${delay}s forwards`;
            
            container.appendChild(confetti);
        }
        
        if (!document.getElementById('confetti-keyframes')) {
            const keyframes = document.createElement('style');
            keyframes.id = 'confetti-keyframes';
            keyframes.textContent = `
                @keyframes fall {
                    0% {
                        transform: translateY(0) rotate(0deg);
                        opacity: 0;
                    }
                    50% {
                        opacity: 1;
                    }
                    100% {
                        transform: translateY(1000%) rotate(960deg);
                        opacity: 0;
                    }
                }
            `;
            document.head.appendChild(keyframes);
        }
    }
    
}


document.addEventListener('DOMContentLoaded', replaceOrderSuccessAlert);


