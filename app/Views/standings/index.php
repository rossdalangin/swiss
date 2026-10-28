<?php
$pageTitle = "Standings & Leaderboard";
require __DIR__ . '/../layouts/header.php';
?>

<div class="row mb-4 align-items-center">
    <div class="col-md-7">
        <h2 class="fw-bold"><i class="fa-solid fa-list-ol text-warning me-2"></i>Tournament Standings & Leaderboard</h2>
        <p class="text-muted mb-0">Official standings automatically computed according to FIDE Swiss tiebreak regulations.</p>
    </div>
    <div class="col-md-5 text-md-end mt-3 mt-md-0 no-print">
        <a href="?route=export/standings&tournament_id=<?= $tournament['id'] ?>" class="btn btn-outline-dark fw-bold me-2">
            <i class="fa-solid fa-file-csv me-1"></i> Export Standings CSV
        </a>
        <button onclick="window.print()" class="btn btn-secondary fw-bold">
            <i class="fa-solid fa-print me-1"></i> Print Standings
        </button>
    </div>
</div>

<?php if ($tournament['status'] === 'Completed' && !empty($standings)): ?>
    <div class="champion-banner mb-4 text-center">
        <i class="fa-solid fa-crown fa-3x text-warning mb-2"></i>
        <h3 class="fw-bold text-dark mb-1">OFFICIAL FINAL STANDINGS</h3>
        <h2 class="display-6 fw-bold text-success mb-2"><?= htmlspecialchars($standings[0]['full_name']) ?></h2>
        <p class="fs-5 text-muted mb-0">
            Tournament Champion | Final Score: <strong><?= number_format($standings[0]['points'], 1) ?> Points</strong> | Buchholz Cut-1: <strong><?= number_format($standings[0]['tb_buchholz_cut1'], 2) ?></strong>
        </p>
    </div>
<?php endif; ?>

<div class="card pcc-card shadow mb-4">
    <div class="card-header pcc-card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fa-solid fa-trophy text-warning me-2"></i>
            <?= ($tournament['status'] === 'Completed') ? 'FINAL STANDINGS' : "Provisional Standings — Round {$tournament['current_round']} of {$tournament['total_rounds']}" ?>
        </h5>
        <span class="badge bg-secondary"><?= count($standings) ?> Players</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-pcc table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Player Name</th>
                        <th>Rating</th>
                        <th>Initial Seed</th>
                        <th>Played</th>
                        <th>Wins</th>
                        <th>Draws</th>
                        <th>Losses</th>
                        <th>Byes</th>
                        <th>Points</th>
                        <th>Buchholz Cut-1</th>
                        <th>Buchholz</th>
                        <th>Sonneborn-Berger</th>
                        <th>Progressive</th>
                        <th class="no-print">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($standings)): ?>
                        <tr>
                            <td colspan="15" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-list-ol fa-2x mb-3 d-block"></i>
                                No standings recorded yet. Start Round 1 and submit game results to calculate rankings.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($standings as $s): ?>
                            <tr class="<?= ($s['rank'] == 1) ? 'table-warning fw-bold' : '' ?>">
                                <td>
                                    <?php if ($s['rank'] == 1): ?>
                                        <i class="fa-solid fa-trophy text-warning me-1"></i>
                                    <?php elseif ($s['rank'] == 2): ?>
                                        <i class="fa-solid fa-medal text-secondary me-1"></i>
                                    <?php elseif ($s['rank'] == 3): ?>
                                        <i class="fa-solid fa-award text-amber me-1"></i>
                                    <?php endif; ?>
                                    #<?= $s['rank'] ?>
                                </td>
                                <td>
                                    <a href="#" onclick="showPlayerHistory(<?= $s['player_id'] ?>, <?= $tournament['id'] ?>); return false;" class="text-dark fw-semibold text-decoration-none">
                                        <?= htmlspecialchars($s['full_name']) ?>
                                    </a>
                                </td>
                                <td><?= $s['fide_rating'] ? htmlspecialchars($s['fide_rating']) : ($s['national_rating'] ? htmlspecialchars($s['national_rating']) : '<span class="badge bg-light text-dark border">Unrated</span>') ?></td>
                                <td>#<?= $s['initial_seed'] ?></td>
                                <td><?= $s['played_games'] ?></td>
                                <td class="text-success"><?= $s['wins'] ?></td>
                                <td class="text-primary"><?= $s['draws'] ?></td>
                                <td class="text-danger"><?= $s['losses'] ?></td>
                                <td><?= $s['byes'] ?></td>
                                <td class="fw-bold text-success fs-6"><?= number_format($s['points'], 1) ?></td>
                                <td><?= number_format($s['tb_buchholz_cut1'], 2) ?></td>
                                <td><?= number_format($s['tb_buchholz'], 2) ?></td>
                                <td><?= number_format($s['tb_sonneborn_berger'], 2) ?></td>
                                <td><?= number_format($s['tb_progressive'], 2) ?></td>
                                <td class="no-print">
                                    <button class="btn btn-sm btn-outline-primary" onclick="showPlayerHistory(<?= $s['player_id'] ?>, <?= $tournament['id'] ?>)">
                                        <i class="fa-solid fa-user me-1"></i> History
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Tiebreak Methodology Reference Box -->
<div class="card pcc-card no-print mb-4">
    <div class="card-header bg-dark text-white fw-bold">
        <i class="fa-solid fa-calculator text-warning me-2"></i> Official FIDE Tiebreak Methodology Reference
    </div>
    <div class="card-body small text-muted">
        <div class="row g-3">
            <div class="col-md-3">
                <strong>1. Buchholz Cut-1:</strong><br>
                Sum of scores of all opponents faced, excluding the lowest-scoring opponent.
            </div>
            <div class="col-md-3">
                <strong>2. Buchholz Total:</strong><br>
                Sum of scores of all opponents faced in the tournament.
            </div>
            <div class="col-md-3">
                <strong>3. Sonneborn-Berger:</strong><br>
                Sum of scores of defeated opponents plus half the scores of drawn opponents.
            </div>
            <div class="col-md-3">
                <strong>4. Progressive Score:</strong><br>
                Cumulative total of tournament points accumulated after each round.
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
