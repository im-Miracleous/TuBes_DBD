<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

require_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Get query parameters
$aircraftId = $_GET['id'] ?? null;
$searchTerm = $_GET['search'] ?? null;

if ($aircraftId) {
    // Get single aircraft with airline details
    $query = "SELECT a.*, al.AirlineName 
              FROM Aircraft a
              LEFT JOIN Airline al ON a.AirlineCode = al.AirlineCode
              WHERE a.AircraftID = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$aircraftId]);
    
    $aircraft = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($aircraft) {
        echo json_encode($aircraft);
    } else {
        http_response_code(404);
        echo json_encode(['message' => 'Aircraft not found']);
    }
} else {
    // Get all aircraft with optional search
    $query = "SELECT a.*, al.AirlineName 
              FROM Aircraft a
              LEFT JOIN Airline al ON a.AirlineCode = al.AirlineCode";
    $params = [];
    
    if ($searchTerm) {
        $query .= " WHERE 
            a.AircraftType LIKE ? OR 
            a.RegistrationNumber LIKE ? OR 
            al.AirlineName LIKE ? OR 
            al.AirlineCode LIKE ?";
        $searchParam = "%$searchTerm%";
        $params = array_fill(0, 4, $searchParam);
    }
    
    $query .= " ORDER BY a.AircraftID";
    
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    
    $aircraft = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $aircraft[] = $row;
    }
    
    echo json_encode($aircraft);
}
?>