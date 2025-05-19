<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

require_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Get query parameters
$baggageId = $_GET['id'] ?? null;
$status = $_GET['status'] ?? null;
$type = $_GET['type'] ?? null;

if ($baggageId) {
    // Get single baggage with booking details
    $query = "SELECT b.*, 
                     CONCAT(p.FirstName, ' ', p.LastName) AS PassengerName
              FROM Baggage b
              JOIN Booking bk ON b.BookingID = bk.BookingID
              JOIN Passenger p ON bk.PassengerID = p.PassengerID
              WHERE b.BaggageID = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$baggageId]);
    
    $baggage = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($baggage) {
        echo json_encode($baggage);
    } else {
        http_response_code(404);
        echo json_encode(['message' => 'Baggage not found']);
    }
} else {
    // Get all baggage with optional filters
    $query = "SELECT b.*, 
                     CONCAT(p.FirstName, ' ', p.LastName) AS PassengerName
              FROM Baggage b
              JOIN Booking bk ON b.BookingID = bk.BookingID
              JOIN Passenger p ON bk.PassengerID = p.PassengerID";
    
    $conditions = [];
    $params = [];
    
    if ($status) {
        $conditions[] = "b.Status = ?";
        $params[] = $status;
    }
    
    if ($type) {
        $conditions[] = "b.BaggageType = ?";
        $params[] = $type;
    }
    
    if (!empty($conditions)) {
        $query .= " WHERE " . implode(" AND ", $conditions);
    }
    
    $query .= " ORDER BY b.BaggageID DESC";
    
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    
    $baggage = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $baggage[] = $row;
    }
    
    echo json_encode($baggage);
}
?>