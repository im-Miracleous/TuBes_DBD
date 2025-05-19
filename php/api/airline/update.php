<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT");
header("Access-Control-Allow-Headers: Content-Type");

require_once '../../config/database.php';
require_once '../../includes/functions.php';

$database = new Database();
$db = $database->getConnection();

$originalCode = $_GET['code'] ?? null;
$data = json_decode(file_get_contents("php://input"), true);

if (!$originalCode) {
    sendResponse(false, 'Original airline code is required.');
}

// Sanitize input
$newCode = strtoupper(substr(sanitizeInput($data['airline-code'] ?? ''), 0, 2));
$airlineName = sanitizeInput($data['airline-name'] ?? '');
$contactNumber = sanitizeInput($data['contact-number'] ?? null);
$operatingRegion = sanitizeInput($data['operating-region'] ?? null);

// Validate
if (empty($newCode) || strlen($newCode) !== 2 || !ctype_alpha($newCode)) {
    sendResponse(false, 'Valid 2-letter airline code is required.');
}

if (empty($airlineName)) {
    sendResponse(false, 'Airline name is required.');
}

try {
    // Check if airline exists
    $checkQuery = "SELECT AirlineCode FROM Airline WHERE AirlineCode = ?";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->execute([$originalCode]);
    
    if ($checkStmt->rowCount() === 0) {
        sendResponse(false, 'Airline not found.');
    }
    
    // Check if new code is already used by another airline
    if ($newCode !== $originalCode) {
        $codeCheckQuery = "SELECT AirlineCode FROM Airline WHERE AirlineCode = ?";
        $codeCheckStmt = $db->prepare($codeCheckQuery);
        $codeCheckStmt->execute([$newCode]);
        
        if ($codeCheckStmt->rowCount() > 0) {
            sendResponse(false, 'Airline code already used by another airline.');
        }
    }
    
    // Check if airline is referenced in Flight or Aircraft table
    if ($newCode !== $originalCode) {
        // Check Flight table
        $flightCheckQuery = "SELECT FlightID FROM Flight WHERE AirlineCode = ? LIMIT 1";
        $flightCheckStmt = $db->prepare($flightCheckQuery);
        $flightCheckStmt->execute([$originalCode]);
        
        if ($flightCheckStmt->rowCount() > 0) {
            sendResponse(false, 'Cannot change code of airline that is referenced in flights.');
        }
        
        // Check Aircraft table
        $aircraftCheckQuery = "SELECT AircraftID FROM Aircraft WHERE AirlineCode = ? LIMIT 1";
        $aircraftCheckStmt = $db->prepare($aircraftCheckQuery);
        $aircraftCheckStmt->execute([$originalCode]);
        
        if ($aircraftCheckStmt->rowCount() > 0) {
            sendResponse(false, 'Cannot change code of airline that is referenced in aircraft.');
        }
    }
    
    // Update airline
    $query = "UPDATE Airline SET
                AirlineCode = ?,
                AirlineName = ?,
                ContactNumber = ?,
                OperatingRegion = ?
              WHERE AirlineCode = ?";
    
    $stmt = $db->prepare($query);
    $success = $stmt->execute([
        $newCode,
        $airlineName,
        $contactNumber,
        $operatingRegion,
        $originalCode
    ]);
    
    if ($success) {
        sendResponse(true, 'Airline updated successfully.', [
            'newCode' => $newCode
        ]);
    } else {
        sendResponse(false, 'Unable to update airline.');
    }
} catch (PDOException $e) {
    sendResponse(false, 'Database error: ' . $e->getMessage());
}
?>