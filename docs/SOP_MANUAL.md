# Paete Chess Club - Standard Operating Procedures (SOP) & Tournament Management Manual

This Standard Operating Procedure (SOP) manual defines official protocols for running chess tournaments using the **Paete Chess Club Swiss-System Tournament Manager**.

---

## 1. PRE-TOURNAMENT SETUP & CONFIGURATION

### 1.1 Creating a Tournament
1. Log in to the Arbiter Portal (`?route=login`).
2. Navigate to **Tournaments** -> **Create Tournament**.
3. Fill in mandatory fields:
   - **Tournament Name**: Official event title (e.g., *1st Paete Youth Swiss Tournament 2025*).
   - **Date**: Asia/Manila calendar date.
   - **Venue**: Specific hall or plaza in Paete, Laguna.
   - **Chief Arbiter / Director**: Licensed arbiter name.
   - **Rounds Recommendation**:
     - 4 players or fewer: 3 rounds
     - 5 - 8 players: 4 rounds
     - 9 - 16 players: 5 rounds
     - 17 - 32 players: 6 rounds
     - 33 - 64 players: 7 rounds
   - **Time Control**: e.g., `15 mins + 5 secs increment`.
   - **Pairing Mode**: Select *Club Swiss Pairing — Simplified (Dutch Variant)* or *FIDE Dutch System Rules*.
   - **Game Scoring**: Select *Standard Chess (1 - ½ - 0)* or *Football Scoring (3 - 1 - 0)*.

---

## 2. PLAYER REGISTRATION & ROSTER MANAGEMENT

### 2.1 Individual Player Registration
1. Navigate to **Players** -> **Add Individual Player**.
2. Enter Full Name, Age, School/Organization, and official ratings if available (FIDE / National).
3. **Unrated Players Handling**: Leave rating blank. Do NOT enter arbitrary or estimated official ratings.

### 2.2 Bulk Import via CSV or Text Area
1. Navigate to **Players** -> **Bulk Import**.
2. Option A: Paste names into text area (`Name, Rating, School, Age` or simply `Name`).
3. Option B: Upload a `.csv` file.
4. **Duplicate Resolution**: Names matching existing players in the database will automatically link to the existing player record, avoiding duplicate profiles.

### 2.3 Freezing Seeding
- Prior to Round 1 generation, initial seeding is automatically calculated:
  1. Rating descending (FIDE -> National -> Club)
  2. Alphabetical name ascending
- Once Round 1 is generated, initial seeding is permanently frozen for that event.

---

## 3. ROUND PAIRINGS & MATCH MANAGEMENT

### 3.1 Generating Pairings
1. Navigate to **Pairings**.
2. Confirm player list and click **Generate Round [X]**.
3. The engine computes score groups, downfloaters, non-repeat opponent checks, color allocation balance, and 1.0-point Swiss Bye assignment for odd participant counts.
4. Click **Print Board Pairings** to print A4 pairing sheets for display in the playing hall.

### 3.2 Result Entry & Verification
- Match results must be submitted using official signed match result slips.
- **Supported Result Codes**:
  - `1-0`: White wins played game (1 point White, 0 Black).
  - `0-1`: Black wins played game (0 point White, 1 Black).
  - `½-½`: Draw (0.5 point each).
  - `1-0F`: White wins by forfeit (Default/No-show). Awards 1.0 point to White but does NOT increment played games count for tiebreak adjustments.
  - `0-1F`: Black wins by forfeit (Default/No-show).
  - `0-0F`: Double forfeit (0 points each).

### 3.3 Result Correction & Audit Trail
1. To correct an incorrectly entered result, click **Result** on the board.
2. Select the corrected result radio option.
3. Enter a mandatory **Result Edit Reason** note.
4. Click **Confirm & Save Result**.
5. The system automatically recalculates standings and logs the change in `audit_logs` with timestamp, administrator ID, original result, new result, and explanation.

---

## 4. STANDINGS & TIEBREAK CALCULATIONS

### 4.1 Scoring System
- **Win**: 1.0 point
- **Draw**: 0.5 point
- **Loss**: 0.0 point
- **Swiss Bye**: 1.0 point

### 4.2 Tiebreak Priorities
When two or more players finish with equal tournament points, ranks are decided in order:
1. **Buchholz Cut-1 (BH-C1)**: Sum of opponents' final scores, excluding lowest-scoring opponent.
2. **Buchholz (BH)**: Total sum of all opponents' final scores.
3. **Sonneborn-Berger (SB)**: Sum of scores of defeated opponents plus half of drawn opponents' scores.
4. **Progressive Score (PS)**: Cumulative sum of player's score at the end of each round.
5. **Direct Encounter**: Outcome of head-to-head match between tied players.
6. **Total Wins & Black Wins**.

---

## 5. TOURNAMENT FINALIZATION & REOPENING

### 5.1 Finalizing a Tournament
1. After all configured rounds are completed and all match results are recorded, click **Finalize Tournament**.
2. The system calculates final standings, displays the **Tournament Champion & Podium Finishers**, and locks editing.

### 5.2 Administrative Reopening
1. If a result error or protest is upheld after finalization, an arbiter can click **Reopen Tournament**.
2. Provide a mandatory reason in the confirmation modal.
3. Reopening is logged in the audit trail, allowing necessary result corrections before re-finalizing.
