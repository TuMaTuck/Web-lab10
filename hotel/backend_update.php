<?php
require_once 'db.php';

header('Content-Type: application/json');

// Перевірка обов'язкових полів
if (empty($_POST['id']) || empty($_POST['name']) || empty($_POST['start']) || empty($_POST['end']) || empty($_POST['room'])) {
    echo json_encode(['result' => 'ERROR', 'message' => 'Всі поля обов\'язкові']);
    exit;
}

try {
    $stmt = $db->prepare("UPDATE reservations SET name = :name, start = :start, end = :end, room_id = :room, status = :status, paid = :paid WHERE id = :id");
    $stmt->bindParam(':id', $_POST['id']);
    $stmt->bindParam(':name', $_POST['name']);
    $stmt->bindParam(':start', $_POST['start']);
    $stmt->bindParam(':end', $_POST['end']);
    $stmt->bindParam(':room', $_POST['room']);
    $stmt->bindParam(':status', $_POST['status']);
    $stmt->bindParam(':paid', $_POST['paid']);
    $stmt->execute();

    class Result {}
    $response = new Result();
    $response->result = 'OK';
    $response->message = 'Update successful';

    echo json_encode($response);
} catch(PDOException $e) {
    echo json_encode(['result' => 'ERROR', 'message' => $e->getMessage()]);
}
?>