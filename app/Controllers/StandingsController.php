<?php

namespace App\Controllers;

use App\Services\AuthManager;
use App\Services\ScoringService;
use App\Services\TournamentService;
use App\Services\Database;
use PDO;

class StandingsController {
    private ScoringService $scoringService;
    private TournamentService $tournamentService;
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->scoringService = new ScoringService($this->db);
        $this->tournamentService = new TournamentService($this->db);
    }

    public function index(): void {
        $tournamentId = (int)($_GET['tournament_id'] ?? 0);
        $tournament = $tournamentId ? $this->tournamentService->getTournament($tournamentId) : null;

        if (!$tournament) {
            $tournaments = $this->tournamentService->getAllTournaments();
            if (!empty($tournaments)) {
                $tournament = $this->tournamentService->getTournament((int)$tournaments[0]['id']);
                $tournamentId = (int)$tournament['id'];
            }
        }

        $standings = [];
        if ($tournamentId) {
            $standings = $this->scoringService->getStandings($tournamentId);
        }

        require __DIR__ . '/../Views/standings/index.php';
    }

    public function playerHistory(): void {
        $tournamentId = (int)($_GET['tournament_id'] ?? 0);
        $playerId = (int)($_GET['player_id'] ?? 0);

        $stmt = $this->db->prepare("SELECT * FROM players WHERE id = :p_id LIMIT 1");
        $stmt->execute([':p_id' => $playerId]);
        $player = $stmt->fetch(PDO::FETCH_ASSOC);

        // Fetch player pairings round by round
        $stmt = $this->db->prepare("
            SELECT pr.*, r.round_number,
                   pw.full_name as white_name, pw.fide_rating as white_rating,
                   pb.full_name as black_name, pb.fide_rating as black_rating
            FROM pairings pr
            JOIN rounds r ON pr.round_id = r.id
            LEFT JOIN players pw ON pr.white_player_id = pw.id
            LEFT JOIN players pb ON pr.black_player_id = pb.id
            WHERE r.tournament_id = :t_id
              AND (pr.white_player_id = :p_id OR pr.black_player_id = :p_id OR pr.bye_player_id = :p_id)
            ORDER BY r.round_number ASC
        ");
        $stmt->execute([':t_id' => $tournamentId, ':p_id' => $playerId]);
        $history = $stmt->fetchAll(PDO::FETCH_ASSOC);

        header('Content-Type: application/json');
        echo json_encode([
            'player' => $player,
            'history' => $history
        ]);
        exit;
    }
}
