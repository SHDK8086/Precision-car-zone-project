<?php
@include 'connection.php';

$sql = "SELECT DISTINCT category FROM store";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Online Store</title>
        <link rel="stylesheet" href="Store.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    </head>
    
<body>
    
    <div class="Starting-Page">

    <div class="mobile-menu-btn">
            <i class="fa fa-bars"></i>
        </div>

        <div class="navbar">
            <img src="images/IMG_0803.PNG" class="Logo">

            <div class="top-right">
            <div class="icon-container" onclick="openCartPopup()">
            <img src="images/Shopping Cart.png" class="cart">
            <span id="cart-badge" class="cart-badge">0</span>
            <p class="text">View Cart</p>
    </div>            
        </div>

        <div class="home-content">

            <h1 class="beat-the">Every upgrade</h1> 
            <h1 class="black-friday">TELLS A <span class="friday">STORY</span></h1> 

        </div>

        <div id="search-bar"> 
    <input id="search-input" type="text" placeholder="| Search products with serial no...">
    <button id="search-button" onclick="searchBySerialNumber()" aria-label="Search">
        <i class="fa fa-search"></i>
    </button>
</div>

        
        <div class="circle-container">
            <div class="circle-item">
                <div class="circle">
                    <img src="images/Automation.png" alt="Profile Image" class="circle-img">
                </div>
                <div class="text">Engine Parts</div>
            </div>
            <div class="circle-item">
                <div class="circle">
                    <img src="images/Harvester.png" alt="Profile Image" class="circle-img">
                </div>
                <div class="text">Suspension & Steering</div>
            </div>
            <div class="circle-item">
                <div class="circle">
                    <img src="images/Switches.png" alt="Profile Image" class="circle-img">
                </div>
                <div class="text">Braking System</div>
            </div>
            <div class="circle-item">
                <div class="circle">
                    <img src="images/Tools.png" alt="Profile Image" class="circle-img">
                </div>
                <div class="text">Electrical Components</div>
            </div>
            <div class="circle-item">
                <div class="circle">
                    <img src="images/Motorcycle.png" alt="Profile Image" class="circle-img">
                </div>
                <div class="text">Body & Exterior Parts</div>
            </div>
            <div class="circle-item">
                <div class="circle">
                    <img src="images/Wheel.png" alt="Profile Image" class="circle-img">
                </div>
                <div class="text">Transmission & Drivetrain</div>
            </div>
            <div class="circle-item">
                <div class="circle">
                    <img src="images/Engine Oil.png" alt="Profile Image" class="circle-img">
                </div>
                <div class="text">Cooling & Heating</div>
            </div>
            
        </div>

        <div class="slider-container">
                <div class="slider">
                <div class="slide-content">
                    <h1 class="discount">20% OFF</h1>
                    <p class="description">Upgrade Your Ride with Premium Parts at Discounted Prices. Limited-time offer!</p>
                </div>

                </div>
            </div>

    </div>

    <div class="categories-set">
    
    <?php


$sql = "SELECT DISTINCT category FROM store";
$result = $conn->query($sql);
?>

<div class="categories-set">
    <?php
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $category = $row['category'];
            echo "<div class='horizontal-scroll-section'>";
            echo "<h2>" . ucfirst($category) . "</h2>";
            echo "<div class='scroll-container'>";
            echo "<button class='scroll-arrow left-arrow' onclick='scrollLeftBtn(this)'>❮</button>";
            echo "<div class='scroll-wrapper'>";

            $productSql = "SELECT productName, price, productImage, description, ratings FROM store WHERE category='$category'";
            $productResult = $conn->query($productSql);

            if ($productResult && $productResult->num_rows > 0) {
                while ($product = $productResult->fetch_assoc()) {
                    echo "<div class='category-card'>";
                    echo "<img src='" . $product['productImage'] . "' alt='" . $product['productName'] . "'>";
                    echo "<h3>" . $product['productName'] . "</h3>";
                    echo "<p>Price: Rs." . $product['price'] . "</p>";
                    echo "<button onclick='openProductPopup(\"" . $product['productName'] . "\")'>See More</button>";
                    echo "</div>";
                }
            } else {
                echo "<p>No products found in this category.</p>";
            }

            echo "</div>";
            echo "<button class='scroll-arrow right-arrow' onclick='scrollRightBtn(this)'>❯</button>";
            echo "</div>"; 
            echo "</div>"; 
        }
    } else {
        echo "<p>No categories found.</p>";
    }
    ?>
</div>

<div id="cart-container" class="cart-popup" style="display: none;">
    <button class="close" onclick="closeCartPopup()">&times;</button>
    <h3>Shopping Cart</h3>
    <div id="cart-items"></div>
    <div class="cart-summary">
        <p id="cart-total">Total: Rs. 0.00</p>
        
        <div id="customer-email-form">
            <label for="customer-email">Email Address:</label>
            <input type="email" id="customer-email" placeholder="Enter your email" required>
        </div>
        
        <button id="place-order-btn" class="place-order-btn" onclick="placeOrder()">Place Order</button>
    </div>
</div>

<div id="product-popup" class="product-popup" style="display: none;">
    <div class="product-popup-content">
        <span class="close" onclick="closeProductPopup()">&times;</span>
        <img id="popup-image" src="" alt="Product Image">
        
        <div class="vertical-line"></div>
        <h3 id="popup-name"></h3>
        <p id="popup-serial"></p>
        <p id="popup-description"></p>
        <p id="popup-price">Rs.</p>
        <span id="popup-ratings" class="stars"></span>
        <button class="btn" onclick="addProductFromPopup()">Add to Cart</button>
    </div>
</div>


<div id="alert-templates" style="display: none;">
    
    <template id="order-confirmation-template">
        <div class="custom-alert alert-info">
            <div class="alert-header">
                <h3>Confirm Your Order</h3>
                <span class="close-btn">&times;</span>
            </div>
            <div class="alert-body">
                <div class="alert-icon">
                    <i class="fas fa-shopping-bag fa-3x"></i>
                </div>
                <div class="order-details">
                    <p>Your order total is:</p>
                    <p class="order-total">Rs. <span class="total-amount">0.00</span></p>
                </div>
                <p>Are you ready to complete your purchase?</p>
            </div>
            <div class="alert-buttons">
                <button class="alert-btn alert-btn-cancel">Cancel</button>
                <button class="alert-btn alert-btn-confirm">Place Order</button>
            </div>
        </div>
    </template>
    
    <template id="order-success-template">
        <div class="custom-alert alert-success">
            <div class="alert-header">
                <h3>Order Placed Successfully!</h3>
                <span class="close-btn">&times;</span>
            </div>
            <div class="alert-body">
                <div class="alert-icon">
                    <i class="fas fa-check-circle fa-3x"></i>
                </div>
                <p>Thank you for your purchase!</p>
                <p>Your order has been placed successfully and is being processed.</p>
                <p>An email confirmation has been sent to <span class="customer-email">your email</span>.</p>
            </div>
            <div class="alert-buttons">
                <button class="alert-btn alert-btn-confirm">Continue Shopping</button>
            </div>
        </div>
    </template>
    
    <template id="cart-empty-template">
        <div class="custom-alert alert-warning">
            <div class="alert-header">
                <h3>Empty Cart</h3>
                <span class="close-btn">&times;</span>
            </div>
            <div class="alert-body">
                <div class="alert-icon">
                    <i class="fas fa-shopping-cart fa-3x"></i>
                </div>
                <p>Your shopping cart is empty!</p>
                <p>Please add some products before proceeding to checkout.</p>
            </div>
            <div class="alert-buttons">
                <button class="alert-btn alert-btn-confirm">Continue Shopping</button>
            </div>
        </div>
    </template>
    
    <template id="email-required-template">
        <div class="custom-alert alert-warning">
            <div class="alert-header">
                <h3>Email Required</h3>
                <span class="close-btn">&times;</span>
            </div>
            <div class="alert-body">
                <div class="alert-icon">
                    <i class="fas fa-envelope fa-3x"></i>
                </div>
                <p>Please enter your email address to continue.</p>
                <p>We need your email to send order confirmation and shipping updates.</p>
            </div>
            <div class="alert-buttons">
                <button class="alert-btn alert-btn-confirm">OK</button>
            </div>
        </div>
    </template>
    
    <template id="order-error-template">
        <div class="custom-alert alert-error">
            <div class="alert-header">
                <h3>Order Error</h3>
                <span class="close-btn">&times;</span>
            </div>
            <div class="alert-body">
                <div class="alert-icon">
                    <i class="fas fa-exclamation-circle fa-3x"></i>
                </div>
                <p>We encountered an error while processing your order.</p>
                <p class="error-message">Error details will appear here.</p>
                <p>Please try again or contact customer support for assistance.</p>
            </div>
            <div class="alert-buttons">
                <button class="alert-btn alert-btn-confirm">Try Again</button>
            </div>
        </div>
    </template>
    
    <template id="processing-order-template">
        <div class="custom-alert alert-info">
            <div class="alert-header">
                <h3>Processing Your Order</h3>
            </div>
            <div class="alert-body">
                <div class="alert-icon">
                    <i class="fas fa-spinner fa-pulse fa-3x"></i>
                </div>
                <p>Please wait while we process your order...</p>
            </div>
        </div>
    </template>
</div>

<div id="thank-you-alert" class="thank-you-alert">
    <div class="thank-you-content">
        <div class="success-icon">
            <svg viewBox="0 0 52 52" class="checkmark">
                <circle cx="26" cy="26" r="25" fill="none" class="checkmark-circle"/>
                <path fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8" class="checkmark-check"/>
            </svg>
        </div>
        <h2>Thank You!</h2>
        <p>Your order has been placed successfully.</p>
        <div class="confetti"></div>
        <button class="thank-you-btn" onclick="closeThankYouAlert()">Continue Shopping</button>
    </div>
</div>

<style>
/* Thank You Alert Styles */
.thank-you-alert {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.3s ease, visibility 0.3s;
}

.thank-you-alert.show {
    opacity: 1;
    visibility: visible;
}

.thank-you-content {
    background-color: white;
    border-radius: 12px;
    padding: 30px 40px;
    text-align: center;
    max-width: 450px;
    width: 90%;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
    position: relative;
    animation: popup 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
}

@keyframes popup {
    0% { transform: scale(0.6); opacity: 0; }
    100% { transform: scale(1); opacity: 1; }
}

.thank-you-content h2 {
    color: #2c3e50;
    font-size: 28px;
    margin: 15px 0 10px;
}

.thank-you-content p {
    color: #7f8c8d;
    font-size: 18px;
    margin-bottom: 25px;
}

.success-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto 20px;
    position: relative;
}

.checkmark {
    width: 80px;
    height: 80px;
}

.checkmark-circle {
    stroke-dasharray: 166;
    stroke-dashoffset: 166;
    stroke-width: 2;
    stroke-miterlimit: 10;
    stroke: #4CAF50;
    fill: none;
    animation: stroke 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;
}

.checkmark-check {
    transform-origin: 50% 50%;
    stroke-dasharray: 48;
    stroke-dashoffset: 48;
    stroke-width: 3;
    stroke: #4CAF50;
    animation: stroke 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.8s forwards;
}

@keyframes stroke {
    100% { stroke-dashoffset: 0; }
}

.thank-you-btn {
    background-color: #4CAF50;
    color: white;
    border: none;
    padding: 12px 30px;
    border-radius: 6px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    margin-top: 10px;
    box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
}

.thank-you-btn:hover {
    background-color: #45a049;
    transform: translateY(-2px);
    box-shadow: 0 5px 10px rgba(0, 0, 0, 0.15);
}

.thank-you-btn:active {
    transform: translateY(0);
}

/* Confetti animation elements */
.confetti {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
    z-index: -1;
}

.confetti-piece {
    position: absolute;
    width: 10px;
    height: 10px;
    background: #ffd300;
    top: 0;
    opacity: 0;
}

.confetti-piece:nth-child(1) {
    left: 7%;
    transform: rotate(-40deg);
    animation: makeItRain 1000ms infinite ease-out;
    animation-delay: 182ms;
    animation-duration: 1116ms;
    background: #0cbde8;
}

.confetti-piece:nth-child(2) {
    left: 14%;
    transform: rotate(4deg);
    animation: makeItRain 1000ms infinite ease-out;
    animation-delay: 161ms;
    animation-duration: 1076ms;
    background: #fb6962;
}

.confetti-piece:nth-child(3) {
    left: 21%;
    transform: rotate(-51deg);
    animation: makeItRain 1000ms infinite ease-out;
    animation-delay: 481ms;
    animation-duration: 1103ms;
    background: #f2d74e;
}

.confetti-piece:nth-child(4) {
    left: 28%;
    transform: rotate(61deg);
    animation: makeItRain 1000ms infinite ease-out;
    animation-delay: 334ms;
    animation-duration: 708ms;
    background: #01d0b1;
}

.confetti-piece:nth-child(5) {
    left: 35%;
    transform: rotate(-52deg);
    animation: makeItRain 1000ms infinite ease-out;
    animation-delay: 302ms;
    animation-duration: 776ms;
    background: #bb8dee;
}

.confetti-piece:nth-child(6) {
    left: 42%;
    transform: rotate(38deg);
    animation: makeItRain 1000ms infinite ease-out;
    animation-delay: 180ms;
    animation-duration: 1168ms;
    background: #e78d3a;
}

.confetti-piece:nth-child(7) {
    left: 49%;
    transform: rotate(11deg);
    animation: makeItRain 1000ms infinite ease-out;
    animation-delay: 395ms;
    animation-duration: 1200ms;
    background: #5fc6e9;
}

.confetti-piece:nth-child(8) {
    left: 56%;
    transform: rotate(49deg);
    animation: makeItRain 1000ms infinite ease-out;
    animation-delay: 14ms;
    animation-duration: 887ms;
    background: #f769a3;
}

.confetti-piece:nth-child(9) {
    left: 63%;
    transform: rotate(-72deg);
    animation: makeItRain 1000ms infinite ease-out;
    animation-delay: 149ms;
    animation-duration: 805ms;
    background: #7ce490;
}

.confetti-piece:nth-child(10) {
    left: 70%;
    transform: rotate(10deg);
    animation: makeItRain 1000ms infinite ease-out;
    animation-delay: 351ms;
    animation-duration: 1059ms;
    background: #fcb05a;
}

.confetti-piece:nth-child(11) {
    left: 77%;
    transform: rotate(4deg);
    animation: makeItRain 1000ms infinite ease-out;
    animation-delay: 307ms;
    animation-duration: 1132ms;
    background: #63b4fc;
}

.confetti-piece:nth-child(12) {
    left: 84%;
    transform: rotate(42deg);
    animation: makeItRain 1000ms infinite ease-out;
    animation-delay: 464ms;
    animation-duration: 776ms;
    background: #eb68fc;
}

.confetti-piece:nth-child(13) {
    left: 91%;
    transform: rotate(-72deg);
    animation: makeItRain 1000ms infinite ease-out;
    animation-delay: 429ms;
    animation-duration: 818ms;
    background: #8dfc63;
}

@keyframes makeItRain {
    from {
        opacity: 0;
        transform: translateY(0%) scale(0.3);
    }
    50% {
        opacity: 1;
        transform: translateY(500%) scale(0.8);
    }
    to {
        opacity: 0;
        transform: translateY(1000%) scale(0.3);
    }
}
</style>

<script>
function createConfetti() {
    const confettiContainer = document.querySelector('.confetti');
    if (confettiContainer) {
        confettiContainer.innerHTML = '';
        for (let i = 0; i < 13; i++) {
            const confetti = document.createElement('div');
            confetti.className = 'confetti-piece';
            confettiContainer.appendChild(confetti);
        }
    }
}

function showThankYouAlert() {
    const alert = document.getElementById('thank-you-alert');
    if (alert) {
        createConfetti();
        alert.classList.add('show');
        
        setTimeout(() => {
            closeThankYouAlert();
        }, 5000);
    }
}

function closeThankYouAlert() {
    const alert = document.getElementById('thank-you-alert');
    if (alert) {
        alert.classList.remove('show');
    }
}

const originalAlert = window.alert;
window.alert = function(message) {
    if (message && message.includes("Thank you for your order") && message.includes("successfully")) {
        if (!document.getElementById('thank-you-alert')) {
            const alertDiv = document.createElement('div');
            alertDiv.innerHTML = document.querySelector('style') ? 
                document.querySelector('#thank-you-alert').outerHTML + document.querySelector('style').outerHTML : 
                document.querySelector('#thank-you-alert').outerHTML;
            document.body.appendChild(alertDiv.firstChild);
        }
        
        showThankYouAlert();
    } else {
        originalAlert(message);
    }
};
</script>

    <footer>
    </footer>

    <script src="store.js"></script>
</body>
</html>
