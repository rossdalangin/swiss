<?php

namespace App\Services;

use PDO;
use InvalidArgumentException;

class PlayerService {
    private PDO $db;

    public function __construct(?PDO $db = null) {
        $this->db = $db ?? Database::getInstance();
    }

    public function createPlayer(array $data): int {
        $fullName = trim($data['full_name'] ?? '');
        if (empty($fullName)) {
            throw new InvalidArgumentException("Player full name is required.");
        }

        $stmt = $this->db->prepare("INSERT INTO players (
            full_name, nickname, age, school_org, fide_id, national_id,
            fide_rating, national_rating, club_rating, rating_source, rating_date, contact_info
        ) VALUES (
            :full_name, :nickname, :age, :school_org, :fide_id, :national_id,
            :fide_rating, :national_rating, :club_rating, :rating_source, :rating_date, :contact_info
        )");

        $stmt->execute([
            ':full_name' => $fullName,
            ':nickname' => !empty($data['nickname']) ? trim($data['nickname']) : null,
            ':age' => !empty($data['age']) ? (int)$data['age'] : null,
            ':school_org' => !empty($data['school_org']) ? trim($data['school_org']) : null,
            ':fide_id' => !empty($data['fide_id']) ? trim($data['fide_id']) : null,
            ':national_id' => !empty($data['national_id']) ? trim($data['national_id']) : null,
            ':fide_rating' => (isset($data['fide_rating']) && $data['fide_rating'] !== '') ? (int)$data['fide_rating'] : null,
            ':national_rating' => (isset($data['national_rating']) && $data['national_rating'] !== '') ? (int)$data['national_rating'] : null,
            ':club_rating' => (isset($data['club_rating']) && $data['club_rating'] !== '') ? (int)$data['club_rating'] : null,
            ':rating_source' => !empty($data['rating_source']) ? trim($data['rating_source']) : 'Unrated',
            ':rating_date' => !empty($data['rating_date']) ? $data['rating_date'] : null,
            ':contact_info' => !empty($data['contact_info']) ? trim($data['contact_info']) : null,
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function updatePlayer(int $id, array $data): bool {
        $fullName = trim($data['full_name'] ?? '');
        if (empty($fullName)) {
            throw new InvalidArgumentException("Player full name is required.");
        }

        $stmt = $this->db->prepare("UPDATE players SET
            full_name = :full_name,
            nickname = :nickname,
            age = :age,
            school_org = :school_org,
            fide_id = :fide_id,
            national_id = :national_id,
            fide_rating = :fide_rating,
            national_rating = :national_rating,
            club_rating = :club_rating,
            rating_source = :rating_source,
            rating_date = :rating_date,
            contact_info = :contact_info
            WHERE id = :id
        ");

        return $stmt->execute([
            ':id' => $id,
            ':full_name' => $fullName,
            ':nickname' => !empty($data['nickname']) ? trim($data['nickname']) : null,
            ':age' => !empty($data['age']) ? (int)$data['age'] : null,
            ':school_org' => !empty($data['school_org']) ? trim($data['school_org']) : null,
            ':fide_id' => !empty($data['fide_id']) ? trim($data['fide_id']) : null,
            ':national_id' => !empty($data['national_id']) ? trim($data['national_id']) : null,
            ':fide_rating' => (isset($data['fide_rating']) && $data['fide_rating'] !== '') ? (int)$data['fide_rating'] : null,
            ':national_rating' => (isset($data['national_rating']) && $data['national_rating'] !== '') ? (int)$data['national_rating'] : null,
            ':club_rating' => (isset($data['club_rating']) && $data['club_rating'] !== '') ? (int)$data['club_rating'] : null,
            ':rating_source' => !empty($data['rating_source']) ? trim($data['rating_source']) : 'Unrated',
            ':rating_date' => !empty($data['rating_date']) ? $data['rating_date'] : null,
            ':contact_info' => !empty($data['contact_info']) ? trim($data['contact_info']) : null,
        ]);
    }

    public function getPlayer(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM players WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ?: null;
    }

    public function getAllPlayers(string $search = '', string $sortBy = 'full_name', string $sortDir = 'ASC'): array {
        $validSorts = ['full_name', 'fide_rating', 'national_rating', 'club_rating', 'age', 'school_org', 'created_at'];
        if (!in_array($sortBy, $validSorts)) $sortBy = 'full_name';
        $sortDir = strtoupper($sortDir) === 'DESC' ? 'DESC' : 'ASC';

        $sql = "SELECT * FROM players";
        $params = [];

        if (!empty($search)) {
            $sql .= " WHERE full_name LIKE :search OR school_org LIKE :search OR fide_id LIKE :search";
            $params[':search'] = '%' . $search . '%';
        }

        $sql .= " ORDER BY {$sortBy} {$sortDir}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deletePlayer(int $id): bool {
        // Check if player is part of any active/in-progress/completed tournament
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM tournament_players tp
            JOIN tournaments t ON tp.tournament_id = t.id
            WHERE tp.player_id = :id AND t.status IN ('In Progress', 'Completed')
        ");
        $stmt->execute([':id' => $id]);
        if ((int)$stmt->fetchColumn() > 0) {
            throw new InvalidArgumentException("Cannot delete player who is participating in an active or completed tournament.");
        }

        $stmt = $this->db->prepare("DELETE FROM players WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function registerPlayerToTournament(int $tournamentId, int $playerId, ?int $localPairingValue = 0): bool {
        // Check tournament status
        $stmt = $this->db->prepare("SELECT status FROM tournaments WHERE id = :id");
        $stmt->execute([':id' => $tournamentId]);
        $status = $stmt->fetchColumn();

        if ($status === 'Completed' || $status === 'Archived') {
            throw new InvalidArgumentException("Cannot add players to a completed or archived tournament.");
        }

        $driver = $this->db->getAttribute(PDO::ATTR_DRIVER_NAME);
        if ($driver === 'sqlite') {
            $sql = "INSERT OR IGNORE INTO tournament_players (tournament_id, player_id, local_pairing_value) VALUES (:t_id, :p_id, :lpv)";
        } else {
            $sql = "INSERT IGNORE INTO tournament_players (tournament_id, player_id, local_pairing_value) VALUES (:t_id, :p_id, :lpv)";
        }
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':t_id' => $tournamentId, ':p_id' => $playerId, ':lpv' => $localPairingValue]);
    }

    public function removePlayerFromTournament(int $tournamentId, int $playerId): bool {
        $stmt = $this->db->prepare("SELECT status FROM tournaments WHERE id = :id");
        $stmt->execute([':id' => $tournamentId]);
        $status = $stmt->fetchColumn();

        if ($status === 'In Progress' || $status === 'Completed') {
            throw new InvalidArgumentException("Cannot remove players after tournament has started. Mark player as withdrawn instead.");
        }

        $stmt = $this->db->prepare("DELETE FROM tournament_players WHERE tournament_id = :t_id AND player_id = :p_id");
        return $stmt->execute([':t_id' => $tournamentId, ':p_id' => $playerId]);
    }

    public function parseBulkText(string $text): array {
        $lines = explode("\n", str_replace("\r", "", $text));
        $parsed = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Check for comma separation
            if (str_contains($line, ',')) {
                $parts = array_map('trim', explode(',', $line));
                $parsed[] = [
                    'full_name' => $parts[0] ?? '',
                    'rating' => isset($parts[1]) && $parts[1] !== '' ? (int)$parts[1] : null,
                    'school_org' => $parts[2] ?? null,
                    'age' => isset($parts[3]) && $parts[3] !== '' ? (int)$parts[3] : null,
                ];
            } else {
                $parsed[] = [
                    'full_name' => $line,
                    'rating' => null,
                    'school_org' => null,
                    'age' => null,
                ];
            }
        }
        return $parsed;
    }

    public function importBulkPlayers(array $playersData, ?int $tournamentId = null): array {
        $createdIds = [];
        $duplicates = [];
        $invalid = [];

        foreach ($playersData as $item) {
            $fullName = trim($item['full_name'] ?? '');
            if (empty($fullName)) {
                $invalid[] = "Empty name row";
                continue;
            }

            // Check if player with same name already exists
            $stmt = $this->db->prepare("SELECT id FROM players WHERE LOWER(full_name) = LOWER(:name) LIMIT 1");
            $stmt->execute([':name' => $fullName]);
            $existingId = $stmt->fetchColumn();

            if ($existingId) {
                $duplicates[] = $fullName;
                $playerId = (int)$existingId;
            } else {
                $rating = isset($item['rating']) && $item['rating'] !== '' ? (int)$item['rating'] : null;
                $playerId = $this->createPlayer([
                    'full_name' => $fullName,
                    'fide_rating' => $rating,
                    'school_org' => $item['school_org'] ?? null,
                    'age' => $item['age'] ?? null,
                    'rating_source' => $rating ? 'Imported' : 'Unrated'
                ]);
                $createdIds[] = $playerId;
            }

            if ($tournamentId) {
                $this->registerPlayerToTournament($tournamentId, $playerId);
            }
        }

        return [
            'created_count' => count($createdIds),
            'duplicates' => $duplicates,
            'invalid' => $invalid
        ];
    }
}
