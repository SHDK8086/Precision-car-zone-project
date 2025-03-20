<?php
include('db/dbconn.php');
session_start();

if(!isset($_SESSION['Id']) && !isset($_SESSION['email'])){
    header("location:login.php");
    exit;
}

$logged_in_user_id = $_SESSION['Id'];

if(isset($_POST['delete_order']) && isset($_POST['order_id'])) {
    $order_id = $_POST['order_id'];
    
    $delete_sql = "DELETE FROM orders_all WHERE id = ? AND customer_id = ? AND status = 'pending'";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("ii", $order_id, $logged_in_user_id);
    $delete_stmt->execute();
    
    if($delete_stmt->affected_rows > 0) {
        $delete_message = "Order #" . $order_id . " has been successfully deleted.";
    } else {
        $delete_error = "Unable to delete the order. The order may not exist or it's already confirmed.";
    }
    
    $delete_stmt->close();
}

$pending_orders_sql = "SELECT id, product_name, quantity, price, item_total, 
                       order_total, order_date, status, created_at
                       FROM orders_all 
                       WHERE customer_id = ? AND status = 'pending'
                       ORDER BY order_date DESC";

$pending_stmt = $conn->prepare($pending_orders_sql);
$pending_stmt->bind_param("i", $logged_in_user_id);
$pending_stmt->execute();
$pending_result = $pending_stmt->get_result();

$confirmed_orders_sql = "SELECT id, product_name, quantity, price, item_total, 
                         order_total, order_date, status, created_at
                         FROM orders_all 
                         WHERE customer_id = ? AND status = 'confirmed'
                         ORDER BY order_date DESC";

$confirmed_stmt = $conn->prepare($confirmed_orders_sql);
$confirmed_stmt->bind_param("i", $logged_in_user_id);
$confirmed_stmt->execute();
$confirmed_result = $confirmed_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - REPAIR</title>
    <link rel="icon" href="Images/favicon.ico" type="Images/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #1e3a8a;
            --primary-light: #3b82f6;
            --primary-dark: #0f172a;
            --accent-color: #f43f5e;
            --accent-hover: #e11d48;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --light-bg: #f8fafc;
            --card-bg: #ffffff;
            --text-dark: #1e293b;
            --text-medium: #475569;
            --text-light: #94a3b8;
            --border-color: #e2e8f0;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.1);
            --shadow-md: 0 4px 6px rgba(0,0,0,0.1);
            --shadow-lg: 0 10px 15px rgba(0,0,0,0.1);
            --radius-sm: 0.375rem;
            --radius-md: 0.5rem;
            --radius-lg: 0.75rem;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--light-bg);
            color: var(--text-dark);
            line-height: 1.6;
        }

        .page-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 15px;
        }

        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 2rem;
            border-bottom: 2px solid var(--border-color);
            padding-bottom: 1rem;
        }

        .page-title {
            color: var(--primary-color);
            font-weight: 700;
            font-size: 1.75rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .page-title i {
            color: var(--accent-color);
        }

        .page-navigation {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            font-weight: 500;
            color: white;
            background-color: var(--primary-color);
            border-radius: var(--radius-md);
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: var(--shadow-sm);
        }

        .back-btn:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .order-section {
            background-color: var(--card-bg);
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-md);
            animation: fadeIn 0.5s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--primary-color);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .section-title i {
            color: var(--accent-color);
        }

        .section-badge {
            background-color: var(--primary-light);
            color: white;
            border-radius: 50px;
            padding: 0.25rem 0.75rem;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .section-description {
            color: var(--text-medium);
            margin-bottom: 1.5rem;
            font-size: 0.95rem;
        }

        .orders-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 1rem;
        }

        .orders-table th {
            background-color: var(--primary-color);
            color: white;
            padding: 0.75rem 1rem;
            text-align: left;
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .orders-table th:first-child {
            border-top-left-radius: var(--radius-md);
        }

        .orders-table th:last-child {
            border-top-right-radius: var(--radius-md);
        }

        .orders-table td {
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
            font-size: 0.95rem;
            vertical-align: middle;
        }

        .orders-table tr:last-child td {
            border-bottom: none;
        }

        .orders-table tr:nth-child(even) {
            background-color: rgba(241, 245, 249, 0.7);
        }

        .orders-table tr:hover {
            background-color: rgba(241, 245, 249, 1);
        }

        .product-name {
            font-weight: 500;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            max-width: 200px;
        }

        .order-price, .order-total {
            font-weight: 600;
            color: var(--primary-dark);
        }

        .order-date {
            color: var(--text-medium);
            white-space: nowrap;
        }

        .status-badge {
            padding: 0.35rem 0.75rem;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }

        .status-pending {
            background-color: var(--warning-color);
            color: white;
        }

        .status-confirmed {
            background-color: var(--success-color);
            color: white;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .btn-view {
            padding: 0.35rem 0.75rem;
            border-radius: var(--radius-sm);
            font-size: 0.85rem;
            font-weight: 500;
            text-decoration: none;
            background-color: var(--primary-light);
            color: white;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }

        .btn-view:hover {
            background-color: var(--primary-color);
            transform: translateY(-2px);
        }

        .btn-delete {
            padding: 0.35rem 0.75rem;
            border-radius: var(--radius-sm);
            font-size: 0.85rem;
            font-weight: 500;
            text-decoration: none;
            background-color: var(--danger-color);
            color: white;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }

        .btn-delete:hover {
            background-color: #dc2626;
            transform: translateY(-2px);
        }

        .no-orders-message {
            padding: 2rem;
            text-align: center;
            background-color: rgba(241, 245, 249, 0.7);
            border-radius: var(--radius-md);
            font-size: 1rem;
            color: var(--text-medium);
            margin-bottom: 1rem;
            border: 1px dashed var(--border-color);
        }

        .no-orders-message i {
            font-size: 2.5rem;
            color: var(--text-light);
            margin-bottom: 1rem;
            display: block;
        }

        .alert {
            animation: slideDown 0.5s ease-out;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Order popup styling */
        .order-popup {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(15, 23, 42, 0.7);
            z-index: 1000;
            overflow: auto;
            animation: fadeIn 0.3s ease-out;
        }
        
        .order-popup-content {
            background-color: white;
            margin: 5% auto;
            padding: 2rem;
            border-radius: var(--radius-lg);
            width: 90%;
            max-width: 700px;
            position: relative;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            animation: slideUp 0.4s ease-out;
        }
        
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(50px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .close {
            position: absolute;
            top: 1.25rem;
            right: 1.5rem;
            font-size: 1.75rem;
            font-weight: bold;
            cursor: pointer;
            color: var(--text-medium);
            transition: color 0.2s ease;
        }
        
        .close:hover {
            color: var(--accent-color);
        }
        
        .order-popup h2 {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid var(--border-color);
            font-size: 1.5rem;
        }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .order-header h4 {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 0.25rem;
        }
        
        .order-header p {
            margin: 0;
            color: var(--text-medium);
            font-size: 0.9rem;
        }
        
        .order-status {
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-weight: 500;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .order-details {
            margin-top: 1.5rem;
        }
        
        .order-details h5 {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 1rem;
            font-size: 1.1rem;
        }
        
        .order-details p {
            margin-bottom: 0.75rem;
            color: var(--text-medium);
        }
        
        .order-details p strong {
            color: var(--text-dark);
            font-weight: 500;
            display: inline-block;
            width: 120px;
        }
        
        .order-details .row {
            margin-bottom: 1rem;
        }
        
        .delete-warning-popup {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(15, 23, 42, 0.8);
            z-index: 2000;
            overflow: auto;
            animation: fadeIn 0.3s ease-out;
        }
        
        .delete-warning-content {
            background-color: white;
            margin: 10% auto;
            padding: 2rem;
            border-radius: var(--radius-lg);
            width: 90%;
            max-width: 500px;
            position: relative;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            text-align: center;
            animation: shake 0.5s ease-out;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-10px); }
            40%, 80% { transform: translateX(10px); }
        }
        
        .delete-warning-content h4 {
            color: var(--danger-color);
            margin-bottom: 1rem;
            font-weight: 600;
        }
        
        .delete-warning-content p {
            color: var(--text-medium);
            margin-bottom: 1.5rem;
        }
        
        .delete-actions {
            display: flex;
            justify-content: center;
            gap: 1rem;
        }
        
        .delete-actions button {
            padding: 0.75rem 1.5rem;
            border-radius: var(--radius-md);
            font-weight: 500;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .delete-actions button[type="submit"] {
            background-color: var(--danger-color);
            color: white;
        }
        
        .delete-actions button[type="submit"]:hover {
            background-color: #dc2626;
            transform: translateY(-2px);
        }
        
        .delete-actions button[type="button"] {
            background-color: var(--light-bg);
            color: var(--text-dark);
        }
        
        .delete-actions button[type="button"]:hover {
            background-color: var(--border-color);
        }

        /* Responsive styles */
        @media (max-width: 992px) {
            .orders-table th, .orders-table td {
                padding: 0.75rem 0.5rem;
                font-size: 0.9rem;
            }
            
            .product-name {
                max-width: 150px;
            }
            
            .action-buttons {
                flex-direction: column;
                gap: 0.25rem;
            }
            
            .btn-view, .btn-delete {
                width: 100%;
                justify-content: center;
                padding: 0.25rem 0.5rem;
            }
        }
        
        @media (max-width: 768px) {
            .page-container {
                padding: 20px 10px;
            }
            
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
            
            .order-section {
                padding: 1.25rem;
            }
            
            .order-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.75rem;
            }
            
            .orders-table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }
            
            .orders-table th:nth-child(3), 
            .orders-table td:nth-child(3) {
                display: none;
            }
            
            .order-popup-content {
                width: 95%;
                padding: 1.5rem;
                margin: 10% auto;
            }
            
            .order-details .row {
                flex-direction: column;
            }
            
            .order-details .col-md-6:first-child {
                margin-bottom: 1.5rem;
            }
        }
        
        @media (max-width: 576px) {
            .page-title {
                font-size: 1.5rem;
            }
            
            .section-title {
                font-size: 1.1rem;
            }
            
            .orders-table th:nth-child(4), 
            .orders-table td:nth-child(4) {
                display: none;
            }
            
            .delete-warning-content {
                width: 95%;
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="page-container">
        <div class="page-header">
            <h1 class="page-title"><i class="fas fa-shopping-cart"></i> My Orders</h1>
            <div class="page-navigation">
                <a href="profile.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back to Profile</a>
            </div>
        </div>
        
        <?php if(isset($delete_message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong><i class="fas fa-check-circle"></i> Success!</strong> <?php echo htmlspecialchars($delete_message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>
        
        <?php if(isset($delete_error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong><i class="fas fa-exclamation-circle"></i> Error!</strong> <?php echo htmlspecialchars($delete_error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>
    
        <div class="order-section">
            <div class="section-header">
                <h2 class="section-title"><i class="fas fa-clock"></i> Current Orders</h2>
                <?php if ($pending_result && $pending_result->num_rows > 0): ?>
                <span class="section-badge"><?php echo $pending_result->num_rows; ?> Pending</span>
                <?php endif; ?>
            </div>
            <p class="section-description">Below are your pending orders that are currently being processed.</p>
            
            <?php if ($pending_result && $pending_result->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Total</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $pending_result->fetch_assoc()): ?>
                        <tr>
                            <td>#<?php echo htmlspecialchars($row['id']); ?></td>
                            <td>
                                <div class="product-name"><?php echo htmlspecialchars($row['product_name']); ?></div>
                            </td>
                            <td><?php echo htmlspecialchars($row['quantity']); ?></td>
                            <td class="order-price">$<?php echo htmlspecialchars(number_format($row['price'], 2)); ?></td>
                            <td class="order-total">$<?php echo htmlspecialchars(number_format($row['order_total'], 2)); ?></td>
                            <td class="order-date"><?php echo date('M d, Y', strtotime($row['order_date'])); ?></td>
                            <td><span class="status-badge status-pending"><i class="fas fa-spinner fa-spin"></i> <?php echo htmlspecialchars(ucwords($row['status'])); ?></span></td>
                            <td>
                                <div class="action-buttons">
                                    <button 
                                        class="btn-view view-details" 
                                        data-id="<?php echo $row['id']; ?>"
                                        data-product="<?php echo htmlspecialchars($row['product_name']); ?>"
                                        data-quantity="<?php echo htmlspecialchars($row['quantity']); ?>"
                                        data-price="<?php echo htmlspecialchars($row['price']); ?>"
                                        data-item-total="<?php echo htmlspecialchars($row['item_total']); ?>"
                                        data-order-total="<?php echo htmlspecialchars($row['order_total']); ?>"
                                        data-date="<?php echo date('M d, Y', strtotime($row['order_date'])); ?>"
                                        data-status="<?php echo htmlspecialchars($row['status']); ?>"
                                        data-created="<?php echo date('M d, Y h:i A', strtotime($row['created_at'])); ?>"
                                    ><i class="fas fa-eye"></i> View</button>
                                    <button 
                                        class="btn-delete delete-order" 
                                        data-id="<?php echo $row['id']; ?>"
                                    ><i class="fas fa-trash"></i> Delete</button>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="no-orders-message">
                <i class="fas fa-shopping-cart"></i>
                <p>You don't have any pending orders at the moment.</p>
            </div>
            <?php endif; ?>
        </div>

        <div class="order-section">
            <div class="section-header">
                <h2 class="section-title"><i class="fas fa-history"></i> Order History</h2>
                <?php if ($confirmed_result && $confirmed_result->num_rows > 0): ?>
                <span class="section-badge"><?php echo $confirmed_result->num_rows; ?> Confirmed</span>
                <?php endif; ?>
            </div>
            <p class="section-description">Below are the details of all your confirmed orders.</p>
            
            <?php if ($confirmed_result && $confirmed_result->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Total</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $confirmed_result->fetch_assoc()): ?>
                        <tr>
                            <td>#<?php echo htmlspecialchars($row['id']); ?></td>
                            <td>
                                <div class="product-name"><?php echo htmlspecialchars($row['product_name']); ?></div>
                            </td>
                            <td><?php echo htmlspecialchars($row['quantity']); ?></td>
                            <td class="order-price">$<?php echo htmlspecialchars(number_format($row['price'], 2)); ?></td>
                            <td class="order-total">$<?php echo htmlspecialchars(number_format($row['order_total'], 2)); ?></td>
                            <td class="order-date"><?php echo date('M d, Y', strtotime($row['order_date'])); ?></td>
                            <td><span class="status-badge status-confirmed"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars(ucwords($row['status'])); ?></span></td>
                            <td>
                                <button 
                                    class="btn-view view-details" 
                                    data-id="<?php echo $row['id']; ?>"
                                    data-product="<?php echo htmlspecialchars($row['product_name']); ?>"
                                    data-quantity="<?php echo htmlspecialchars($row['quantity']); ?>"
                                    data-price="<?php echo htmlspecialchars($row['price']); ?>"
                                    data-item-total="<?php echo htmlspecialchars($row['item_total']); ?>"
                                    data-order-total="<?php echo htmlspecialchars($row['order_total']); ?>"
                                    data-date="<?php echo date('M d, Y', strtotime($row['order_date'])); ?>"
                                    data-status="<?php echo htmlspecialchars($row['status']); ?>"
                                    data-created="<?php echo date('M d, Y h:i A', strtotime($row['created_at'])); ?>"
                                ><i class="fas fa-eye"></i> Details</button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="no-orders-message">
                <i class="fas fa-history"></i>
                <p>You don't have any confirmed orders yet.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Order Details Popup -->
    <div id="order-popup" class="order-popup">
        <div class="order-popup-content">
            <span class="close" onclick="closeOrderPopup()">&times;</span>
            <h2><i class="fas fa-shopping-bag"></i> Order Details</h2>
            
            <div class="order-header">
                <div>
                    <h4>Order #<span id="popup-order-id"></span></h4>
                    <p>Placed on: <span id="popup-order-date"></span></p>
                </div>
                <div>
                    <span id="popup-order-status" class="order-status"></span>
                </div>
            </div>
            
            <div class="order-details">
                <div class="row">
                    <div class="col-md-6">
                        <h5><i class="fas fa-box"></i> Product Information</h5>
                        <p><strong>Product:</strong> <span id="popup-product-name"></span></p>
                        <p><strong>Quantity:</strong> <span id="popup-quantity"></span></p>
                        <p><strong>Unit Price:</strong> $<span id="popup-price"></span></p>
                        <p><strong>Item Total:</strong> $<span id="popup-item-total"></span></p>
                    </div>
                    <div class="col-md-6">
                        <h5><i class="fas fa-file-invoice-dollar"></i> Order Summary</h5>
                        <p><strong>Order Total:</strong> $<span id="popup-order-total"></span></p>
                        <p><strong>Order Status:</strong> <span id="popup-status-text"></span></p>
                        <p><strong>Created At:</strong> <span id="popup-created-at"></span></p>
                    </div>
                </div>
                <div class="mt-4" id="popup-delete-container">
                    <button class="btn-delete delete-order-from-popup" data-id="">
                        <i class="fas fa-trash"></i> Delete This Order
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Delete Confirmation Popup -->
    <div id="delete-warning-popup" class="delete-warning-popup">
        <div class="delete-warning-content">
            <h4><i class="fas fa-exclamation-triangle"></i> Delete Confirmation</h4>
            <p>Are you sure you want to delete Order #<span id="delete-order-id"></span>? This action cannot be undone.</p>
            
            <div class="delete-actions">
                <form method="POST" id="delete-form">
                    <input type="hidden" name="order_id" id="delete-form-order-id">
                    <input type="hidden" name="delete_order" value="1">
                    <button type="button" class="btn btn-secondary" onclick="closeDeleteWarning()">Cancel</button>
                    <button type="submit" class="btn btn-danger">Yes, Delete Order</button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap & Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const viewButtons = document.querySelectorAll('.view-details');
            const deleteButtons = document.querySelectorAll('.delete-order');
            
            viewButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const orderId = this.getAttribute('data-id');
                    const product = this.getAttribute('data-product');
                    const quantity = this.getAttribute('data-quantity');
                    const price = this.getAttribute('data-price');
                    const itemTotal = this.getAttribute('data-item-total');
                    const orderTotal = this.getAttribute('data-order-total');
                    const date = this.getAttribute('data-date');
                    const status = this.getAttribute('data-status');
                    const created = this.getAttribute('data-created');

                    document.getElementById('popup-order-id').textContent = orderId;
                    document.getElementById('popup-product-name').textContent = product;
                    document.getElementById('popup-quantity').textContent = quantity;
                    document.getElementById('popup-price').textContent = parseFloat(price).toFixed(2);
                    document.getElementById('popup-item-total').textContent = parseFloat(itemTotal || price * quantity).toFixed(2);
                    document.getElementById('popup-order-total').textContent = parseFloat(orderTotal).toFixed(2);
                    document.getElementById('popup-order-date').textContent = date;
                    document.getElementById('popup-status-text').textContent = status.charAt(0).toUpperCase() + status.slice(1);
                    document.getElementById('popup-created-at').textContent = created;

                    const statusElement = document.getElementById('popup-order-status');
                    statusElement.textContent = status.charAt(0).toUpperCase() + status.slice(1);
                    
                    if (status === 'pending') {
                        statusElement.className = 'order-status status-pending status-badge';
                        statusElement.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ' + status.charAt(0).toUpperCase() + status.slice(1);
                        document.getElementById('popup-delete-container').style.display = 'block';
                        document.querySelector('.delete-order-from-popup').setAttribute('data-id', orderId);
                    } else if (status === 'confirmed') {
                        statusElement.className = 'order-status status-confirmed status-badge';
                        statusElement.innerHTML = '<i class="fas fa-check-circle"></i> ' + status.charAt(0).toUpperCase() + status.slice(1);
                        document.getElementById('popup-delete-container').style.display = 'none';
                    }

                    document.getElementById('order-popup').style.display = 'block';
                    
                    // Add body class to prevent scrolling
                    document.body.style.overflow = 'hidden';
                });
            });
            
            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const orderId = this.getAttribute('data-id');
                    showDeleteWarning(orderId);
                });
            });
            
            // Setup delete from popup button
            const deleteFromPopupButton = document.querySelector('.delete-order-from-popup');
            if (deleteFromPopupButton) {
                deleteFromPopupButton.addEventListener('click', function() {
                    const orderId = this.getAttribute('data-id');
                    closeOrderPopup();
                    showDeleteWarning(orderId);
                });
            }
            
            // Setup alert auto-close
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    const closeButton = alert.querySelector('.btn-close');
                    if (closeButton) {
                        closeButton.click();
                    }
                }, 5000);
            });
        });

        function closeOrderPopup() {
            document.getElementById('order-popup').style.display = 'none';
            document.body.style.overflow = 'auto';
        }
        
        function showDeleteWarning(orderId) {
            document.getElementById('delete-order-id').textContent = orderId;
            document.getElementById('delete-form-order-id').value = orderId;
            document.getElementById('delete-warning-popup').style.display = 'block';
            document.body.style.overflow = 'hidden';
        }
        
        function closeDeleteWarning() {
            document.getElementById('delete-warning-popup').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        // Close modals when clicking outside
        window.addEventListener('click', function(event) {
            const orderPopup = document.getElementById('order-popup');
            const deleteWarningPopup = document.getElementById('delete-warning-popup');
            
            if (event.target === orderPopup) {
                closeOrderPopup();
            }
            
            if (event.target === deleteWarningPopup) {
                closeDeleteWarning();
            }
        });
        
        // Escape key to close modals
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeOrderPopup();
                closeDeleteWarning();
            }
        });
    </script>
</body>
</html>

<?php 
if (isset($pending_stmt)) {
    $pending_stmt->close();
}
if (isset($confirmed_stmt)) {
    $confirmed_stmt->close();
}
$conn->close(); 
?>