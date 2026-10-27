<?php
$pageTitle = "Create Tournament";
require __DIR__ . '/../layouts/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-9 col-lg-8">
        <div class="card pcc-card shadow">
            <div class="card-header pcc-card-header bg-dark text-white d-flex align-items-center">
                <i class="fa-solid fa-trophy text-warning fa-lg me-2"></i>
                <h4 class="mb-0">Create New Swiss Tournament</h4>
            </div>
            <div class="card-body p-4">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fa-solid fa-circle-exclamation me-2"></i><?= htmlspecialchars($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" action="?route=tournament/create">
                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

                    <div class="row g-3 mb-3">
                        <div class="col-md-8">
                            <label for="name" class="form-label fw-bold">Tournament Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" required placeholder="e.g. 1st Paete Youth Swiss Tournament 2025">
                        </div>
                        <div class="col-md-4">
                            <label for="date" class="form-label fw-bold">Tournament Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="date" name="date" required value="<?= date('Y-m-d') ?>">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="venue" class="form-label fw-bold">Venue <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="venue" name="venue" required placeholder="e.g. Paete Town Plaza, Paete, Laguna">
                        </div>
                        <div class="col-md-6">
                            <label for="organizer_name" class="form-label fw-bold">Organizer / Arbiter Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="organizer_name" name="organizer_name" required value="Paete Chess Club Chief Arbiter">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label for="total_rounds" class="form-label fw-bold">Number of Rounds <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="total_rounds" name="total_rounds" min="1" max="15" value="5" required>
                            <small class="text-muted d-block mt-1"><i class="fa-solid fa-info-circle"></i> Recommended: 5 rounds for 16 players, 6-7 rounds for 32+ players.</small>
                        </div>
                        <div class="col-md-4">
                            <label for="time_control" class="form-label fw-bold">Time Control</label>
                            <input type="text" class="form-control" id="time_control" name="time_control" value="15 mins + 5 secs increment">
                        </div>
                        <div class="col-md-4">
                            <label for="rating_source" class="form-label fw-bold">Rating Source</label>
                            <select class="form-select" id="rating_source" name="rating_source">
                                <option value="FIDE">FIDE Rating</option>
                                <option value="National">National Federation Rating (NCFP)</option>
                                <option value="Club">Paete Club Local Rating</option>
                                <option value="Mixed">Mixed / Unrated Friendly</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="pairing_mode" class="form-label fw-bold">Pairing Mode</label>
                            <select class="form-select" id="pairing_mode" name="pairing_mode">
                                <option value="club_swiss">Club Swiss Pairing — Simplified (Dutch Variant)</option>
                                <option value="fide_dutch">FIDE Dutch System Rules</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="scoring_system" class="form-label fw-bold">Scoring System</label>
                            <select class="form-select" id="scoring_system" name="scoring_system">
                                <option value="standard">Standard Chess (Win 1.0, Draw 0.5, Loss 0.0)</option>
                                <option value="football">3-1-0 Scoring (Win 3, Draw 1, Loss 0)</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label fw-bold">Tournament Description / Remarks</label>
                        <textarea class="form-control" id="description" name="description" rows="3" placeholder="Additional details, prizes, eligibility guidelines..."></textarea>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-4 border-top pt-3">
                        <a href="?route=dashboard" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-1"></i> Back to Dashboard</a>
                        <button type="submit" class="btn btn-success px-4 py-2 fw-bold"><i class="fa-solid fa-check me-2"></i> Create Tournament</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
