<?php

namespace App\Controllers;

use App\Services\AuthManager;
use App\Services\Database;
use App\Services\AuditLogger;

class SettingsController
{
    private AuthManager $auth;
    private \PDO $db;

    public function __construct()
    {
        $this->auth = new AuthManager();
        $this->auth->requireLogin();
        $this->db = Database::getInstance();
    }

    public function index(): void
    {
        $user = $this->auth->getCurrentUser();

        // Get audit logs
        $stmt = $this->db->query("SELECT a.*, u.username, t.name as tournament_name FROM audit_logs a LEFT JOIN users u ON a.user_id = u.id LEFT JOIN tournaments t ON a.tournament_id = t.id ORDER BY a.created_at DESC LIMIT 50");
        $auditLogs = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Get system/app defaults from database if exists or use defaults
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$user['id']]);
        $userData = $stmt->fetch();

        $flashSuccess = $_SESSION['flash_success'] ?? null;
        $flashError = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        require __DIR__ . '/../Views/settings/index.php';
    }

    public function updateProfile(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?route=settings');
            exit;
        }

        if (!AuthManager::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Invalid security token. Please try again.';
            header('Location: index.php?route=settings');
            exit;
        }

        $user = $this->auth->getCurrentUser();
        $fullName = trim($_POST['full_name'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($fullName) || empty($username)) {
            $_SESSION['flash_error'] = 'Full Name and Username are required fields.';
            header('Location: index.php?route=settings');
            exit;
        }

        // Verify current password
        $stmt = Database::getInstance()->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$user['id']]);
        $dbUser = $stmt->fetch();

        if (!password_verify($currentPassword, $dbUser['password_hash'])) {
            $_SESSION['flash_error'] = 'Incorrect current password.';
            header('Location: index.php?route=settings');
            exit;
        }

        // Check if username is taken by another user
        $stmt = Database::getInstance()->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
        $stmt->execute([$username, $user['id']]);
        if ($stmt->fetch()) {
            $_SESSION['flash_error'] = 'Username is already taken by another account.';
            header('Location: index.php?route=settings');
            exit;
        }

        // Handle optional password change
        $passwordHash = $dbUser['password_hash'];
        if (!empty($newPassword)) {
            if (strlen($newPassword) < 6) {
                $_SESSION['flash_error'] = 'New password must be at least 6 characters long.';
                header('Location: index.php?route=settings');
                exit;
            }
            if ($newPassword !== $confirmPassword) {
                $_SESSION['flash_error'] = 'New password and confirmation do not match.';
                header('Location: index.php?route=settings');
                exit;
            }
            $passwordHash = password_hash($newPassword, PASSWORD_BCRYPT);
        }

        // Update database
        $stmt = Database::getInstance()->prepare("UPDATE users SET full_name = ?, username = ?, password_hash = ? WHERE id = ?");
        $stmt->execute([$fullName, $username, $passwordHash, $user['id']]);

        // Refresh session data
        $_SESSION['user']['full_name'] = $fullName;
        $_SESSION['user']['username'] = $username;
        $_SESSION['full_name'] = $fullName;
        $_SESSION['username'] = $username;

        AuditLogger::log(null, $user['id'], 'SETTINGS_UPDATE_PROFILE', "Updated administrator profile details.");

        $_SESSION['flash_success'] = 'Profile and security settings updated successfully!';
        header('Location: index.php?route=settings');
        exit;
    }
}
