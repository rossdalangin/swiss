<?php

namespace App\Services;

use PDO;
use RuntimeException;
use InvalidArgumentException;

class PairingEngine {
    private PDO $db;

    public function __construct(?PDO $db = null) {
        $this->db = $db ?? Database::getInstance();
    }

    /**
     * Generate pairings for specified round.
     */
    public function generateRound(int $tournamentId, ?int $targetRound = null): int {
        $stmt = $this->db->prepare("SELECT * FROM tournaments WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $tournamentId]);
        $tournament = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$tournament) {
            throw new InvalidArgumentException("Tournament not found.");
        }

        $currentRound = (int)$tournament['current_round'];
        $totalRounds = (int)$tournament['total_rounds'];
        $nextRound = $targetRound ?? ($currentRound + 1);

        if ($nextRound > $totalRounds) {
            throw new RuntimeException("All configured $totalRounds rounds have been completed.");
        }

        // Verify previous round results are resolved
        if ($currentRound > 0) {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) FROM pairings p
                JOIN rounds r ON p.round_id = r.id
                WHERE r.tournament_id = :t_id AND r.round_number = :r_num AND p.result = 'pending'
            ");
            $stmt->execute([':t_id' => $tournamentId, ':r_num' => $currentRound]);
            if ((int)$stmt->fetchColumn() > 0) {
                throw new RuntimeException("Cannot generate Round $nextRound because Round $currentRound has pending results.");
            }
        }

        // Prevent generating same round twice
        $stmt = $this->db->prepare("SELECT id FROM rounds WHERE tournament_id = :t_id AND round_number = :r_num");
        $stmt->execute([':t_id' => $tournamentId, ':r_num' => $nextRound]);
        if ($stmt->fetch()) {
            throw new RuntimeException("Round $nextRound pairings have already been generated.");
        }

        // Freeze seeding if Round 1
        if ($currentRound === 0) {
            $tService = new TournamentService($this->db);
            $tService->freezeInitialSeeding($tournamentId);
            $tService->updateTournamentStatus($tournamentId, 'In Progress');
        }

        // Get active eligible players
        $stmt = $this->db->prepare("
            SELECT tp.player_id, tp.initial_seed, tp.local_pairing_value,
                   p.full_name, p.fide_rating, p.national_rating, p.club_rating
            FROM tournament_players tp
            JOIN players p ON tp.player_id = p.id
            WHERE tp.tournament_id = :t_id AND tp.status = 'active'
            ORDER BY tp.initial_seed ASC
        ");
        $stmt->execute([':t_id' => $tournamentId]);
        $activePlayers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($activePlayers) < 2) {
            throw new RuntimeException("At least 2 active players are required to generate pairings.");
        }

        // Get player history (opponents, colors, points, byes)
        $history = $this->getPlayerHistories($tournamentId);

        // Calculate current score for each active player
        foreach ($activePlayers as &$p) {
            $pid = (int)$p['player_id'];
            $p['points'] = $history[$pid]['points'] ?? 0.0;
            $p['byes_received'] = $history[$pid]['byes_count'] ?? 0;
            $p['opponents'] = $history[$pid]['opponents'] ?? [];
            $p['color_history'] = $history[$pid]['color_history'] ?? []; // 'W', 'B'
            $p['effective_rating'] = $p['fide_rating'] ?? $p['national_rating'] ?? $p['club_rating'] ?? $p['local_pairing_value'] ?? 0;
        }
        unset($p);

        $this->db->beginTransaction();

        try {
            // Create round record
            $stmt = $this->db->prepare("INSERT INTO rounds (tournament_id, round_number, status) VALUES (:t_id, :r_num, 'in_progress')");
            $stmt->execute([':t_id' => $tournamentId, ':r_num' => $nextRound]);
            $roundId = (int)$this->db->lastInsertId();

            // Perform Swiss pairing algorithm
            $pairings = $this->computeSwissPairings($activePlayers, $nextRound);

            // Save pairings to DB
            $boardNum = 1;
            $stmtPair = $this->db->prepare("INSERT INTO pairings (
                tournament_id, round_id, round_number, board_number,
                white_player_id, black_player_id, result, is_bye, bye_player_id, bye_type, bye_points, notes
            ) VALUES (
                :t_id, :r_id, :r_num, :board, :white_id, :black_id, :result, :is_bye, :bye_p_id, :bye_type, :bye_pts, :notes
            )");

            foreach ($pairings['games'] as $game) {
                $stmtPair->execute([
                    ':t_id' => $tournamentId,
                    ':r_id' => $roundId,
                    ':r_num' => $nextRound,
                    ':board' => $boardNum++,
                    ':white_id' => $game['white_id'],
                    ':black_id' => $game['black_id'],
                    ':result' => 'pending',
                    ':is_bye' => 0,
                    ':bye_p_id' => null,
                    ':bye_type' => null,
                    ':bye_pts' => 0.0,
                    ':notes' => $game['notes'] ?? null
                ]);
            }

            if (isset($pairings['bye'])) {
                $byePlayer = $pairings['bye'];
                $stmtPair->execute([
                    ':t_id' => $tournamentId,
                    ':r_id' => $roundId,
                    ':r_num' => $nextRound,
                    ':board' => $boardNum++,
                    ':white_id' => null,
                    ':black_id' => null,
                    ':result' => '1-0', // Automatic win for Bye
                    ':is_bye' => 1,
                    ':bye_p_id' => $byePlayer['player_id'],
                    ':bye_type' => 'pairing_bye',
                    ':bye_pts' => 1.0,
                    ':notes' => 'Half/Full Point Swiss Bye assigned for odd player count'
                ]);
            }

            // Update tournament current_round
            $stmt = $this->db->prepare("UPDATE tournaments SET current_round = :r_num WHERE id = :t_id");
            $stmt->execute([':r_num' => $nextRound, ':t_id' => $tournamentId]);

            $this->db->commit();
            return $nextRound;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Compute Swiss pairings
     */
    private function computeSwissPairings(array $players, int $roundNumber): array {
        // Step 1: Assign Bye if odd number of players
        $byePlayer = null;
        $eligibleForBye = $players;

        if (count($players) % 2 !== 0) {
            // Sort by score ascending, then by initial_seed descending to find candidate for bye
            usort($eligibleForBye, function($a, $b) {
                if ($a['points'] != $b['points']) {
                    return $a['points'] <=> $b['points']; // Lowest score first
                }
                return $b['initial_seed'] <=> $a['initial_seed']; // Lowest seed (highest seed number) first
            });

            // Find first player who has not received a bye yet
            foreach ($eligibleForBye as $idx => $candidate) {
                if (($candidate['byes_received'] ?? 0) === 0) {
                    $byePlayer = $candidate;
                    // Remove candidate from paired list
                    $pIndex = array_search($candidate['player_id'], array_column($players, 'player_id'));
                    if ($pIndex !== false) {
                        array_splice($players, $pIndex, 1);
                    }
                    break;
                }
            }

            // Fallback: if all active players received a bye, pick lowest scoring player
            if ($byePlayer === null && !empty($eligibleForBye)) {
                $byePlayer = $eligibleForBye[0];
                $pIndex = array_search($byePlayer['player_id'], array_column($players, 'player_id'));
                if ($pIndex !== false) {
                    array_splice($players, $pIndex, 1);
                }
            }
        }

        // Group players into Score Groups sorted descending
        usort($players, function($a, $b) {
            if ($a['points'] != $b['points']) {
                return $b['points'] <=> $a['points'];
            }
            return $a['initial_seed'] <=> $b['initial_seed'];
        });

        // Backtracking matching engine to pair players without repeat opponents and balance colors
        $pairedGames = $this->pairPlayersRecursive($players, $roundNumber);

        return [
            'games' => $pairedGames,
            'bye' => $byePlayer
        ];
    }

    /**
     * Recursive matching engine for Swiss pairing
     */
    private function pairPlayersRecursive(array $players, int $roundNumber): array {
        if (empty($players)) {
            return [];
        }

        $player1 = array_shift($players);
        $p1Id = (int)$player1['player_id'];

        // Find candidate opponents
        for ($i = 0; $i < count($players); $i++) {
            $candidate = $players[$i];
            $candId = (int)$candidate['player_id'];

            // Constraint: Non-repeat opponents
            if (in_array($candId, $player1['opponents'])) {
                continue; // Repeat opponent, skip
            }

            // Valid candidate found! Determine colors
            $colorAlloc = $this->determineColors($player1, $candidate, $roundNumber);

            $game = [
                'white_id' => $colorAlloc['white_id'],
                'black_id' => $colorAlloc['black_id'],
                'notes' => $colorAlloc['notes']
            ];

            // Recurse with remaining players
            $remaining = $players;
            array_splice($remaining, $i, 1);

            $subResult = $this->pairPlayersRecursive($remaining, $roundNumber);

            if ($subResult !== null) { // Success!
                array_unshift($subResult, $game);
                return $subResult;
            }
        }

        // Fallback: If strict non-repeat fails (unavoidable in tight score groups/late rounds), relax non-repeat
        // Try pairing with first available candidate with log note
        if (!empty($players)) {
            $candidate = $players[0];
            $remaining = $players;
            array_shift($remaining);

            $colorAlloc = $this->determineColors($player1, $candidate, $roundNumber);

            $game = [
                'white_id' => $colorAlloc['white_id'],
                'black_id' => $colorAlloc['black_id'],
                'notes' => "Unavoidable pairing exception: repeated opponent due to limited player pool."
            ];

            $subResult = $this->pairPlayersRecursive($remaining, $roundNumber);
            if ($subResult !== null) {
                array_unshift($subResult, $game);
                return $subResult;
            }
        }

        return [];
    }

    /**
     * Determine White vs Black allocation
     */
    private function determineColors(array $p1, array $p2, int $roundNumber): array {
        $p1Hist = $p1['color_history'] ?? [];
        $p2Hist = $p2['color_history'] ?? [];

        $p1WhiteCount = count(array_filter($p1Hist, fn($c) => $c === 'W'));
        $p1BlackCount = count(array_filter($p1Hist, fn($c) => $c === 'B'));
        $p2WhiteCount = count(array_filter($p2Hist, fn($c) => $c === 'W'));
        $p2BlackCount = count(array_filter($p2Hist, fn($c) => $c === 'B'));

        $p1Diff = $p1WhiteCount - $p1BlackCount; // > 0 means White preference, < 0 Black preference
        $p2Diff = $p2WhiteCount - $p2BlackCount;

        // Check last colors
        $p1Last = end($p1Hist);
        $p2Last = end($p2Hist);

        // Preference: lower diff gets White (needs White), or alternating last color
        if ($p1Diff < $p2Diff) {
            $white = $p1; $black = $p2;
        } elseif ($p2Diff < $p1Diff) {
            $white = $p2; $black = $p1;
        } else {
            // Equal diff, check last color alternate
            if ($p1Last === 'B' && $p2Last === 'W') {
                $white = $p1; $black = $p2;
            } elseif ($p2Last === 'B' && $p1Last === 'W') {
                $white = $p2; $black = $p1;
            } else {
                // Higher initial seed gets White preference in odd round, Black in even
                if ($p1['initial_seed'] < $p2['initial_seed']) {
                    $white = ($roundNumber % 2 !== 0) ? $p1 : $p2;
                    $black = ($roundNumber % 2 !== 0) ? $p2 : $p1;
                } else {
                    $white = ($roundNumber % 2 !== 0) ? $p2 : $p1;
                    $black = ($roundNumber % 2 !== 0) ? $p1 : $p2;
                }
            }
        }

        return [
            'white_id' => (int)$white['player_id'],
            'black_id' => (int)$black['player_id'],
            'notes' => null
        ];
    }

    /**
     * Helper to load player match history
     */
    public function getPlayerHistories(int $tournamentId): array {
        $stmt = $this->db->prepare("
            SELECT p.id as player_id FROM players p
            JOIN tournament_players tp ON p.id = tp.player_id
            WHERE tp.tournament_id = :t_id
        ");
        $stmt->execute([':t_id' => $tournamentId]);
        $players = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $histories = [];
        foreach ($players as $p) {
            $pid = (int)$p['player_id'];
            $histories[$pid] = [
                'points' => 0.0,
                'byes_count' => 0,
                'opponents' => [],
                'color_history' => []
            ];
        }

        // Fetch all pairings in completed/in_progress rounds
        $stmt = $this->db->prepare("
            SELECT pr.*, r.round_number
            FROM pairings pr
            JOIN rounds r ON pr.round_id = r.id
            WHERE r.tournament_id = :t_id
            ORDER BY r.round_number ASC, pr.board_number ASC
        ");
        $stmt->execute([':t_id' => $tournamentId]);
        $pairings = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($pairings as $pair) {
            if ($pair['is_bye'] == 1) {
                $byePid = (int)$pair['bye_player_id'];
                if (isset($histories[$byePid])) {
                    $histories[$byePid]['byes_count']++;
                    $histories[$byePid]['points'] += (float)$pair['bye_points'];
                }
                continue;
            }

            $wId = (int)$pair['white_player_id'];
            $bId = (int)$pair['black_player_id'];
            $res = $pair['result'];

            if (isset($histories[$wId])) {
                $histories[$wId]['opponents'][] = $bId;
                $histories[$wId]['color_history'][] = 'W';
            }
            if (isset($histories[$bId])) {
                $histories[$bId]['opponents'][] = $wId;
                $histories[$bId]['color_history'][] = 'B';
            }

            // Results point allocation
            if ($res === '1-0' || $res === '1-0F') {
                if (isset($histories[$wId])) $histories[$wId]['points'] += 1.0;
            } elseif ($res === '0-1' || $res === '0-1F') {
                if (isset($histories[$bId])) $histories[$bId]['points'] += 1.0;
            } elseif ($res === '1/2-1/2' || $res === '0.5-0.5' || $res === '½-½') {
                if (isset($histories[$wId])) $histories[$wId]['points'] += 0.5;
                if (isset($histories[$bId])) $histories[$bId]['points'] += 0.5;
            }
        }

        return $histories;
    }
}
