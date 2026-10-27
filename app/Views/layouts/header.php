<?php
use App\Services\AuthManager;
$currentUser = AuthManager::getCurrentUser();
$csrfToken = AuthManager::generateCsrfToken();
$currentRoute = $_GET['route'] ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Paete Chess Club') ?> - Tournament Manager</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark navbar-pcc no-print">
    <div class="container-fluid">
        <a class="navbar-brand pcc-brand-title d-flex align-items-center" href="?route=dashboard">
            <i class="fa-solid fa-chess-king fa-lg me-2 text-warning"></i>
            <span>Paete Chess Club <small class="text-white-50 fs-6">| Tournament Manager</small></span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarMain">
            <?php if ($currentUser): ?>
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link <?= ($currentRoute === 'dashboard') ? 'active' : '' ?>" href="?route=dashboard"><i class="fa-solid fa-gauge me-1"></i> Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= str_starts_with($currentRoute, 'tournament') ? 'active' : '' ?>" href="?route=tournament/create"><i class="fa-solid fa-trophy me-1"></i> Tournaments</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= str_starts_with($currentRoute, 'players') ? 'active' : '' ?>" href="?route=players"><i class="fa-solid fa-users me-1"></i> Players</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= str_starts_with($currentRoute, 'pairings') ? 'active' : '' ?>" href="?route=pairings"><i class="fa-solid fa-chess-board me-1"></i> Pairings</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= str_starts_with($currentRoute, 'standings') ? 'active' : '' ?>" href="?route=standings"><i class="fa-solid fa-list-ol me-1"></i> Standings</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= str_starts_with($currentRoute, 'settings') ? 'active' : '' ?>" href="?route=settings"><i class="fa-solid fa-gear me-1"></i> Settings</a>
                    </li>
                </ul>
                <div class="d-flex align-items-center text-white me-3">
                    <i class="fa-solid fa-user-shield me-2 text-warning"></i>
                    <span><?= htmlspecialchars($currentUser['full_name']) ?></span>
                </div>
                <a href="?route=logout" class="btn btn-outline-light btn-sm"><i class="fa-solid fa-right-from-bracket me-1"></i> Logout</a>
            <?php else: ?>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="?route=login"><i class="fa-solid fa-right-to-bracket me-1"></i> Arbiter Login</a>
                    </li>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</nav>

<main class="container-fluid py-4">
