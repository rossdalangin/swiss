<?php
$pageTitle = "Arbiter Login";
require __DIR__ . '/../layouts/header.php';
?>

<div class="row justify-content-center my-5">
    <div class="col-md-5 col-lg-4">
        <div class="card pcc-card shadow">
            <div class="card-header pcc-card-header text-center bg-dark text-white">
                <i class="fa-solid fa-chess-king fa-2x text-warning mb-2"></i>
                <h4 class="mb-0">Paete Chess Club</h4>
                <small class="text-white-50">Arbiter & Director Portal</small>
            </div>
            <div class="card-body p-4">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fa-solid fa-circle-exclamation me-2"></i><?= htmlspecialchars($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" action="?route=login">
                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                    <div class="mb-3">
                        <label for="username" class="form-label fw-bold">Username</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-user"></i></span>
                            <input type="text" class="form-control" id="username" name="username" placeholder="e.g. admin" required autofocus>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="password" class="form-label fw-bold">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                            <input type="password" class="form-control" id="password" name="password" placeholder="••••••••" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">
                        <i class="fa-solid fa-right-to-bracket me-2"></i> Log In
                    </button>
                </form>
            </div>
            <div class="card-footer text-center text-muted fs-7 py-3">
                Default Credentials: <code>admin</code> / <code>admin123</code>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
