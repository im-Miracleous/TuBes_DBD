<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: DELETE");
header("Access-Control-Allow-Headers: Content-Type");

require_once '../../config/database.php';
require_once '../../includes/functions.php';

$database = new Database();
$db = $database->getConnection();

$baggageId = $_GET['id'] ?? null;

if (!$baggageId) {
    sendResponse(false, 'Baggage ID is required.');
}

try {
    // Check if baggage exists
    $checkQuery = "SELECT BaggageID FROM Baggage WHERE BaggageID = ?";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->execute([$baggageId]);
    
    if ($checkStmt->rowCount() === 0) {
        sendResponse(false, 'Baggage not found.');
    }
    
    // Delete baggage
    $query = "DELETE FROM Baggage WHERE BaggageID = ?";
    $stmt = $db->prepare($query);
    $success = $stmt->execute([$baggageId]);
    
    if ($success) {
        sendResponse(true, 'Baggage deleted successfully.');
    } else {
        sendResponse(false, 'Unable to delete baggage.');
    }
} catch (PDOException $e) {
    sendResponse(false, 'Database error: ' . $e->getMessage());
}
?>