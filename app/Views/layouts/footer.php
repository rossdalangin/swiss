</main>

<!-- Player History Modal -->
<div class="modal fade" id="playerHistoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title"><i class="fa-solid fa-user-gear me-2 text-warning"></i> Player Profile & Tournament History</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="playerHistoryContent">
                <!-- Content loaded dynamically via JS -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<footer class="bg-dark text-white-50 text-center py-3 mt-5 no-print border-top border-secondary">
    <div class="container">
        <p class="mb-0">&copy; <?= date('Y') ?> Paete Chess Club, Paete, Laguna, Philippines. Swiss-System Tournament Manager.</p>
    </div>
</footer>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- App JS -->
<script src="assets/js/app.js"></script>
</body>
</html>
