<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

require_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Get query parameters
$bookingId = $_GET['id'] ?? null;
$status = $_GET['status'] ?? null;
$date = $_GET['date'] ?? null;

if ($bookingId) {
    // Get single booking with details
    $query = "SELECT 
                b.*,
                f.FlightNumber,
                al.AirlineCode,
                CONCAT(p.FirstName, ' ', p.LastName) AS PassengerName,
                p.PassportNumber
              FROM Booking b
              JOIN Flight f ON b.FlightID = f.FlightID
              JOIN Airline al ON f.AirlineCode = al.AirlineCode
              JOIN Passenger p ON b.PassengerID = p.PassengerID
              WHERE b.BookingID = ?";
    
    $stmt = $db->prepare($query);
    $stmt->execute([$bookingId]);
    
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($booking) {
        echo json_encode($booking);
    } else {
        http_response_code(404);
        echo json_encode(['message' => 'Booking not found']);
    }
} else {
    // Get all bookings with optional filters
    $query = "SELECT 
                b.*,
                f.FlightNumber,
                al.AirlineCode,
                CONCAT(p.FirstName, ' ', p.LastName) AS PassengerName,
                p.PassportNumber
              FROM Booking b
              JOIN Flight f ON b.FlightID = f.FlightID
              JOIN Airline al ON f.AirlineCode = al.AirlineCode
              JOIN Passenger p ON b.PassengerID = p.PassengerID";
    
    $conditions = [];
    $params = [];
    
    if ($status) {
        $conditions[] = "b.PaymentStatus = ?";
        $params[] = $status;
    }
    
    if ($date) {
        $conditions[] = "DATE(b.BookingDate) = ?";
        $params[] = $date;
    }
    
    if (!empty($conditions)) {
        $query .= " WHERE " . implode(" AND ", $conditions);
    }
    
    $query .= " ORDER BY b.BookingDate DESC";
    
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    
    $bookings = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $bookings[] = $row;
    }
    
    echo json_encode($bookings);
}
?>