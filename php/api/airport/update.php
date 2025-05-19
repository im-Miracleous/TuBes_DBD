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
    sendResponse(false, 'Original airport code is required.');
}

// Sanitize input
$newCode = strtoupper(substr(sanitizeInput($data['airport-code'] ?? ''), 0, 3));
$airportName = sanitizeInput($data['airport-name'] ?? '');
$city = sanitizeInput($data['city'] ?? '');
$country = sanitizeInput($data['country'] ?? '');

// Validate
if (empty($newCode) || strlen($newCode) !== 3 || !ctype_alpha($newCode)) {
    sendResponse(false, 'Valid 3-letter airport code is required.');
}

if (empty($airportName)) {
    sendResponse(false, 'Airport name is required.');
}

if (empty($city)) {
    sendResponse(false, 'City is required.');
}

if (empty($country)) {
    sendResponse(false, 'Country is required.');
}

try {
    // Check if airport exists
    $checkQuery = "SELECT AirportCode FROM Airport WHERE AirportCode = ?";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->execute([$originalCode]);
    
    if ($checkStmt->rowCount() === 0) {
        sendResponse(false, 'Airport not found.');
    }
    
    // Check if new code is already used by another airport
    if ($newCode !== $originalCode) {
        $codeCheckQuery = "SELECT AirportCode FROM Airport WHERE AirportCode = ?";
        $codeCheckStmt = $db->prepare($codeCheckQuery);
        $codeCheckStmt->execute([$newCode]);
        
        if ($codeCheckStmt->rowCount() > 0) {
            sendResponse(false, 'Airport code already used by another airport.');
        }
    }
    
    // Check if airport is referenced in Flight table
    if ($newCode !== $originalCode) {
        $flightCheckQuery = "SELECT FlightID FROM Flight WHERE OriginAirportCode = ? OR DestinationAirportCode = ? LIMIT 1";
        $flightCheckStmt = $db->prepare($flightCheckQuery);
        $flightCheckStmt->execute([$originalCode, $originalCode]);
        
        if ($flightCheckStmt->rowCount() > 0) {
            sendResponse(false, 'Cannot change code of airport that is referenced in flights.');
        }
    }
    
    // Update airport
    $query = "UPDATE Airport SET
                AirportCode = ?,
                AirportName = ?,
                City = ?,
                Country = ?
              WHERE AirportCode = ?";
    
    $stmt = $db->prepare($query);
    $success = $stmt->execute([
        $newCode,
        $airportName,
        $city,
        $country,
        $originalCode
    ]);
    
    if ($success) {
        sendResponse(true, 'Airport updated successfully.', [
            'newCode' => $newCode
        ]);
    } else {
        sendResponse(false, 'Unable to update airport.');
    }
} catch (PDOException $e) {
    sendResponse(false, 'Database error: ' . $e->getMessage());
}
?>