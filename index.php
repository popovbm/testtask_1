<?php

require 'Database.php';
require 'Rules.php';

define('MYSQL_HOST', 'mysql');
define('MYSQL_USER', $_ENV['MYSQL_USER']);
define('MYSQL_PASSWORD', $_ENV['MYSQL_PASSWORD']);
define('MYSQL_DB', $_ENV['MYSQL_DATABASE']);

$db = Database::getInstance(MYSQL_HOST, MYSQL_DB, MYSQL_USER, MYSQL_PASSWORD);
$conn = $db->getConnection();

$rules = new Rules($conn);

$hotelsQuery = $conn->query("SELECT * FROM hotels");
$hotels = $hotelsQuery->fetchAll(PDO::FETCH_ASSOC);

$hotel_id = $_GET['hotel_id'] ?? null;

$result = [];

if ($hotel_id) {
    $agenciesQuery = $conn->prepare("SELECT * FROM agencies");
    $agenciesQuery->execute();
    $agencies = $agenciesQuery->fetchAll(PDO::FETCH_ASSOC);

    foreach ($agencies as $agency) {
        $agencyId = $agency['id'];

        $agencyRulesQuery = $conn->prepare("SELECT * FROM rules WHERE active = 1 AND agency_id = :agency_id");
        $agencyRulesQuery->execute([':agency_id' => $agencyId]);
        $agencyRules = $agencyRulesQuery->fetchAll(PDO::FETCH_ASSOC);

        foreach ($agencyRules as $agencyRule) {
            $conditionQuery = $conn->prepare("SELECT * FROM rule_conditions WHERE rule_id = :rule_id");
            $conditionQuery->execute([':rule_id' => $agencyRule['id']]);
            $conditions = $conditionQuery->fetchAll(PDO::FETCH_ASSOC);

            $allConditionsMet = true;

            foreach ($conditions as $condition) {

                if ($condition['field_name'] === 'is_black') {
                    if (!$rules->checkIsInBlackList($hotel_id, $agencyId)) {
                        $allConditionsMet = false;
                        break;
                    }
                }

                if ($condition['field_name'] === 'is_white') {
                    if (!$rules->checkIsInWhiteList($hotel_id, $agencyId)) {
                        $allConditionsMet = false;
                        break;
                    }
                }

                if ($condition['field_name'] === 'is_recomend') {
                    if (!$rules->checkIsRecomended($hotel_id, $agencyId)) {
                        $allConditionsMet = false;
                        break;
                    }
                }

                if ($condition['field_name'] === 'company_in_agreement_with_hotel' ||
                    $condition['field_name'] === 'company_isnt_agreement_with_hotel') {
                    if (!$rules->checkHotelCompany($hotel_id, $condition['operator'], $condition['value'])) {
                        $allConditionsMet = false;
                        break;
                    }
                }

                if ($condition['field_name'] === 'agreement_by_default') {
                    if (!$rules->checkIsAgreementByDefault($hotel_id, $condition['value'])) {
                        $allConditionsMet = false;
                        break;
                    }
                }

                if ($condition['field_name'] === 'agreement_with_commission_or_discount') {
                    if (!$rules->isAgreementHaveComissionOrDiscount($hotel_id, $condition['operator'], $condition['value'])) {
                        $allConditionsMet = false;
                        break;
                    }
                }

                if ($condition['field_name'] === 'is_stars_equals' ||
                    $condition['field_name'] === 'is_stars_not_equals') {
                    if (!$rules->checkHotelStart($hotel_id, $condition['operator'], $condition['value'])) {
                        $allConditionsMet = false;
                        break;
                    }
                }

                if ($condition['field_name'] === 'is_city_equals' ||
                    $condition['field_name'] === 'is_city_not_equals') {
                    if (!$rules->checkHotelCity($hotel_id, $condition['operator'], $condition['value'])) {
                        $allConditionsMet = false;
                        break;
                    }
                }

                if ($condition['field_name'] === 'is_country_equals' ||
                    $condition['field_name'] === 'is_country_not_equals') {
                    if (!$rules->checkHotelCountry($hotel_id, $condition['operator'], $condition['value'])) {
                        $allConditionsMet = false;
                        break;
                    }
                }
            }

            if ($allConditionsMet) {
                $result[$agency['name']][] = 'Правило: '. $agencyRule['name'] . ' - ' . 'Сообщение: "' . $agencyRule['notification_text'] . '"';
            }

        }
    }
}

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Проверка отелей</title>
</head>
<body>
<ul>
    <li style="display: inline; margin-right: 15px;"><a href="create_rule.php">Добавить правило</a></li>
    <li style="display: inline; margin-right: 15px;"><a href="create_condition.php">Добавить условие</a></li>
</ul>

<h1>Проверка отелей</h1>

<!-- Форма для выбора отеля -->
<form method="get">
    <label for="hotel_id">Выберите отель:</label>
    <select id="hotel_id" name="hotel_id" required>
        <?php foreach ($hotels as $hotel): ?>
            <option value="<?= htmlspecialchars($hotel['id']) ?>" <?= ($hotel['id'] == $hotel_id) ? 'selected' : '' ?>>
                <?= htmlspecialchars($hotel['name']) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <button type="submit">Проверить</button>
</form>

<?php if (!empty($result)): ?>
    <h2>Результаты проверки:</h2>
    <?php foreach ($result as $agencyName => $agencyRulesMsgs): ?>
        <h3>Агентство: <?= htmlspecialchars($agencyName) ?></h3>
        <ul>
            <?php foreach ($agencyRulesMsgs as $agencyRuleMsg): ?>
                <li><strong><?= htmlspecialchars($agencyRuleMsg) ?></strong></li>
            <?php endforeach; ?>
        </ul>
    <?php endforeach; ?>
<?php endif; ?>

</body>
</html>
