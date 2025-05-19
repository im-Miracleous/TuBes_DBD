<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

require_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Get query parameters
$paymentId = $_GET['id'] ?? null;
$dateFrom = $_GET['date_from'] ?? null;
$dateTo = $_GET['date_to'] ?? null;
$method = $_GET['method'] ?? null;

if ($paymentId) {
    // Get single payment with booking details
    $query = "SELECT p.*, 
                     CONCAT(ps.FirstName, ' ', ps.LastName) AS PassengerName
              FROM Payment p
              JOIN Booking b ON p.BookingID = b.BookingID
              JOIN Passenger ps ON b.PassengerID = ps.PassengerID
              WHERE p.PaymentID = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$paymentId]);
    
    $payment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($payment) {
        echo json_encode($payment);
    } else {
        http_response_code(404);
        echo json_encode(['message' => 'Payment not found']);
    }
} else {
    // Get all payments with optional filters
    $query = "SELECT p.*, 
                     CONCAT(ps.FirstName, ' ', ps.LastName) AS PassengerName
              FROM Payment p
              JOIN Booking b ON p.BookingID = b.BookingID
              JOIN Passenger ps ON b.PassengerID = ps.PassengerID";
    
    $conditions = [];
    $params = [];
    
    if ($dateFrom) {
        $conditions[] = "DATE(p.TransactionDateTime) >= ?";
        $params[] = $dateFrom;
    }
    if ($dateTo) {
        $conditions[] = "DATE(p.TransactionDateTime) <= ?";
        $params[] = $dateTo;
    }
    if ($method) {
        $conditions[] = "p.PaymentMethod = ?";
        $params[] = $method;
    }

    if ($conditions) {
        $query .= " WHERE " . implode(" AND ", $conditions);
    }

    $stmt = $db->prepare($query);
    $stmt->execute($params);

    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($payments);
}