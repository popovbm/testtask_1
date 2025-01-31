<?php


class Rules {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    private function fetchColumn(string $query, array $params) {
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_COLUMN);
    }

    private function checkCondition($valueFromDb, string $operator, $value): bool {
        return match ($operator) {
            'equals' => $valueFromDb == $value,
            'not_equals' => $valueFromDb != $value,
            'greater_than' => $valueFromDb > $value,
            'less_than' => $valueFromDb < $value,
            default => throw new InvalidArgumentException("Неподдерживаемый оператор: " . $operator),
        };
    }

    public function checkIsInBlackList(int $hotelId, int $agencyId): bool {
        $isBlack = $this->fetchColumn(
            "SELECT is_black FROM agency_hotel_options WHERE hotel_id = :hotel_id AND agency_id = :agency_id",
            [
                ':hotel_id' => $hotelId,
                ':agency_id' => $agencyId
            ]
        );
        return (bool)$isBlack;
    }

    public function checkIsInWhiteList(int $hotelId, int $agencyId): bool {
        return (bool)$this->fetchColumn(
            "SELECT is_white FROM agency_hotel_options WHERE hotel_id = :hotel_id AND agency_id = :agency_id",
            [
                ':hotel_id' => $hotelId,
                ':agency_id' => $agencyId
            ]
        );
    }

    public function checkIsRecomended(int $hotelId, int $agencyId): bool {
        return (bool)$this->fetchColumn(
            "SELECT is_recomend FROM agency_hotel_options WHERE hotel_id = :hotel_id AND agency_id = :agency_id",
            [
                ':hotel_id' => $hotelId,
                ':agency_id' => $agencyId
            ]
        );
    }

    public function checkHotelCompany(int $hotelId, string $operator, int $companyId): bool {
        $companyIdFromDb = $this->fetchColumn(
            "SELECT company_id FROM hotel_agreements WHERE hotel_id = :hotel_id",
            [
                ':hotel_id' => $hotelId
            ]
        );

        return $this->checkCondition($companyIdFromDb, $operator, $companyId);
    }

    public function checkIsAgreementByDefault(int $hotelId, string $value): bool {
        return (bool)$this->fetchColumn(
            "SELECT is_default FROM hotel_agreements WHERE hotel_id = :hotel_id",
            [
                ':hotel_id' => $hotelId
            ]
            ) == (int)$value;
    }

    public function isAgreementHaveComissionOrDiscount(int $hotelId, string $operator, string $value): bool {
        $result = $this->db->prepare("SELECT discount_percent, comission_percent FROM hotel_agreements WHERE hotel_id = :hotel_id");
        $result->execute([':hotel_id' => $hotelId]);
        $agreementsResult = $result->fetch(PDO::FETCH_ASSOC);

        return (
            ($this->checkCondition($agreementsResult['discount_percent'], $operator, (int)$value)) ||
            ($this->checkCondition($agreementsResult['comission_percent'], $operator, (int)$value))
        );
    }

    public function checkHotelStart(int $hotelId, string $operator, string $value): bool {
        return $this->checkCondition(
            (int)$this->fetchColumn("SELECT stars FROM hotels WHERE id = :hotel_id", [':hotel_id' => $hotelId]),
            $operator,
            (int)$value
        );
    }

    public function checkHotelCity(int $hotelId, string $operator, string $value): bool {
        return $this->checkCondition(
            (int)$this->fetchColumn("SELECT city_id FROM hotels WHERE id = :hotel_id", [':hotel_id' => $hotelId]),
            $operator,
            (int)$value
        );
    }

    public function checkHotelCountry(int $hotelId, string $operator, int $value): bool {
        return $this->checkCondition(
            (int)$this->fetchColumn("SELECT c.country_id FROM hotels AS h LEFT JOIN cities AS c ON c.id = h.city_id WHERE h.id = :hotel_id",
                [':hotel_id' =>  $hotelId]
            ),
            $operator,
            $value
        );
    }

}