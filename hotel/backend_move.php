<?php
// backend_move.php - Обробка переміщення бронювань
require_once 'db.php';

header('Content-Type: application/json');

class Result {}

$stmt = $db->prepare("SELECT * FROM reservations WHERE NOT ((end <= :start) OR (start >= :end)) AND id <> :id AND room_id = :resource");
$stmt->bindParam(':start', $_POST['newStart']);
$stmt->bindParam(':end', $_POST['newEnd']);
$stmt->bindParam(':id', $_POST['id']);
$stmt->bindParam(':resource', $_POST['newResource']);
$stmt->execute();

if ($stmt->rowCount() > 0) {
    $response = new Result();
    $response->result = 'Error';
    $response->message = 'Це бронювання накладається з існуючим!';
    echo json_encode($response);
    exit;
}

$stmt = $db->prepare("UPDATE reservations SET start = :start, end = :end, room_id = :resource WHERE id = :id");
$stmt->bindParam(':id', $_POST['id']);
$stmt->bindParam(':start', $_POST['newStart']);
$stmt->bindParam(':end', $_POST['newEnd']);
$stmt->bindParam(':resource', $_POST['newResource']);
$stmt->execute();

$response = new Result();
$response->result = 'OK';
$response->message = 'Update successful';

echo json_encode($response);
?>