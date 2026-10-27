<?php
$pageTitle = "Edit Tournament";
require __DIR__ . '/../layouts/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-9 col-lg-8">
        <div class="card pcc-card shadow">
            <div class="card-header pcc-card-header bg-dark text-white d-flex align-items-center">
                <i class="fa-solid fa-pen-to-square text-warning fa-lg me-2"></i>
                <h4 class="mb-0">Edit Tournament Settings</h4>
            </div>
            <div class="card-body p-4">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fa-solid fa-circle-exclamation me-2"></i><?= htmlspecialchars($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="alert alert-warning border-0 shadow-sm mb-4">
                    <div class="d-flex align-items-start">
                        <i class="fa-solid fa-triangle-exclamation fa-lg me-3 mt-1 text-warning"></i>
                        <div>
                            <strong>Arbiter Notice on Modifying Settings:</strong>
                            <p class="mb-0 small text-dark">
                                Modifying tournament settings after games have been played or pairings generated should be done with caution. All modifications are logged in the audit trail.
                            </p>
                        </div>
                    </div>
                </div>

                <form method="POST" action="?route=tournament/edit&id=<?= $tournament['id'] ?>">
                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

                    <div class="row g-3 mb-3">
                        <div class="col-md-8">
                            <label for="name" class="form-label fw-bold">Tournament Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" required value="<?= htmlspecialchars($tournament['name']) ?>">
                            <small class="text-muted">Official name printed on pairing sheets and standings.</small>
                        </div>
                        <div class="col-md-4">
                            <label for="date" class="form-label fw-bold">Tournament Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="date" name="date" required value="<?= htmlspecialchars($tournament['date']) ?>">
                            <small class="text-muted">Scheduled date (YYYY-MM-DD).</small>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="venue" class="form-label fw-bold">Venue <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="venue" name="venue" required value="<?= htmlspecialchars($tournament['venue']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="organizer_name" class="form-label fw-bold">Organizer / Chief Arbiter Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="organizer_name" name="organizer_name" required value="<?= htmlspecialchars($tournament['organizer_name']) ?>">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label for="total_rounds" class="form-label fw-bold">Number of Rounds <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="total_rounds" name="total_rounds" min="1" max="15" required value="<?= htmlspecialchars($tournament['total_rounds']) ?>">
                            <small class="text-muted">Currently at Round <?= $tournament['current_round'] ?> of <?= $tournament['total_rounds'] ?>.</small>
                        </div>
                        <div class="col-md-4">
                            <label for="time_control" class="form-label fw-bold">Time Control</label>
                            <input type="text" class="form-control" id="time_control" name="time_control" value="<?= htmlspecialchars($tournament['time_control'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="rating_source" class="form-label fw-bold">Primary Rating Source</label>
                            <select class="form-select" id="rating_source" name="rating_source">
                                <option value="FIDE" <?= ($tournament['rating_source'] === 'FIDE') ? 'selected' : '' ?>>FIDE Rating</option>
                                <option value="National" <?= ($tournament['rating_source'] === 'National') ? 'selected' : '' ?>>National Federation Rating (NCFP)</option>
                                <option value="Club" <?= ($tournament['rating_source'] === 'Club') ? 'selected' : '' ?>>Paete Club Local Rating</option>
                                <option value="Mixed" <?= ($tournament['rating_source'] === 'Mixed') ? 'selected' : '' ?>>Mixed / Unrated Friendly</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label fw-bold">Tournament Description / Remarks</label>
                        <textarea class="form-control" id="description" name="description" rows="3"><?= htmlspecialchars($tournament['description'] ?? '') ?></textarea>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-4 border-top pt-3">
                        <a href="?route=dashboard&tournament_id=<?= $tournament['id'] ?>" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-1"></i> Cancel</a>
                        <button type="submit" class="btn btn-primary px-4 py-2 fw-bold"><i class="fa-solid fa-floppy-disk me-2"></i> Update Tournament Settings</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
