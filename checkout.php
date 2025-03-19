<?php
include('db/dbconn.php');
session_start();

if (!isset($_SESSION['Id']) || !isset($_SESSION['email'])) {
    header("location:login.php");
    exit;
}

require __DIR__ . "/vendor/autoload.php";

require_once 'configer.php';
$stripe_secret_key = STRIPE_SECRET_KEY;

$booking_id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;

if (!$booking_id) {
    header("location:profile.php");
    exit;
}

$sql = "SELECT b.booking_id, b.service_type, b.booking_date, b.booking_time, 
        b.progress_status, b.price, v.vehicle_number, v.vehicle_model, v.vehicle_year,
        u.fname, u.lname, u.email
        FROM bookings b
        JOIN vehicles v ON b.vehicle_id = v.vehicle_id
        JOIN usertable u ON b.user_id = u.Id
        WHERE b.booking_id = ? AND b.user_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $booking_id, $_SESSION['Id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("location:profile.php");
    exit;
}

$booking = $result->fetch_assoc();
$service_name = ucwords(str_replace('_', ' ', $booking['service_type']));
$amount_in_cents = round($booking['price'] * 100); 

\Stripe\Stripe::setApiKey($stripe_secret_key);

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$base_url = $protocol . $_SERVER['HTTP_HOST'] . "/FinalProject";

$success_url = $base_url . "/success.php?booking_id={$booking_id}";
$cancel_url = $base_url . "/profile.php";

try {
    $checkout_session = \Stripe\Checkout\Session::create([
        "mode" => "payment",
        "success_url" => $success_url,
        "cancel_url" => $cancel_url,
        "locale" => "auto",
        "payment_method_types" => ["card"],
        "client_reference_id" => $booking_id,
        "customer_email" => $booking['email'],
        "line_items" => [
            [
                "quantity" => 1,
                "price_data" => [
                    "currency" => "LKR",
                    "unit_amount" => $amount_in_cents,
                    "product_data" => [
                        "name" => "Service: {$service_name}",
                        "description" => "Booking ID: {$booking_id} - {$booking['vehicle_number']} - {$booking['vehicle_model']} ({$booking['vehicle_year']})"
                    ]
                ]
            ]
        ],
        "metadata" => [
            "booking_id" => $booking_id,
            "vehicle" => $booking['vehicle_number'],
            "service" => $booking['service_type']
        ]
    ]);

    http_response_code(303);
    header("Location: " . $checkout_session->url);
    exit;
} catch (\Stripe\Exception\ApiErrorException $e) {
    error_log('Stripe API Error: ' . $e->getMessage());
    echo "Payment initialization failed. Please try again later.";
    exit;
}