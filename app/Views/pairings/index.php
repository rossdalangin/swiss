<?php
$pageTitle = "Pairings & Result Entry";
require __DIR__ . '/../layouts/header.php';
$error = $_GET['error'] ?? null;
?>

<div class="row mb-4 align-items-center">
    <div class="col-md-7">
        <h2 class="fw-bold mb-1"><i class="fa-solid fa-chess text-warning me-2"></i> Round Pairings & Results</h2>
        <p class="text-muted mb-0">
            <?= htmlspecialchars($tournament['name']) ?> &mdash;
            <strong>Round <?= $roundNumber ?> of <?= $tournament['total_rounds'] ?></strong>
        </p>
    </div>
    <div class="col-md-5 text-md-end mt-3 mt-md-0 no-print">
        <?php if ($tournament['current_round'] < $tournament['total_rounds'] && $tournament['status'] !== 'Completed'): ?>
            <a href="?route=pairings/generate&tournament_id=<?= $tournament['id'] ?>&csrf_token=<?= $csrfToken ?>" class="btn btn-success me-1" onclick="return confirm('Generate pairings for next round?')">
                <i class="fa-solid fa-play me-1"></i> Generate Round <?= $tournament['current_round'] + 1 ?>
            </a>
        <?php endif; ?>
        <button onclick="window.print()" class="btn btn-secondary me-1"><i class="fa-solid fa-print me-1"></i> Print Pairings</button>
        <a href="?route=standings&tournament_id=<?= $tournament['id'] ?>" class="btn btn-outline-dark"><i class="fa-solid fa-list-ol me-1"></i> Standings</a>
    </div>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fa-solid fa-triangle-exclamation me-2"></i><?= htmlspecialchars($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Round Navigation Pills -->
<div class="mb-4 no-print">
    <ul class="nav nav-pills">
        <?php for ($r = 1; $r <= $tournament['current_round']; $r++): ?>
            <li class="nav-item">
                <a class="nav-link <?= ($r === $roundNumber) ? 'active fw-bold' : '' ?>" href="?route=pairings&tournament_id=<?= $tournament['id'] ?>&round=<?= $r ?>">
                    Round <?= $r ?>
                </a>
            </li>
        <?php endfor; ?>
    </ul>
</div>

<!-- Pairings Table -->
<div class="card pcc-card">
    <div class="card-header pcc-card-header d-flex justify-content-between align-items-center">
        <span class="fs-5 fw-bold"><i class="fa-solid fa-chess-board me-2 text-warning"></i> Board Pairings &mdash; Round <?= $roundNumber ?></span>
        <span class="badge bg-dark">Total Boards: <?= count($pairings) ?></span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th width="80" class="text-center">Board</th>
                        <th>White Player</th>
                        <th width="120">Rating</th>
                        <th class="text-center" width="100">VS</th>
                        <th>Black Player</th>
                        <th width="120">Rating</th>
                        <th width="140" class="text-center">Result</th>
                        <th width="130" class="text-center no-print">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($pairings)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-chess-king fa-3x mb-3 text-muted d-block"></i>
                                No pairings generated for this round yet. Click <strong>Generate Round</strong> above.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($pairings as $p): ?>
                            <tr>
                                <td class="text-center fw-bold">
                                    <span class="board-badge"><?= $p['board_number'] ?></span>
                                </td>
                                <?php if ($p['is_bye'] == 1): ?>
                                    <td colspan="4">
                                        <div class="d-flex align-items-center">
                                            <strong class="fs-6 me-2"><?= htmlspecialchars($p['bye_name']) ?></strong>
                                            <span class="badge bg-warning text-dark"><i class="fa-solid fa-star me-1"></i> SWISS BYE</span>
                                        </div>
                                    </td>
                                    <td class="text-center"><span class="badge bg-success fs-6">1 - 0 (Bye)</span></td>
                                    <td class="text-center text-muted fs-7 no-print">Auto Awarded</td>
                                <?php else: ?>
                                    <td>
                                        <a href="javascript:void(0)" onclick="viewPlayerHistory(<?= $tournament['id'] ?>, <?= $p['white_player_id'] ?>)" class="text-decoration-none fw-bold text-dark">
                                            <?= htmlspecialchars($p['white_name']) ?>
                                        </a>
                                    </td>
                                    <td><?= $p['white_rating'] ? htmlspecialchars($p['white_rating']) : '<span class="text-muted">Unrated</span>' ?></td>
                                    <td class="text-center fw-bold text-muted">vs</td>
                                    <td>
                                        <a href="javascript:void(0)" onclick="viewPlayerHistory(<?= $tournament['id'] ?>, <?= $p['black_player_id'] ?>)" class="text-decoration-none fw-bold text-dark">
                                            <?= htmlspecialchars($p['black_name']) ?>
                                        </a>
                                    </td>
                                    <td><?= $p['black_rating'] ? htmlspecialchars($p['black_rating']) : '<span class="text-muted">Unrated</span>' ?></td>
                                    <td class="text-center">
                                        <?php if ($p['result'] === 'pending'): ?>
                                            <span class="badge bg-secondary fs-6"><i class="fa-regular fa-clock me-1"></i> Pending</span>
                                        <?php elseif ($p['result'] === '1-0' || $p['result'] === '1-0F'): ?>
                                            <span class="badge bg-success fs-6">1 - 0</span>
                                        <?php elseif ($p['result'] === '0-1' || $p['result'] === '0-1F'): ?>
                                            <span class="badge bg-success fs-6">0 - 1</span>
                                        <?php elseif (in_array($p['result'], ['1/2-1/2', '0.5-0.5', '½-½'])): ?>
                                            <span class="badge bg-info text-dark fs-6">½ - ½</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger fs-6"><?= htmlspecialchars($p['result']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center no-print">
                                        <button class="btn btn-sm btn-primary fw-bold" data-bs-toggle="modal" data-bs-target="#resultModal<?= $p['id'] ?>">
                                            <i class="fa-solid fa-pen-to-square me-1"></i> Result
                                        </button>
                                    </td>
                                <?php endif; ?>
                            </tr>

                            <!-- Result Entry Modal for Board -->
                            <?php if ($p['is_bye'] == 0): ?>
                                <div class="modal fade" id="resultModal<?= $p['id'] ?>" tabindex="-1">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <form method="POST" action="?route=pairings/record_result">
                                                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                                <input type="hidden" name="tournament_id" value="<?= $tournament['id'] ?>">
                                                <input type="hidden" name="pairing_id" value="<?= $p['id'] ?>">

                                                <div class="modal-header bg-dark text-white">
                                                    <h5 class="modal-title"><i class="fa-solid fa-chess-board me-2 text-warning"></i> Board <?= $p['board_number'] ?> Match Result</h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row text-center mb-4">
                                                        <div class="col-6 border-end">
                                                            <div class="badge bg-light text-dark border mb-1">WHITE</div>
                                                            <h5 class="fw-bold mb-0"><?= htmlspecialchars($p['white_name']) ?></h5>
                                                        </div>
                                                        <div class="col-6">
                                                            <div class="badge bg-dark text-white mb-1">BLACK</div>
                                                            <h5 class="fw-bold mb-0"><?= htmlspecialchars($p['black_name']) ?></h5>
                                                        </div>
                                                    </div>

                                                    <label class="form-label fw-bold mb-2">Select Game Result:</label>
                                                    <div class="d-grid gap-2">
                                                        <input type="radio" class="btn-check" name="result" id="res_w<?= $p['id'] ?>" value="1-0" <?= ($p['result'] === '1-0') ? 'checked' : '' ?> required>
                                                        <label class="btn btn-outline-success text-start fw-bold" for="res_w<?= $p['id'] ?>"><i class="fa-solid fa-trophy me-2"></i> 1 - 0 (White Wins)</label>

                                                        <input type="radio" class="btn-check" name="result" id="res_d<?= $p['id'] ?>" value="1/2-1/2" <?= (in_array($p['result'], ['1/2-1/2', '0.5-0.5', '½-½'])) ? 'checked' : '' ?>>
                                                        <label class="btn btn-outline-info text-dark text-start fw-bold" for="res_d<?= $p['id'] ?>"><i class="fa-solid fa-handshake me-2"></i> ½ - ½ (Draw)</label>

                                                        <input type="radio" class="btn-check" name="result" id="res_b<?= $p['id'] ?>" value="0-1" <?= ($p['result'] === '0-1') ? 'checked' : '' ?>>
                                                        <label class="btn btn-outline-primary text-start fw-bold" for="res_b<?= $p['id'] ?>"><i class="fa-solid fa-trophy me-2"></i> 0 - 1 (Black Wins)</label>

                                                        <input type="radio" class="btn-check" name="result" id="res_wf<?= $p['id'] ?>" value="1-0F" <?= ($p['result'] === '1-0F') ? 'checked' : '' ?>>
                                                        <label class="btn btn-outline-secondary text-start" for="res_wf<?= $p['id'] ?>"><i class="fa-solid fa-flag me-2"></i> 1 - 0F (White Wins by Forfeit)</label>

                                                        <input type="radio" class="btn-check" name="result" id="res_bf<?= $p['id'] ?>" value="0-1F" <?= ($p['result'] === '0-1F') ? 'checked' : '' ?>>
                                                        <label class="btn btn-outline-secondary text-start" for="res_bf<?= $p['id'] ?>"><i class="fa-solid fa-flag me-2"></i> 0 - 1F (Black Wins by Forfeit)</label>

                                                        <input type="radio" class="btn-check" name="result" id="res_df<?= $p['id'] ?>" value="0-0F" <?= ($p['result'] === '0-0F') ? 'checked' : '' ?>>
                                                        <label class="btn btn-outline-danger text-start" for="res_df<?= $p['id'] ?>"><i class="fa-solid fa-ban me-2"></i> 0 - 0F (Double Forfeit)</label>
                                                    </div>

                                                    <?php if ($p['result'] !== 'pending'): ?>
                                                        <div class="mt-3">
                                                            <label for="reason<?= $p['id'] ?>" class="form-label text-danger small fw-bold">Result Edit Reason (Audit Log):</label>
                                                            <input type="text" class="form-control form-control-sm" id="reason<?= $p['id'] ?>" name="admin_reason" placeholder="e.g. Correcting score input error">
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-success fw-bold"><i class="fa-solid fa-floppy-disk me-1"></i> Save Result</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
