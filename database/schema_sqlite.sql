-- SQLite Schema for Paete Chess Club Tournament Management System

DROP TABLE IF EXISTS audit_logs;
DROP TABLE IF EXISTS tournament_standings;
DROP TABLE IF EXISTS pairings;
DROP TABLE IF EXISTS rounds;
DROP TABLE IF EXISTS tournament_players;
DROP TABLE IF EXISTS players;
DROP TABLE IF EXISTS tournaments;
DROP TABLE IF EXISTS users;

-- Table: users
CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    role TEXT NOT NULL DEFAULT 'admin',
    full_name TEXT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Table: tournaments
CREATE TABLE tournaments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    date DATE NOT NULL,
    venue TEXT NOT NULL,
    organizer_name TEXT NOT NULL,
    total_rounds INTEGER NOT NULL DEFAULT 5,
    current_round INTEGER NOT NULL DEFAULT 0,
    scoring_system TEXT NOT NULL DEFAULT 'standard',
    time_control TEXT DEFAULT '15 mins + 5 secs',
    description TEXT,
    logo_path TEXT DEFAULT NULL,
    rating_source TEXT DEFAULT 'FIDE',
    pairing_mode TEXT DEFAULT 'club_swiss',
    tiebreak_config TEXT,
    allow_withdrawals INTEGER DEFAULT 1,
    allow_late_entries INTEGER DEFAULT 1,
    status TEXT NOT NULL DEFAULT 'Draft',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Table: players
CREATE TABLE players (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    full_name TEXT NOT NULL,
    nickname TEXT DEFAULT NULL,
    age INTEGER DEFAULT NULL,
    school_org TEXT DEFAULT NULL,
    fide_id TEXT DEFAULT NULL,
    national_id TEXT DEFAULT NULL,
    fide_rating INTEGER DEFAULT NULL,
    national_rating INTEGER DEFAULT NULL,
    club_rating INTEGER DEFAULT NULL,
    rating_source TEXT DEFAULT NULL,
    rating_date DATE DEFAULT NULL,
    contact_info TEXT DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Table: tournament_players
CREATE TABLE tournament_players (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    tournament_id INTEGER NOT NULL,
    player_id INTEGER NOT NULL,
    initial_seed INTEGER DEFAULT NULL,
    local_pairing_value INTEGER DEFAULT 0,
    status TEXT NOT NULL DEFAULT 'active',
    registration_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(tournament_id, player_id),
    FOREIGN KEY (tournament_id) REFERENCES tournaments(id) ON DELETE CASCADE,
    FOREIGN KEY (player_id) REFERENCES players(id) ON DELETE CASCADE
);

-- Table: rounds
CREATE TABLE rounds (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    tournament_id INTEGER NOT NULL,
    round_number INTEGER NOT NULL,
    status TEXT NOT NULL DEFAULT 'pending',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(tournament_id, round_number),
    FOREIGN KEY (tournament_id) REFERENCES tournaments(id) ON DELETE CASCADE
);

-- Table: pairings
CREATE TABLE pairings (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    tournament_id INTEGER NOT NULL,
    round_id INTEGER NOT NULL,
    round_number INTEGER NOT NULL,
    board_number INTEGER NOT NULL,
    white_player_id INTEGER DEFAULT NULL,
    black_player_id INTEGER DEFAULT NULL,
    result TEXT NOT NULL DEFAULT 'pending',
    is_bye INTEGER NOT NULL DEFAULT 0,
    bye_player_id INTEGER DEFAULT NULL,
    bye_type TEXT DEFAULT NULL,
    bye_points REAL NOT NULL DEFAULT 0.0,
    notes TEXT DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tournament_id) REFERENCES tournaments(id) ON DELETE CASCADE,
    FOREIGN KEY (round_id) REFERENCES rounds(id) ON DELETE CASCADE,
    FOREIGN KEY (white_player_id) REFERENCES players(id) ON DELETE SET NULL,
    FOREIGN KEY (black_player_id) REFERENCES players(id) ON DELETE SET NULL,
    FOREIGN KEY (bye_player_id) REFERENCES players(id) ON DELETE SET NULL
);

-- Table: tournament_standings
CREATE TABLE tournament_standings (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    tournament_id INTEGER NOT NULL,
    player_id INTEGER NOT NULL,
    rank INTEGER DEFAULT NULL,
    points REAL NOT NULL DEFAULT 0.0,
    played_games INTEGER NOT NULL DEFAULT 0,
    wins INTEGER NOT NULL DEFAULT 0,
    draws INTEGER NOT NULL DEFAULT 0,
    losses INTEGER NOT NULL DEFAULT 0,
    byes INTEGER NOT NULL DEFAULT 0,
    tb_buchholz_cut1 REAL NOT NULL DEFAULT 0.0,
    tb_buchholz REAL NOT NULL DEFAULT 0.0,
    tb_sonneborn_berger REAL NOT NULL DEFAULT 0.0,
    tb_progressive REAL NOT NULL DEFAULT 0.0,
    tb_direct_encounter REAL NOT NULL DEFAULT 0.0,
    tb_wins INTEGER NOT NULL DEFAULT 0,
    tb_black_wins INTEGER NOT NULL DEFAULT 0,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(tournament_id, player_id),
    FOREIGN KEY (tournament_id) REFERENCES tournaments(id) ON DELETE CASCADE,
    FOREIGN KEY (player_id) REFERENCES players(id) ON DELETE CASCADE
);

-- Table: audit_logs
CREATE TABLE audit_logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    tournament_id INTEGER DEFAULT NULL,
    user_id INTEGER DEFAULT NULL,
    action TEXT NOT NULL,
    details TEXT DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tournament_id) REFERENCES tournaments(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);
