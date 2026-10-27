# Paete Chess Club - Swiss-System Chess Tournament Management System

A complete, secure, browser-based Swiss-System Chess Tournament Management System custom-built for the Paete Chess Club in Paete, Laguna, Philippines.

## Technology Stack
- **Backend**: PHP 8.2+ (Clean MVC & Service Pattern)
- **Database Access**: PDO with Prepared Statements (MySQL 8.0+ / MariaDB and SQLite3 fallback for local testing)
- **Frontend**: HTML5, CSS3, JavaScript, Bootstrap 5, FontAwesome Icons
- **Time Zone**: Asia/Manila
- **Public Root**: `public/`

---

## Directory Structure
```
chess-tournament/
    app/
        Controllers/
            AuthController.php
            DashboardController.php
            TournamentController.php
            PlayerController.php
            PairingController.php
            StandingsController.php
            ExportController.php
        Services/
            Database.php
            AuthManager.php
            PlayerService.php
            TournamentService.php
            PairingEngine.php
            ScoringService.php
            AuditLogger.php
            CsvService.php
        Views/
            auth/
            dashboard/
            tournaments/
            players/
            pairings/
            standings/
            layouts/
    config/
        database.php
    database/
        schema.sql          # MySQL / MariaDB schema
        schema_sqlite.sql   # SQLite schema
        seed.sql            # Seed data (Default admin account)
    public/
        index.php           # Front Controller & Router
        assets/
            css/
                style.css   # Chess-inspired theme & A4 print CSS
            js/
                app.js      # Player history modal & interactive features
    storage/
        db/                 # SQLite database storage (if used)
        logs/
    tests/
        RunTests.php        # Comprehensive automated test suite (Scenarios 1-13)
    README.md
```

---

## Key Features

### 1. Tournament Dashboard
- Real-time tournament statistics (total registered players, rated vs unrated count, current round vs total rounds, completed vs pending games, byes count, current leader).
- Quick action controls: Create Tournament, Manage Players, Generate Pairings, Enter Results, View Standings, Print Summary, Export Standings CSV, Finalize/Reopen Tournament.

### 2. Player Management & Registration
- Individual player registration with FIDE ID, NCFP National Rating, Club Rating, Age, and School/Organization fields.
- Support for **Unrated Players** without inventing artificial ratings.
- **Bulk Import**: Import players by pasting a list of names or uploading a CSV file (`Name, Rating, School, Age`). Includes automatic duplicate detection and preview.
- Initial seeding freezing (Rating descending then Alphabetical).

### 3. Genuine Swiss Pairing Engine ("Club Swiss Pairing — Simplified (Dutch Variant)")
- Groups players into score groups descending.
- Handles odd participant counts by assigning 1.0-point Swiss Byes to eligible lower-seeded/scoring players who haven't received a bye.
- Downfloater logic and strict non-repeat opponent checking.
- Color balance and allocation control (alternating colors, preventing >=3 consecutive same colors, minimizing color differences).
- Protection against generating duplicate rounds or pairing new rounds with unresolved matches.

### 4. Result Recording & Automatic Standings
- Supported outcomes: `1-0`, `0-1`, `½-½`, `1-0F` (White Win by Forfeit), `0-1F` (Black Win by Forfeit), `0-0F` (Double Forfeit).
- Distinguishes between played games and forfeits/byes for tiebreak accuracy.
- Centralized Scoring Service calculates cumulative standings and tiebreaks:
  1. Buchholz Cut-1
  2. Buchholz (Total)
  3. Sonneborn-Berger
  4. Progressive Score
  5. Direct Encounter
  6. Win Count & Most Black Wins
- Champion and Podium finisher panel display on tournament completion.

### 5. Audit Logging & Security
- Mandatory audit logging for result modifications, pairing overrides, and administrative tournament reopenings.
- CSRF token validation on all state-changing forms.
- Password hashing using `password_hash()` (BCRYPT) and verification with `password_verify()`.
- XSS prevention via HTML output escaping (`htmlspecialchars`).

### 6. Printing & CSV Exports
- A4 printer-friendly layout for pairing boards, result sheets, player cards, and standings.
- One-click CSV exports for player rosters and standings tables.

---

## Installation & Setup Instructions

### Local Development / SQLite Setup
1. Ensure PHP 8.2 or later is installed with PDO SQLite support.
2. Initialize SQLite schema and seed default admin:
   ```bash
   php -r "
   require_once 'app/Services/Database.php';
   \$pdo = App\Services\Database::getInstance();
   \$pdo->exec(file_get_contents('database/schema_sqlite.sql'));
   \$pdo->exec(file_get_contents('database/seed.sql'));
   "
   ```
3. Start local PHP server:
   ```bash
   php -S 127.0.0.1:8000 -t public/
   ```
4. Access in browser at `http://127.0.0.1:8000`.

### Production / cPanel Hosting Setup (MySQL / MariaDB)
1. Log in to cPanel and open **MySQL Database Wizard**.
2. Create a new database (e.g., `paetecc_chess`) and MySQL user with full privileges.
3. Open **phpMyAdmin**, select the created database, and import `database/schema.sql` followed by `database/seed.sql`.
4. Upload project files to hosting environment. Point document root to `public/` directory.
5. Update database environment variables or edit `config/database.php`:
   ```php
   'db' => [
       'driver' => 'mysql',
       'host' => '127.0.0.1',
       'port' => '3306',
       'dbname' => 'paetecc_chess',
       'user' => 'your_db_username',
       'pass' => 'your_db_password',
   ]
   ```

---

## Admin Credentials
- **Username**: `admin`
- **Password**: `admin123`

---

## Automated Test Suite

Run all 13 test scenarios covering pairing logic, odd counts, unrated players, color balance, forfeits, result corrections, and persistence:

```bash
php tests/RunTests.php
```

All 16 assertions across Scenarios 1 through 13 pass with 100% success rate.
