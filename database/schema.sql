-- MySQL / MariaDB Schema for Paete Chess Club Tournament Management System

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS audit_logs;
DROP TABLE IF EXISTS tournament_standings;
DROP TABLE IF EXISTS pairings;
DROP TABLE IF EXISTS rounds;
DROP TABLE IF EXISTS tournament_players;
DROP TABLE IF EXISTS players;
DROP TABLE IF EXISTS tournaments;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

-- Table: users
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'admin',
    full_name VARCHAR(100) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: tournaments
CREATE TABLE tournaments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    date DATE NOT NULL,
    venue VARCHAR(150) NOT NULL,
    organizer_name VARCHAR(100) NOT NULL,
    total_rounds INT NOT NULL DEFAULT 5,
    current_round INT NOT NULL DEFAULT 0,
    scoring_system VARCHAR(20) NOT NULL DEFAULT 'standard',
    time_control VARCHAR(100) DEFAULT '15 mins + 5 secs',
    description TEXT,
    logo_path VARCHAR(255) DEFAULT NULL,
    rating_source VARCHAR(50) DEFAULT 'FIDE',
    pairing_mode VARCHAR(50) DEFAULT 'club_swiss',
    tiebreak_config TEXT,
    allow_withdrawals TINYINT(1) DEFAULT 1,
    allow_late_entries TINYINT(1) DEFAULT 1,
    status VARCHAR(30) NOT NULL DEFAULT 'Draft',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: players
CREATE TABLE players (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    nickname VARCHAR(50) DEFAULT NULL,
    age INT DEFAULT NULL,
    school_org VARCHAR(100) DEFAULT NULL,
    fide_id VARCHAR(30) DEFAULT NULL,
    national_id VARCHAR(30) DEFAULT NULL,
    fide_rating INT DEFAULT NULL,
    national_rating INT DEFAULT NULL,
    club_rating INT DEFAULT NULL,
    rating_source VARCHAR(50) DEFAULT NULL,
    rating_date DATE DEFAULT NULL,
    contact_info VARCHAR(100) DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: tournament_players
CREATE TABLE tournament_players (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tournament_id INT NOT NULL,
    player_id INT NOT NULL,
    initial_seed INT DEFAULT NULL,
    local_pairing_value INT DEFAULT 0,
    status VARCHAR(20) NOT NULL DEFAULT 'active',
    registration_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_tourn_player (tournament_id, player_id),
    FOREIGN KEY (tournament_id) REFERENCES tournaments(id) ON DELETE CASCADE,
    FOREIGN KEY (player_id) REFERENCES players(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: rounds
CREATE TABLE rounds (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tournament_id INT NOT NULL,
    round_number INT NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'pending',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_tourn_round (tournament_id, round_number),
    FOREIGN KEY (tournament_id) REFERENCES tournaments(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: pairings
CREATE TABLE pairings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tournament_id INT NOT NULL,
    round_id INT NOT NULL,
    round_number INT NOT NULL,
    board_number INT NOT NULL,
    white_player_id INT DEFAULT NULL,
    black_player_id INT DEFAULT NULL,
    result VARCHAR(20) NOT NULL DEFAULT 'pending',
    is_bye TINYINT(1) NOT NULL DEFAULT 0,
    bye_player_id INT DEFAULT NULL,
    bye_type VARCHAR(20) DEFAULT NULL,
    bye_points DECIMAL(3,1) NOT NULL DEFAULT 0.0,
    notes TEXT DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tournament_id) REFERENCES tournaments(id) ON DELETE CASCADE,
    FOREIGN KEY (round_id) REFERENCES rounds(id) ON DELETE CASCADE,
    FOREIGN KEY (white_player_id) REFERENCES players(id) ON DELETE SET NULL,
    FOREIGN KEY (black_player_id) REFERENCES players(id) ON DELETE SET NULL,
    FOREIGN KEY (bye_player_id) REFERENCES players(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: tournament_standings
CREATE TABLE tournament_standings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tournament_id INT NOT NULL,
    player_id INT NOT NULL,
    rank INT DEFAULT NULL,
    points DECIMAL(4,1) NOT NULL DEFAULT 0.0,
    played_games INT NOT NULL DEFAULT 0,
    wins INT NOT NULL DEFAULT 0,
    draws INT NOT NULL DEFAULT 0,
    losses INT NOT NULL DEFAULT 0,
    byes INT NOT NULL DEFAULT 0,
    tb_buchholz_cut1 DECIMAL(6,2) NOT NULL DEFAULT 0.0,
    tb_buchholz DECIMAL(6,2) NOT NULL DEFAULT 0.0,
    tb_sonneborn_berger DECIMAL(6,2) NOT NULL DEFAULT 0.0,
    tb_progressive DECIMAL(6,2) NOT NULL DEFAULT 0.0,
    tb_direct_encounter DECIMAL(4,1) NOT NULL DEFAULT 0.0,
    tb_wins INT NOT NULL DEFAULT 0,
    tb_black_wins INT NOT NULL DEFAULT 0,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_tourn_standing (tournament_id, player_id),
    FOREIGN KEY (tournament_id) REFERENCES tournaments(id) ON DELETE CASCADE,
    FOREIGN KEY (player_id) REFERENCES players(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: audit_logs
CREATE TABLE audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tournament_id INT DEFAULT NULL,
    user_id INT DEFAULT NULL,
    action VARCHAR(100) NOT NULL,
    details TEXT DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tournament_id) REFERENCES tournaments(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
