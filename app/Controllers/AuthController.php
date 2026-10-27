<?php

namespace App\Controllers;

use App\Services\AuthManager;

class AuthController {
    private AuthManager $authManager;

    public function __construct() {
        $this->authManager = new AuthManager();
    }

    public function login(): void {
        if (AuthManager::isLoggedIn()) {
            header('Location: ?route=dashboard');
            exit;
        }

        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $password = trim($_POST['password'] ?? '');
            $token = $_POST['csrf_token'] ?? '';

            if (!AuthManager::verifyCsrfToken($token)) {
                $error = "Invalid security token. Please try again.";
            } elseif ($this->authManager->login($username, $password)) {
                header('Location: ?route=dashboard');
                exit;
            } else {
                $error = "Invalid username or password.";
            }
        }

        require __DIR__ . '/../Views/auth/login.php';
    }

    public function logout(): void {
        $this->authManager->logout();
        header('Location: ?route=login');
        exit;
    }
}
