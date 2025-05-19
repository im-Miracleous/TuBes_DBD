<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: DELETE");
header("Access-Control-Allow-Headers: Content-Type");

require_once '../../config/database.php';
require_once '../../includes/functions.php';

$database = new Database();
$db = $database->getConnection();

$airportCode = $_GET['code'] ?? null;

if (!$airportCode) {
    sendResponse(false, 'Airport code is required.');
}

try {
    // Check if airport exists
    $checkQuery = "SELECT AirportCode FROM Airport WHERE AirportCode = ?";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->execute([$airportCode]);
    
    if ($checkStmt->rowCount() === 0) {
        sendResponse(false, 'Airport not found.');
    }
    
    // Check if airport is referenced in Flight table
    $flightQuery = "SELECT FlightID FROM Flight WHERE OriginAirportCode = ? OR DestinationAirportCode = ? LIMIT 1";
    $flightStmt = $db->prepare($flightQuery);
    $flightStmt->execute([$airportCode, $airportCode]);
    
    if ($flightStmt->rowCount() > 0) {
        sendResponse(false, 'Cannot delete airport that is referenced in flights.');
    }
    
    // Delete airport
    $query = "DELETE FROM Airport WHERE AirportCode = ?";
    $stmt = $db->prepare($query);
    $success = $stmt->execute([$airportCode]);
    
    if ($success) {
        sendResponse(true, 'Airport deleted successfully.');
    } else {
        sendResponse(false, 'Unable to delete airport.');
    }
} catch (PDOException $e) {
    sendResponse(false, 'Database error: ' . $e->getMessage());
}
?>