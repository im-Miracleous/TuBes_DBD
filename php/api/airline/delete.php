<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: DELETE");
header("Access-Control-Allow-Headers: Content-Type");

require_once '../../config/database.php';
require_once '../../includes/functions.php';

$database = new Database();
$db = $database->getConnection();

$airlineCode = $_GET['code'] ?? null;

if (!$airlineCode) {
    sendResponse(false, 'Airline code is required.');
}

try {
    // Check if airline exists
    $checkQuery = "SELECT AirlineCode FROM Airline WHERE AirlineCode = ?";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->execute([$airlineCode]);
    
    if ($checkStmt->rowCount() === 0) {
        sendResponse(false, 'Airline not found.');
    }
    
    // Check if airline is referenced in Flight table
    $flightQuery = "SELECT FlightID FROM Flight WHERE AirlineCode = ? LIMIT 1";
    $flightStmt = $db->prepare($flightQuery);
    $flightStmt->execute([$airlineCode]);
    
    if ($flightStmt->rowCount() > 0) {
        sendResponse(false, 'Cannot delete airline that is referenced in flights.');
    }
    
    // Check if airline is referenced in Aircraft table
    $aircraftQuery = "SELECT AircraftID FROM Aircraft WHERE AirlineCode = ? LIMIT 1";
    $aircraftStmt = $db->prepare($aircraftQuery);
    $aircraftStmt->execute([$airlineCode]);
    
    if ($aircraftStmt->rowCount() > 0) {
        sendResponse(false, 'Cannot delete airline that is referenced in aircraft.');
    }
    
    // Delete airline
    $query = "DELETE FROM Airline WHERE AirlineCode = ?";
    $stmt = $db->prepare($query);
    $success = $stmt->execute([$airlineCode]);
    
    if ($success) {
        sendResponse(true, 'Airline deleted successfully.');
    } else {
        sendResponse(false, 'Unable to delete airline.');
    }
} catch (PDOException $e) {
    sendResponse(false, 'Database error: ' . $e->getMessage());
}
?>