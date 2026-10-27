<?php
$pageTitle = "Tournament Dashboard";
require __DIR__ . '/../layouts/header.php';
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h2 class="fw-bold mb-1"><i class="fa-solid fa-chess-board text-warning me-2"></i><?= htmlspecialchars($tournament['name'] ?? 'No Active Tournament') ?></h2>
        <p class="text-muted">
            <?php if ($tournament): ?>
                <i class="fa-solid fa-calendar me-1"></i> <?= htmlspecialchars($tournament['date']) ?> |
                <i class="fa-solid fa-location-dot me-1"></i> <?= htmlspecialchars($tournament['venue']) ?> |
                <i class="fa-solid fa-user-tie me-1"></i> <?= htmlspecialchars($tournament['organizer_name']) ?>
            <?php else: ?>
                Create a new tournament to start generating Swiss pairings.
            <?php endif; ?>
        </p>
    </div>
    <div class="col-md-4 text-md-end no-print">
        <?php if (!empty($tournaments)): ?>
            <form method="GET" action="" class="d-inline-block">
                <input type="hidden" name="route" value="dashboard">
                <select name="tournament_id" class="form-select form-select-lg" onchange="this.form.submit()">
                    <?php foreach ($tournaments as $t): ?>
                        <option value="<?= $t['id'] ?>" <?= ($tournament && $tournament['id'] == $t['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($t['name']) ?> (<?= $t['status'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php if ($tournament): ?>
    <!-- Tournament Control Action Buttons -->
    <div class="row g-2 mb-4 no-print">
        <div class="col-auto">
            <a href="?route=tournament/create" class="btn btn-success"><i class="fa-solid fa-plus me-1"></i> Create Tournament</a>
        </div>
        <div class="col-auto">
            <a href="?route=players&tournament_id=<?= $tournament['id'] ?>" class="btn btn-primary"><i class="fa-solid fa-user-plus me-1"></i> Manage Players</a>
        </div>
        <div class="col-auto">
            <a href="?route=pairings&tournament_id=<?= $tournament['id'] ?>" class="btn btn-warning text-dark"><i class="fa-solid fa-chess me-1"></i> Generate / View Pairings</a>
        </div>
        <div class="col-auto">
            <a href="?route=standings&tournament_id=<?= $tournament['id'] ?>" class="btn btn-info text-white"><i class="fa-solid fa-list-ol me-1"></i> View Standings</a>
        </div>
        <div class="col-auto">
            <button onclick="window.print()" class="btn btn-secondary"><i class="fa-solid fa-print me-1"></i> Print Summary</button>
        </div>
        <div class="col-auto">
            <a href="?route=export/standings&tournament_id=<?= $tournament['id'] ?>" class="btn btn-outline-dark"><i class="fa-solid fa-file-csv me-1"></i> Export Standings CSV</a>
        </div>
        <div class="col-auto">
            <?php if ($tournament['status'] === 'Completed'): ?>
                <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#reopenModal"><i class="fa-solid fa-folder-open me-1"></i> Reopen Tournament</button>
            <?php else: ?>
                <a href="?route=tournament/finalize&id=<?= $tournament['id'] ?>&csrf_token=<?= $csrfToken ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to finalize this tournament?')"><i class="fa-solid fa-check-double me-1"></i> Finalize Tournament</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="card pcc-card p-3 border-start border-primary border-4">
                <div class="d-flex align-items-center">
                    <div class="fs-1 text-primary me-3"><i class="fa-solid fa-users"></i></div>
                    <div>
                        <div class="text-muted small">Total Players</div>
                        <div class="fs-3 fw-bold"><?= $stats['total_players'] ?></div>
                        <small class="text-muted"><?= $stats['rated_players'] ?> Rated / <?= $stats['unrated_players'] ?> Unrated</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card pcc-card p-3 border-start border-warning border-4">
                <div class="d-flex align-items-center">
                    <div class="fs-1 text-warning me-3"><i class="fa-solid fa-chess-board"></i></div>
                    <div>
                        <div class="text-muted small">Rounds</div>
                        <div class="fs-3 fw-bold">Round <?= $stats['current_round'] ?> / <?= $stats['total_rounds'] ?></div>
                        <small class="text-muted">Status: <strong><?= $stats['status'] ?></strong></small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card pcc-card p-3 border-start border-success border-4">
                <div class="d-flex align-items-center">
                    <div class="fs-1 text-success me-3"><i class="fa-solid fa-gamepad"></i></div>
                    <div>
                        <div class="text-muted small">Games Completed</div>
                        <div class="fs-3 fw-bold"><?= $stats['completed_games'] ?></div>
                        <small class="text-muted"><?= $stats['pending_games'] ?> Awaiting Result</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card pcc-card p-3 border-start border-info border-4">
                <div class="d-flex align-items-center">
                    <div class="fs-1 text-info me-3"><i class="fa-solid fa-trophy"></i></div>
                    <div>
                        <div class="text-muted small">Tournament Leader</div>
                        <div class="fs-5 fw-bold text-truncate" style="max-width: 160px;"><?= htmlspecialchars($stats['leader']) ?></div>
                        <small class="text-muted"><?= $stats['byes_count'] ?> Byes Awarded</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Leaderboard & Recent Standings Table -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card pcc-card">
                <div class="card-header pcc-card-header d-flex justify-content-between align-items-center">
                    <span class="fs-5 fw-bold"><i class="fa-solid fa-ranking-star me-2 text-warning"></i>
                        <?= ($tournament['status'] === 'Completed') ? 'FINAL STANDINGS' : 'Provisional Standings (Round ' . $stats['current_round'] . ' of ' . $stats['total_rounds'] . ')' ?>
                    </span>
                    <a href="?route=standings&tournament_id=<?= $tournament['id'] ?>" class="btn btn-sm btn-outline-primary no-print">Full Standings Table &rarr;</a>
                </div>
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
                                    <th>Points</th>
                                    <th>Buchholz Cut-1</th>
                                    <th>Sonneborn-Berger</th>
                                    <th width="100">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($standings)): ?>
                                    <tr>
                                        <td colspan="9" class="text-center py-4 text-muted">No standings generated yet. Start Round 1 and enter match results.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach (array_slice($standings, 0, 10) as $s): ?>
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
                                            <td class="fs-5 text-primary fw-bold"><?= number_format($s['points'], 1) ?></td>
                                            <td><?= number_format($s['tb_buchholz_cut1'], 1) ?></td>
                                            <td><?= number_format($s['tb_sonneborn_berger'], 1) ?></td>
                                            <td>
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
        </div>
    </div>

    <!-- Reopen Modal -->
    <div class="modal fade" id="reopenModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="?route=tournament/reopen&id=<?= $tournament['id'] ?>">
                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title"><i class="fa-solid fa-folder-open me-2"></i> Reopen Finalized Tournament</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted">Reopening a finalized tournament allows recording or correcting results. This action will be logged in the audit trail.</p>
                        <div class="mb-3">
                            <label for="reopen_reason" class="form-label fw-bold">Reason for Reopening</label>
                            <textarea class="form-control" name="reason" id="reopen_reason" rows="3" required placeholder="Provide reason for administrative reopening..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Confirm Reopen</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<?php else: ?>
    <div class="text-center my-5 py-5 bg-white pcc-card">
        <i class="fa-solid fa-chess-king fa-4x text-muted mb-3"></i>
        <h3>Welcome to Paete Chess Club Tournament Manager</h3>
        <p class="text-muted mb-4">No tournaments found in the database. Create your first tournament to get started!</p>
        <a href="?route=tournament/create" class="btn btn-success btn-lg"><i class="fa-solid fa-plus me-2"></i> Create First Tournament</a>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
