<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

require_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Get query parameters
$airportCode = $_GET['code'] ?? null;
$searchTerm = $_GET['search'] ?? null;

if ($airportCode) {
    // Get single airport
    $query = "SELECT * FROM Airport WHERE AirportCode = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$airportCode]);
    
    $airport = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($airport) {
        echo json_encode($airport);
    } else {
        http_response_code(404);
        echo json_encode(['message' => 'Airport not found']);
    }
} else {
    // Get all airports with optional search
    $query = "SELECT * FROM Airport";
    $params = [];
    
    if ($searchTerm) {
        $query .= " WHERE 
            AirportCode LIKE ? OR 
            AirportName LIKE ? OR 
            City LIKE ? OR 
            Country LIKE ?";
        $searchParam = "%$searchTerm%";
        $params = array_fill(0, 4, $searchParam);
    }
    
    $query .= " ORDER BY Country, City, AirportName";
    
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    
    $airports = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $airports[] = $row;
    }
    
    echo json_encode($airports);
}
?>