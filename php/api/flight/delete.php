<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: DELETE");
header("Access-Control-Allow-Headers: Content-Type");

require_once '../../config/database.php';
require_once '../../includes/functions.php';

$database = new Database();
$db = $database->getConnection();

$flightId = $_GET['id'] ?? null;

if (!$flightId) {
    sendResponse(false, 'Flight ID is required.');
}

try {
    // Check if flight exists
    $checkQuery = "SELECT FlightID FROM Flight WHERE FlightID = ?";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->execute([$flightId]);
    
    if ($checkStmt->rowCount() === 0) {
        sendResponse(false, 'Flight not found.');
    }
    
    // Check if there are bookings for this flight
    $bookingsQuery = "SELECT BookingID FROM Booking WHERE FlightID = ? LIMIT 1";
    $bookingsStmt = $db->prepare($bookingsQuery);
    $bookingsStmt->execute([$flightId]);
    
    if ($bookingsStmt->rowCount() > 0) {
        sendResponse(false, 'Cannot delete flight with existing bookings.');
    }
    
    // Delete flight
    $query = "DELETE FROM Flight WHERE FlightID = ?";
    $stmt = $db->prepare($query);
    $success = $stmt->execute([$flightId]);
    
    if ($success) {
        sendResponse(true, 'Flight deleted successfully.');
    } else {
        sendResponse(false, 'Unable to delete flight.');
    }
} catch (PDOException $e) {
    sendResponse(false, 'Database error: ' . $e->getMessage());
}
?>