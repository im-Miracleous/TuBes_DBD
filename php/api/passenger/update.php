<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$database = new Database();
$db = $database->getConnection();

// Get passenger ID from URL
$passengerId = $_GET['id'] ?? null;

// Get JSON input
$rawInput = file_get_contents('php://input');
if (empty($rawInput)) {
    sendResponse(false, 'No input data provided.');
}
$input = json_decode($rawInput, true);
if (json_last_error() !== JSON_ERROR_NONE || !is_array($input)) {
    sendResponse(false, 'Invalid JSON input');
}

if (!$passengerId) {
    sendResponse(false, 'Passenger ID is required.');
}

// Sanitize input
$firstName = sanitizeInput($input['first_name'] ?? '');
$lastName = sanitizeInput($input['last_name'] ?? '');
$email = sanitizeInput($input['email'] ?? '');
$passportNumber = sanitizeInput($input['passport_number'] ?? '');

// Validate
if (empty($firstName)) {
    sendResponse(false, 'First name is required.');
}

if (empty($lastName)) {
    sendResponse(false, 'Last name is required.');
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendResponse(false, 'Valid email is required.');
}

if (empty($passportNumber)) {
    sendResponse(false, 'Passport number is required.');
}

try {
    // Check if passenger exists
    $checkQuery = "SELECT PassengerID FROM Passenger WHERE PassengerID = ?";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->execute([$passengerId]);
    
    if ($checkStmt->rowCount() === 0) {
        sendResponse(false, 'Passenger not found.');
    }
    
    // Check if email is already used by another passenger
    $emailCheckQuery = "SELECT PassengerID FROM Passenger WHERE Email = ? AND PassengerID != ?";
    $emailCheckStmt = $db->prepare($emailCheckQuery);
    $emailCheckStmt->execute([$email, $passengerId]);
    
    if ($emailCheckStmt->rowCount() > 0) {
        sendResponse(false, 'Email already used by another passenger.');
    }
    
    // Check if passport number is already used by another passenger
    $passportCheckQuery = "SELECT PassengerID FROM Passenger WHERE PassportNumber = ? AND PassengerID != ?";
    $passportCheckStmt = $db->prepare($passportCheckQuery);
    $passportCheckStmt->execute([$passportNumber, $passengerId]);
    
    if ($passportCheckStmt->rowCount() > 0) {
        sendResponse(false, 'Passport number already used by another passenger.');
    }
    
    // Update passenger
    $query = "UPDATE Passenger SET
                FirstName = ?,
                LastName = ?,
                Email = ?,
                PassportNumber = ?
              WHERE PassengerID = ?";
    
    $stmt = $db->prepare($query);
    $success = $stmt->execute([
        $firstName,
        $lastName,
        $email,
        $passportNumber,
        $passengerId
    ]);
    
    if ($success) {
        sendResponse(true, 'Passenger updated successfully.');
    } else {
        sendResponse(false, 'Unable to update passenger.');
    }
} catch (PDOException $e) {
    sendResponse(false, 'Database error: ' . $e->getMessage());
}
?>