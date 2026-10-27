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
                                <strong>Note:</strong> Matched <?= count($result['duplicates']) ?> existing player name(s): <?= htmlspecialchars(implode(', ', $result['duplicates'])) ?>.
                            </div>
                        <?php endif; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" action="?route=players/bulk_import&tournament_id=<?= $tournamentId ?>" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

                    <!-- Option 1: Paste Text List -->
                    <div class="mb-4">
                        <label for="bulk_text" class="form-label fw-bold">Option 1: Paste List of Names / CSV Text</label>
                        <p class="text-muted small mb-2">
                            Enter one player per line. Format: <code>Name, Rating, School, Age</code> or just <code>Name</code>.<br>
                            <em>Unrated players can leave rating blank.</em>
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
                        <small class="text-muted d-block mt-1">Expected columns: <code>Name, Rating, School/Org, Age</code></small>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-4 border-top pt-3">
                        <a href="?route=players&tournament_id=<?= $tournamentId ?>" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-1"></i> Back to Roster</a>
                        <button type="submit" class="btn btn-primary px-4 py-2 fw-bold"><i class="fa-solid fa-upload me-2"></i> Import Players</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
