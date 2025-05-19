<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: DELETE");
header("Access-Control-Allow-Headers: Content-Type");

require_once '../../config/database.php';
require_once '../../includes/functions.php';

$database = new Database();
$db = $database->getConnection();

$paymentId = $_GET['id'] ?? null;

if (!$paymentId) {
    sendResponse(false, 'Payment ID is required.');
}

try {
    // First get the booking ID associated with this payment
    $getBookingQuery = "SELECT BookingID FROM Payment WHERE PaymentID = ?";
    $getBookingStmt = $db->prepare($getBookingQuery);
    $getBookingStmt->execute([$paymentId]);
    
    $paymentData = $getBookingStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$paymentData) {
        sendResponse(false, 'Payment not found.');
    }
    
    $bookingId = $paymentData['BookingID'];

    // Begin transaction
    $db->beginTransaction();

    // Delete payment
    $deleteQuery = "DELETE FROM Payment WHERE PaymentID = ?";
    $deleteStmt = $db->prepare($deleteQuery);
    $deleteSuccess = $deleteStmt->execute([$paymentId]);

    if ($deleteSuccess) {
        // Update booking status to Pending after payment deletion
        $updateBookingQuery = "UPDATE Booking SET PaymentStatus = 'Pending' WHERE BookingID = ?";
        $updateBookingStmt = $db->prepare($updateBookingQuery);
        $updateBookingSuccess = $updateBookingStmt->execute([$bookingId]);

        if ($updateBookingSuccess) {
            $db->commit();
            sendResponse(true, 'Payment deleted successfully. Booking status set to Pending.');
        } else {
            $db->rollBack();
            sendResponse(false, 'Payment deleted but failed to update booking status.');
        }
    } else {
        $db->rollBack();
        sendResponse(false, 'Unable to delete payment.');
    }
} catch (PDOException $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    sendResponse(false, 'Database error: ' . $e->getMessage());
}
?>