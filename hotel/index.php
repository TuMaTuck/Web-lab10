<?php
// index.php - Головний файл застосунку
require_once 'db.php';

if (!$db) {
    die("Помилка підключення до бази даних");
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Система бронювання кімнат готелю</title>
    
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="js/daypilot-all.min.js"></script>
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #e9ecef; padding: 20px; }
        .container { max-width: 1400px; margin: 0 auto; background: #fff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); overflow: hidden; }
        .toolbar { padding: 15px 20px; background: #f8f9fa; border-bottom: 1px solid #dee2e6; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; }
        .toolbar h2 { color: #333; font-size: 1.2em; }
        .filter-group { display: flex; align-items: center; gap: 10px; }
        .filter-group label { font-weight: bold; color: #555; }
        .filter-select { padding: 6px 12px; border: 1px solid #ced4da; border-radius: 5px; background: #fff; cursor: pointer; }
        .btn { background: #007bff; color: white; border: none; padding: 8px 16px; border-radius: 5px; cursor: pointer; margin-left: 5px; }
        .btn:hover { background: #0069d9; }
        .btn-success { background: #28a745; }
        .btn-success:hover { background: #218838; }
        #scheduler { width: 100%; min-height: 600px; }
        
        /* Стилі для статусів кімнат */
        .status_dirty { color: #dc3545 !important; font-weight: bold; }
        .status_cleanup { color: #fd7e14 !important; font-weight: bold; }
        
        .scheduler_default_rowheader_inner { border-right: 1px solid #ccc; }
        .scheduler_default_rowheader.scheduler_default_rowheadercol2 { background: #fff; }
        .scheduler_default_rowheadercol2 .scheduler_default_rowheader_inner {
            top: 2px; bottom: 2px; left: 2px; background-color: transparent;
            border-left: 5px solid #28a745; border-right: 0px none;
        }
        .status_dirty.scheduler_default_rowheadercol2 .scheduler_default_rowheader_inner { border-left: 5px solid #dc3545; }
        .status_cleanup.scheduler_default_rowheadercol2 .scheduler_default_rowheader_inner { border-left: 5px solid #fd7e14; }
        
        footer { background: #2d3748; color: #a0aec0; text-align: center; padding: 15px; margin-top: 20px; }
        
        /* Модальне вікно для кімнат */
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); }
        .modal-content { background: #fff; margin: 10% auto; padding: 25px; width: 90%; max-width: 400px; border-radius: 12px; position: relative; }
        .close { position: absolute; right: 20px; top: 15px; font-size: 24px; cursor: pointer; }
        .modal-content h3 { margin-bottom: 20px; }
        .modal-content label { display: block; margin: 12px 0 4px; font-weight: bold; }
        .modal-content input, .modal-content select { width: 100%; padding: 8px 12px; border: 1px solid #ced4da; border-radius: 6px; }
        .modal-buttons { display: flex; gap: 10px; margin-top: 20px; }
        .modal-buttons button { flex: 1; padding: 10px; border: none; border-radius: 6px; cursor: pointer; }
        .btn-save { background: #28a745; color: white; }
        .btn-cancel { background: #6c757d; color: white; }
    </style>
</head>
<body>
    <div class="container">
        <div class="toolbar">
            <h2>🏨 Система бронювання кімнат готелю</h2>
            <div class="filter-group">
                <label>Показати кімнати:</label>
                <select id="capacityFilter" class="filter-select">
                    <option value="0">Всі кімнати</option>
                    <option value="1">Одномісні (1 bed)</option>
                    <option value="2">Двомісні (2 beds)</option>
                    <option value="3">Трьохмісні (3 beds)</option>
                    <option value="4">Сімейні (4+ beds)</option>
                </select>
                <button class="btn" onclick="loadResources(); loadEvents();">🔄 Оновити</button>
                <button class="btn btn-success" id="addRoomBtn">➕ Нова кімната</button>
            </div>
        </div>
        <div id="scheduler"></div>
    </div>
    
    <footer>
        <address>© Автор лабораторної роботи: студент спеціальності G7 «Робототехніка» Мельников Дмитро Олександрович</address>
    </footer>
    
    <!-- Модальне вікно для додавання кімнати -->
    <div id="roomModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3>➕ Нова кімната</h3>
            <form id="roomForm">
                <label>Назва кімнати:</label>
                <input type="text" id="roomName" required placeholder="Наприклад: Люкс">
                <label>Місткість (кількість місць):</label>
                <select id="roomCapacity" required>
                    <option value="1">1 місце</option>
                    <option value="2">2 місця</option>
                    <option value="3">3 місця</option>
                    <option value="4">4 місця</option>
                    <option value="5">5 місць</option>
                    <option value="6">6 місць</option>
                </select>
                <label>Статус:</label>
                <select id="roomStatus" required>
                    <option value="Ready">Ready (готово)</option>
                    <option value="Cleanup">Cleanup (прибирання)</option>
                    <option value="Dirty">Dirty (брудна)</option>
                </select>
                <div class="modal-buttons">
                    <button type="submit" class="btn-save">Зберегти</button>
                    <button type="button" class="btn-cancel" onclick="closeRoomModal()">Скасувати</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        var scheduler;
        
        function initScheduler() {
            if (typeof DayPilot === 'undefined') {
                document.getElementById('scheduler').innerHTML = '<div style="padding: 50px; text-align: center; color: red;">❌ Помилка: Бібліотека DayPilot не завантажилась</div>';
                return;
            }
            
            scheduler = new DayPilot.Scheduler("scheduler");
            
            // Налаштування
            scheduler.startDate = DayPilot.Date.today().firstDayOfMonth();
            scheduler.days = DayPilot.Date.today().daysInMonth();
            scheduler.scale = "Day";
            scheduler.timeHeaders = [
                { groupBy: "Month", format: "MMMM yyyy" },
                { groupBy: "Day", format: "d" }
            ];
            
            scheduler.rowHeaderColumns = [
                { title: "Room", width: 120 },
                { title: "Capacity", width: 80 },
                { title: "Status", width: 100 }
            ];
            
            // Заборона накладання бронювань
            scheduler.allowEventOverlap = false;
            
            // Видалення бронювання через хрестик
            scheduler.eventDeleteHandling = "Update";
            
            // Рендеринг заголовків рядків
            scheduler.onBeforeResHeaderRender = function(args) {
                var beds = function(count) { return count + " bed" + (count > 1 ? "s" : ""); };
                if (args.resource) {
                    args.resource.columns[0].html = args.resource.name || '';
                    args.resource.columns[1].html = beds(args.resource.capacity) || '';
                    args.resource.columns[2].html = args.resource.status || '';
                    switch (args.resource.status) {
                        case "Dirty": args.resource.cssClass = "status_dirty"; break;
                        case "Cleanup": args.resource.cssClass = "status_cleanup"; break;
                        default: args.resource.cssClass = "";
                    }
                }
            };
            
            // Рендеринг подій (бронювань) з додатковою інформацією
            scheduler.onBeforeEventRender = function(args) {
                var start = new DayPilot.Date(args.e.start);
                var end = new DayPilot.Date(args.e.end);
                var today = DayPilot.Date.today();
                var now = new DayPilot.Date();
                
                // Основний текст бронювання
                args.e.html = args.e.text + " (" + start.toString("d") + " - " + end.toString("d") + ")";
                
                // Налаштування кольору та підказки залежно від статусу
                switch (args.e.status) {
                    case "New":
                        var in2days = today.addDays(1);
                        if (start < in2days) {
                            args.e.barColor = '#dc3545';
                            args.e.toolTip = 'Застаріле (не підтверджено вчасно)';
                        } else {
                            args.e.barColor = '#fd7e14';
                            args.e.toolTip = 'Новий';
                        }
                        break;
                    case "Confirmed":
                        var arrivalDeadline = today.addHours(18);
                        if (start < today || (start.getDatePart() === today.getDatePart() && now > arrivalDeadline)) {
                            args.e.barColor = '#dc3545';
                            args.e.toolTip = 'Пізнє прибуття';
                        } else {
                            args.e.barColor = '#28a745';
                            args.e.toolTip = "Підтверджено";
                        }
                        break;
                    case 'Arrived':
                        var checkoutDeadline = today.addHours(10);
                        if (end < today || (end.getDatePart() === today.getDatePart() && now > checkoutDeadline)) {
                            args.e.barColor = '#dc3545';
                            args.e.toolTip = "Пізній виїзд";
                        } else {
                            args.e.barColor = '#3498db';
                            args.e.toolTip = "Прибув";
                        }
                        break;
                    case 'CheckedOut':
                        args.e.barColor = '#95a5a6';
                        args.e.toolTip = "Перевірено";
                        break;
                    default:
                        args.e.toolTip = "Невизначений стан";
                }
                
                args.e.html = args.e.html + "<br><span style='color:gray; font-size:10px;'>" + args.e.toolTip + "</span>";
                
                // Індикатор оплати
                var paid = args.e.paid || 0;
                var paidColor = "#aaaaaa";
                args.e.areas = [
                    { bottom: 4, right: 4, html: "<div style='color:" + paidColor + "; font-size: 10pt;'>💵 " + paid + "%</div>" },
                    { left: 4, bottom: 4, right: 4, height: 3, html: "<div style='background-color:#3498db; height: 100%; width:" + paid + "%'></div>" }
                ];
            };
            
            // Обробка перетягування бронювання
            scheduler.onEventMoved = function(args) {
                $.post("backend_move.php", {
                    id: args.e.id(),
                    newStart: args.newStart.toString(),
                    newEnd: args.newEnd.toString(),
                    newResource: args.newResource
                }, function(data) {
                    if (data.result === 'OK') {
                        scheduler.message("✅ Переміщено успішно");
                    } else {
                        scheduler.message("❌ " + data.message);
                        loadEvents(); // Відновлюємо стан
                    }
                }, "json");
            };
            
            // Обробка видалення бронювання
            scheduler.onEventDeleted = function(args) {
                $.post("backend_delete.php", { id: args.e.id() }, function(data) {
                    if (data.result === 'OK') {
                        scheduler.message("🗑️ Бронювання видалено");
                        loadEvents();
                    } else {
                        scheduler.message("❌ Помилка видалення");
                    }
                }, "json");
            };
            
            // Створення нового бронювання
            scheduler.onTimeRangeSelected = function(args) {
                var modal = new DayPilot.Modal();
                modal.closed = function() {
                    scheduler.clearSelection();
                    if (this.result && this.result.result === "OK") loadEvents();
                };
                modal.showUrl("new.php?start=" + args.start + "&end=" + args.end + "&resource=" + args.resource);
            };
            
            // Редагування бронювання
            scheduler.onEventClick = function(args) {
                var modal = new DayPilot.Modal();
                modal.closed = function() {
                    if (this.result && this.result.result === "OK") loadEvents();
                };
                modal.showUrl("edit.php?id=" + args.e.id());
            };
            
            scheduler.init();
            loadResources();
            loadEvents();
        }
        
        // Завантаження кімнат з фільтром
        function loadResources() {
            $.post("backend_rooms.php", { capacity: $("#capacityFilter").val() }, function(data) {
                if (data && Array.isArray(data)) {
                    scheduler.resources = data;
                    scheduler.update();
                }
            }, "json").fail(function() { console.error("Помилка завантаження кімнат"); });
        }
        
        // Завантаження бронювань
        function loadEvents() {
            if (!scheduler.visibleStart) return;
            $.post("backend_events.php", {
                start: scheduler.visibleStart().toString(),
                end: scheduler.visibleEnd().toString()
            }, function(data) {
                if (data && Array.isArray(data)) {
                    scheduler.events.list = data;
                    scheduler.update();
                }
            }, "json").fail(function() { console.error("Помилка завантаження бронювань"); });
        }
        
        // Фільтрація при зміні
        $("#capacityFilter").change(function() { loadResources(); });
        
        // Модальне вікно для нової кімнати
        $("#addRoomBtn").click(function() { $("#roomModal").show(); });
        $(".close").click(function() { closeRoomModal(); });
        
        function closeRoomModal() { $("#roomModal").hide(); $("#roomForm")[0].reset(); }
        
        // Додавання нової кімнати
        $("#roomForm").submit(function(e) {
            e.preventDefault();
            $.post("room_new.php", {
                name: $("#roomName").val(),
                capacity: $("#roomCapacity").val(),
                status: $("#roomStatus").val()
            }, function(data) {
                if (data.result === 'OK') {
                    closeRoomModal();
                    loadResources();
                    scheduler.message("✅ Кімнату додано успішно");
                } else {
                    scheduler.message("❌ " + data.message);
                }
            }, "json");
        });
        
        $(document).ready(function() { initScheduler(); });
    </script>
</body>
</html>