<?php

namespace App\Controllers;

use App\Services\AuthManager;
use App\Services\PlayerService;
use App\Services\TournamentService;
use App\Services\Database;
use PDO;

class PlayerController {
    private PlayerService $playerService;
    private TournamentService $tournamentService;
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->playerService = new PlayerService($this->db);
        $this->tournamentService = new TournamentService($this->db);
    }

    public function index(): void {
        AuthManager::requireLogin();
        $tournamentId = (int)($_GET['tournament_id'] ?? 0);

        $search = trim($_GET['search'] ?? '');
        $sortBy = $_GET['sort_by'] ?? 'full_name';
        $sortDir = $_GET['sort_dir'] ?? 'ASC';

        $allPlayers = $this->playerService->getAllPlayers($search, $sortBy, $sortDir);
        $tournament = $tournamentId ? $this->tournamentService->getTournament($tournamentId) : null;

        $registeredPlayerIds = [];
        if ($tournamentId) {
            $registered = $this->tournamentService->getTournamentPlayers($tournamentId, 'all');
            $registeredPlayerIds = array_column($registered, 'player_id');
        }

        require __DIR__ . '/../Views/players/index.php';
    }

    public function create(): void {
        AuthManager::requireLogin();
        $tournamentId = (int)($_GET['tournament_id'] ?? 0);
        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? '';
            if (!AuthManager::verifyCsrfToken($token)) {
                $error = "Invalid security token.";
            } else {
                try {
                    $pId = $this->playerService->createPlayer($_POST);
                    if ($tournamentId) {
                        $this->playerService->registerPlayerToTournament($tournamentId, $pId);
                    }
                    header("Location: ?route=players&tournament_id=" . $tournamentId);
                    exit;
                } catch (\Exception $e) {
                    $error = $e->getMessage();
                }
            }
        }

        require __DIR__ . '/../Views/players/create.php';
    }

    public function bulkImport(): void {
        AuthManager::requireLogin();
        $tournamentId = (int)($_GET['tournament_id'] ?? 0);
        $error = null;
        $result = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? '';
            if (!AuthManager::verifyCsrfToken($token)) {
                $error = "Invalid security token.";
            } else {
                try {
                    $parsed = [];
                    // Handle file upload CSV if present
                    if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] === UPLOAD_ERR_OK) {
                        $fileTmp = $_FILES['csv_file']['tmp_name'];
                        $handle = fopen($fileTmp, 'r');
                        $header = null;
                        while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                            if (!$header) {
                                // First line check
                                if (strcasecmp($row[0], 'Name') === 0 || strcasecmp($row[0], 'Full Name') === 0) {
                                    $header = $row;
                                    continue;
                                }
                            }
                            $parsed[] = [
                                'full_name' => $row[0] ?? '',
                                'rating' => isset($row[1]) && $row[1] !== '' ? (int)$row[1] : null,
                                'school_org' => $row[2] ?? null,
                                'age' => isset($row[3]) && $row[3] !== '' ? (int)$row[3] : null
                            ];
                        }
                        fclose($handle);
                    } elseif (!empty($_POST['bulk_text'])) {
                        $parsed = $this->playerService->parseBulkText($_POST['bulk_text']);
                    }

                    if (!empty($parsed)) {
                        $result = $this->playerService->importBulkPlayers($parsed, $tournamentId ?: null);
                    } else {
                        $error = "No valid player records found to import.";
                    }
                } catch (\Exception $e) {
                    $error = $e->getMessage();
                }
            }
        }

        require __DIR__ . '/../Views/players/bulk_import.php';
    }

    public function registerToTournament(): void {
        AuthManager::requireLogin();
        $tournamentId = (int)($_GET['tournament_id'] ?? 0);
        $playerId = (int)($_GET['player_id'] ?? 0);
        $token = $_GET['csrf_token'] ?? '';

        if (AuthManager::verifyCsrfToken($token) && $tournamentId && $playerId) {
            $this->playerService->registerPlayerToTournament($tournamentId, $playerId);
        }

        header("Location: ?route=players&tournament_id=" . $tournamentId);
        exit;
    }

    public function removeFromTournament(): void {
        AuthManager::requireLogin();
        $tournamentId = (int)($_GET['tournament_id'] ?? 0);
        $playerId = (int)($_GET['player_id'] ?? 0);
        $token = $_GET['csrf_token'] ?? '';

        if (AuthManager::verifyCsrfToken($token) && $tournamentId && $playerId) {
            $this->playerService->removePlayerFromTournament($tournamentId, $playerId);
        }

        header("Location: ?route=players&tournament_id=" . $tournamentId);
        exit;
    }
}
