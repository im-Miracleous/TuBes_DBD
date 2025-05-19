<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: DELETE");
header("Access-Control-Allow-Headers: Content-Type");

require_once '../../config/database.php';
require_once '../../includes/functions.php';

$database = new Database();
$db = $database->getConnection();

$aircraftId = $_GET['id'] ?? null;

if (!$aircraftId) {
    sendResponse(false, 'Aircraft ID is required.');
}

try {
    // Check if aircraft exists
    $checkQuery = "SELECT AircraftID FROM Aircraft WHERE AircraftID = ?";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->execute([$aircraftId]);
    
    if ($checkStmt->rowCount() === 0) {
        sendResponse(false, 'Aircraft not found.');
    }
    
    // Check if aircraft is referenced in any other tables
    // (Add checks here if aircraft is referenced in other tables in your schema)
    
    // Delete aircraft
    $query = "DELETE FROM Aircraft WHERE AircraftID = ?";
    $stmt = $db->prepare($query);
    $success = $stmt->execute([$aircraftId]);
    
    if ($success) {
        sendResponse(true, 'Aircraft deleted successfully.');
    } else {
        sendResponse(false, 'Unable to delete aircraft.');
    }
} catch (PDOException $e) {
    sendResponse(false, 'Database error: ' . $e->getMessage());
}
?>