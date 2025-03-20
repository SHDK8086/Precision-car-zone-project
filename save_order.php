<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

@include 'connection.php';

header('Content-Type: application/json');

if (!isset($conn) || $conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

try {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    if (!$data || !isset($data['orders']) || empty($data['orders'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid data received']);
        exit;
    }

    $email = isset($data['email']) ? $data['email'] : '';
    
    if (empty($email)) {
        echo json_encode(['success' => false, 'message' => 'User email is required']);
        exit;
    }
    
    $conn->begin_transaction();
    
    $stmt = $conn->prepare("SELECT id FROM usertable WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $userId = $user['id'];
    } else {
        echo json_encode(['success' => false, 'message' => 'User with this email does not exist']);
        $conn->rollback();
        exit;
    }
    $stmt->close();
    
    $insertCount = 0;
    $orderDate = date('Y-m-d H:i:s');
    
    foreach ($data['orders'] as $item) {
        $orderDate = isset($item['order_date']) ? date('Y-m-d H:i:s', strtotime($item['order_date'])) : $orderDate;
        
        $sql = "INSERT INTO orders_all (product_name, quantity, price, item_total, order_total, order_date, customer_id)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $conn->error);
        }
        
        $productName = $item['product_name'];
        $quantity = (int)$item['quantity'];
        $price = (float)$item['price'];
        $itemTotal = (float)$item['item_total'];
        $orderTotal = (float)$item['order_total'];
        
        $stmt->bind_param("sidddsi",
            $productName,
            $quantity,
            $price,
            $itemTotal,
            $orderTotal,
            $orderDate,
            $userId  
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        $insertCount++;
        $stmt->close();
    }
    
    $conn->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Order placed successfully',
        'items_saved' => $insertCount,
        'user_id' => $userId  
    ]);
    
} catch (Exception $e) {
    if (isset($conn) && $conn->ping()) {
        $conn->rollback();
    }
    
    error_log("Order error: " . $e->getMessage());
    
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
} finally {
    if (isset($conn) && $conn->ping()) {
        $conn->close();
    }
}
?>