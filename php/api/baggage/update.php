<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT");
header("Access-Control-Allow-Headers: Content-Type");

require_once '../../config/database.php';
require_once '../../includes/functions.php';

$database = new Database();
$db = $database->getConnection();

$baggageId = $_GET['id'] ?? null;
$data = json_decode(file_get_contents("php://input"), true);

if (!$baggageId) {
    sendResponse(false, 'Baggage ID is required.');
}

// Sanitize input
$bookingId = intval($data['booking-id'] ?? 0);
$weight = floatval($data['weight'] ?? 0);
$baggageType = sanitizeInput($data['baggage-type'] ?? '');
$status = sanitizeInput($data['status'] ?? '');

// Validate
if ($bookingId <= 0) {
    sendResponse(false, 'Valid booking is required.');
}

if ($weight <= 0) {
    sendResponse(false, 'Valid weight is required (greater than 0).');
}

if (!in_array($baggageType, ['Checked', 'Carry-on'])) {
    sendResponse(false, 'Invalid baggage type.');
}

if (!in_array($status, ['Checked In', 'Onboard', 'In Transit', 'Lost'])) {
    sendResponse(false, 'Invalid status.');
}

try {
    // Check if baggage exists
    $checkQuery = "SELECT BaggageID FROM Baggage WHERE BaggageID = ?";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->execute([$baggageId]);
    
    if ($checkStmt->rowCount() === 0) {
        sendResponse(false, 'Baggage not found.');
    }
    
    // Check if booking exists
    $bookingQuery = "SELECT BookingID FROM Booking WHERE BookingID = ?";
    $bookingStmt = $db->prepare($bookingQuery);
    $bookingStmt->execute([$bookingId]);
    
    if ($bookingStmt->rowCount() === 0) {
        sendResponse(false, 'Booking not found.');
    }
    
    // Update baggage
    $query = "UPDATE Baggage SET
                BookingID = ?,
                Weight = ?,
                BaggageType = ?,
                Status = ?
              WHERE BaggageID = ?";
    
    $stmt = $db->prepare($query);
    $success = $stmt->execute([
        $bookingId,
        $weight,
        $baggageType,
        $status,
        $baggageId
    ]);
    
    if ($success) {
        sendResponse(true, 'Baggage updated successfully.');
    } else {
        sendResponse(false, 'Unable to update baggage.');
    }
} catch (PDOException $e) {
    sendResponse(false, 'Database error: ' . $e->getMessage());
}
?>