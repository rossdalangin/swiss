<?php
$pageTitle = "Bulk Import Players";
require __DIR__ . '/../layouts/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-9 col-lg-8">
        <div class="card pcc-card shadow">
            <div class="card-header pcc-card-header bg-dark text-white d-flex align-items-center">
                <i class="fa-solid fa-file-import text-warning fa-lg me-2"></i>
                <h4 class="mb-0">Bulk Import Player Roster</h4>
            </div>
            <div class="card-body p-4">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fa-solid fa-circle-exclamation me-2"></i><?= htmlspecialchars($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (!empty($result)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fa-solid fa-circle-check me-2"></i>
                        Successfully imported <strong><?= $result['created_count'] ?></strong> new player(s).
                        <?php if (!empty($result['duplicates'])): ?>
                            <div class="mt-2 text-dark">
                                <strong>Duplicate Resolution Notice:</strong> Linked <?= count($result['duplicates']) ?> existing player record(s): <?= htmlspecialchars(implode(', ', $result['duplicates'])) ?>.
                            </div>
                        <?php endif; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="alert alert-info border-0 shadow-sm mb-4">
                    <div class="d-flex align-items-start">
                        <i class="fa-solid fa-circle-info fa-lg me-3 mt-1 text-info"></i>
                        <div>
                            <strong>Bulk Import Format & Field Rules:</strong>
                            <p class="mb-0 small text-dark">
                                • Expected CSV Columns: <code>Name, Rating, School/Org, Age</code><br>
                                • Blank ratings are preserved as <em>Unrated</em>. Never type fake ratings.<br>
                                • Names matching existing database records will register the existing player without creating duplicates.
                            </p>
                        </div>
                    </div>
                </div>

                <form method="POST" action="?route=players/bulk_import&tournament_id=<?= $tournamentId ?>" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

                    <!-- Option 1: Paste Text List -->
                    <div class="mb-4">
                        <label for="bulk_text" class="form-label fw-bold">Option 1: Paste List of Names or CSV Text</label>
                        <p class="text-muted small mb-2">
                            Enter one player per line. Example:<br>
                            <code>Juan Dela Cruz, 1800, Paete High School, 16</code><br>
                            <code>Pedro Santos, , Paete Elementary, 12</code> (Unrated player example)<br>
                            <code>Maria Reyes</code>
                        </p>
                        <textarea class="form-control font-monospace" id="bulk_text" name="bulk_text" rows="8" placeholder="Juan Dela Cruz, 1800, Paete High School, 16
Pedro Santos, , Paete Elementary, 12
Maria Reyes, 1450, Paete Chess Club, 15
Jose Garcia"></textarea>
                    </div>

                    <div class="text-center my-3 fw-bold text-muted">&mdash; OR &mdash;</div>

                    <!-- Option 2: Upload CSV File -->
                    <div class="mb-4">
                        <label for="csv_file" class="form-label fw-bold">Option 2: Upload CSV File</label>
                        <input class="form-control" type="file" id="csv_file" name="csv_file" accept=".csv">
                        <small class="text-muted d-block mt-1">First row header like <code>Name, Rating, School, Age</code> is optional and auto-detected.</small>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-4 border-top pt-3">
                        <a href="?route=players&tournament_id=<?= $tournamentId ?>" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-1"></i> Back to Roster</a>
                        <button type="submit" class="btn btn-primary px-4 py-2 fw-bold"><i class="fa-solid fa-upload me-2"></i> Process & Import Players</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
