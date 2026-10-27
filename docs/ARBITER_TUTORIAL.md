# Paete Chess Club - Volunteer Arbiter & Club Officer Training Guide

Welcome to the **Paete Chess Club Tournament Management System**. This tutorial provides step-by-step instructions for volunteer arbiters, club officers, and tournament directors running local chess competitions in Paete, Laguna.

---

## TUTORIAL 1: YOUR FIRST TOURNAMENT IN 5 EASY STEPS

### Step 1: Log In
1. Open your browser and go to `http://localhost:8000` (or your domain URL).
2. Click **Arbiter Login** in the top navigation bar.
3. Credentials:
   - **Username**: `admin`
   - **Password**: `admin123`

### Step 2: Create a Tournament
1. Click **Create Tournament** on the dashboard.
2. Enter:
   - Tournament Name: `1st Paete Barangay Chess Championship`
   - Date: Select today's date
   - Venue: `Paete Town Plaza`
   - Rounds: `5`
3. Click **Save & Create Tournament**.

### Step 3: Register & Import Players
1. Click **Manage Players** -> **Bulk Import (CSV / Text)**.
2. Paste your player list in the text box:
   ```
   Juan Dela Cruz, 1800, Paete High School, 16
   Pedro Santos, , Paete Elementary, 12
   Maria Reyes, 1450, Paete Chess Club, 15
   Jose Garcia, , Paete Youth, 14
   Ana Mendoza, 1200, Paete High School, 15
   Carlo Ramos, , Paete PCC, 13
   ```
3. Click **Process & Import Players**.

### Step 4: Generate Board Pairings & Print
1. Click **Generate / View Pairings**.
2. Click **Generate Round 1**. The system will create balanced Swiss board assignments.
3. Click **Print Board Pairings** to print out A4 pairing sheets for display on the bulletin board.

### Step 5: Record Match Results & View Standings
1. As games finish, collect the signed match result slips.
2. Click **Result** on the corresponding board.
3. Select `1 - 0` (White Wins), `½ - ½` (Draw), or `0 - 1` (Black Wins).
4. Click **Confirm & Save Result**.
5. Click **View Standings** to view real-time provisional standings and the leaderboard!

---

## FREQUENTLY ASKED QUESTIONS (FAQ) FOR ARBITERS

### Q: What happens if we have an odd number of players (e.g., 17 players)?
**A**: The pairing engine automatically identifies the lowest-scoring, lowest-seeded player who has not received a bye and assigns a **1.0-point Swiss Bye**. No fictitious player is created.

### Q: Can two players play against each other twice?
**A**: No. The Swiss engine maintains a strict match history and prevents repeat opponents across all rounds.

### Q: How do I handle a forfeit (player did not show up)?
**A**: Select `1-0F` (White Win by Forfeit) or `0-1F` (Black Win by Forfeit). This awards 1.0 tournament point to the winner without adding an artificial game to played game statistics for tiebreak calculations.

### Q: How do I fix a result entered by mistake?
**A**: Click **Result** on the board, select the correct score, enter a brief explanation in the **Result Edit Reason** field, and save. The edit is recorded in the permanent audit trail.
