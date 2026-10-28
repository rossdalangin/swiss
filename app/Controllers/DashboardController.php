<?php

namespace App\Controllers;

use App\Services\AuthManager;
use App\Services\TournamentService;
use App\Services\ScoringService;
use App\Services\Database;
use PDO;

class DashboardController {
    private TournamentService $tournamentService;
    private ScoringService $scoringService;
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->tournamentService = new TournamentService($this->db);
        $this->scoringService = new ScoringService($this->db);
    }

    public function index(): void {
        AuthManager::requireLogin();

        $activeTournamentId = $_GET['tournament_id'] ?? null;
        $tournaments = $this->tournamentService->getAllTournaments();

        $tournament = null;
        if ($activeTournamentId) {
            $tournament = $this->tournamentService->getTournament((int)$activeTournamentId);
        } elseif (!empty($tournaments)) {
            $tournament = $this->tournamentService->getTournament((int)$tournaments[0]['id']);
        }

        $allTournaments = $tournaments;

        $stats = [
            'total_players' => 0,
            'rated_players' => 0,
            'unrated_players' => 0,
            'total_rounds' => 0,
            'current_round' => 0,
            'completed_games' => 0,
            'pending_games' => 0,
            'byes_count' => 0,
            'total_byes' => 0,
            'leader' => 'None',
            'leader_name' => 'None',
            'status' => 'Draft'
        ];

        $standings = [];

        if ($tournament) {
            $tId = (int)$tournament['id'];
            $stats['total_rounds'] = $tournament['total_rounds'];
            $stats['current_round'] = $tournament['current_round'];
            $stats['status'] = $tournament['status'];

            // Get player counts
            $players = $this->tournamentService->getTournamentPlayers($tId, 'all');
            $stats['total_players'] = count($players);

            foreach ($players as $p) {
                if (!empty($p['fide_rating']) || !empty($p['national_rating']) || !empty($p['club_rating'])) {
                    $stats['rated_players']++;
                } else {
                    $stats['unrated_players']++;
                }
            }

            // Get completed vs pending games
            $stmt = $this->db->prepare("
                SELECT result, is_bye FROM pairings p
                JOIN rounds r ON p.round_id = r.id
                WHERE r.tournament_id = :t_id
            ");
            $stmt->execute([':t_id' => $tId]);
            $pairings = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($pairings as $pr) {
                if ($pr['is_bye'] == 1) {
                    $stats['byes_count']++;
                }
                if ($pr['result'] === 'pending') {
                    $stats['pending_games']++;
                } else {
                    $stats['completed_games']++;
                }
            }

            // Get standings
            $standings = $this->scoringService->getStandings($tId);
            if (!empty($standings)) {
                $stats['leader'] = $standings[0]['full_name'] . ' (' . $standings[0]['points'] . ' pts)';
                $stats['leader_name'] = $standings[0]['full_name'] . ' (' . $standings[0]['points'] . ' pts)';
            }
            $stats['total_byes'] = $stats['byes_count'];
        }

        require __DIR__ . '/../Views/dashboard/index.php';
    }
}
