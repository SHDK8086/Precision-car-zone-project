<?php
@include 'connection.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data['items'])) {
    echo json_encode(["success" => false, "message" => "Invalid request"]);
    exit;
}

$conn->begin_transaction(); 

try {
    foreach ($data['items'] as $item) {
        $serialNumber = $item['serialNumber'];
        $quantity = (int)$item['quantity'];

        $query = "SELECT stock FROM store WHERE serialNumber = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $serialNumber);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $currentStock = $row['stock'];

            if ($quantity > $currentStock) {
                throw new Exception("Insufficient stock for the product: $serialNumber");
            }

            $newStock = $currentStock - $quantity;
            $updateQuery = "UPDATE store SET stock = ? WHERE serialNumber = ?";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bind_param("is", $newStock, $serialNumber);
            $updateStmt->execute();
        } else {
            throw new Exception("Product not found: $serialNumber");
        }
    }

    $conn->commit(); 
    echo json_encode(["success" => true, "message" => "Order placed successfully"]);

} catch (Exception $e) {
    $conn->rollback(); 
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>
