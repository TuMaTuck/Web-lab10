<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Редагування бронювання</title>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 20px; }
        .container { background: white; max-width: 400px; margin: 0 auto; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h2 { margin-top: 0; color: #333; }
        label { display: block; margin: 10px 0 5px; font-weight: bold; }
        input, select { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        button { margin-top: 15px; padding: 10px 15px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; width: 100%; }
        button:hover { background: #0069d9; }
        .delete { background: #dc3545; }
        .delete:hover { background: #c82333; }
        .cancel { background: #6c757d; margin-top: 10px; }
        .cancel:hover { background: #5a6268; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Редагування бронювання</h2>
        <?php
        require_once 'db.php';
        
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if (!$id) die("Invalid ID");
        
        $stmt = $db->prepare("SELECT * FROM reservations WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $reservation = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$reservation) die("Reservation not found");
        ?>
        
        <form id="f" action="backend_update.php" method="POST">
            <input type="hidden" id="id" name="id" value="<?php echo $reservation['id']; ?>">
            
            <label>Ім'я клієнта:</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($reservation['name']); ?>" required>
            
            <label>Дата заїзду:</label>
            <input type="datetime-local" id="start" name="start" value="<?php echo date('Y-m-d\TH:i', strtotime($reservation['start'])); ?>" required>
            
            <label>Дата виїзду:</label>
            <input type="datetime-local" id="end" name="end" value="<?php echo date('Y-m-d\TH:i', strtotime($reservation['end'])); ?>" required>
            
            <label>Кімната:</label>
            <select id="room" name="room" required>
                <?php
                $rooms = $db->query("SELECT * FROM rooms ORDER BY name");
                foreach ($rooms as $room) {
                    $selected = ($reservation['room_id'] == $room['id']) ? 'selected' : '';
                    echo "<option value='{$room['id']}' $selected>{$room['name']} ({$room['capacity']} місць)</option>";
                }
                ?>
            </select>
            
            <label>Статус:</label>
            <select id="status" name="status">
                <?php
                $statuses = ["New", "Confirmed", "Arrived", "CheckedOut", "Expired"];
                foreach ($statuses as $status) {
                    $selected = ($reservation['status'] == $status) ? 'selected' : '';
                    echo "<option value='$status' $selected>$status</option>";
                }
                ?>
            </select>
            
            <label>Оплата (%):</label>
            <select id="paid" name="paid">
                <?php
                $paidOptions = [0, 25, 50, 75, 100];
                foreach ($paidOptions as $option) {
                    $selected = ($reservation['paid'] == $option) ? 'selected' : '';
                    echo "<option value='$option' $selected>$option%</option>";
                }
                ?>
            </select>
            
            <button type="submit">💾 Зберегти</button>
            <button type="button" class="delete" onclick="deleteReservation()">🗑️ Видалити</button>
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
        
        function deleteReservation() {
            if (confirm("Ви впевнені, що хочете видалити це бронювання?")) {
                var id = $("#id").val();
                $.post("backend_delete.php", { id: id }, function(result) {
                    closeModal(result);
                }, "json");
            }
        }
        
        $(document).ready(function() {
            $("#name").focus();
        });
    </script>
</body>
</html>