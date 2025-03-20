<?php
@include 'connection.php';
header('Content-Type: application/json');

error_reporting(E_ALL);
ini_set('display_errors', 1);

$rawInput = file_get_contents("php://input");
error_log("Raw input: " . $rawInput);

$data = json_decode($rawInput, true);

if (!$data || !isset($data['items']) || !is_array($data['items'])) {
    echo json_encode([
        "success" => false, 
        "message" => "Invalid request format",
        "received" => $rawInput
    ]);
    exit;
}

$conn->begin_transaction();

try {
    error_log("Processing " . count($data['items']) . " items");
    
    foreach ($data['items'] as $item) {
        if (!isset($item['serialNumber']) || !isset($item['quantity'])) {
            error_log("Missing required fields: " . json_encode($item));
            throw new Exception("Item missing required fields (serialNumber or quantity)");
        }
        
        $serialNumber = trim($item['serialNumber']);
        $quantity = (int)$item['quantity'];
        
        error_log("Processing item: Serial=$serialNumber, Qty=$quantity");
        
        if (empty($serialNumber)) {
            throw new Exception("Empty serial number provided");
        }
        
        $query = "SELECT stock FROM store WHERE serialNumber = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $serialNumber);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("Product not found: $serialNumber");
        }
        
        $row = $result->fetch_assoc();
        $currentStock = (int)$row['stock'];
        
        error_log("Current stock for $serialNumber: $currentStock");
        
        if ($quantity > $currentStock) {
            throw new Exception("Insufficient stock for product: $serialNumber (requested: $quantity, available: $currentStock)");
        }
        
        $newStock = $currentStock - $quantity;
        $updateQuery = "UPDATE store SET stock = ? WHERE serialNumber = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param("is", $newStock, $serialNumber);
        
        if (!$updateStmt->execute()) {
            throw new Exception("Failed to update stock: " . $updateStmt->error);
        }
        
        error_log("Updated stock for $serialNumber to $newStock");
    }
    
    $conn->commit();
    
    echo json_encode([
        "success" => true, 
        "message" => "Order placed successfully",
        "items_processed" => count($data['items'])
    ]);
    
} catch (Exception $e) {
    $conn->rollback();
    
    error_log("Order error: " . $e->getMessage());
    
    echo json_encode([
        "success" => false, 
        "message" => $e->getMessage()
    ]);
}
?>