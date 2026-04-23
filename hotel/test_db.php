<?php
// test_db.php - проста перевірка бази даних

// Підключаємо файл з підключенням до БД
require_once 'db.php';

echo "<!DOCTYPE html>";
echo "<html>";
echo "<head><title>Тест підключення до БД</title>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    h1 { color: #333; }
    h2 { color: #555; margin-top: 30px; }
    table { border-collapse: collapse; background: white; width: 100%; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background: #4CAF50; color: white; }
    tr:nth-child(even) { background: #f9f9f9; }
    .success { background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin: 10px 0; }
</style>";
echo "</head>";
echo "<body>";

echo "<h1>🏨 Перевірка бази даних готелю</h1>";

// Перевіряємо, чи існує з'єднання
if (isset($db)) {
    echo "<div class='success'>✅ Підключення до бази даних успішне!</div>";
} else {
    echo "<div class='error'>❌ Помилка підключення до бази даних!</div>";
    exit;
}

// Перевірка таблиці rooms
try {
    $stmt = $db->query("SELECT COUNT(*) as count FROM rooms");
    $count = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<div class='success'>✅ Таблиця 'rooms' існує. Кількість записів: " . $count['count'] . "</div>";
} catch(PDOException $e) {
    echo "<div class='error'>❌ Таблиця 'rooms' не знайдена! Потрібно створити таблиці.</div>";
    echo "<div class='error'>Помилка: " . $e->getMessage() . "</div>";
}

// Виведення кімнат
try {
    $stmt = $db->query("SELECT * FROM rooms ORDER BY id");
    $rooms = $stmt->fetchAll();
    
    if (count($rooms) > 0) {
        echo "<h2>📋 Список кімнат:</h2>";
        echo "<table>";
        echo "<tr><th>ID</th><th>Назва кімнати</th><th>Місткість</th><th>Статус</th></tr>";
        foreach($rooms as $room) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($room['id']) . "</td>";
            echo "<td>" . htmlspecialchars($room['name']) . "</td>";
            echo "<td>" . htmlspecialchars($room['capacity']) . " місць" . "</td>";
            echo "<td>" . htmlspecialchars($room['status']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<div class='error'>⚠️ Немає даних у таблиці 'rooms'. Додайте тестові дані!</div>";
    }
} catch(PDOException $e) {
    echo "<div class='error'>❌ Помилка читання таблиці 'rooms': " . $e->getMessage() . "</div>";
}

// Перевірка таблиці reservations
try {
    $stmt = $db->query("SELECT COUNT(*) as count FROM reservations");
    $count = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<div class='success'>✅ Таблиця 'reservations' існує. Кількість записів: " . $count['count'] . "</div>";
} catch(PDOException $e) {
    echo "<div class='error'>❌ Таблиця 'reservations' не знайдена! Потрібно створити таблиці.</div>";
}

// Виведення бронювань
try {
    $stmt = $db->query("
        SELECT r.*, rooms.name as room_name 
        FROM reservations r 
        LEFT JOIN rooms ON r.room_id = rooms.id 
        ORDER BY r.start
    ");
    $reservations = $stmt->fetchAll();
    
    if (count($reservations) > 0) {
        echo "<h2>📅 Список бронювань:</h2>";
        echo "<table>";
        echo "<tr><th>ID</th><th>Ім'я клієнта</th><th>Дата заїзду</th><th>Дата виїзду</th><th>Кімната</th><th>Статус</th><th>Оплата</th></tr>";
        foreach($reservations as $res) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($res['id']) . "</td>";
            echo "<td>" . htmlspecialchars($res['name']) . "</td>";
            echo "<td>" . htmlspecialchars($res['start']) . "</td>";
            echo "<td>" . htmlspecialchars($res['end']) . "</td>";
            echo "<td>" . htmlspecialchars($res['room_name'] ?? $res['room_id']) . "</td>";
            echo "<td>" . htmlspecialchars($res['status']) . "</td>";
            echo "<td>" . htmlspecialchars($res['paid']) . "%" . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<div class='error'>⚠️ Немає даних у таблиці 'reservations'. Додайте тестові бронювання!</div>";
    }
} catch(PDOException $e) {
    echo "<div class='error'>❌ Помилка читання таблиці 'reservations': " . $e->getMessage() . "</div>";
}

echo "<hr>";
echo "<h3>🔧 Інформація про сервер:</h3>";
echo "<ul>";
echo "<li>PHP версія: " . phpversion() . "</li>";
echo "<li>Драйвер PDO MySQL: " . (extension_loaded('pdo_mysql') ? '✅ Так' : '❌ Ні') . "</li>";
echo "<li>Шлях до файлу: " . __FILE__ . "</li>";
echo "</ul>";

echo "</body>";
echo "</html>";
?>