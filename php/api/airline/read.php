<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

require_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Get query parameters
$airlineCode = $_GET['code'] ?? null;
$searchTerm = $_GET['search'] ?? null;

if ($airlineCode) {
    // Get single airline
    $query = "SELECT * FROM Airline WHERE AirlineCode = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$airlineCode]);
    
    $airline = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($airline) {
        echo json_encode($airline);
    } else {
        http_response_code(404);
        echo json_encode(['message' => 'Airline not found']);
    }
} else {
    // Get all airlines with optional search
    $query = "SELECT * FROM Airline";
    $params = [];
    
    if ($searchTerm) {
        $query .= " WHERE 
            AirlineCode LIKE ? OR 
            AirlineName LIKE ? OR 
            ContactNumber LIKE ? OR 
            OperatingRegion LIKE ?";
        $searchParam = "%$searchTerm%";
        $params = array_fill(0, 4, $searchParam);
    }
    
    $query .= " ORDER BY AirlineName";
    
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    
    $airlines = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $airlines[] = $row;
    }
    
    echo json_encode($airlines);
}
?>