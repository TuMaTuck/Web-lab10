<?php
require_once 'db.php';

header('Content-Type: application/json');

// Перевірка обов'язкових полів
if (empty($_POST['name']) || empty($_POST['start']) || empty($_POST['end']) || empty($_POST['room'])) {
    echo json_encode(['result' => 'ERROR', 'message' => 'Всі поля обов\'язкові']);
    exit;
}

try {
    $stmt = $db->prepare("INSERT INTO reservations (name, start, end, room_id, status, paid) VALUES (:name, :start, :end, :room, 'New', 0)");
    $stmt->bindParam(':start', $_POST['start']);
    $stmt->bindParam(':end', $_POST['end']);
    $stmt->bindParam(':name', $_POST['name']);
    $stmt->bindParam(':room', $_POST['room']);
    $stmt->execute();

    class Result {}
    $response = new Result();
    $response->result = 'OK';
    $response->message = 'Created with id: ' . $db->lastInsertId();
    $response->id = $db->lastInsertId();

    echo json_encode($response);
} catch(PDOException $e) {
    echo json_encode(['result' => 'ERROR', 'message' => $e->getMessage()]);
}
?>