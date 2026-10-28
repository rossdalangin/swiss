function showPlayerHistory(playerId, tournamentId) {
    return viewPlayerHistory(tournamentId, playerId);
}

function viewPlayerHistory(tournamentId, playerId) {
    const modalEl = document.getElementById('playerHistoryModal');
    if (!modalEl) return;

    const modal = new bootstrap.Modal(modalEl);
    const contentEl = document.getElementById('playerHistoryContent');
    contentEl.innerHTML = '<div class="text-center p-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Loading player history...</p></div>';
    modal.show();

    fetch(`?route=standings/player_history&tournament_id=${tournamentId}&player_id=${playerId}`)
        .then(response => response.json())
        .then(data => {
            if (!data.player) {
                contentEl.innerHTML = '<div class="alert alert-danger">Player details not found.</div>';
                return;
            }

            const p = data.player;
            const history = data.history || [];

            let html = `
                <div class="mb-3 border-bottom pb-2">
                    <h4>${p.full_name} ${p.nickname ? `(${p.nickname})` : ''}</h4>
                    <p class="text-muted mb-0">
                        Rating: <strong>${p.fide_rating || p.national_rating || p.club_rating || 'Unrated'}</strong> |
                        School/Org: <strong>${p.school_org || 'N/A'}</strong> |
                        Age: <strong>${p.age || 'N/A'}</strong>
                    </p>
                </div>
                <h5 class="fw-bold mb-3">Round-by-Round History</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Round</th>
                                <th>Board</th>
                                <th>Color</th>
                                <th>Opponent</th>
                                <th>Opponent Rating</th>
                                <th>Result</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

            if (history.length === 0) {
                html += '<tr><td colspan="6" class="text-center text-muted">No games recorded yet.</td></tr>';
            } else {
                history.forEach(row => {
                    if (row.is_bye == 1) {
                        html += `
                            <tr>
                                <td>Round ${row.round_number}</td>
                                <td>-</td>
                                <td>-</td>
                                <td colspan="2"><span class="badge bg-warning text-dark">BYE</span> (Points awarded: ${row.bye_points})</td>
                                <td><span class="badge bg-success">Win (1.0)</span></td>
                            </tr>
                        `;
                    } else {
                        const isWhite = (row.white_player_id == playerId);
                        const color = isWhite ? 'White' : 'Black';
                        const oppName = isWhite ? row.black_name : row.white_name;
                        const oppRating = isWhite ? (row.black_rating || 'Unrated') : (row.white_rating || 'Unrated');

                        let outcomeBadge = '<span class="badge bg-secondary">Pending</span>';
                        if (row.result === '1-0') {
                            outcomeBadge = isWhite ? '<span class="badge bg-success">Win (1.0)</span>' : '<span class="badge bg-danger">Loss (0.0)</span>';
                        } else if (row.result === '0-1') {
                            outcomeBadge = isWhite ? '<span class="badge bg-danger">Loss (0.0)</span>' : '<span class="badge bg-success">Win (1.0)</span>';
                        } else if (['1/2-1/2', '0.5-0.5', '½-½'].includes(row.result)) {
                            outcomeBadge = '<span class="badge bg-info text-dark">Draw (0.5)</span>';
                        }

                        html += `
                            <tr>
                                <td>Round ${row.round_number}</td>
                                <td>Board ${row.board_number}</td>
                                <td><span class="badge ${isWhite ? 'bg-light text-dark border' : 'bg-dark'}">${color}</span></td>
                                <td><strong>${oppName}</strong></td>
                                <td>${oppRating}</td>
                                <td>${outcomeBadge}</td>
                            </tr>
                        `;
                    }
                });
            }

            html += `
                        </tbody>
                    </table>
                </div>
            `;

            contentEl.innerHTML = html;
        })
        .catch(err => {
            contentEl.innerHTML = `<div class="alert alert-danger">Error loading history: ${err.message}</div>`;
        });
}
