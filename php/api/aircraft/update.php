<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT");
header("Access-Control-Allow-Headers: Content-Type");

require_once '../../config/database.php';
require_once '../../includes/functions.php';

$database = new Database();
$db = $database->getConnection();

$aircraftId = $_GET['id'] ?? null;
$data = json_decode(file_get_contents("php://input"), true);

if (!$aircraftId) {
    sendResponse(false, 'Aircraft ID is required.');
}

// Sanitize input
$aircraftType = sanitizeInput($data['aircraft-type'] ?? '');
$registrationNumber = sanitizeInput($data['registration-number'] ?? '');
$capacity = intval($data['capacity'] ?? 0);
$airlineCode = sanitizeInput($data['airline-code'] ?? '');

// Validate
if (empty($aircraftType)) {
    sendResponse(false, 'Aircraft type is required.');
}

if (empty($registrationNumber)) {
    sendResponse(false, 'Registration number is required.');
}

if ($capacity <= 0) {
    sendResponse(false, 'Valid capacity is required (minimum 1).');
}

if (empty($airlineCode)) {
    sendResponse(false, 'Airline is required.');
}

try {
    // Check if aircraft exists
    $checkQuery = "SELECT AircraftID FROM Aircraft WHERE AircraftID = ?";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->execute([$aircraftId]);
    
    if ($checkStmt->rowCount() === 0) {
        sendResponse(false, 'Aircraft not found.');
    }
    
    // Check if registration number is already used by another aircraft
    $regCheckQuery = "SELECT AircraftID FROM Aircraft WHERE RegistrationNumber = ? AND AircraftID != ?";
    $regCheckStmt = $db->prepare($regCheckQuery);
    $regCheckStmt->execute([$registrationNumber, $aircraftId]);
    
    if ($regCheckStmt->rowCount() > 0) {
        sendResponse(false, 'Registration number already used by another aircraft.');
    }
    
    // Check if airline exists
    $airlineCheckQuery = "SELECT AirlineCode FROM Airline WHERE AirlineCode = ?";
    $airlineCheckStmt = $db->prepare($airlineCheckQuery);
    $airlineCheckStmt->execute([$airlineCode]);
    
    if ($airlineCheckStmt->rowCount() === 0) {
        sendResponse(false, 'Airline not found.');
    }
    
    // Update aircraft
    $query = "UPDATE Aircraft SET
                AircraftType = ?,
                RegistrationNumber = ?,
                Capacity = ?,
                AirlineCode = ?
              WHERE AircraftID = ?";
    
    $stmt = $db->prepare($query);
    $success = $stmt->execute([
        $aircraftType,
        $registrationNumber,
        $capacity,
        $airlineCode,
        $aircraftId
    ]);
    
    if ($success) {
        sendResponse(true, 'Aircraft updated successfully.');
    } else {
        sendResponse(false, 'Unable to update aircraft.');
    }
} catch (PDOException $e) {
    sendResponse(false, 'Database error: ' . $e->getMessage());
}
?>