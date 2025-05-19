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
$airlineCode = strtoupper(substr(sanitizeInput($data['airline-code'] ?? ''), 0, 2));
$airlineName = sanitizeInput($data['airline-name'] ?? '');
$contactNumber = sanitizeInput($data['contact-number'] ?? null);
$operatingRegion = sanitizeInput($data['operating-region'] ?? null);

// Validate
if (empty($airlineCode) || strlen($airlineCode) !== 2 || !ctype_alpha($airlineCode)) {
    sendResponse(false, 'Valid 2-letter airline code is required.');
}

if (empty($airlineName)) {
    sendResponse(false, 'Airline name is required.');
}

try {
    // Check if airline code already exists
    $checkQuery = "SELECT AirlineCode FROM Airline WHERE AirlineCode = ?";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->execute([$airlineCode]);
    
    if ($checkStmt->rowCount() > 0) {
        sendResponse(false, 'Airline code already exists.');
    }
    
    // Insert new airline
    $query = "INSERT INTO Airline (
                AirlineCode, 
                AirlineName, 
                ContactNumber, 
                OperatingRegion
              ) VALUES (?, ?, ?, ?)";
    
    $stmt = $db->prepare($query);
    $success = $stmt->execute([
        $airlineCode,
        $airlineName,
        $contactNumber,
        $operatingRegion
    ]);
    
    if ($success) {
        sendResponse(true, 'Airline created successfully.', [
            'AirlineCode' => $airlineCode
        ]);
    } else {
        sendResponse(false, 'Unable to create airline.');
    }
} catch (PDOException $e) {
    sendResponse(false, 'Database error: ' . $e->getMessage());
}
?>