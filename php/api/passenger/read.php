<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

require_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Get query parameters
$passengerId = $_GET['id'] ?? null;
$searchTerm = $_GET['search'] ?? null;

if ($passengerId) {
    // Get single passenger
    $query = "SELECT * FROM Passenger WHERE PassengerID = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$passengerId]);
    
    $passenger = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($passenger) {
        echo json_encode($passenger);
    } else {
        http_response_code(404);
        echo json_encode(['message' => 'Passenger not found']);
    }
} else {
    // Get all passengers with optional search
    $query = "SELECT * FROM Passenger";
    $params = [];
    
    if ($searchTerm) {
        $query .= " WHERE 
            FirstName LIKE ? OR 
            LastName LIKE ? OR 
            Email LIKE ? OR 
            PassportNumber LIKE ?";
        $searchParam = "%$searchTerm%";
        $params = array_fill(0, 4, $searchParam);
    }
    
    $query .= " ORDER BY LastName, FirstName";
    
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    
    $passengers = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $passengers[] = $row;
    }
    
    echo json_encode($passengers);
}
?>