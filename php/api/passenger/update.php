<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT");
header("Access-Control-Allow-Headers: Content-Type");

require_once '../../config/database.php';
require_once '../../includes/functions.php';

$database = new Database();
$db = $database->getConnection();

$passengerId = $_GET['id'] ?? null;
$data = json_decode(file_get_contents("php://input"), true);

if (!$passengerId) {
    sendResponse(false, 'Passenger ID is required.');
}

// Sanitize input
$firstName = sanitizeInput($data['first-name'] ?? '');
$lastName = sanitizeInput($data['last-name'] ?? '');
$email = sanitizeInput($data['email'] ?? '');
$passportNumber = sanitizeInput($data['passport-number'] ?? '');

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