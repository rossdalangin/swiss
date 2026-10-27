<?php

namespace App\Services;

use PDO;
use InvalidArgumentException;

class TournamentService {
    private PDO $db;

    public function __construct(?PDO $db = null) {
        $this->db = $db ?? Database::getInstance();
    }

    public function createTournament(array $data): int {
        $name = trim($data['name'] ?? '');
        $date = trim($data['date'] ?? '');
        $venue = trim($data['venue'] ?? '');
        $organizer = trim($data['organizer_name'] ?? '');

        if (empty($name) || empty($date) || empty($venue) || empty($organizer)) {
            throw new InvalidArgumentException("Tournament name, date, venue, and organizer are required.");
        }

        $totalRounds = isset($data['total_rounds']) ? max(1, (int)$data['total_rounds']) : 5;
        $scoringSystem = $data['scoring_system'] ?? 'standard';
        $timeControl = $data['time_control'] ?? '15 mins + 5 secs';
        $description = $data['description'] ?? null;
        $logoPath = $data['logo_path'] ?? null;
        $ratingSource = $data['rating_source'] ?? 'FIDE';
        $pairingMode = $data['pairing_mode'] ?? 'club_swiss';
        $tiebreakConfig = is_array($data['tiebreak_config'] ?? null)
            ? json_encode($data['tiebreak_config'])
            : ($data['tiebreak_config'] ?? json_encode(['buchholz_cut1', 'buchholz', 'sonneborn_berger', 'progressive', 'direct_encounter', 'wins', 'black_wins']));
        $allowWithdrawals = isset($data['allow_withdrawals']) ? (int)(bool)$data['allow_withdrawals'] : 1;
        $allowLateEntries = isset($data['allow_late_entries']) ? (int)(bool)$data['allow_late_entries'] : 1;

        $stmt = $this->db->prepare("INSERT INTO tournaments (
            name, date, venue, organizer_name, total_rounds, scoring_system,
            time_control, description, logo_path, rating_source, pairing_mode,
            tiebreak_config, allow_withdrawals, allow_late_entries, status
        ) VALUES (
            :name, :date, :venue, :organizer_name, :total_rounds, :scoring_system,
            :time_control, :description, :logo_path, :rating_source, :pairing_mode,
            :tiebreak_config, :allow_withdrawals, :allow_late_entries, 'Draft'
        )");

        $stmt->execute([
            ':name' => $name,
            ':date' => $date,
            ':venue' => $venue,
            ':organizer_name' => $organizer,
            ':total_rounds' => $totalRounds,
            ':scoring_system' => $scoringSystem,
            ':time_control' => $timeControl,
            ':description' => $description,
            ':logo_path' => $logoPath,
            ':rating_source' => $ratingSource,
            ':pairing_mode' => $pairingMode,
            ':tiebreak_config' => $tiebreakConfig,
            ':allow_withdrawals' => $allowWithdrawals,
            ':allow_late_entries' => $allowLateEntries,
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function updateTournament(int $id, array $data): bool {
        $stmt = $this->db->prepare("UPDATE tournaments SET
            name = :name,
            date = :date,
            venue = :venue,
            organizer_name = :organizer_name,
            total_rounds = :total_rounds,
            scoring_system = :scoring_system,
            time_control = :time_control,
            description = :description,
            rating_source = :rating_source,
            pairing_mode = :pairing_mode,
            tiebreak_config = :tiebreak_config,
            allow_withdrawals = :allow_withdrawals,
            allow_late_entries = :allow_late_entries
            WHERE id = :id
        ");

        $tiebreakConfig = is_array($data['tiebreak_config'] ?? null)
            ? json_encode($data['tiebreak_config'])
            : ($data['tiebreak_config'] ?? json_encode(['buchholz_cut1', 'buchholz', 'sonneborn_berger', 'progressive', 'direct_encounter', 'wins', 'black_wins']));

        return $stmt->execute([
            ':id' => $id,
            ':name' => trim($data['name']),
            ':date' => trim($data['date']),
            ':venue' => trim($data['venue']),
            ':organizer_name' => trim($data['organizer_name']),
            ':total_rounds' => max(1, (int)$data['total_rounds']),
            ':scoring_system' => $data['scoring_system'] ?? 'standard',
            ':time_control' => $data['time_control'] ?? '15 mins + 5 secs',
            ':description' => $data['description'] ?? null,
            ':rating_source' => $data['rating_source'] ?? 'FIDE',
            ':pairing_mode' => $data['pairing_mode'] ?? 'club_swiss',
            ':tiebreak_config' => $tiebreakConfig,
            ':allow_withdrawals' => isset($data['allow_withdrawals']) ? (int)(bool)$data['allow_withdrawals'] : 1,
            ':allow_late_entries' => isset($data['allow_late_entries']) ? (int)(bool)$data['allow_late_entries'] : 1,
        ]);
    }

    public function getTournament(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM tournaments WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $t = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($t && !empty($t['tiebreak_config']) && is_string($t['tiebreak_config'])) {
            $t['tiebreak_config_array'] = json_decode($t['tiebreak_config'], true) ?? [];
        }
        return $t ?: null;
    }

    public function getAllTournaments(): array {
        $stmt = $this->db->query("SELECT t.*,
            (SELECT COUNT(*) FROM tournament_players tp WHERE tp.tournament_id = t.id AND tp.status = 'active') as player_count
            FROM tournaments t ORDER BY t.date DESC, t.id DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRecommendedRounds(int $playerCount): int {
        if ($playerCount <= 4) return 3;
        if ($playerCount <= 8) return 4;
        if ($playerCount <= 16) return 5;
        if ($playerCount <= 32) return 6;
        if ($playerCount <= 64) return 7;
        return 8;
    }

    public function getTournamentPlayers(int $tournamentId, string $statusFilter = 'active'): array {
        $sql = "SELECT tp.*, p.full_name, p.nickname, p.fide_id, p.fide_rating, p.national_rating, p.club_rating,
                       p.school_org, p.age, p.contact_info, p.rating_source
                FROM tournament_players tp
                JOIN players p ON tp.player_id = p.id
                WHERE tp.tournament_id = :t_id";

        if ($statusFilter !== 'all') {
            $sql .= " AND tp.status = :status";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':t_id' => $tournamentId, ':status' => $statusFilter]);
        } else {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':t_id' => $tournamentId]);
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function freezeInitialSeeding(int $tournamentId, string $seedingPolicy = 'rating_desc'): void {
        $players = $this->getTournamentPlayers($tournamentId, 'active');

        // Sorting strategy
        usort($players, function($a, $b) use ($seedingPolicy) {
            if ($seedingPolicy === 'alphabetical') {
                return strcasecmp($a['full_name'], $b['full_name']);
            }

            // Rating descending (fide_rating -> national_rating -> club_rating -> local_pairing_value)
            $rA = $a['fide_rating'] ?? $a['national_rating'] ?? $a['club_rating'] ?? $a['local_pairing_value'] ?? 0;
            $rB = $b['fide_rating'] ?? $b['national_rating'] ?? $b['club_rating'] ?? $b['local_pairing_value'] ?? 0;

            if ($rA !== $rB) {
                return $rB <=> $rA; // Descending
            }

            // Secondary: Alphabetical
            return strcasecmp($a['full_name'], $b['full_name']);
        });

        // Assign initial_seed 1..N
        $stmt = $this->db->prepare("UPDATE tournament_players SET initial_seed = :seed WHERE tournament_id = :t_id AND player_id = :p_id");
        $seed = 1;
        foreach ($players as $p) {
            $stmt->execute([
                ':seed' => $seed++,
                ':t_id' => $tournamentId,
                ':p_id' => $p['player_id']
            ]);
        }
    }

    public function updateTournamentStatus(int $id, string $status): bool {
        $stmt = $this->db->prepare("UPDATE tournaments SET status = :status WHERE id = :id");
        return $stmt->execute([':status' => $status, ':id' => $id]);
    }

    public function withdrawPlayer(int $tournamentId, int $playerId, string $reason = 'Withdrawn'): bool {
        $stmt = $this->db->prepare("UPDATE tournament_players SET status = 'withdrawn' WHERE tournament_id = :t_id AND player_id = :p_id");
        return $stmt->execute([':t_id' => $tournamentId, ':p_id' => $playerId]);
    }
}
