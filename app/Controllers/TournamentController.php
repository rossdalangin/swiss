<?php

namespace App\Controllers;

use App\Services\AuthManager;
use App\Services\TournamentService;
use App\Services\AuditLogger;
use App\Services\Database;
use PDO;

class TournamentController {
    private TournamentService $tournamentService;
    private AuditLogger $auditLogger;
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->tournamentService = new TournamentService($this->db);
        $this->auditLogger = new AuditLogger($this->db);
    }

    public function create(): void {
        AuthManager::requireLogin();
        $error = null;
        $success = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? '';
            if (!AuthManager::verifyCsrfToken($token)) {
                $error = "Invalid security token.";
            } else {
                try {
                    $tId = $this->tournamentService->createTournament($_POST);
                    $currentUser = AuthManager::getCurrentUser();
                    $this->auditLogger->log($tId, $currentUser['id'], 'CREATE_TOURNAMENT', "Created tournament '{$_POST['name']}'");
                    header("Location: ?route=dashboard&tournament_id=" . $tId);
                    exit;
                } catch (\Exception $e) {
                    $error = $e->getMessage();
                }
            }
        }

        require __DIR__ . '/../Views/tournaments/create.php';
    }

    public function edit(): void {
        AuthManager::requireLogin();
        $id = (int)($_GET['id'] ?? 0);
        $tournament = $this->tournamentService->getTournament($id);

        if (!$tournament) {
            header('Location: ?route=dashboard');
            exit;
        }

        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? '';
            if (!AuthManager::verifyCsrfToken($token)) {
                $error = "Invalid security token.";
            } else {
                try {
                    $this->tournamentService->updateTournament($id, $_POST);
                    $currentUser = AuthManager::getCurrentUser();
                    $this->auditLogger->log($id, $currentUser['id'], 'EDIT_TOURNAMENT', "Updated tournament configuration");
                    header("Location: ?route=dashboard&tournament_id=" . $id);
                    exit;
                } catch (\Exception $e) {
                    $error = $e->getMessage();
                }
            }
        }

        require __DIR__ . '/../Views/tournaments/edit.php';
    }

    public function finalize(): void {
        AuthManager::requireLogin();
        $id = (int)($_GET['id'] ?? 0);
        $token = $_GET['csrf_token'] ?? $_POST['csrf_token'] ?? '';

        if (AuthManager::verifyCsrfToken($token)) {
            $this->tournamentService->updateTournamentStatus($id, 'Completed');
            $currentUser = AuthManager::getCurrentUser();
            $this->auditLogger->log($id, $currentUser['id'], 'FINALIZE_TOURNAMENT', "Tournament finalized");
        }

        header("Location: ?route=dashboard&tournament_id=" . $id);
        exit;
    }

    public function reopen(): void {
        AuthManager::requireLogin();
        $id = (int)($_GET['id'] ?? 0);
        $token = $_POST['csrf_token'] ?? '';
        $reason = trim($_POST['reason'] ?? 'Administrative reopen');

        if (AuthManager::verifyCsrfToken($token)) {
            $this->tournamentService->updateTournamentStatus($id, 'In Progress');
            $currentUser = AuthManager::getCurrentUser();
            $this->auditLogger->log($id, $currentUser['id'], 'REOPEN_TOURNAMENT', "Reopened tournament. Reason: $reason");
        }

        header("Location: ?route=dashboard&tournament_id=" . $id);
        exit;
    }
}
