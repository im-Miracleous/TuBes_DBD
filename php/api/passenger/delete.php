<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: DELETE");
header("Access-Control-Allow-Headers: Content-Type");

require_once '../../config/database.php';
require_once '../../includes/functions.php';

$database = new Database();
$db = $database->getConnection();

$passengerId = $_GET['id'] ?? null;

if (!$passengerId) {
    sendResponse(false, 'Passenger ID is required.');
}

try {
    // Check if passenger exists
    $checkQuery = "SELECT PassengerID FROM Passenger WHERE PassengerID = ?";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->execute([$passengerId]);
    
    if ($checkStmt->rowCount() === 0) {
        sendResponse(false, 'Passenger not found.');
    }
    
    // Check if passenger has bookings
    $bookingsQuery = "SELECT BookingID FROM Booking WHERE PassengerID = ? LIMIT 1";
    $bookingsStmt = $db->prepare($bookingsQuery);
    $bookingsStmt->execute([$passengerId]);
    
    if ($bookingsStmt->rowCount() > 0) {
        sendResponse(false, 'Cannot delete passenger with existing bookings.');
    }
    
    // Delete passenger
    $query = "DELETE FROM Passenger WHERE PassengerID = ?";
    $stmt = $db->prepare($query);
    $success = $stmt->execute([$passengerId]);
    
    if ($success) {
        sendResponse(true, 'Passenger deleted successfully.');
    } else {
        sendResponse(false, 'Unable to delete passenger.');
    }
} catch (PDOException $e) {
    sendResponse(false, 'Database error: ' . $e->getMessage());
}
?>