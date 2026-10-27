<?php
$title = "Admin Settings & Audit Trail - Paete Chess Club";
require __DIR__ . '/../layouts/header.php';
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h2 class="fw-bold"><i class="bi bi-gear-fill me-2 text-primary"></i>Admin Settings & System Logs</h2>
        <p class="text-muted">Manage arbiter account credentials, security preferences, and inspect full system audit logs.</p>
    </div>
</div>

<?php if (!empty($flashSuccess)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($flashSuccess) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (!empty($flashError)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($flashError) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row g-4">
    <!-- Admin Security Profile -->
    <div class="col-lg-5">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-dark text-white fw-bold py-3">
                <i class="bi bi-shield-lock-fill me-2 text-warning"></i>Arbiter Account Security
            </div>
            <div class="card-body p-4">
                <form action="index.php?route=settings/update_profile" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Services\AuthManager::generateCsrfToken()) ?>">

                    <div class="mb-3">
                        <label for="full_name" class="form-label fw-bold">Full Name / Arbiter Title</label>
                        <input type="text" class="form-control" id="full_name" name="full_name" value="<?= htmlspecialchars($userData['full_name'] ?? $user['full_name']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="username" class="form-label fw-bold">Username</label>
                        <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($userData['username'] ?? $user['username']) ?>" required>
                    </div>

                    <hr class="my-4">
                    <h6 class="fw-bold text-muted mb-3"><i class="bi bi-key-fill me-1"></i> Password Change</h6>

                    <div class="mb-3">
                        <label for="current_password" class="form-label fw-bold">Current Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="current_password" name="current_password" required placeholder="Enter current password to authorize changes">
                    </div>

                    <div class="mb-3">
                        <label for="new_password" class="form-label fw-bold">New Password (optional)</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" placeholder="Leave blank to keep current password">
                    </div>

                    <div class="mb-4">
                        <label for="confirm_password" class="form-label fw-bold">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Re-enter new password">
                    </div>

                    <button type="submit" class="btn btn-primary w-100 fw-bold">
                        <i class="bi bi-save me-1"></i> Update Security Credentials
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Audit Log Viewer -->
    <div class="col-lg-7">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-dark text-white fw-bold py-3 d-flex justify-content-between align-items-center">
                <span><i class="bi bi-journal-text me-2 text-info"></i>System Audit Trail & Arbiter Logs</span>
                <span class="badge bg-secondary"><?= count($auditLogs) ?> Entries</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 520px; overflow-y: auto;">
                    <table class="table table-hover table-striped align-middle mb-0">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th>Timestamp</th>
                                <th>Action</th>
                                <th>Arbiter / User</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($auditLogs)): ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">No audit log entries recorded yet.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($auditLogs as $log): ?>
                                    <tr>
                                        <td class="small text-nowrap text-muted"><?= htmlspecialchars(date('M d, Y H:i:s', strtotime($log['created_at']))) ?></td>
                                        <td>
                                            <span class="badge bg-outline-primary text-dark border">
                                                <?= htmlspecialchars($log['action']) ?>
                                            </span>
                                        </td>
                                        <td class="small fw-semibold"><?= htmlspecialchars($log['username'] ?? 'System') ?></td>
                                        <td class="small text-wrap"><?= htmlspecialchars($log['details'] ?? '-') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-light small text-muted">
                <i class="bi bi-info-circle me-1"></i> Audit log records sensitive tournament actions such as result edits, pairing manual overrides, and tournament status resets.
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
