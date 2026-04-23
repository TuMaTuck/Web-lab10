<?php
require_once 'db.php';

header('Content-Type: application/json');

// Перевірка наявності ID
if (empty($_POST['id'])) {
    echo json_encode(['result' => 'ERROR', 'message' => 'ID бронювання не вказано']);
    exit;
}

try {
    $stmt = $db->prepare("DELETE FROM reservations WHERE id = :id");
    $stmt->bindParam(':id', $_POST['id']);
    $stmt->execute();

    class Result {}
    $response = new Result();
    $response->result = 'OK';
    $response->message = 'Delete successful';

    echo json_encode($response);
} catch(PDOException $e) {
    echo json_encode(['result' => 'ERROR', 'message' => $e->getMessage()]);
}
?>
