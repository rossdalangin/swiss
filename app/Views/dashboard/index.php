<?php
$pageTitle = "Tournament Dashboard";
require __DIR__ . '/../layouts/header.php';
?>

<?php if ($tournament): ?>
    <!-- Tournament Header Banner -->
    <div class="card pcc-card mb-4 border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <div class="d-flex align-items-center mb-2">
                        <span class="badge bg-dark text-warning me-2 px-3 py-2 fs-6">
                            <i class="fa-solid fa-trophy me-1"></i> <?= htmlspecialchars($tournament['status']) ?>
                        </span>
                        <small class="text-muted"><i class="fa-solid fa-calendar me-1"></i> <?= htmlspecialchars($tournament['date']) ?> | <i class="fa-solid fa-location-dot me-1"></i> <?= htmlspecialchars($tournament['venue']) ?></small>
                    </div>
                    <h2 class="fw-bold mb-1 text-dark"><?= htmlspecialchars($tournament['name']) ?></h2>
                    <p class="text-muted mb-0">
                        <i class="fa-solid fa-user-shield me-1"></i> Organizer / Chief Arbiter: <strong><?= htmlspecialchars($tournament['organizer_name']) ?></strong>
                    </p>
                </div>
                <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                    <!-- Tournament Selector Dropdown -->
                    <form method="GET" action="index.php" class="d-inline-block">
                        <input type="hidden" name="route" value="dashboard">
                        <select name="id" class="form-select border-secondary fw-semibold" onchange="this.form.submit()">
                            <?php foreach ($allTournaments as $t): ?>
                                <option value="<?= $t['id'] ?>" <?= ($t['id'] == $tournament['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($t['name']) ?> (<?= $t['status'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Action Bar -->
    <div class="d-flex flex-wrap gap-2 mb-4">
        <a href="?route=tournament/create" class="btn btn-success fw-bold"><i class="fa-solid fa-plus me-1"></i> Create Tournament</a>
        <a href="?route=players&tournament_id=<?= $tournament['id'] ?>" class="btn btn-primary fw-bold"><i class="fa-solid fa-users me-1"></i> Manage Players</a>
        <a href="?route=pairings&tournament_id=<?= $tournament['id'] ?>" class="btn btn-pcc-gold fw-bold"><i class="fa-solid fa-chess-board me-1"></i> Generate / View Pairings</a>
        <a href="?route=standings&tournament_id=<?= $tournament['id'] ?>" class="btn btn-info text-white fw-bold"><i class="fa-solid fa-list-ol me-1"></i> View Standings</a>
        <a href="?route=standings&tournament_id=<?= $tournament['id'] ?>&print=1" target="_blank" class="btn btn-secondary fw-bold"><i class="fa-solid fa-print me-1"></i> Print Summary</a>
        <a href="?route=export/standings&tournament_id=<?= $tournament['id'] ?>" class="btn btn-outline-dark fw-bold"><i class="fa-solid fa-file-csv me-1"></i> Export Standings CSV</a>
        <?php if ($tournament['status'] !== 'Completed' && $tournament['status'] !== 'Archived'): ?>
            <a href="?route=tournament/finalize&id=<?= $tournament['id'] ?>&csrf_token=<?= $csrfToken ?>" class="btn btn-danger fw-bold" onclick="return confirm('Finalize this tournament and lock standings?')"><i class="fa-solid fa-flag-checkered me-1"></i> Finalize Tournament</a>
        <?php elseif ($tournament['status'] === 'Completed'): ?>
            <a href="?route=tournament/reopen&id=<?= $tournament['id'] ?>&csrf_token=<?= $csrfToken ?>" class="btn btn-warning text-dark fw-bold" onclick="return confirm('Reopen tournament for result corrections?')"><i class="fa-solid fa-unlock me-1"></i> Reopen Tournament</a>
        <?php endif; ?>
    </div>

    <!-- KPI Statistics Widgets -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-widget">
                <div class="stat-widget-title">Total Registered</div>
                <div class="stat-widget-value"><?= $stats['total_players'] ?></div>
                <small class="text-muted"><?= $stats['rated_players'] ?> Rated / <?= $stats['unrated_players'] ?> Unrated</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-widget stat-widget-gold">
                <div class="stat-widget-title">Rounds Status</div>
                <div class="stat-widget-value">Round <?= $stats['current_round'] ?> / <?= $stats['total_rounds'] ?></div>
                <small class="text-muted">Status: <?= htmlspecialchars($tournament['status']) ?></small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-widget stat-widget-success">
                <div class="stat-widget-title">Games Completed</div>
                <div class="stat-widget-value"><?= $stats['completed_games'] ?></div>
                <small class="text-muted"><?= $stats['pending_games'] ?> Awaiting Result</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-widget stat-widget-info">
                <div class="stat-widget-title">Tournament Leader</div>
                <div class="stat-widget-value fs-5 text-truncate"><?= htmlspecialchars($stats['leader_name']) ?></div>
                <small class="text-muted"><?= $stats['total_byes'] ?> Byes Awarded</small>
            </div>
        </div>
    </div>

    <!-- Final Champion Panel (When Completed) -->
    <?php if ($tournament['status'] === 'Completed' && !empty($standings)): ?>
        <div class="champion-banner mb-4 text-center">
            <i class="fa-solid fa-crown fa-3x text-warning mb-2"></i>
            <h3 class="fw-bold text-dark mb-1">TOURNAMENT CHAMPION DECLARED</h3>
            <h2 class="display-6 fw-bold text-success mb-2"><?= htmlspecialchars($standings[0]['full_name']) ?></h2>
            <p class="fs-5 text-muted mb-0">
                Score: <strong><?= number_format($standings[0]['points'], 1) ?> Points</strong> | Buchholz Cut-1: <strong><?= number_format($standings[0]['tb_buchholz_cut1'], 2) ?></strong>
            </p>
        </div>
    <?php endif; ?>

    <!-- Standings Table Summary -->
    <div class="card pcc-card shadow-sm mb-4">
        <div class="card-header pcc-card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fa-solid fa-ranking-star text-warning me-2"></i>
                <?= ($tournament['status'] === 'Completed') ? 'FINAL STANDINGS' : "Provisional Standings (Round {$stats['current_round']} of {$stats['total_rounds']})" ?>
            </h5>
            <a href="?route=standings&tournament_id=<?= $tournament['id'] ?>" class="btn btn-sm btn-outline-light">Full Standings Table &rarr;</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-pcc align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Player Name</th>
                            <th>Rating</th>
                            <th>Initial Seed</th>
                            <th>Played</th>
                            <th>Points</th>
                            <th>Buchholz Cut-1</th>
                            <th>Sonneborn-Berger</th>
                            <th>Actions</th>
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
                                            <i class="fa-solid fa-trophy text-warning me-1"></i>
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
                                    <td class="fw-bold text-success fs-6"><?= number_format($s['points'], 1) ?></td>
                                    <td><?= number_format($s['tb_buchholz_cut1'], 2) ?></td>
                                    <td><?= number_format($s['tb_sonneborn_berger'], 2) ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" onclick="showPlayerHistory(<?= $s['player_id'] ?>, <?= $tournament['id'] ?>)">
                                            <i class="fa-solid fa-user me-1"></i> Profile
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

<?php else: ?>
    <!-- Empty State: No Tournament Created -->
    <div class="card pcc-card text-center p-5 shadow my-5">
        <i class="fa-solid fa-trophy fa-4x text-muted mb-3"></i>
        <h3 class="fw-bold text-dark">No Tournament Created Yet</h3>
        <p class="text-muted max-w-md mx-auto">Get started by creating your first Paete Chess Club Swiss Tournament. Register players, generate round pairings, and automatically compute official standings.</p>
        <div class="mt-3">
            <a href="?route=tournament/create" class="btn btn-pcc-gold px-4 py-2 fw-bold"><i class="fa-solid fa-plus me-1"></i> Create First Tournament</a>
        </div>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
