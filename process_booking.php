<?php
session_start(); 
include("db/dbconn.php");

$response = [
    'status' => 'error',
    'message' => 'An error occurred while processing your booking'
];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_SESSION['Id'])) {
        $response['message'] = 'User not logged in';
        echo json_encode($response);
        exit;
    }
    $user_id = $_SESSION['Id'];

    $name = filter_input(INPUT_POST, 'Name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'Email', FILTER_SANITIZE_EMAIL);
    $contact = filter_input(INPUT_POST, 'Contact', FILTER_SANITIZE_STRING);
    $address = filter_input(INPUT_POST, 'Address', FILTER_SANITIZE_STRING);
    
    $vehicleNumber = filter_input(INPUT_POST, 'VehicleNumber', FILTER_SANITIZE_STRING);
    $vehicleModel = filter_input(INPUT_POST, 'VehicleModel', FILTER_SANITIZE_STRING);
    $vehicleYear = filter_input(INPUT_POST, 'VehicleYear', FILTER_VALIDATE_INT);
    $serviceType = filter_input(INPUT_POST, 'ServiceType', FILTER_SANITIZE_STRING);
    
    $bookingDate = filter_input(INPUT_POST, 'BookingDate', FILTER_SANITIZE_STRING);
    $bookingTime = filter_input(INPUT_POST, 'BookingTime', FILTER_SANITIZE_STRING);
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Invalid email address';
        echo json_encode($response);
        exit;
    }
    
    if (empty($name) || empty($email) || empty($contact) || empty($address) ||
        empty($vehicleNumber) || empty($vehicleModel) || empty($vehicleYear) || empty($serviceType) ||
        empty($bookingDate) || empty($bookingTime)) {
        $response['message'] = 'All fields are required';
        echo json_encode($response);
        exit;
    }
    
    $conn->begin_transaction();
    
    try {
        $stmt = $conn->prepare("INSERT INTO customers (user_id, name, email, contact, address) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $user_id, $name, $email, $contact, $address);
        $stmt->execute();
        $customerId = $conn->insert_id;
        $stmt->close();
        
        $stmt = $conn->prepare("INSERT INTO vehicles (customer_id, vehicle_number, vehicle_model, vehicle_year) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("issi", $customerId, $vehicleNumber, $vehicleModel, $vehicleYear);
        $stmt->execute();
        $vehicleId = $conn->insert_id;
        $stmt->close();
        
        $stmt = $conn->prepare("INSERT INTO bookings (user_id, customer_id, vehicle_id, service_type, booking_date, booking_time) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iiisss", $user_id, $customerId, $vehicleId, $serviceType, $bookingDate, $bookingTime);
        $stmt->execute();
        $bookingId = $conn->insert_id;
        $stmt->close();
        
        $conn->commit();
        
        $response = [
            'status' => 'success',
            'message' => 'Booking created successfully!',
            'booking_id' => $bookingId
        ];
        
    } catch (Exception $e) {
        $conn->rollback();
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
}

header('Content-Type: application/json');
echo json_encode($response);
?>