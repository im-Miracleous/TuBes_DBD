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
    // Check if email already exists
    $emailCheckQuery = "SELECT PassengerID FROM Passenger WHERE Email = ?";
    $emailCheckStmt = $db->prepare($emailCheckQuery);
    $emailCheckStmt->execute([$email]);
    
    if ($emailCheckStmt->rowCount() > 0) {
        sendResponse(false, 'Email already registered.');
    }
    
    // Check if passport number already exists
    $passportCheckQuery = "SELECT PassengerID FROM Passenger WHERE PassportNumber = ?";
    $passportCheckStmt = $db->prepare($passportCheckQuery);
    $passportCheckStmt->execute([$passportNumber]);
    
    if ($passportCheckStmt->rowCount() > 0) {
        sendResponse(false, 'Passport number already registered.');
    }
    
    // Insert new passenger
    $query = "INSERT INTO Passenger (
                FirstName, 
                LastName, 
                Email, 
                PassportNumber
              ) VALUES (?, ?, ?, ?)";
    
    $stmt = $db->prepare($query);
    $success = $stmt->execute([
        $firstName,
        $lastName,
        $email,
        $passportNumber
    ]);
    
    if ($success) {
        sendResponse(true, 'Passenger created successfully.', [
            'PassengerID' => $db->lastInsertId()
        ]);
    } else {
        sendResponse(false, 'Unable to create passenger.');
    }
} catch (PDOException $e) {
    sendResponse(false, 'Database error: ' . $e->getMessage());
}
?>