<?php

require 'Database.php';

define('MYSQL_HOST', 'mysql');
define('MYSQL_USER', $_ENV['MYSQL_USER']);
define('MYSQL_PASSWORD', $_ENV['MYSQL_PASSWORD']);
define('MYSQL_DB', $_ENV['MYSQL_DATABASE']);

$db = Database::getInstance(MYSQL_HOST, MYSQL_DB, MYSQL_USER, MYSQL_PASSWORD);
$conn = $db->getConnection();

$agenciesQuery = $conn->query("SELECT * FROM agencies");
$agencies = $agenciesQuery->fetchAll(PDO::FETCH_ASSOC);

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $agency_id = $_POST['agency_id'];
    $active = isset($_POST['active']) ? 1 : 0;
    $notification_text = $_POST['notification_text'];

    $stmt = $conn->prepare("INSERT INTO rules (name, agency_id, active, notification_text) VALUES (:name, :agency_id, :active, :notification_text)");
    $stmt->execute([
        ':name' => $name,
        ':agency_id' => $agency_id,
        ':active' => $active,
        ':notification_text' => $notification_text
    ]);

    echo "Правило успешно добавлено!";
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Добавить правило</title>
</head>
<body>
<ul>
    <li style="display: inline; margin-right: 15px;"><a href="create_condition.php">Добавить условие</a></li>
    <li style="display: inline; margin-right: 15px;"><a href="index.php">Главная</a></li>
</ul>

<h1>Добавить правило</h1>
<form method="post">
    <label for="name">Название правила:</label>
    <input type="text" id="name" name="name" required><br>

    <label for="agency_id">ID агентства:</label>
    <select id="agency_id" name="agency_id" required>
        <?php foreach ($agencies as $agency): ?>
            <option value="<?= htmlspecialchars($agency['id']) ?>"><?= htmlspecialchars($agency['name']) ?></option>
        <?php endforeach; ?>
    </select><br>



    <label for="active">Активное:</label>
    <input type="checkbox" id="active" name="active"><br>

    <label for="notification_text">Текст уведомления:</label>
    <textarea id="notification_text" name="notification_text" required></textarea><br>

    <button type="submit">Добавить правило</button>
</form>

<a href="create_condition.php">Добавить условие</a>
</body>
</html>
