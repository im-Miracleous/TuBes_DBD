<?php
include '../config/database.php';

$db = new Database();
$conn = $db->getConnection();

$stats = [
    'upcoming_flights' => 0,
    'total_passengers' => 0,
    'active_bookings' => 0,
    'total_airlines' => 0,
    'recent_flights' => []
];

// Example queries (adjust as needed)
$stats['upcoming_flights'] = $conn->query("SELECT COUNT(*) FROM flights WHERE departure_time > NOW()")->fetchColumn();
$stats['total_passengers'] = $conn->query("SELECT COUNT(*) FROM passengers")->fetchColumn();
$stats['active_bookings'] = $conn->query("SELECT COUNT(*) FROM bookings WHERE status = 'active'")->fetchColumn();
$stats['total_airlines'] = $conn->query("SELECT COUNT(*) FROM airlines")->fetchColumn();

$stmt = $conn->query("SELECT f.flight_number, a.name AS airline, f.departure_time, f.arrival_time, f.status
    FROM flights f
    JOIN airlines a ON f.airline_id = a.id
    ORDER BY f.departure_time DESC
    LIMIT 5");
$stats['recent_flights'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($stats);
?>