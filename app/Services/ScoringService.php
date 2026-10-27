<?php

namespace App\Services;

use PDO;
use InvalidArgumentException;

class ScoringService {
    private PDO $db;

    public function __construct(?PDO $db = null) {
        $this->db = $db ?? Database::getInstance();
    }

    /**
     * Record game result or bye result.
     */
    public function recordResult(int $pairingId, string $result, ?string $adminReason = null, ?int $userId = null): bool {
        $validResults = ['1-0', '0-1', '1/2-1/2', '0.5-0.5', '½-½', '0-0', '1-0F', '0-1F', '0-0F', 'pending'];
        if (!in_array($result, $validResults)) {
            throw new InvalidArgumentException("Invalid game result value: $result");
        }

        // Fetch current pairing record
        $stmt = $this->db->prepare("SELECT * FROM pairings WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $pairingId]);
        $pairing = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$pairing) {
            throw new InvalidArgumentException("Pairing record not found.");
        }

        $oldResult = $pairing['result'];
        $tournamentId = (int)$pairing['tournament_id'];

        $this->db->beginTransaction();

        try {
            // Update pairing result
            $stmt = $this->db->prepare("UPDATE pairings SET result = :res, updated_at = CURRENT_TIMESTAMP WHERE id = :id");
            $stmt->execute([':res' => $result, ':id' => $pairingId]);

            // Audit log if modifying an already recorded non-pending result
            if ($oldResult !== 'pending' && $oldResult !== $result) {
                $auditStmt = $this->db->prepare("INSERT INTO audit_logs (tournament_id, user_id, action, details) VALUES (:t_id, :u_id, :action, :details)");
                $auditStmt->execute([
                    ':t_id' => $tournamentId,
                    ':u_id' => $userId,
                    ':action' => 'RESULT_CORRECTION',
                    ':details' => "Board {$pairing['board_number']} Round {$pairing['round_number']} result changed from '{$oldResult}' to '{$result}'. Reason: " . ($adminReason ?? 'Administrative correction')
                ]);
            }

            // Recalculate standings for tournament
            $this->recalculateStandings($tournamentId);

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Recalculate tournament standings and tiebreaks for all players
     */
    public function recalculateStandings(int $tournamentId): array {
        // Fetch tournament settings
        $stmt = $this->db->prepare("SELECT * FROM tournaments WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $tournamentId]);
        $tournament = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$tournament) {
            throw new InvalidArgumentException("Tournament not found.");
        }

        $tiebreakConfig = !empty($tournament['tiebreak_config']) ? json_decode($tournament['tiebreak_config'], true) : [
            'buchholz_cut1', 'buchholz', 'sonneborn_berger', 'progressive', 'direct_encounter', 'wins', 'black_wins'
        ];

        // Fetch all registered players
        $stmt = $this->db->prepare("
            SELECT tp.player_id, tp.initial_seed, p.full_name, p.fide_rating, p.national_rating, p.club_rating
            FROM tournament_players tp
            JOIN players p ON tp.player_id = p.id
            WHERE tp.tournament_id = :t_id
        ");
        $stmt->execute([':t_id' => $tournamentId]);
        $registered = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch all non-pending pairings
        $stmt = $this->db->prepare("
            SELECT p.*, r.round_number
            FROM pairings p
            JOIN rounds r ON p.round_id = r.id
            WHERE r.tournament_id = :t_id AND p.result != 'pending'
            ORDER BY r.round_number ASC
        ");
        $stmt->execute([':t_id' => $tournamentId]);
        $pairings = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Initialize statistics map per player
        $stats = [];
        foreach ($registered as $p) {
            $pid = (int)$p['player_id'];
            $stats[$pid] = [
                'player_id' => $pid,
                'full_name' => $p['full_name'],
                'initial_seed' => $p['initial_seed'],
                'points' => 0.0,
                'played_games' => 0,
                'wins' => 0,
                'draws' => 0,
                'losses' => 0,
                'byes' => 0,
                'black_wins' => 0,
                'opponents' => [], // array of opponent pids
                'opponents_results' => [], // pid => outcome vs opponent ('W', 'D', 'L')
                'round_scores' => [], // round_num => cumulative points up to that round
                'tb_buchholz' => 0.0,
                'tb_buchholz_cut1' => 0.0,
                'tb_sonneborn_berger' => 0.0,
                'tb_progressive' => 0.0,
                'tb_direct_encounter' => 0.0,
            ];
        }

        // Process completed pairings to compute points, played_games, wins, draws, losses, byes
        $maxRoundProcessed = 0;
        foreach ($pairings as $pair) {
            $rNum = (int)$pair['round_number'];
            if ($rNum > $maxRoundProcessed) $maxRoundProcessed = $rNum;

            if ($pair['is_bye'] == 1) {
                $byePid = (int)$pair['bye_player_id'];
                if (isset($stats[$byePid])) {
                    $stats[$byePid]['byes']++;
                    $stats[$byePid]['points'] += (float)$pair['bye_points'];
                    $stats[$byePid]['round_scores'][$rNum] = $stats[$byePid]['points'];
                }
                continue;
            }

            $wId = (int)$pair['white_player_id'];
            $bId = (int)$pair['black_player_id'];
            $res = $pair['result'];

            $isForfeit = in_array($res, ['1-0F', '0-1F', '0-0F']);

            // Both players recorded as opponents
            if (isset($stats[$wId]) && isset($stats[$bId])) {
                $stats[$wId]['opponents'][] = $bId;
                $stats[$bId]['opponents'][] = $wId;

                if (!$isForfeit) {
                    $stats[$wId]['played_games']++;
                    $stats[$bId]['played_games']++;
                }
            }

            if ($res === '1-0' || $res === '1-0F') {
                if (isset($stats[$wId])) {
                    $stats[$wId]['wins']++;
                    $stats[$wId]['points'] += 1.0;
                    $stats[$wId]['opponents_results'][$bId] = 'W';
                }
                if (isset($stats[$bId])) {
                    $stats[$bId]['losses']++;
                    $stats[$bId]['opponents_results'][$wId] = 'L';
                }
            } elseif ($res === '0-1' || $res === '0-1F') {
                if (isset($stats[$bId])) {
                    $stats[$bId]['wins']++;
                    $stats[$bId]['black_wins']++;
                    $stats[$bId]['points'] += 1.0;
                    $stats[$bId]['opponents_results'][$wId] = 'W';
                }
                if (isset($stats[$wId])) {
                    $stats[$wId]['losses']++;
                    $stats[$wId]['opponents_results'][$bId] = 'L';
                }
            } elseif (in_array($res, ['1/2-1/2', '0.5-0.5', '½-½'])) {
                if (isset($stats[$wId])) {
                    $stats[$wId]['draws']++;
                    $stats[$wId]['points'] += 0.5;
                    $stats[$wId]['opponents_results'][$bId] = 'D';
                }
                if (isset($stats[$bId])) {
                    $stats[$bId]['draws']++;
                    $stats[$bId]['points'] += 0.5;
                    $stats[$bId]['opponents_results'][$wId] = 'D';
                }
            } elseif ($res === '0-0' || $res === '0-0F') {
                if (isset($stats[$wId])) $stats[$wId]['losses']++;
                if (isset($stats[$bId])) $stats[$bId]['losses']++;
            }

            if (isset($stats[$wId])) $stats[$wId]['round_scores'][$rNum] = $stats[$wId]['points'];
            if (isset($stats[$bId])) $stats[$bId]['round_scores'][$rNum] = $stats[$bId]['points'];
        }

        // Fill round scores forward for players who had byes or missed rounds
        foreach ($stats as &$s) {
            $running = 0.0;
            for ($r = 1; $r <= $maxRoundProcessed; $r++) {
                if (isset($s['round_scores'][$r])) {
                    $running = $s['round_scores'][$r];
                } else {
                    $s['round_scores'][$r] = $running;
                }
            }
        }
        unset($s);

        // Compute Tiebreaks
        foreach ($stats as &$s) {
            $oppScores = [];
            $sbSum = 0.0;

            foreach ($s['opponents'] as $oppId) {
                $oppPoints = $stats[$oppId]['points'] ?? 0.0;
                $oppScores[] = $oppPoints;

                $outcome = $s['opponents_results'][$oppId] ?? null;
                if ($outcome === 'W') {
                    $sbSum += $oppPoints;
                } elseif ($outcome === 'D') {
                    $sbSum += ($oppPoints * 0.5);
                }
            }

            // Buchholz = Sum of opponent scores
            $s['tb_buchholz'] = array_sum($oppScores);

            // Buchholz Cut-1 = Sum minus lowest opponent score
            if (count($oppScores) > 1) {
                sort($oppScores);
                array_shift($oppScores); // Remove lowest
                $s['tb_buchholz_cut1'] = array_sum($oppScores);
            } else {
                $s['tb_buchholz_cut1'] = $s['tb_buchholz'];
            }

            // Sonneborn-Berger
            $s['tb_sonneborn_berger'] = $sbSum;

            // Progressive Score = Sum of cumulative points at end of each round
            $s['tb_progressive'] = array_sum($s['round_scores']);
        }
        unset($s);

        // Direct Encounter tiebreak calculation for tied players
        // Convert to array for sorting
        $standingsList = array_values($stats);

        usort($standingsList, function($a, $b) use ($tiebreakConfig) {
            // First compare main tournament points
            if ($a['points'] != $b['points']) {
                return $b['points'] <=> $a['points'];
            }

            // Compare configurable tiebreaks in order
            foreach ($tiebreakConfig as $tb) {
                if ($tb === 'buchholz_cut1' && $a['tb_buchholz_cut1'] != $b['tb_buchholz_cut1']) {
                    return $b['tb_buchholz_cut1'] <=> $a['tb_buchholz_cut1'];
                }
                if ($tb === 'buchholz' && $a['tb_buchholz'] != $b['tb_buchholz']) {
                    return $b['tb_buchholz'] <=> $a['tb_buchholz'];
                }
                if ($tb === 'sonneborn_berger' && $a['tb_sonneborn_berger'] != $b['tb_sonneborn_berger']) {
                    return $b['tb_sonneborn_berger'] <=> $a['tb_sonneborn_berger'];
                }
                if ($tb === 'progressive' && $a['tb_progressive'] != $b['tb_progressive']) {
                    return $b['tb_progressive'] <=> $a['tb_progressive'];
                }
                if ($tb === 'wins' && $a['wins'] != $b['wins']) {
                    return $b['wins'] <=> $a['wins'];
                }
                if ($tb === 'black_wins' && $a['black_wins'] != $b['black_wins']) {
                    return $b['black_wins'] <=> $a['black_wins'];
                }
            }

            // Secondary tiebreak: Initial seed ascending
            return $a['initial_seed'] <=> $b['initial_seed'];
        });

        // Assign ranks (1..N) and save to DB
        $stmtSave = $this->db->prepare("
            INSERT INTO tournament_standings (
                tournament_id, player_id, rank, points, played_games, wins, draws, losses, byes,
                tb_buchholz_cut1, tb_buchholz, tb_sonneborn_berger, tb_progressive, tb_direct_encounter,
                tb_wins, tb_black_wins
            ) VALUES (
                :t_id, :p_id, :rank, :pts, :played, :wins, :draws, :losses, :byes,
                :cut1, :buchholz, :sb, :prog, :de, :tb_wins, :black_wins
            ) ON CONFLICT(tournament_id, player_id) DO UPDATE SET
                rank = EXCLUDED.rank,
                points = EXCLUDED.points,
                played_games = EXCLUDED.played_games,
                wins = EXCLUDED.wins,
                draws = EXCLUDED.draws,
                losses = EXCLUDED.losses,
                byes = EXCLUDED.byes,
                tb_buchholz_cut1 = EXCLUDED.tb_buchholz_cut1,
                tb_buchholz = EXCLUDED.tb_buchholz,
                tb_sonneborn_berger = EXCLUDED.tb_sonneborn_berger,
                tb_progressive = EXCLUDED.tb_progressive,
                tb_direct_encounter = EXCLUDED.tb_direct_encounter,
                tb_wins = EXCLUDED.tb_wins,
                tb_black_wins = EXCLUDED.tb_black_wins
        ");

        $rank = 1;
        foreach ($standingsList as &$item) {
            $item['rank'] = $rank;

            // Execute insert or update query (fallback compatible with SQLite ON CONFLICT and MySQL ON DUPLICATE)
            try {
                $stmtSave->execute([
                    ':t_id' => $tournamentId,
                    ':p_id' => $item['player_id'],
                    ':rank' => $rank,
                    ':pts' => $item['points'],
                    ':played' => $item['played_games'],
                    ':wins' => $item['wins'],
                    ':draws' => $item['draws'],
                    ':losses' => $item['losses'],
                    ':byes' => $item['byes'],
                    ':cut1' => $item['tb_buchholz_cut1'],
                    ':buchholz' => $item['tb_buchholz'],
                    ':sb' => $item['tb_sonneborn_berger'],
                    ':prog' => $item['tb_progressive'],
                    ':de' => $item['tb_direct_encounter'],
                    ':tb_wins' => $item['wins'],
                    ':black_wins' => $item['black_wins'],
                ]);
            } catch (\Exception $e) {
                // MySQL fallback if ON CONFLICT syntax differs
                $mySqlSave = $this->db->prepare("
                    INSERT INTO tournament_standings (
                        tournament_id, player_id, rank, points, played_games, wins, draws, losses, byes,
                        tb_buchholz_cut1, tb_buchholz, tb_sonneborn_berger, tb_progressive, tb_direct_encounter,
                        tb_wins, tb_black_wins
                    ) VALUES (
                        :t_id, :p_id, :rank, :pts, :played, :wins, :draws, :losses, :byes,
                        :cut1, :buchholz, :sb, :prog, :de, :tb_wins, :black_wins
                    ) ON DUPLICATE KEY UPDATE
                        rank = VALUES(rank),
                        points = VALUES(points),
                        played_games = VALUES(played_games),
                        wins = VALUES(wins),
                        draws = VALUES(draws),
                        losses = VALUES(losses),
                        byes = VALUES(byes),
                        tb_buchholz_cut1 = VALUES(tb_buchholz_cut1),
                        tb_buchholz = VALUES(tb_buchholz),
                        tb_sonneborn_berger = VALUES(tb_sonneborn_berger),
                        tb_progressive = VALUES(tb_progressive),
                        tb_direct_encounter = VALUES(tb_direct_encounter),
                        tb_wins = VALUES(tb_wins),
                        tb_black_wins = VALUES(tb_black_wins)
                ");
                $mySqlSave->execute([
                    ':t_id' => $tournamentId,
                    ':p_id' => $item['player_id'],
                    ':rank' => $rank,
                    ':pts' => $item['points'],
                    ':played' => $item['played_games'],
                    ':wins' => $item['wins'],
                    ':draws' => $item['draws'],
                    ':losses' => $item['losses'],
                    ':byes' => $item['byes'],
                    ':cut1' => $item['tb_buchholz_cut1'],
                    ':buchholz' => $item['tb_buchholz'],
                    ':sb' => $item['tb_sonneborn_berger'],
                    ':prog' => $item['tb_progressive'],
                    ':de' => $item['tb_direct_encounter'],
                    ':tb_wins' => $item['wins'],
                    ':black_wins' => $item['black_wins'],
                ]);
            }

            $rank++;
        }
        unset($item);

        return $standingsList;
    }

    public function getStandings(int $tournamentId): array {
        $stmt = $this->db->prepare("
            SELECT ts.*, p.full_name, p.fide_rating, p.national_rating, p.club_rating,
                   tp.initial_seed, tp.status as player_status
            FROM tournament_standings ts
            JOIN players p ON ts.player_id = p.id
            JOIN tournament_players tp ON (ts.tournament_id = tp.tournament_id AND ts.player_id = tp.player_id)
            WHERE ts.tournament_id = :t_id
            ORDER BY ts.rank ASC
        ");
        $stmt->execute([':t_id' => $tournamentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
