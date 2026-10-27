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

                <div class="alert alert-info border-0 shadow-sm mb-4">
                    <div class="d-flex align-items-start">
                        <i class="fa-solid fa-circle-info fa-lg me-3 mt-1 text-info"></i>
                        <div>
                            <strong>Tournament Creation Guidelines & Instructions:</strong>
                            <p class="mb-0 small text-dark">
                                Configure the official settings for your Paete Chess Club tournament below. The number of rounds can be set manually or based on arbiter planning guidelines. Changing parameters after Round 1 starts is logged in the audit trail.
                            </p>
                        </div>
                    </div>
                </div>

                <form method="POST" action="?route=tournament/create">
                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

                    <div class="row g-3 mb-3">
                        <div class="col-md-8">
                            <label for="name" class="form-label fw-bold">Tournament Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" required placeholder="e.g. 1st Paete Youth Swiss Tournament 2025">
                            <small class="text-muted">Specify the full official title of the event for diplomas and reports.</small>
                        </div>
                        <div class="col-md-4">
                            <label for="date" class="form-label fw-bold">Tournament Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="date" name="date" required value="<?= date('Y-m-d') ?>">
                            <small class="text-muted">Start date of competition (Asia/Manila time).</small>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="venue" class="form-label fw-bold">Venue / Location <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="venue" name="venue" required placeholder="e.g. Paete Town Plaza / Paete Municipal Gym">
                            <small class="text-muted">Playing hall location in Paete, Laguna.</small>
                        </div>
                        <div class="col-md-6">
                            <label for="organizer_name" class="form-label fw-bold">Organizer / Chief Arbiter Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="organizer_name" name="organizer_name" required value="Paete Chess Club Chief Arbiter">
                            <small class="text-muted">Licensed arbiter or tournament director responsible for pairing oversight.</small>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label for="total_rounds" class="form-label fw-bold">Number of Rounds <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="total_rounds" name="total_rounds" min="1" max="15" value="5" required>
                            <div class="card bg-light p-2 mt-2 border-0 small text-muted">
                                <strong>Planning Recommendation:</strong><br>
                                • 1-4 players: 3 rounds<br>
                                • 5-8 players: 4 rounds<br>
                                • 9-16 players: 5 rounds<br>
                                • 17-32 players: 6 rounds<br>
                                • 33-64 players: 7 rounds
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="time_control" class="form-label fw-bold">Time Control</label>
                            <input type="text" class="form-control" id="time_control" name="time_control" value="15 mins + 5 secs increment">
                            <small class="text-muted">Example: <code>10 mins Rapid</code>, <code>15m + 5s</code>, or <code>5m + 3s Blitz</code>.</small>
                        </div>
                        <div class="col-md-4">
                            <label for="rating_source" class="form-label fw-bold">Primary Rating Source</label>
                            <select class="form-select" id="rating_source" name="rating_source">
                                <option value="FIDE">FIDE Official Standard / Rapid Rating</option>
                                <option value="National">National Federation Rating (NCFP)</option>
                                <option value="Club">Paete Club Local Rating</option>
                                <option value="Mixed">Mixed / Unrated Friendly Tournament</option>
                            </select>
                            <small class="text-muted">Official rating benchmark for initial seeding order.</small>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="pairing_mode" class="form-label fw-bold">Pairing Mode & Rules</label>
                            <select class="form-select" id="pairing_mode" name="pairing_mode">
                                <option value="club_swiss">Club Swiss Pairing — Simplified (Dutch Variant)</option>
                                <option value="fide_dutch">FIDE Dutch System Rules</option>
                            </select>
                            <small class="text-muted d-block mt-1">
                                <strong>Club Swiss:</strong> Strict non-repeat opponent check, score grouping, color balancing, and 1.0-point Swiss Bye for odd player counts.
                            </small>
                        </div>
                        <div class="col-md-6">
                            <label for="scoring_system" class="form-label fw-bold">Game Scoring System</label>
                            <select class="form-select" id="scoring_system" name="scoring_system">
                                <option value="standard">Standard Chess (Win = 1.0, Draw = 0.5, Loss = 0.0)</option>
                                <option value="football">3-1-0 Scoring (Win = 3.0, Draw = 1.0, Loss = 0.0)</option>
                            </select>
                            <small class="text-muted d-block mt-1">
                                Standard scoring is FIDE-compliant. 3-1-0 is optional for friendly/youth club events.
                            </small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label fw-bold">Tournament Description / Special Regulations</label>
                        <textarea class="form-control" id="description" name="description" rows="3" placeholder="Specify tournament rules, eligibility criteria (e.g., Paete residents / Under-16 Division), tiebreak priority, or prize distribution details..."></textarea>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-4 border-top pt-3">
                        <a href="?route=dashboard" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-1"></i> Back to Dashboard</a>
                        <button type="submit" class="btn btn-success px-4 py-2 fw-bold"><i class="fa-solid fa-check me-2"></i> Save & Create Tournament</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
