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
$airportCode = strtoupper(substr(sanitizeInput($data['airport-code'] ?? ''), 0, 3));
$airportName = sanitizeInput($data['airport-name'] ?? '');
$city = sanitizeInput($data['city'] ?? '');
$country = sanitizeInput($data['country'] ?? '');

// Validate
if (empty($airportCode) || strlen($airportCode) !== 3 || !ctype_alpha($airportCode)) {
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
    // Check if airport code already exists
    $checkQuery = "SELECT AirportCode FROM Airport WHERE AirportCode = ?";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->execute([$airportCode]);
    
    if ($checkStmt->rowCount() > 0) {
        sendResponse(false, 'Airport code already exists.');
    }
    
    // Insert new airport
    $query = "INSERT INTO Airport (
                AirportCode, 
                AirportName, 
                City, 
                Country
              ) VALUES (?, ?, ?, ?)";
    
    $stmt = $db->prepare($query);
    $success = $stmt->execute([
        $airportCode,
        $airportName,
        $city,
        $country
    ]);
    
    if ($success) {
        sendResponse(true, 'Airport created successfully.', [
            'AirportCode' => $airportCode
        ]);
    } else {
        sendResponse(false, 'Unable to create airport.');
    }
} catch (PDOException $e) {
    sendResponse(false, 'Database error: ' . $e->getMessage());
}
?>