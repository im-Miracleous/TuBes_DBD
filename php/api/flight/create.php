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
$flightNumber = sanitizeInput($data['flight-number'] ?? '');
$airlineCode = sanitizeInput($data['airline-code'] ?? '');
$departure = sanitizeInput($data['departure-datetime'] ?? '');
$arrival = sanitizeInput($data['arrival-datetime'] ?? '');
$origin = sanitizeInput($data['origin-airport'] ?? '');
$destination = sanitizeInput($data['destination-airport'] ?? '');
$seats = intval($data['available-seats'] ?? 0);
$status = sanitizeInput($data['status'] ?? 'Terjadwal');

// Validate
if (empty($flightNumber)) {
    sendResponse(false, 'Flight number is required.');
}

if (empty($airlineCode)) {
    sendResponse(false, 'Airline is required.');
}

if (empty($departure) || !validateDate($departure, 'Y-m-d\TH:i')) {
    sendResponse(false, 'Valid departure date/time is required.');
}

if (empty($arrival) || !validateDate($arrival, 'Y-m-d\TH:i')) {
    sendResponse(false, 'Valid arrival date/time is required.');
}

if (new DateTime($departure) >= new DateTime($arrival)) {
    sendResponse(false, 'Arrival must be after departure.');
}

if (empty($origin)) {
    sendResponse(false, 'Origin airport is required.');
}

if (empty($destination)) {
    sendResponse(false, 'Destination airport is required.');
}

if ($origin === $destination) {
    sendResponse(false, 'Origin and destination cannot be the same.');
}

if ($seats < 0) {
    sendResponse(false, 'Available seats cannot be negative.');
}

if (!in_array($status, ['Terjadwal', 'Ditunda', 'Dibatalkan'])) {
    sendResponse(false, 'Invalid flight status.');
}

try {
    // Check if flight number already exists
    $checkQuery = "SELECT FlightID FROM Flight WHERE FlightNumber = ?";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->execute([$flightNumber]);
    
    if ($checkStmt->rowCount() > 0) {
        sendResponse(false, 'Flight number already exists.');
    }
    
    // Insert new flight
    $query = "INSERT INTO Flight (
                FlightNumber, 
                AirlineCode, 
                DepartureDateTime, 
                ArrivalDateTime, 
                OriginAirportCode, 
                DestinationAirportCode, 
                AvailableSeats, 
                Status
              ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $db->prepare($query);
    $success = $stmt->execute([
        $flightNumber,
        $airlineCode,
        $departure,
        $arrival,
        $origin,
        $destination,
        $seats,
        $status
    ]);
    
    if ($success) {
        sendResponse(true, 'Flight created successfully.', [
            'FlightID' => $db->lastInsertId()
        ]);
    } else {
        sendResponse(false, 'Unable to create flight.');
    }
} catch (PDOException $e) {
    sendResponse(false, 'Database error: ' . $e->getMessage());
}
?>