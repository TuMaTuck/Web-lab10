<?php
// backend_rooms.php - Повертає JSON список кімнат з фільтрацією
require_once 'db.php';

header('Content-Type: application/json');

try {
    $capacity = isset($_POST['capacity']) ? $_POST['capacity'] : (isset($_GET['capacity']) ? $_GET['capacity'] : 0);
    
    if ($capacity == 0) {
        $stmt = $db->prepare("SELECT * FROM rooms ORDER BY name");
    } else {
        $stmt = $db->prepare("SELECT * FROM rooms WHERE capacity = :capacity ORDER BY name");
        $stmt->bindParam(':capacity', $capacity);
    }
    
    $stmt->execute();
    $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $result = [];
    foreach($rooms as $room) {
        $result[] = [
            'id' => (string)$room['id'],
            'name' => $room['name'],
            'capacity' => (int)$room['capacity'],
            'status' => $room['status']
        ];
    }
    
    echo json_encode($result);
    
} catch(PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>