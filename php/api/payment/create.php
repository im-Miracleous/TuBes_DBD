<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once '../../config/database.php';
require_once '../../includes/functions.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"), true);

// Sanitize input
$bookingId = intval($data['booking-id'] ?? 0);
$amount = floatval($data['amount'] ?? 0);
$paymentMethod = sanitizeInput($data['payment-method'] ?? '');
$transactionDate = sanitizeInput($data['transaction-date'] ?? '');

// Validate
if ($bookingId <= 0) {
    sendResponse(false, 'Valid booking is required.');
}

if ($amount <= 0) {
    sendResponse(false, 'Valid amount is required (greater than 0).');
}

if (!in_array($paymentMethod, ['Credit Card', 'Bank Transfer', 'E-Wallet', 'Cash'])) {
    sendResponse(false, 'Invalid payment method.');
}

if (empty($transactionDate) || !validateDate($transactionDate, 'Y-m-d\TH:i')) {
    sendResponse(false, 'Valid transaction date/time is required.');
}

try {
    // Check if booking exists
    $bookingQuery = "SELECT BookingID FROM Booking WHERE BookingID = ?";
    $bookingStmt = $db->prepare($bookingQuery);
    $bookingStmt->execute([$bookingId]);
    
    if ($bookingStmt->rowCount() === 0) {
        sendResponse(false, 'Booking not found.');
    }
    
    // Check if booking already has a payment
    $paymentCheckQuery = "SELECT PaymentID FROM Payment WHERE BookingID = ?";
    $paymentCheckStmt = $db->prepare($paymentCheckQuery);
    $paymentCheckStmt->execute([$bookingId]);
    
    if ($paymentCheckStmt->rowCount() > 0) {
        sendResponse(false, 'Booking already has a payment.');
    }
    
    // Insert new payment
    $query = "INSERT INTO Payment (
                BookingID, 
                PaymentMethod, 
                Amount, 
                TransactionDateTime
              ) VALUES (?, ?, ?, ?)";
    
    $stmt = $db->prepare($query);
    $success = $stmt->execute([
        $bookingId,
        $paymentMethod,
        $amount,
        $transactionDate
    ]);
    
    if ($success) {
        sendResponse(true, 'Payment created successfully.', [
            'PaymentID' => $db->lastInsertId()
        ]);
    } else {
        sendResponse(false, 'Unable to create payment.');
    }
} catch (PDOException $e) {
    sendResponse(false, 'Database error: ' . $e->getMessage());
}
?>