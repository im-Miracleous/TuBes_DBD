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
$flightId = intval($data['flight-id'] ?? 0);
$passengerId = intval($data['passenger-id'] ?? 0);
$paymentStatus = sanitizeInput($data['payment-status'] ?? 'Pending');

// Validate
if ($flightId <= 0) {
    sendResponse(false, 'Valid flight is required.');
}

if ($passengerId <= 0) {
    sendResponse(false, 'Valid passenger is required.');
}

if (!in_array($paymentStatus, ['Pending', 'Paid', 'Cancelled', 'Rescheduled'])) {
    sendResponse(false, 'Invalid payment status.');
}

try {
    // Check if flight exists
    $flightQuery = "SELECT FlightID FROM Flight WHERE FlightID = ?";
    $flightStmt = $db->prepare($flightQuery);
    $flightStmt->execute([$flightId]);
    
    if ($flightStmt->rowCount() === 0) {
        sendResponse(false, 'Flight not found.');
    }
    
    // Check if passenger exists
    $passengerQuery = "SELECT PassengerID FROM Passenger WHERE PassengerID = ?";
    $passengerStmt = $db->prepare($passengerQuery);
    $passengerStmt->execute([$passengerId]);
    
    if ($passengerStmt->rowCount() === 0) {
        sendResponse(false, 'Passenger not found.');
    }
    
    // Insert new booking
    $query = "INSERT INTO Booking (
                FlightID, 
                PassengerID, 
                PaymentStatus
              ) VALUES (?, ?, ?)";
    
    $stmt = $db->prepare($query);
    $success = $stmt->execute([
        $flightId,
        $passengerId,
        $paymentStatus
    ]);
    
    if ($success) {
        sendResponse(true, 'Booking created successfully.', [
            'BookingID' => $db->lastInsertId()
        ]);
    } else {
        sendResponse(false, 'Unable to create booking.');
    }
} catch (PDOException $e) {
    sendResponse(false, 'Database error: ' . $e->getMessage());
}
?>