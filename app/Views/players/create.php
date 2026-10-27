<?php
$pageTitle = "Add Individual Player";
require __DIR__ . '/../layouts/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-7">
        <div class="card pcc-card shadow">
            <div class="card-header pcc-card-header bg-dark text-white d-flex align-items-center">
                <i class="fa-solid fa-user-plus text-warning fa-lg me-2"></i>
                <h4 class="mb-0">Add Individual Player Record</h4>
            </div>
            <div class="card-body p-4">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fa-solid fa-circle-exclamation me-2"></i><?= htmlspecialchars($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" action="?route=players/create&tournament_id=<?= $tournamentId ?>">
                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

                    <div class="row g-3 mb-3">
                        <div class="col-md-8">
                            <label for="full_name" class="form-label fw-bold">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="full_name" name="full_name" required placeholder="e.g. Juan Dela Cruz">
                        </div>
                        <div class="col-md-4">
                            <label for="nickname" class="form-label fw-bold">Nickname</label>
                            <input type="text" class="form-control" id="nickname" name="nickname" placeholder="e.g. Juan">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="school_org" class="form-label fw-bold">School / Organization</label>
                            <input type="text" class="form-control" id="school_org" name="school_org" placeholder="e.g. Paete National High School">
                        </div>
                        <div class="col-md-6">
                            <label for="age" class="form-label fw-bold">Age</label>
                            <input type="number" class="form-control" id="age" name="age" min="4" max="100" placeholder="e.g. 16">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label for="fide_rating" class="form-label fw-bold">FIDE Rating</label>
                            <input type="number" class="form-control" id="fide_rating" name="fide_rating" placeholder="Leave blank if unrated">
                        </div>
                        <div class="col-md-4">
                            <label for="national_rating" class="form-label fw-bold">National Rating (NCFP)</label>
                            <input type="number" class="form-control" id="national_rating" name="national_rating" placeholder="Leave blank if unrated">
                        </div>
                        <div class="col-md-4">
                            <label for="fide_id" class="form-label fw-bold">FIDE ID</label>
                            <input type="text" class="form-control" id="fide_id" name="fide_id" placeholder="e.g. 5200123">
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-4 border-top pt-3">
                        <a href="?route=players&tournament_id=<?= $tournamentId ?>" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-1"></i> Back to Roster</a>
                        <button type="submit" class="btn btn-success px-4 py-2 fw-bold"><i class="fa-solid fa-floppy-disk me-2"></i> Save Player Record</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
