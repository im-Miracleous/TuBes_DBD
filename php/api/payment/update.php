<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT");
header("Access-Control-Allow-Headers: Content-Type");

require_once '../../config/database.php';
require_once '../../includes/functions.php';

$database = new Database();
$db = $database->getConnection();

$paymentId = $_GET['id'] ?? null;
$data = json_decode(file_get_contents("php://input"), true);

if (!$paymentId) {
    sendResponse(false, 'Payment ID is required.');
}

// Sanitize input
$bookingId = intval($data['booking-id'] ?? 0);
$paymentMethod = sanitizeInput($data['payment-method'] ?? '');
$amount = floatval($data['amount'] ?? 0);

// Validate
if ($bookingId <= 0) {
    sendResponse(false, 'Valid booking ID is required.');
}

if (empty($paymentMethod)) {
    sendResponse(false, 'Payment method is required.');
}

if ($amount <= 0) {
    sendResponse(false, 'Valid payment amount is required.');
}

try {
    // Check if payment exists
    $checkQuery = "SELECT PaymentID FROM Payment WHERE PaymentID = ?";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->execute([$paymentId]);
    
    if ($checkStmt->rowCount() === 0) {
        sendResponse(false, 'Payment not found.');
    }

    // Check if booking exists
    $bookingQuery = "SELECT BookingID FROM Booking WHERE BookingID = ?";
    $bookingStmt = $db->prepare($bookingQuery);
    $bookingStmt->execute([$bookingId]);
    
    if ($bookingStmt->rowCount() === 0) {
        sendResponse(false, 'Booking not found.');
    }

    // Check if this booking already has another payment
    $existingPaymentQuery = "SELECT PaymentID FROM Payment WHERE BookingID = ? AND PaymentID != ?";
    $existingPaymentStmt = $db->prepare($existingPaymentQuery);
    $existingPaymentStmt->execute([$bookingId, $paymentId]);
    
    if ($existingPaymentStmt->rowCount() > 0) {
        sendResponse(false, 'This booking already has another payment record.');
    }

    // Update payment
    $query = "UPDATE Payment SET
                BookingID = ?,
                PaymentMethod = ?,
                Amount = ?,
                TransactionDateTime = CURRENT_TIMESTAMP
              WHERE PaymentID = ?";
    
    $stmt = $db->prepare($query);
    $success = $stmt->execute([
        $bookingId,
        $paymentMethod,
        $amount,
        $paymentId
    ]);

    if ($success) {
        // Update booking payment status if payment is successful
        if (strtolower($paymentMethod) !== 'failed') {
            $updateBookingQuery = "UPDATE Booking SET PaymentStatus = 'Paid' WHERE BookingID = ?";
            $updateBookingStmt = $db->prepare($updateBookingQuery);
            $updateBookingStmt->execute([$bookingId]);
        }

        sendResponse(true, 'Payment updated successfully.');
    } else {
        sendResponse(false, 'Unable to update payment.');
    }
} catch (PDOException $e) {
    sendResponse(false, 'Database error: ' . $e->getMessage());
}
?>