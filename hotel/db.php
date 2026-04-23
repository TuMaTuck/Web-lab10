<?php
// db.php - Підключення до бази даних
$host = "127.0.0.1";
$port = 3306;
$username = "root";
$password = "";
$database = "hotel_booking";

try {
    $db = new PDO("mysql:host=$host;port=$port;charset=utf8mb4",
                   $username,
                   $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->exec("USE `$database`");
    
} catch(PDOException $e) {
    die("Помилка підключення: " . $e->getMessage());
}
?>