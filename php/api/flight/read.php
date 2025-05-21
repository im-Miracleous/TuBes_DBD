<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

require_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Get query parameters
$flightId = $_GET['id'] ?? null;
$status = $_GET['status'] ?? null;
$date = $_GET['date'] ?? null;

if ($flightId) {
    // Get single flight
    $query = "SELECT 
                f.*, 
                al.AirlineName,
                a1.AirportName AS OriginName,
                a2.AirportName AS DestinationName
              FROM Flight f
              LEFT JOIN Airline al ON f.AirlineCode = al.AirlineCode
              LEFT JOIN Airport a1 ON f.OriginAirportCode = a1.AirportCode
              LEFT JOIN Airport a2 ON f.DestinationAirportCode = a2.AirportCode
              WHERE f.FlightID = ?";
    
    $stmt = $db->prepare($query);
    $stmt->execute([$flightId]);
    
    $flight = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($flight) {
        echo json_encode($flight);
    } else {
        http_response_code(404);
        echo json_encode(['message' => 'Flight not found']);
    }
} else {
    // Get all flights with optional filters
    $query = "SELECT 
                f.*, 
                al.AirlineName,
                a1.AirportName AS OriginName,
                a2.AirportName AS DestinationName
              FROM Flight f
              LEFT JOIN Airline al ON f.AirlineCode = al.AirlineCode
              LEFT JOIN Airport a1 ON f.OriginAirportCode = a1.AirportCode
              LEFT JOIN Airport a2 ON f.DestinationAirportCode = a2.AirportCode";
    
    $conditions = [];
    $params = [];
    
    if ($status) {
        $conditions[] = "f.Status = ?";
        $params[] = $status;
    }
    
    if ($date) {
        $conditions[] = "DATE(f.DepartureDateTime) = ?";
        $params[] = $date;
    }
    
    if (!empty($conditions)) {
        $query .= " WHERE " . implode(" AND ", $conditions);
    }
    
    $query .= " ORDER BY f.DepartureDateTime DESC";
    
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    
    $flights = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $flights[] = $row;
    }
    
    echo json_encode($flights);
}
?>