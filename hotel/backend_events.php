<?php
// backend_events.php - Повертає список бронювань у форматі JSON
require_once 'db.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    // Отримуємо параметри (для GET або POST)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $start = isset($_POST['start']) ? $_POST['start'] : date('Y-m-d');
        $end = isset($_POST['end']) ? $_POST['end'] : date('Y-m-d', strtotime('+30 days'));
    } else {
        $start = isset($_GET['start']) ? $_GET['start'] : date('Y-m-d');
        $end = isset($_GET['end']) ? $_GET['end'] : date('Y-m-d', strtotime('+30 days'));
    }
    
    $stmt = $db->prepare("SELECT * FROM reservations WHERE NOT ((end <= :start) OR (start >= :end)) ORDER BY start");
    $stmt->bindParam(':start', $start);
    $stmt->bindParam(':end', $end);
    $stmt->execute();
    $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $result = array();
    foreach($reservations as $row) {
        $result[] = array(
            'id' => (string)$row['id'],
            'text' => $row['name'],
            'start' => $row['start'],
            'end' => $row['end'],
            'resource' => (string)$row['room_id'],
            'status' => $row['status'],
            'paid' => (int)$row['paid']
        );
    }
    
    echo json_encode($result);
    
} catch(PDOException $e) {
    echo json_encode(array('error' => $e->getMessage()));
}
?>