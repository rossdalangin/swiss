# Paete Chess Club — Swiss-System Chess Tournament Management System

A complete, secure, lightweight, and modern PHP/MySQL browser-based **Swiss-System Chess Tournament Management System** custom-designed for the **Paete Chess Club** in Paete, Laguna, Philippines.

The application allows tournament directors and arbiters to manage player rosters, generate round-by-round Swiss pairings, record match results, calculate FIDE-compliant tiebreak standings, inspect full audit logs, and export printable reports and CSV files.

---

## Key Features

1. **Dashboard & Event Management**
   - Displays real-time tournament statistics: Total Players, Rated vs Unrated counts, Active Round, Completed vs Pending Games, Byes Awarded, and Tournament Leaders.
   - Status workflow: `Draft` &rarr; `Registration Open` &rarr; `In Progress` &rarr; `Completed` &rarr; `Archived`.
   - Tournament status lock and administrative reopening with full audit logging.

2. **Swiss-System Pairing Engine ("Club Swiss — Dutch Variant")**
   - Score group matching with downfloater tracking.
   - Strict non-repeat opponent checks.
   - White vs Black color balance allocation and preference rules.
   - Automatic 1.0-point Swiss Bye assignment for odd player counts without creating fictional played games.

3. **Player Registration & Bulk Import**
   - Individual player registration with FIDE, National (NCFP), and Club rating support.
   - Unrated players are preserved as unrated without assigning fake artificial ratings.
   - Bulk text-area paste import (`Juan Dela Cruz, 1800, Paete High, 16`).
   - Bulk CSV file import with duplicate detection.

4. **Result Recording & Forfeits**
   - Supported results: `1-0` (White Win), `0-1` (Black Win), `½-½` (Draw), `1-0F` (White Forfeit Win), `0-1F` (Black Forfeit Win), `0-0F` (Double Forfeit).
   - Played games vs unplayed forfeit tracking for accurate tiebreak calculations.
   - Administrative result corrections with audit logging.

5. **Automatic Tiebreak Standings**
   - FIDE-compliant tiebreaks:
     1. Buchholz Cut-1
     2. Buchholz Total
     3. Sonneborn-Berger
     4. Progressive Score
     5. Direct Encounter
     6. Win Count
     7. Most Black Wins

6. **Admin Settings & Security**
   - BCRYPT password hashing and secure authorization checks.
   - PDO prepared statements across all database interactions.
   - Session protection and CSRF token verification.
   - Arbiter security profile management (password change and full name/username update).
   - System audit trail log viewer.

7. **Documentation & Printing**
   - Printer-friendly layout with `@media print` rules for A4 paper export.
   - CSV exports for standings and player lists.
   - Complete Standard Operating Procedures (`docs/SOP_MANUAL.md`).
   - Step-by-step Arbiter Training Tutorial (`docs/ARBITER_TUTORIAL.md`).
   - Detailed Installation Guide for Localhost and cPanel (`INSTALLATION_TUTORIAL.txt`).

---

## Tech Stack & System Requirements

- **PHP**: PHP 8.2 or higher
- **Database**: MySQL 8.0+, MariaDB 10.3+, or SQLite3
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla ES6), Bootstrap 5.3, FontAwesome 6
- **Architecture**: MVC & Service Pattern using Object-Oriented PHP
- **Timezone**: `Asia/Manila` (PHT, UTC+8)

---

## Quick Local Installation Guide

1. Clone or extract the project repository into your web root or project directory.
2. Start the built-in PHP development server:
   ```bash
   php -S 127.0.0.1:8000 -t public/
   ```
3. Open your web browser and navigate to:
   ```
   http://127.0.0.1:8000
   ```
4. Log in using default arbiter credentials:
   - **Username**: `admin`
   - **Password**: `admin123`

---

## Running Automated Tests

Run the 13-scenario automated test suite to verify pairing logic, scoring calculations, forfeit handling, result corrections, and audit logging:

```bash
php tests/RunTests.php
```

---

## License & Support

Developed for the **Paete Chess Club**, Paete, Laguna, Philippines.
All rights reserved.
