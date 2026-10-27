<?php

namespace App\Controllers;

use App\Services\AuthManager;
use App\Services\CsvService;
use App\Services\ScoringService;
use App\Services\TournamentService;
use App\Services\PlayerService;
use App\Services\Database;
use PDO;

class ExportController {
    private TournamentService $tournamentService;
    private ScoringService $scoringService;
    private PlayerService $playerService;
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->tournamentService = new TournamentService($this->db);
        $this->scoringService = new ScoringService($this->db);
        $this->playerService = new PlayerService($this->db);
    }

    public function exportStandings(): void {
        AuthManager::requireLogin();
        $tournamentId = (int)($_GET['tournament_id'] ?? 0);
        $tournament = $this->tournamentService->getTournament($tournamentId);
        $standings = $this->scoringService->getStandings($tournamentId);

        $headers = ['Rank', 'Player Name', 'FIDE Rating', 'Points', 'Played Games', 'Wins', 'Draws', 'Losses', 'Byes', 'Buchholz Cut-1', 'Buchholz', 'Sonneborn-Berger', 'Progressive'];
        $rows = [];

        foreach ($standings as $s) {
            $rows[] = [
                $s['rank'],
                $s['full_name'],
                $s['fide_rating'] ?? 'Unrated',
                $s['points'],
                $s['played_games'],
                $s['wins'],
                $s['draws'],
                $s['losses'],
                $s['byes'],
                $s['tb_buchholz_cut1'],
                $s['tb_buchholz'],
                $s['tb_sonneborn_berger'],
                $s['tb_progressive']
            ];
        }

        $filename = 'standings_tournament_' . $tournamentId . '.csv';
        CsvService::exportCsv($filename, $headers, $rows);
    }

    public function exportPlayers(): void {
        AuthManager::requireLogin();
        $tournamentId = (int)($_GET['tournament_id'] ?? 0);
        $players = $tournamentId ? $this->tournamentService->getTournamentPlayers($tournamentId, 'all') : $this->playerService->getAllPlayers();

        $headers = ['ID', 'Full Name', 'Nickname', 'FIDE Rating', 'National Rating', 'School/Org', 'Age', 'Contact Info'];
        $rows = [];

        foreach ($players as $p) {
            $rows[] = [
                $p['id'] ?? $p['player_id'],
                $p['full_name'],
                $p['nickname'] ?? '',
                $p['fide_rating'] ?? 'Unrated',
                $p['national_rating'] ?? '',
                $p['school_org'] ?? '',
                $p['age'] ?? '',
                $p['contact_info'] ?? ''
            ];
        }

        $filename = 'players_export_' . ($tournamentId ? "tournament_$tournamentId" : 'all') . '.csv';
        CsvService::exportCsv($filename, $headers, $rows);
    }
}
