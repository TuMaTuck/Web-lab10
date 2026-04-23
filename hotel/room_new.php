<?php
// room_new.php - Додавання нової кімнати
require_once 'db.php';

header('Content-Type: application/json');

if (empty($_POST['name']) || empty($_POST['capacity'])) {
    echo json_encode(['result' => 'ERROR', 'message' => 'Всі поля обов\'язкові']);
    exit;
}

try {
    $stmt = $db->prepare("INSERT INTO rooms (name, capacity, status) VALUES (:name, :capacity, :status)");
    $stmt->bindParam(':name', $_POST['name']);
    $stmt->bindParam(':capacity', $_POST['capacity']);
    $stmt->bindParam(':status', $_POST['status']);
    $stmt->execute();
    
    echo json_encode(['result' => 'OK', 'message' => 'Кімнату додано', 'id' => $db->lastInsertId()]);
    
} catch(PDOException $e) {
    echo json_encode(['result' => 'ERROR', 'message' => $e->getMessage()]);
}
?>