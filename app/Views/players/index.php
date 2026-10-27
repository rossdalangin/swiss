<?php
$pageTitle = "Player Management";
require __DIR__ . '/../layouts/header.php';
?>

<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h2 class="fw-bold mb-1"><i class="fa-solid fa-users text-warning me-2"></i> Player Roster Management</h2>
        <p class="text-muted mb-0">Register individual players, bulk import via paste/CSV, and manage tournament entry status.</p>
    </div>
    <div class="col-md-6 text-md-end mt-3 mt-md-0 no-print">
        <a href="?route=players/create&tournament_id=<?= $tournamentId ?>" class="btn btn-success me-1"><i class="fa-solid fa-user-plus me-1"></i> Add Individual Player</a>
        <a href="?route=players/bulk_import&tournament_id=<?= $tournamentId ?>" class="btn btn-primary me-1"><i class="fa-solid fa-file-import me-1"></i> Bulk Import (CSV / Text)</a>
        <a href="?route=export/players&tournament_id=<?= $tournamentId ?>" class="btn btn-outline-dark"><i class="fa-solid fa-download me-1"></i> Export Players CSV</a>
    </div>
</div>

<!-- Search & Sort Controls -->
<div class="card pcc-card mb-4 no-print">
    <div class="card-body">
        <form method="GET" action="" class="row g-2 align-items-center">
            <input type="hidden" name="route" value="players">
            <input type="hidden" name="tournament_id" value="<?= $tournamentId ?>">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Search by name, school, FIDE ID..." value="<?= htmlspecialchars($search) ?>">
                </div>
            </div>
            <div class="col-md-3">
                <select name="sort_by" class="form-select">
                    <option value="full_name" <?= ($sortBy === 'full_name') ? 'selected' : '' ?>>Sort by Name</option>
                    <option value="fide_rating" <?= ($sortBy === 'fide_rating') ? 'selected' : '' ?>>Sort by FIDE Rating</option>
                    <option value="school_org" <?= ($sortBy === 'school_org') ? 'selected' : '' ?>>Sort by School/Club</option>
                    <option value="age" <?= ($sortBy === 'age') ? 'selected' : '' ?>>Sort by Age</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="sort_dir" class="form-select">
                    <option value="ASC" <?= ($sortDir === 'ASC') ? 'selected' : '' ?>>Ascending</option>
                    <option value="DESC" <?= ($sortDir === 'DESC') ? 'selected' : '' ?>>Descending</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-dark w-100"><i class="fa-solid fa-filter me-1"></i> Apply Filter</button>
            </div>
        </form>
    </div>
</div>

<!-- Players Table -->
<div class="card pcc-card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th width="50">#</th>
                        <th>Player Name</th>
                        <th>Rating</th>
                        <th>School / Organization</th>
                        <th>Age</th>
                        <th>FIDE ID</th>
                        <?php if ($tournamentId): ?>
                            <th>Tournament Status</th>
                        <?php endif; ?>
                        <th width="150" class="no-print">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($allPlayers)): ?>
                        <tr>
                            <td colspan="<?= $tournamentId ? 8 : 7 ?>" class="text-center py-4 text-muted">No players found in the database. Add or bulk import players to populate the roster.</td>
                        </tr>
                    <?php else: ?>
                        <?php $count = 1; foreach ($allPlayers as $p): ?>
                            <?php $isRegistered = in_array($p['id'], $registeredPlayerIds); ?>
                            <tr>
                                <td><?= $count++ ?></td>
                                <td>
                                    <strong class="fs-6"><?= htmlspecialchars($p['full_name']) ?></strong>
                                    <?php if (!empty($p['nickname'])): ?>
                                        <small class="text-muted d-block">"<?= htmlspecialchars($p['nickname']) ?>"</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($p['fide_rating'])): ?>
                                        <span class="badge bg-primary fs-6"><?= htmlspecialchars($p['fide_rating']) ?> FIDE</span>
                                    <?php elseif (!empty($p['national_rating'])): ?>
                                        <span class="badge bg-secondary fs-6"><?= htmlspecialchars($p['national_rating']) ?> NCFP</span>
                                    <?php else: ?>
                                        <span class="badge bg-light text-dark border">Unrated</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($p['school_org'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($p['age'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($p['fide_id'] ?? 'N/A') ?></td>
                                <?php if ($tournamentId): ?>
                                    <td>
                                        <?php if ($isRegistered): ?>
                                            <span class="badge bg-success"><i class="fa-solid fa-check me-1"></i> Registered</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Not Registered</span>
                                        <?php endif; ?>
                                    </td>
                                <?php endif; ?>
                                <td class="no-print">
                                    <?php if ($tournamentId): ?>
                                        <?php if ($isRegistered): ?>
                                            <a href="?route=players/remove&tournament_id=<?= $tournamentId ?>&player_id=<?= $p['id'] ?>&csrf_token=<?= $csrfToken ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Remove player from tournament?')" title="Remove from Tournament"><i class="fa-solid fa-user-minus"></i></a>
                                        <?php else: ?>
                                            <a href="?route=players/register&tournament_id=<?= $tournamentId ?>&player_id=<?= $p['id'] ?>&csrf_token=<?= $csrfToken ?>" class="btn btn-sm btn-success" title="Add to Tournament"><i class="fa-solid fa-user-plus"></i></a>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    <button onclick="viewPlayerHistory(<?= $tournamentId ?: 0 ?>, <?= $p['id'] ?>)" class="btn btn-sm btn-outline-info" title="View Profile"><i class="fa-solid fa-eye"></i></button>
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
