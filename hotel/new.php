<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Нове бронювання</title>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 20px; }
        .container { background: white; max-width: 400px; margin: 0 auto; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h2 { margin-top: 0; color: #333; }
        label { display: block; margin: 10px 0 5px; font-weight: bold; }
        input, select { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        button { margin-top: 15px; padding: 10px 15px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; width: 100%; }
        button:hover { background: #218838; }
        .cancel { background: #6c757d; margin-top: 10px; }
        .cancel:hover { background: #5a6268; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Нове бронювання</h2>
        <form id="f" action="backend_create.php" method="POST">
            <label>Ім'я клієнта:</label>
            <input type="text" id="name" name="name" required>
            
            <label>Дата заїзду:</label>
            <input type="datetime-local" id="start" name="start" value="<?php echo isset($_GET['start']) ? date('Y-m-d\TH:i', strtotime($_GET['start'])) : ''; ?>" required>
            
            <label>Дата виїзду:</label>
            <input type="datetime-local" id="end" name="end" value="<?php echo isset($_GET['end']) ? date('Y-m-d\TH:i', strtotime($_GET['end'])) : ''; ?>" required>
            
            <label>Кімната:</label>
            <select id="room" name="room" required>
                <?php
                require_once 'db.php';
                $rooms = $db->query("SELECT * FROM rooms ORDER BY name");
                $selectedRoom = isset($_GET['resource']) ? (int)$_GET['resource'] : 0;
                foreach ($rooms as $room) {
                    $selected = ($selectedRoom == $room['id']) ? 'selected' : '';
                    echo "<option value='{$room['id']}' $selected>{$room['name']} ({$room['capacity']} місць)</option>";
                }
                ?>
            </select>
            
            <button type="submit">Зберегти</button>
            <button type="button" class="cancel" onclick="closeModal()">Скасувати</button>
        </form>
    </div>
    
    <script>
        function closeModal(result) {
            if (parent && parent.DayPilot && parent.DayPilot.ModalStatic) {
                parent.DayPilot.ModalStatic.close(result);
            }
        }
        
        $("#f").submit(function(e) {
            e.preventDefault();
            $.post($(this).attr("action"), $(this).serialize(), function(result) {
                closeModal(result);
            }, "json");
            return false;
        });
        
        $(document).ready(function() {
            $("#name").focus();
        });
    </script>
</body>
</html>