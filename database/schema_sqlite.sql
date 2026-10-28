-- SQLite Schema for Paete Chess Club Tournament Management System

PRAGMA foreign_keys = ON;

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
    UNIQUE (tournament_id, player_id),
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
    UNIQUE (tournament_id, round_number),
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
    UNIQUE (tournament_id, player_id),
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

-- Seed Default Admin
INSERT INTO users (id, username, password_hash, role, full_name) VALUES
(1, 'admin', '$2y$10$X49dGyw/8PRxxqmN7KCi9umbLCSyd.DAlQEuqJTAGvXYx4we6K8gO', 'admin', 'Paete Chess Club Chief Arbiter');

-- Seed 5 Premium Tournaments for Paete Chess Club
INSERT INTO tournaments (id, name, date, venue, organizer_name, total_rounds, current_round, scoring_system, time_control, description, rating_source, pairing_mode, allow_withdrawals, allow_late_entries, status) VALUES
(1, '1st Paete Laguna Youth Swiss Championship 2025', '2025-05-15', 'Paete Town Plaza Hall', 'Paete Chess Club Chief Arbiter', 5, 5, 'standard', '15 mins + 5 secs', 'Official youth tournament for Paete elementary and high school competitors.', 'FIDE', 'club_swiss', 1, 1, 'Completed'),
(2, 'Paete Town Fiesta Open Rapid Swiss 2025', '2025-07-28', 'Paete Municipal Gym', 'Paete Chess Club Committee', 5, 3, 'standard', '15 mins + 5 secs', 'Annual Paete fiesta open chess tournament for all ages and skill levels.', 'FIDE', 'club_swiss', 1, 1, 'In Progress'),
(3, 'Paete Inter-Barangay Swiss Chess Cup 2025', '2025-09-10', 'Barangay Ibaba Social Hall', 'Brgy. Arbiter Council', 5, 0, 'standard', '15 mins + 5 secs', 'Barangay championship featuring team representatives across Paete, Laguna.', 'National', 'club_swiss', 1, 1, 'Registration Open'),
(4, 'Paete Elementary & High School Interschool Grand Prix', '2025-10-18', 'Paete National High School Gym', 'Paete School Board & PCC', 5, 0, 'standard', '10 mins Rapid', 'Interschool grand prix for scholastic chess development in Paete.', 'Club', 'club_swiss', 1, 1, 'Draft'),
(5, 'Paete Masters & Executive Rapid Invitational 2025', '2025-12-05', 'Paete Cultural Center', 'Paete Executive Council', 5, 0, 'standard', '15 mins + 5 secs', 'Invitational rapid tournament for senior players and experienced local masters.', 'FIDE', 'club_swiss', 1, 1, 'Draft');

-- Seed Official Players
INSERT INTO players (id, full_name, nickname, age, school_org, fide_rating, national_rating, club_rating, rating_source, contact_info) VALUES
(1, 'Juan Dela Cruz', 'Juaning', 16, 'Paete National High School', 1850, 1820, 1850, 'FIDE', 'juan@paetechess.org'),
(2, 'Pedro Santos', 'Pedring', 12, 'Paete Elementary School', NULL, NULL, NULL, 'Unrated', 'pedro@paetechess.org'),
(3, 'Maria Reyes', 'Masha', 15, 'Paete Chess Club', 1450, 1420, 1450, 'FIDE', 'maria@paetechess.org'),
(4, 'Jose Garcia', 'Pepe', 17, 'Laguna State Polytechnic Univ', 1200, 1250, 1200, 'National', 'jose@paetechess.org'),
(5, 'Ana Mendoza', 'Anak', 14, 'Paete Science High School', NULL, NULL, NULL, 'Unrated', 'ana@paetechess.org'),
(6, 'Carlo Ramos', 'Caloy', 18, 'Paete Chess Club', 1600, 1580, 1600, 'FIDE', 'carlo@paetechess.org'),
(7, 'Elena Quesada', 'Nene', 13, 'Paete Elementary School', NULL, NULL, NULL, 'Unrated', 'elena@paetechess.org'),
(8, 'Gabriel Bañagale', 'Gabby', 16, 'Paete National High School', 1350, 1320, 1350, 'Club', 'gabriel@paetechess.org');

-- Register Players to Completed Tournament 1
INSERT INTO tournament_players (tournament_id, player_id, initial_seed, local_pairing_value, status) VALUES
(1, 1, 1, 0, 'active'),
(1, 6, 2, 0, 'active'),
(1, 3, 3, 0, 'active'),
(1, 8, 4, 0, 'active'),
(1, 4, 5, 0, 'active'),
(1, 2, 6, 0, 'active'),
(1, 5, 7, 0, 'active'),
(1, 7, 8, 0, 'active');

-- Register Players to In Progress Tournament 2
INSERT INTO tournament_players (tournament_id, player_id, initial_seed, local_pairing_value, status) VALUES
(2, 1, 1, 0, 'active'),
(2, 6, 2, 0, 'active'),
(2, 3, 3, 0, 'active'),
(2, 8, 4, 0, 'active'),
(2, 4, 5, 0, 'active'),
(2, 2, 6, 0, 'active');

-- Register Players to Open Registration Tournament 3
INSERT INTO tournament_players (tournament_id, player_id, initial_seed, local_pairing_value, status) VALUES
(3, 1, 1, 0, 'active'),
(3, 3, 2, 0, 'active'),
(3, 4, 3, 0, 'active'),
(3, 6, 4, 0, 'active');

-- Seed Final Standings for Tournament 1 (Completed Champion: Juan Dela Cruz)
INSERT INTO tournament_standings (tournament_id, player_id, rank, points, played_games, wins, draws, losses, byes, tb_buchholz_cut1, tb_buchholz, tb_sonneborn_berger, tb_progressive, tb_direct_encounter, tb_wins, tb_black_wins) VALUES
(1, 1, 1, 4.5, 5, 4, 1, 0, 0, 12.0, 14.5, 12.75, 13.5, 0.0, 4, 2),
(1, 6, 2, 4.0, 5, 4, 0, 1, 0, 11.5, 13.0, 9.50, 12.0, 0.0, 4, 2),
(1, 3, 3, 3.5, 5, 3, 1, 1, 0, 12.5, 14.0, 8.25, 10.5, 0.0, 3, 1),
(1, 8, 4, 3.0, 5, 3, 0, 2, 0, 10.0, 11.5, 5.00, 8.0, 0.0, 3, 1),
(1, 4, 5, 2.0, 5, 2, 0, 3, 0, 9.5, 10.5, 3.00, 5.0, 0.0, 2, 1),
(1, 2, 6, 1.5, 5, 1, 1, 3, 0, 8.5, 9.5, 2.25, 4.5, 0.0, 1, 0),
(1, 5, 7, 1.0, 5, 1, 0, 4, 0, 8.0, 8.5, 1.00, 3.0, 0.0, 1, 1),
(1, 7, 8, 0.5, 5, 0, 1, 4, 0, 7.5, 8.0, 0.75, 1.5, 0.0, 0, 0);

-- Seed Audit Log Entries
INSERT INTO audit_logs (tournament_id, user_id, action, details) VALUES
(1, 1, 'CREATE_TOURNAMENT', 'Created tournament ''1st Paete Laguna Youth Swiss Championship 2025'''),
(1, 1, 'FINALIZE_TOURNAMENT', 'Finalized tournament and declared champion Juan Dela Cruz.'),
(2, 1, 'CREATE_TOURNAMENT', 'Created tournament ''Paete Town Fiesta Open Rapid Swiss 2025'''),
(3, 1, 'CREATE_TOURNAMENT', 'Created tournament ''Paete Inter-Barangay Swiss Chess Cup 2025'''),
(4, 1, 'CREATE_TOURNAMENT', 'Created tournament ''Paete Elementary & High School Interschool Grand Prix'''),
(5, 1, 'CREATE_TOURNAMENT', 'Created tournament ''Paete Masters & Executive Rapid Invitational 2025''');
