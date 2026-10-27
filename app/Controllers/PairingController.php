<?php

namespace App\Controllers;

use App\Services\AuthManager;
use App\Services\PairingEngine;
use App\Services\ScoringService;
use App\Services\TournamentService;
use App\Services\AuditLogger;
use App\Services\Database;
use PDO;

class PairingController {
    private PairingEngine $pairingEngine;
    private ScoringService $scoringService;
    private TournamentService $tournamentService;
    private AuditLogger $auditLogger;
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->pairingEngine = new PairingEngine($this->db);
        $this->scoringService = new ScoringService($this->db);
        $this->tournamentService = new TournamentService($this->db);
        $this->auditLogger = new AuditLogger($this->db);
    }

    public function index(): void {
        AuthManager::requireLogin();
        $tournamentId = (int)($_GET['tournament_id'] ?? 0);
        $tournament = $this->tournamentService->getTournament($tournamentId);

        if (!$tournament) {
            header('Location: ?route=dashboard');
            exit;
        }

        $roundNumber = (int)($_GET['round'] ?? $tournament['current_round']);
        if ($roundNumber <= 0 && $tournament['current_round'] > 0) {
            $roundNumber = $tournament['current_round'];
        }

        // Fetch pairings for requested round
        $pairings = [];
        if ($roundNumber > 0) {
            $stmt = $this->db->prepare("
                SELECT pr.*,
                       pw.full_name as white_name, pw.fide_rating as white_rating,
                       pb.full_name as black_name, pb.fide_rating as black_rating,
                       pbye.full_name as bye_name
                FROM pairings pr
                JOIN rounds r ON pr.round_id = r.id
                LEFT JOIN players pw ON pr.white_player_id = pw.id
                LEFT JOIN players pb ON pr.black_player_id = pb.id
                LEFT JOIN players pbye ON pr.bye_player_id = pbye.id
                WHERE r.tournament_id = :t_id AND r.round_number = :r_num
                ORDER BY pr.board_number ASC
            ");
            $stmt->execute([':t_id' => $tournamentId, ':r_num' => $roundNumber]);
            $pairings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        require __DIR__ . '/../Views/pairings/index.php';
    }

    public function generate(): void {
        AuthManager::requireLogin();
        $tournamentId = (int)($_GET['tournament_id'] ?? $_POST['tournament_id'] ?? 0);
        $token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';

        if (!AuthManager::verifyCsrfToken($token)) {
            header("Location: ?route=pairings&tournament_id=" . $tournamentId . "&error=" . urlencode("Invalid security token."));
            exit;
        }

        try {
            $newRound = $this->pairingEngine->generateRound($tournamentId);
            $currentUser = AuthManager::getCurrentUser();
            $this->auditLogger->log($tournamentId, $currentUser['id'], 'GENERATE_PAIRINGS', "Generated pairings for Round $newRound");
            header("Location: ?route=pairings&tournament_id=" . $tournamentId . "&round=" . $newRound);
            exit;
        } catch (\Exception $e) {
            header("Location: ?route=pairings&tournament_id=" . $tournamentId . "&error=" . urlencode($e->getMessage()));
            exit;
        }
    }

    public function recordResult(): void {
        AuthManager::requireLogin();
        $tournamentId = (int)($_POST['tournament_id'] ?? 0);
        $pairingId = (int)($_POST['pairing_id'] ?? 0);
        $result = trim($_POST['result'] ?? '');
        $reason = trim($_POST['admin_reason'] ?? '');
        $token = $_POST['csrf_token'] ?? '';

        if (!AuthManager::verifyCsrfToken($token)) {
            header("Location: ?route=pairings&tournament_id=" . $tournamentId . "&error=" . urlencode("Invalid security token."));
            exit;
        }

        try {
            $currentUser = AuthManager::getCurrentUser();
            $this->scoringService->recordResult($pairingId, $result, $reason, $currentUser['id']);
            header("Location: ?route=pairings&tournament_id=" . $tournamentId);
            exit;
        } catch (\Exception $e) {
            header("Location: ?route=pairings&tournament_id=" . $tournamentId . "&error=" . urlencode($e->getMessage()));
            exit;
        }
    }
}
