<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: DELETE");
header("Access-Control-Allow-Headers: Content-Type");

require_once '../../config/database.php';
require_once '../../includes/functions.php';

$database = new Database();
$db = $database->getConnection();

$bookingId = $_GET['id'] ?? null;

if (!$bookingId) {
    sendResponse(false, 'Booking ID is required.');
}

try {
    // Check if booking exists
    $checkQuery = "SELECT BookingID FROM Booking WHERE BookingID = ?";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->execute([$bookingId]);
    
    if ($checkStmt->rowCount() === 0) {
        sendResponse(false, 'Booking not found.');
    }
    
    // Check if there's a payment for this booking
    $paymentQuery = "SELECT PaymentID FROM Payment WHERE BookingID = ? LIMIT 1";
    $paymentStmt = $db->prepare($paymentQuery);
    $paymentStmt->execute([$bookingId]);
    
    if ($paymentStmt->rowCount() > 0) {
        sendResponse(false, 'Cannot delete booking with existing payment.');
    }
    
    // Check if there's baggage for this booking
    $baggageQuery = "SELECT BaggageID FROM Baggage WHERE BookingID = ? LIMIT 1";
    $baggageStmt = $db->prepare($baggageQuery);
    $baggageStmt->execute([$bookingId]);
    
    if ($baggageStmt->rowCount() > 0) {
        sendResponse(false, 'Cannot delete booking with existing baggage.');
    }
    
    // Delete booking
    $query = "DELETE FROM Booking WHERE BookingID = ?";
    $stmt = $db->prepare($query);
    $success = $stmt->execute([$bookingId]);
    
    if ($success) {
        sendResponse(true, 'Booking deleted successfully.');
    } else {
        sendResponse(false, 'Unable to delete booking.');
    }
} catch (PDOException $e) {
    sendResponse(false, 'Database error: ' . $e->getMessage());
}
?>