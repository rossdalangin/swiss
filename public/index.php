<?php

// Autoloader for App namespace
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\TournamentController;
use App\Controllers\PlayerController;
use App\Controllers\PairingController;
use App\Controllers\StandingsController;
use App\Controllers\ExportController;

$route = $_GET['route'] ?? 'dashboard';

switch ($route) {
    case 'login':
        (new AuthController())->login();
        break;
    case 'logout':
        (new AuthController())->logout();
        break;
    case 'dashboard':
        (new DashboardController())->index();
        break;
    case 'tournament/create':
        (new TournamentController())->create();
        break;
    case 'tournament/edit':
        (new TournamentController())->edit();
        break;
    case 'tournament/finalize':
        (new TournamentController())->finalize();
        break;
    case 'tournament/reopen':
        (new TournamentController())->reopen();
        break;
    case 'players':
        (new PlayerController())->index();
        break;
    case 'players/create':
        (new PlayerController())->create();
        break;
    case 'players/bulk_import':
        (new PlayerController())->bulkImport();
        break;
    case 'players/register':
        (new PlayerController())->registerToTournament();
        break;
    case 'players/remove':
        (new PlayerController())->removeFromTournament();
        break;
    case 'pairings':
        (new PairingController())->index();
        break;
    case 'pairings/generate':
        (new PairingController())->generate();
        break;
    case 'pairings/record_result':
        (new PairingController())->recordResult();
        break;
    case 'standings':
        (new StandingsController())->index();
        break;
    case 'standings/player_history':
        (new StandingsController())->playerHistory();
        break;
    case 'export/standings':
        (new ExportController())->exportStandings();
        break;
    case 'export/players':
        (new ExportController())->exportPlayers();
        break;
    default:
        header("HTTP/1.0 404 Not Found");
        echo "404 Page Not Found";
        break;
}
