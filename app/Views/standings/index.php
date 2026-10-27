<?php
$pageTitle = "Tournament Standings";
require __DIR__ . '/../layouts/header.php';
?>

<div class="row mb-4 align-items-center">
    <div class="col-md-7">
        <h2 class="fw-bold mb-1"><i class="fa-solid fa-list-ol text-warning me-2"></i> Tournament Leaderboard & Standings</h2>
        <p class="text-muted mb-0">
            <?= htmlspecialchars($tournament['name']) ?> &mdash;
            <strong><?= ($tournament['status'] === 'Completed') ? 'FINAL STANDINGS' : 'Provisional Standings (Round ' . $tournament['current_round'] . ' of ' . $tournament['total_rounds'] . ')' ?></strong>
        </p>
    </div>
    <div class="col-md-5 text-md-end mt-3 mt-md-0 no-print">
        <button onclick="window.print()" class="btn btn-secondary me-1"><i class="fa-solid fa-print me-1"></i> Print Standings</button>
        <a href="?route=export/standings&tournament_id=<?= $tournament['id'] ?>" class="btn btn-outline-dark"><i class="fa-solid fa-file-csv me-1"></i> Export Standings CSV</a>
    </div>
</div>

<!-- Tiebreak Methodology Reference Box -->
<div class="alert alert-info border-0 shadow-sm mb-4 no-print">
    <div class="d-flex align-items-start">
        <i class="fa-solid fa-calculator text-info fa-lg me-3 mt-1"></i>
        <div>
            <strong>Tiebreak Calculation Rules & Methodology:</strong>
            <p class="mb-0 small text-dark">
                • <strong>Buchholz Cut-1 (BH-C1):</strong> Sum of opponents' final tournament scores, excluding the lowest-scoring opponent.<br>
                • <strong>Buchholz (BH):</strong> Total sum of all opponents' final scores.<br>
                • <strong>Sonneborn-Berger (SB):</strong> Sum of defeated opponents' scores plus half of drawn opponents' scores.<br>
                • <strong>Progressive Score (PS):</strong> Cumulative sum of tournament score at the end of each round.
            </p>
        </div>
    </div>
</div>

<!-- Champion / Podium Display if Completed -->
<?php if (!empty($standings) && $tournament['status'] === 'Completed'): ?>
    <div class="card pcc-card champion-card mb-4 p-4 text-center">
        <div class="row align-items-center">
            <div class="col-md-4 mb-3 mb-md-0 border-end">
                <i class="fa-solid fa-trophy fa-4x text-warning mb-2"></i>
                <div class="text-uppercase fw-bold text-muted small">Tournament Champion</div>
                <h2 class="fw-bold text-dark mb-0"><?= htmlspecialchars($standings[0]['full_name']) ?></h2>
                <div class="fs-4 text-primary fw-bold mt-1"><?= number_format($standings[0]['points'], 1) ?> Points</div>
            </div>
            <div class="col-md-8">
                <div class="row g-2 text-start">
                    <?php if (isset($standings[1])): ?>
                        <div class="col-6">
                            <div class="p-3 bg-white rounded border">
                                <div class="badge bg-secondary mb-1">2nd Place</div>
                                <h5 class="fw-bold mb-1"><?= htmlspecialchars($standings[1]['full_name']) ?></h5>
                                <div class="text-primary fw-bold"><?= number_format($standings[1]['points'], 1) ?> pts</div>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if (isset($standings[2])): ?>
                        <div class="col-6">
                            <div class="p-3 bg-white rounded border">
                                <div class="badge bg-bronze mb-1" style="background:#cd7f32; color:#fff;">3rd Place</div>
                                <h5 class="fw-bold mb-1"><?= htmlspecialchars($standings[2]['full_name']) ?></h5>
                                <div class="text-primary fw-bold"><?= number_format($standings[2]['points'], 1) ?> pts</div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Full Standings Table -->
<div class="card pcc-card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th width="60">Rank</th>
                        <th>Player Name</th>
                        <th>Rating</th>
                        <th>Initial Seed</th>
                        <th>Played</th>
                        <th>W</th>
                        <th>D</th>
                        <th>L</th>
                        <th>Byes</th>
                        <th>Points</th>
                        <th>BH Cut-1</th>
                        <th>Buchholz</th>
                        <th>Sonneborn-Berger</th>
                        <th>Progressive</th>
                        <th width="80" class="no-print">History</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($standings)): ?>
                        <tr>
                            <td colspan="15" class="text-center py-5 text-muted">No standings generated yet. Enter match results in Pairings to populate standings.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($standings as $s): ?>
                            <tr class="<?= ($s['rank'] == 1) ? 'table-warning fw-bold' : '' ?>">
                                <td>
                                    <?php if ($s['rank'] == 1): ?>
                                        <span class="badge bg-warning text-dark"><i class="fa-solid fa-crown me-1"></i>1</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary"><?= $s['rank'] ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="javascript:void(0)" onclick="viewPlayerHistory(<?= $tournament['id'] ?>, <?= $s['player_id'] ?>)" class="text-decoration-none fw-bold text-dark">
                                        <?= htmlspecialchars($s['full_name']) ?>
                                    </a>
                                </td>
                                <td><?= $s['fide_rating'] ? htmlspecialchars($s['fide_rating']) : '<span class="text-muted">Unrated</span>' ?></td>
                                <td>#<?= $s['initial_seed'] ?></td>
                                <td><?= $s['played_games'] ?></td>
                                <td><span class="text-success fw-bold"><?= $s['wins'] ?></span></td>
                                <td><span class="text-info fw-bold"><?= $s['draws'] ?></span></td>
                                <td><span class="text-danger fw-bold"><?= $s['losses'] ?></span></td>
                                <td><?= $s['byes'] ?></td>
                                <td class="fs-5 text-primary fw-bold"><?= number_format($s['points'], 1) ?></td>
                                <td><?= number_format($s['tb_buchholz_cut1'], 1) ?></td>
                                <td><?= number_format($s['tb_buchholz'], 1) ?></td>
                                <td><?= number_format($s['tb_sonneborn_berger'], 1) ?></td>
                                <td><?= number_format($s['tb_progressive'], 1) ?></td>
                                <td class="no-print">
                                    <button onclick="viewPlayerHistory(<?= $tournament['id'] ?>, <?= $s['player_id'] ?>)" class="btn btn-sm btn-outline-info" title="View Profile & History"><i class="fa-solid fa-eye"></i></button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
