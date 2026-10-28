-- Default seed data for Paete Chess Club Tournament Management System
-- Password for default admin account is 'admin123'

INSERT INTO users (id, username, password_hash, role, full_name) VALUES
(1, 'admin', '$2y$10$X49dGyw/8PRxxqmN7KCi9umbLCSyd.DAlQEuqJTAGvXYx4we6K8gO', 'admin', 'Paete Chess Club Chief Arbiter');

-- Seed 5 Premium Tournaments for Paete Chess Club
INSERT INTO tournaments (id, name, date, venue, organizer_name, total_rounds, current_round, scoring_system, time_control, description, rating_source, pairing_mode, allow_withdrawals, allow_late_entries, status) VALUES
(1, '1st Paete Laguna Youth Swiss Championship 2025', '2025-05-15', 'Paete Town Plaza Hall', 'Paete Chess Club Chief Arbiter', 5, 5, 'standard', '15 mins + 5 secs', 'Official youth championship for elementary and high school chess competitors across Paete, Laguna.', 'FIDE', 'club_swiss', 1, 1, 'Completed'),
(2, 'Paete Town Fiesta Open Rapid Swiss 2025', '2025-07-28', 'Paete Municipal Gym', 'Paete Chess Club Committee', 5, 3, 'standard', '15 mins + 5 secs', 'Annual Paete fiesta open chess tournament open to local residents, club members, and visiting woodpushers.', 'FIDE', 'club_swiss', 1, 1, 'In Progress'),
(3, 'Paete Inter-Barangay Swiss Chess Cup 2025', '2025-09-10', 'Barangay Ibaba Social Hall', 'Brgy. Arbiter Council', 5, 0, 'standard', '15 mins + 5 secs', 'Inter-barangay team and individual championship featuring community players across all barangays in Paete.', 'National', 'club_swiss', 1, 1, 'Registration Open'),
(4, 'Paete Elementary & High School Interschool Grand Prix', '2025-10-18', 'Paete National High School Gym', 'Paete School Board & PCC', 5, 0, 'standard', '10 mins Rapid', 'Scholastic grand prix tournament designed to identify top youth chess prospects in Paete schools.', 'Club', 'club_swiss', 1, 1, 'Draft'),
(5, 'Paete Masters & Executive Rapid Invitational 2025', '2025-12-05', 'Paete Cultural Center', 'Paete Executive Council', 5, 0, 'standard', '15 mins + 5 secs', 'Invitational rapid tournament showcasing senior woodpushers, club alumni, and local chess masters.', 'FIDE', 'club_swiss', 1, 1, 'Draft');

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

-- Seed Rounds for Completed Tournament 1
INSERT INTO rounds (id, tournament_id, round_number, status) VALUES
(1, 1, 1, 'completed'),
(2, 1, 2, 'completed'),
(3, 1, 3, 'completed'),
(4, 1, 4, 'completed'),
(5, 1, 5, 'completed');

-- Seed Pairings & Results for Completed Tournament 1
INSERT INTO pairings (id, tournament_id, round_id, round_number, board_number, white_player_id, black_player_id, result, is_bye, bye_player_id, bye_type, bye_points, notes) VALUES
(1, 1, 1, 1, 1, 1, 4, '1-0', 0, NULL, NULL, 0.0, NULL),
(2, 1, 1, 1, 2, 2, 6, '0-1', 0, NULL, NULL, 0.0, NULL),
(3, 1, 1, 1, 3, 3, 5, '1-0', 0, NULL, NULL, 0.0, NULL),
(4, 1, 1, 1, 4, 7, 8, '0-1', 0, NULL, NULL, 0.0, NULL),
(5, 1, 2, 2, 1, 6, 1, '0-1', 0, NULL, NULL, 0.0, NULL),
(6, 1, 2, 2, 2, 8, 3, '0-1', 0, NULL, NULL, 0.0, NULL),
(7, 1, 2, 2, 3, 4, 7, '1-0', 0, NULL, NULL, 0.0, NULL),
(8, 1, 2, 2, 4, 5, 2, '1-0', 0, NULL, NULL, 0.0, NULL),
(9, 1, 3, 3, 1, 1, 3, '1/2-1/2', 0, NULL, NULL, 0.0, NULL),
(10, 1, 3, 3, 2, 6, 8, '1-0', 0, NULL, NULL, 0.0, NULL),
(11, 1, 3, 3, 3, 4, 5, '1-0', 0, NULL, NULL, 0.0, NULL),
(12, 1, 3, 3, 4, 2, 7, '1/2-1/2', 0, NULL, NULL, 0.0, NULL),
(13, 1, 4, 4, 1, 8, 1, '0-1', 0, NULL, NULL, 0.0, NULL),
(14, 1, 4, 4, 2, 3, 6, '0-1', 0, NULL, NULL, 0.0, NULL),
(15, 1, 4, 4, 3, 2, 4, '1-0', 0, NULL, NULL, 0.0, NULL),
(16, 1, 4, 4, 4, 7, 5, '0-1', 0, NULL, NULL, 0.0, NULL),
(17, 1, 5, 5, 1, 1, 7, '1-0', 0, NULL, NULL, 0.0, NULL),
(18, 1, 5, 5, 2, 6, 4, '1-0', 0, NULL, NULL, 0.0, NULL),
(19, 1, 5, 5, 3, 3, 2, '1-0', 0, NULL, NULL, 0.0, NULL),
(20, 1, 5, 5, 4, 5, 8, '0-1', 0, NULL, NULL, 0.0, NULL);

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
(1, 1, 'CREATE_TOURNAMENT', "Created tournament '1st Paete Laguna Youth Swiss Championship 2025'"),
(1, 1, 'GENERATE_PAIRINGS', "Generated pairings for Round 1."),
(1, 1, 'GENERATE_PAIRINGS', "Generated pairings for Round 5."),
(1, 1, 'FINALIZE_TOURNAMENT', "Finalized tournament and declared champion Juan Dela Cruz."),
(2, 1, 'CREATE_TOURNAMENT', "Created tournament 'Paete Town Fiesta Open Rapid Swiss 2025'"),
(3, 1, 'CREATE_TOURNAMENT', "Created tournament 'Paete Inter-Barangay Swiss Chess Cup 2025'"),
(4, 1, 'CREATE_TOURNAMENT', "Created tournament 'Paete Elementary & High School Interschool Grand Prix'"),
(5, 1, 'CREATE_TOURNAMENT', "Created tournament 'Paete Masters & Executive Rapid Invitational 2025'");
