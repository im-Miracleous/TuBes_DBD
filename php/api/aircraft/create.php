<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once '../../config/database.php';
require_once '../../includes/functions.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"), true);

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
    // Check if registration number already exists
    $regCheckQuery = "SELECT AircraftID FROM Aircraft WHERE RegistrationNumber = ?";
    $regCheckStmt = $db->prepare($regCheckQuery);
    $regCheckStmt->execute([$registrationNumber]);
    
    if ($regCheckStmt->rowCount() > 0) {
        sendResponse(false, 'Registration number already exists.');
    }
    
    // Check if airline exists
    $airlineCheckQuery = "SELECT AirlineCode FROM Airline WHERE AirlineCode = ?";
    $airlineCheckStmt = $db->prepare($airlineCheckQuery);
    $airlineCheckStmt->execute([$airlineCode]);
    
    if ($airlineCheckStmt->rowCount() === 0) {
        sendResponse(false, 'Airline not found.');
    }
    
    // Insert new aircraft
    $query = "INSERT INTO Aircraft (
                AircraftType, 
                RegistrationNumber, 
                Capacity, 
                AirlineCode
              ) VALUES (?, ?, ?, ?)";
    
    $stmt = $db->prepare($query);
    $success = $stmt->execute([
        $aircraftType,
        $registrationNumber,
        $capacity,
        $airlineCode
    ]);
    
    if ($success) {
        sendResponse(true, 'Aircraft created successfully.', [
            'AircraftID' => $db->lastInsertId()
        ]);
    } else {
        sendResponse(false, 'Unable to create aircraft.');
    }
} catch (PDOException $e) {
    sendResponse(false, 'Database error: ' . $e->getMessage());
}
?>