<?php

require 'Database.php';

define('MYSQL_HOST', 'mysql');
define('MYSQL_USER', $_ENV['MYSQL_USER']);
define('MYSQL_PASSWORD', $_ENV['MYSQL_PASSWORD']);
define('MYSQL_DB', $_ENV['MYSQL_DATABASE']);

$db = Database::getInstance(MYSQL_HOST, MYSQL_DB, MYSQL_USER, MYSQL_PASSWORD);
$conn = $db->getConnection();

// Получаем все правила для выбора
$rulesQuery = $conn->query("SELECT * FROM rules");
$rules = $rulesQuery->fetchAll(PDO::FETCH_ASSOC);

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rule_id = $_POST['rule_id'];
    $field_name = $_POST['field_name'];
    $operator = $_POST['operator'];
    $value = $_POST['value'];

    $stmt = $conn->prepare("INSERT INTO rule_conditions (rule_id, field_name, operator, value) VALUES (:rule_id, :field_name, :operator, :value)");
    $stmt->execute([
        ':rule_id' => $rule_id,
        ':field_name' => $field_name,
        ':operator' => $operator,
        ':value' => $value
    ]);

    echo "Условие успешно добавлено!";
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Добавить условие</title>
</head>
<body>
<ul>
    <li style="display: inline; margin-right: 15px;"><a href="create_rule.php">Добавить правило</a></li>
    <li style="display: inline; margin-right: 15px;"><a href="index.php">Главная</a></li>
</ul>

<h1>Добавить условие</h1>
<form method="post">
    <p>* - обязательное поле</p>
    <label for="rule_id">Выберите правило*:</label>
    <select id="rule_id" name="rule_id" required>
        <?php foreach ($rules as $rule): ?>
            <option value="<?= htmlspecialchars($rule['id']) ?>"><?= htmlspecialchars($rule['name']) ?></option>
        <?php endforeach; ?>
    </select><br>

    <label for="field_name">Имя поля*:</label>
    <input type="text" id="field_name" name="field_name" required><br>

    <label for="operator">Оператор*:</label>
    <select id="operator" name="operator" required>
        <option value="equals">Равно</option>
        <option value="not_equals">Не равно</option>
        <option value="greater_than">Больше</option>
        <option value="less_than">Меньше</option>
    </select><br>

    <label for="value">Значение:</label>
    <input type="text" id="value" name="value"><br>

    <button type="submit">Добавить условие</button>
</form>
</body>
</html>
