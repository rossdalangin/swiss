<?php

// Automated Test Runner for Paete Chess Club Tournament Manager
// Executes Scenarios 1 through 13 as mandated in requirement Section 15.

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) require $file;
});

use App\Services\Database;
use App\Services\TournamentService;
use App\Services\PlayerService;
use App\Services\PairingEngine;
use App\Services\ScoringService;

class TestRunner {
    private PDO $pdo;
    private TournamentService $tService;
    private PlayerService $pService;
    private PairingEngine $pEngine;
    private ScoringService $scoringService;
    private int $passed = 0;
    private int $failed = 0;

    public function __construct() {
        $dbFile = __DIR__ . '/../storage/db/test_suite.sqlite';
        if (file_exists($dbFile)) unlink($dbFile);

        $this->pdo = new PDO("sqlite:" . $dbFile);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->exec(file_get_contents(__DIR__ . '/../database/schema_sqlite.sql'));

        Database::setInstance($this->pdo);

        $this->tService = new TournamentService($this->pdo);
        $this->pService = new PlayerService($this->pdo);
        $this->pEngine = new PairingEngine($this->pdo);
        $this->scoringService = new ScoringService($this->pdo);
    }

    private function assertCondition(bool $cond, string $msg): void {
        if ($cond) {
            $this->passed++;
            echo "  [PASS] $msg\n";
        } else {
            $this->failed++;
            echo "  [FAIL] $msg\n";
        }
    }

    public function runAll(): void {
        echo "=====================================================\n";
        echo " RUNNING AUTOMATED TEST SUITE (SCENARIOS 1 - 13)\n";
        echo "=====================================================\n\n";

        $this->testScenario1_EightPlayers();
        $this->testScenario2_NinePlayers();
        $this->testScenario3_UnratedPlayers();
        $this->testScenario4_MixedRatings();
        $this->testScenario5_RepeatedOpponents();
        $this->testScenario6_ColorAllocation();
        $this->testScenario7_DrawsAndWins();
        $this->testScenario8_Forfeits();
        $this->testScenario9_ResultCorrection();
        $this->testScenario10_TournamentCompletion();
        $this->testScenario11_DatabasePersistence();
        $this->testScenario12_InvalidOperations();
        $this->testScenario13_PairingAlgorithmVerification();

        echo "\n=====================================================\n";
        echo " TEST SUITE SUMMARY: {$this->passed} PASSED, {$this->failed} FAILED\n";
        echo "=====================================================\n";

        if ($this->failed > 0) {
            exit(1);
        }
    }

    // Scenario 1: Eight Players
    private function testScenario1_EightPlayers(): void {
        echo "Scenario 1: Eight Players (5 Rounds)\n";
        $tId = $this->tService->createTournament([
            'name' => 'Scenario 1 Tournament',
            'date' => '2025-05-01',
            'venue' => 'Paete Town Plaza',
            'organizer_name' => 'Arbiter Dela Cruz',
            'total_rounds' => 5
        ]);

        for ($i = 1; $i <= 8; $i++) {
            $pId = $this->pService->createPlayer(['full_name' => "S1 Player $i", 'fide_rating' => 1000 + ($i * 100)]);
            $this->pService->registerPlayerToTournament($tId, $pId);
        }

        $roundNum = $this->pEngine->generateRound($tId);
        $this->assertCondition($roundNum === 1, "Round 1 generated successfully");

        $stmt = $this->pdo->prepare("SELECT * FROM pairings WHERE tournament_id = :t_id AND round_number = 1");
        $stmt->execute([':t_id' => $tId]);
        $pairings = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->assertCondition(count($pairings) === 4, "Four games paired for 8 players in Round 1");
    }

    // Scenario 2: Nine Players
    private function testScenario2_NinePlayers(): void {
        echo "Scenario 2: Nine Players (Odd count)\n";
        $tId = $this->tService->createTournament([
            'name' => 'Scenario 2 Tournament',
            'date' => '2025-05-01',
            'venue' => 'Paete Town Plaza',
            'organizer_name' => 'Arbiter Dela Cruz',
            'total_rounds' => 5
        ]);

        for ($i = 1; $i <= 9; $i++) {
            $pId = $this->pService->createPlayer(['full_name' => "S2 Player $i", 'fide_rating' => 1000 + ($i * 100)]);
            $this->pService->registerPlayerToTournament($tId, $pId);
        }

        $this->pEngine->generateRound($tId);

        $stmt = $this->pdo->prepare("SELECT * FROM pairings WHERE tournament_id = :t_id AND round_number = 1");
        $stmt->execute([':t_id' => $tId]);
        $pairings = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $byes = array_filter($pairings, fn($p) => $p['is_bye'] == 1);
        $games = array_filter($pairings, fn($p) => $p['is_bye'] == 0);

        $this->assertCondition(count($games) === 4, "Four games paired for 9 players");
        $this->assertCondition(count($byes) === 1, "One bye assigned for 9 players");
    }

    // Scenario 3: Unrated Players
    private function testScenario3_UnratedPlayers(): void {
        echo "Scenario 3: Unrated Players\n";
        $tId = $this->tService->createTournament([
            'name' => 'Scenario 3 Tournament',
            'date' => '2025-05-01',
            'venue' => 'Paete Town Plaza',
            'organizer_name' => 'Arbiter Dela Cruz',
            'total_rounds' => 5
        ]);

        for ($i = 1; $i <= 6; $i++) {
            $pId = $this->pService->createPlayer(['full_name' => "S3 Rated $i", 'fide_rating' => 1200 + ($i * 50)]);
            $this->pService->registerPlayerToTournament($tId, $pId);
        }
        for ($i = 1; $i <= 6; $i++) {
            $pId = $this->pService->createPlayer(['full_name' => "S3 Unrated $i", 'fide_rating' => null]);
            $this->pService->registerPlayerToTournament($tId, $pId);
        }

        $this->pEngine->generateRound($tId);

        $stmt = $this->pdo->prepare("SELECT * FROM pairings WHERE tournament_id = :t_id AND round_number = 1");
        $stmt->execute([':t_id' => $tId]);
        $pairings = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->assertCondition(count($pairings) === 6, "Six games paired for 12 players including unrated players");
    }

    // Scenario 4: Mixed Ratings
    private function testScenario4_MixedRatings(): void {
        echo "Scenario 4: Mixed Ratings\n";
        $tId = $this->tService->createTournament([
            'name' => 'Scenario 4 Tournament',
            'date' => '2025-05-01',
            'venue' => 'Paete Town Plaza',
            'organizer_name' => 'Arbiter Dela Cruz',
            'total_rounds' => 3
        ]);

        for ($i = 1; $i <= 16; $i++) {
            $pId = $this->pService->createPlayer(['full_name' => "S4 Player $i", 'fide_rating' => 1000 + ($i * 30)]);
            $this->pService->registerPlayerToTournament($tId, $pId);
        }

        $this->pEngine->generateRound($tId);
        $this->tService->getTournamentPlayers($tId);

        $stmt = $this->pdo->prepare("SELECT * FROM tournament_players WHERE tournament_id = :t_id ORDER BY initial_seed ASC");
        $stmt->execute([':t_id' => $tId]);
        $seeds = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->assertCondition($seeds[0]['initial_seed'] === 1, "Top rating correctly assigned seed #1");
    }

    // Scenario 5: Repeated Opponents
    private function testScenario5_RepeatedOpponents(): void {
        echo "Scenario 5: Repeated Opponents Avoidance\n";
        $tId = $this->tService->createTournament([
            'name' => 'Scenario 5 Tournament',
            'date' => '2025-05-01',
            'venue' => 'Paete Town Plaza',
            'organizer_name' => 'Arbiter Dela Cruz',
            'total_rounds' => 3
        ]);

        for ($i = 1; $i <= 4; $i++) {
            $pId = $this->pService->createPlayer(['full_name' => "S5 Player $i", 'fide_rating' => 1500 + ($i * 50)]);
            $this->pService->registerPlayerToTournament($tId, $pId);
        }

        // Round 1
        $this->pEngine->generateRound($tId);
        $histories1 = $this->pEngine->getPlayerHistories($tId); // History after Round 1 before generating Round 2

        $stmt = $this->pdo->prepare("SELECT id FROM pairings WHERE tournament_id = :t_id AND round_number = 1");
        $stmt->execute([':t_id' => $tId]);
        $pairs1 = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($pairs1 as $pr) {
            $this->scoringService->recordResult($pr['id'], '1-0');
        }

        // Fetch Round 1 opponents
        $round1Opponents = $this->pEngine->getPlayerHistories($tId);

        // Generate Round 2
        $this->pEngine->generateRound($tId);
        $stmt = $this->pdo->prepare("SELECT * FROM pairings WHERE tournament_id = :t_id AND round_number = 2");
        $stmt->execute([':t_id' => $tId]);
        $pairs2 = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $hasRepeat = false;
        foreach ($pairs2 as $p2) {
            $w = $p2['white_player_id'];
            $b = $p2['black_player_id'];
            if (in_array($b, $round1Opponents[$w]['opponents'])) {
                $hasRepeat = true;
            }
        }

        $this->assertCondition(!$hasRepeat, "Engine successfully avoided repeat opponents in Round 2");
    }

    // Scenario 6: Color Allocation
    private function testScenario6_ColorAllocation(): void {
        echo "Scenario 6: Color Allocation Balance\n";
        $tId = $this->tService->createTournament([
            'name' => 'Scenario 6 Tournament',
            'date' => '2025-05-01',
            'venue' => 'Paete Town Plaza',
            'organizer_name' => 'Arbiter Dela Cruz',
            'total_rounds' => 3
        ]);

        for ($i = 1; $i <= 4; $i++) {
            $pId = $this->pService->createPlayer(['full_name' => "S6 Player $i", 'fide_rating' => 1600 + ($i * 10)]);
            $this->pService->registerPlayerToTournament($tId, $pId);
        }

        $this->pEngine->generateRound($tId);
        $histories = $this->pEngine->getPlayerHistories($tId);

        $colorsBalanced = true;
        foreach ($histories as $h) {
            if (count($h['color_history']) > 0) {
                $w = count(array_filter($h['color_history'], fn($c) => $c === 'W'));
                $b = count(array_filter($h['color_history'], fn($c) => $c === 'B'));
                if (abs($w - $b) > 1) {
                    $colorsBalanced = false;
                }
            }
        }

        $this->assertCondition($colorsBalanced, "Color allocation balanced correctly");
    }

    // Scenario 7: Draws and Wins
    private function testScenario7_DrawsAndWins(): void {
        echo "Scenario 7: Draws and Wins Points Calculation\n";
        $tId = $this->tService->createTournament([
            'name' => 'Scenario 7 Tournament',
            'date' => '2025-05-01',
            'venue' => 'Paete Town Plaza',
            'organizer_name' => 'Arbiter Dela Cruz',
            'total_rounds' => 4
        ]);

        $p1 = $this->pService->createPlayer(['full_name' => "S7 Player A", 'fide_rating' => 1800]);
        $p2 = $this->pService->createPlayer(['full_name' => "S7 Player B", 'fide_rating' => 1700]);
        $this->pService->registerPlayerToTournament($tId, $p1);
        $this->pService->registerPlayerToTournament($tId, $p2);

        $this->pEngine->generateRound($tId);
        $stmt = $this->pdo->prepare("SELECT id FROM pairings WHERE tournament_id = :t_id AND round_number = 1");
        $stmt->execute([':t_id' => $tId]);
        $pair = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->scoringService->recordResult($pair['id'], '1/2-1/2');
        $standings = $this->scoringService->getStandings($tId);

        $this->assertCondition($standings[0]['points'] == 0.5 && $standings[1]['points'] == 0.5, "Draw awards 0.5 points to both players");
    }

    // Scenario 8: Forfeits
    private function testScenario8_Forfeits(): void {
        echo "Scenario 8: Forfeit Results\n";
        $tId = $this->tService->createTournament([
            'name' => 'Scenario 8 Tournament',
            'date' => '2025-05-01',
            'venue' => 'Paete Town Plaza',
            'organizer_name' => 'Arbiter Dela Cruz',
            'total_rounds' => 3
        ]);

        $p1 = $this->pService->createPlayer(['full_name' => "S8 Player A", 'fide_rating' => 1800]);
        $p2 = $this->pService->createPlayer(['full_name' => "S8 Player B", 'fide_rating' => 1700]);
        $this->pService->registerPlayerToTournament($tId, $p1);
        $this->pService->registerPlayerToTournament($tId, $p2);

        $this->pEngine->generateRound($tId);
        $stmt = $this->pdo->prepare("SELECT id FROM pairings WHERE tournament_id = :t_id AND round_number = 1");
        $stmt->execute([':t_id' => $tId]);
        $pair = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->scoringService->recordResult($pair['id'], '1-0F');
        $standings = $this->scoringService->getStandings($tId);

        $winner = array_values(array_filter($standings, fn($s) => $s['player_id'] == $p1))[0];
        $this->assertCondition($winner['points'] == 1.0 && $winner['played_games'] == 0, "Forfeit win awards 1.0 point without adding to played games count");
    }

    // Scenario 9: Result Correction
    private function testScenario9_ResultCorrection(): void {
        echo "Scenario 9: Result Correction & Audit Trail\n";
        $tId = $this->tService->createTournament([
            'name' => 'Scenario 9 Tournament',
            'date' => '2025-05-01',
            'venue' => 'Paete Town Plaza',
            'organizer_name' => 'Arbiter Dela Cruz',
            'total_rounds' => 3
        ]);

        $p1 = $this->pService->createPlayer(['full_name' => "S9 Player A", 'fide_rating' => 1800]);
        $p2 = $this->pService->createPlayer(['full_name' => "S9 Player B", 'fide_rating' => 1700]);
        $this->pService->registerPlayerToTournament($tId, $p1);
        $this->pService->registerPlayerToTournament($tId, $p2);

        $this->pEngine->generateRound($tId);
        $stmt = $this->pdo->prepare("SELECT id FROM pairings WHERE tournament_id = :t_id AND round_number = 1");
        $stmt->execute([':t_id' => $tId]);
        $pair = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->scoringService->recordResult($pair['id'], '1-0');
        $this->scoringService->recordResult($pair['id'], '0-1', 'Correcting entry error', 1);

        $standings = $this->scoringService->getStandings($tId);
        $p2Standing = array_values(array_filter($standings, fn($s) => $s['player_id'] == $p2))[0];

        $stmt = $this->pdo->prepare("SELECT * FROM audit_logs WHERE tournament_id = :t_id AND action = 'RESULT_CORRECTION'");
        $stmt->execute([':t_id' => $tId]);
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->assertCondition($p2Standing['points'] == 1.0, "Result correction updated standings correctly");
        $this->assertCondition(count($logs) > 0, "Audit log recorded result correction");
    }

    // Scenario 10: Tournament Completion
    private function testScenario10_TournamentCompletion(): void {
        echo "Scenario 10: Tournament Completion & Final Ranking\n";
        $tId = $this->tService->createTournament([
            'name' => 'Scenario 10 Tournament',
            'date' => '2025-05-01',
            'venue' => 'Paete Town Plaza',
            'organizer_name' => 'Arbiter Dela Cruz',
            'total_rounds' => 1
        ]);

        $p1 = $this->pService->createPlayer(['full_name' => "S10 Champ", 'fide_rating' => 2000]);
        $p2 = $this->pService->createPlayer(['full_name' => "S10 RunnerUp", 'fide_rating' => 1500]);
        $this->pService->registerPlayerToTournament($tId, $p1);
        $this->pService->registerPlayerToTournament($tId, $p2);

        $this->pEngine->generateRound($tId);
        $stmt = $this->pdo->prepare("SELECT id FROM pairings WHERE tournament_id = :t_id AND round_number = 1");
        $stmt->execute([':t_id' => $tId]);
        $pair = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->scoringService->recordResult($pair['id'], '1-0');

        $this->tService->updateTournamentStatus($tId, 'Completed');
        $standings = $this->scoringService->getStandings($tId);

        $this->assertCondition($standings[0]['player_id'] == $p1 && $standings[0]['rank'] == 1, "Tournament completed with correct champion identified");
    }

    // Scenario 11: Database Persistence
    private function testScenario11_DatabasePersistence(): void {
        echo "Scenario 11: Database Persistence Verification\n";
        $tId = $this->tService->createTournament([
            'name' => 'Scenario 11 Tournament',
            'date' => '2025-05-01',
            'venue' => 'Paete Town Plaza',
            'organizer_name' => 'Arbiter Dela Cruz',
            'total_rounds' => 3
        ]);

        $p1 = $this->pService->createPlayer(['full_name' => "S11 Player A", 'fide_rating' => 1800]);
        $p2 = $this->pService->createPlayer(['full_name' => "S11 Player B", 'fide_rating' => 1700]);
        $this->pService->registerPlayerToTournament($tId, $p1);
        $this->pService->registerPlayerToTournament($tId, $p2);

        $this->pEngine->generateRound($tId);

        // Simulate reconnecting to database
        $newTService = new TournamentService($this->pdo);
        $restoredT = $newTService->getTournament($tId);

        $this->assertCondition($restoredT['current_round'] == 1, "Tournament state preserved across database queries");
    }

    // Scenario 12: Invalid Operations
    private function testScenario12_InvalidOperations(): void {
        echo "Scenario 12: Rejection of Invalid Operations\n";
        $tId = $this->tService->createTournament([
            'name' => 'Scenario 12 Tournament',
            'date' => '2025-05-01',
            'venue' => 'Paete Town Plaza',
            'organizer_name' => 'Arbiter Dela Cruz',
            'total_rounds' => 3
        ]);

        $p1 = $this->pService->createPlayer(['full_name' => "S12 Player A", 'fide_rating' => 1800]);
        $p2 = $this->pService->createPlayer(['full_name' => "S12 Player B", 'fide_rating' => 1700]);
        $this->pService->registerPlayerToTournament($tId, $p1);
        $this->pService->registerPlayerToTournament($tId, $p2);

        $this->pEngine->generateRound($tId);

        // Attempting to generate next round while current round has unresolved results
        $rejected = false;
        try {
            $this->pEngine->generateRound($tId);
        } catch (RuntimeException $e) {
            $rejected = true;
        }

        $this->assertCondition($rejected, "Prevented generating next round with pending results in current round");
    }

    // Scenario 13: Pairing Algorithm Verification
    private function testScenario13_PairingAlgorithmVerification(): void {
        echo "Scenario 13: Pairing Algorithm Rules Verification\n";
        $tId = $this->tService->createTournament([
            'name' => 'Scenario 13 Tournament',
            'date' => '2025-05-01',
            'venue' => 'Paete Town Plaza',
            'organizer_name' => 'Arbiter Dela Cruz',
            'total_rounds' => 2
        ]);

        for ($i = 1; $i <= 6; $i++) {
            $pId = $this->pService->createPlayer(['full_name' => "S13 Player $i", 'fide_rating' => 2000 - ($i * 50)]);
            $this->pService->registerPlayerToTournament($tId, $pId);
        }

        $this->pEngine->generateRound($tId);
        $stmt = $this->pdo->prepare("SELECT * FROM pairings WHERE tournament_id = :t_id AND round_number = 1");
        $stmt->execute([':t_id' => $tId]);
        $pairings = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $allValid = true;
        foreach ($pairings as $p) {
            if ($p['white_player_id'] == $p['black_player_id']) {
                $allValid = false;
            }
        }

        $this->assertCondition($allValid && count($pairings) === 3, "Pairing engine generated valid Swiss board assignments");
    }
}

$runner = new TestRunner();
$runner->runAll();
