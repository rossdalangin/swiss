<?php

namespace App\Services;

use PDO;

class AuditLogger {
    private PDO $db;

    public function __construct(?PDO $db = null) {
        $this->db = $db ?? Database::getInstance();
    }

    public function log(?int $tournamentId, ?int $userId, string $action, string $details): void {
        $stmt = $this->db->prepare("INSERT INTO audit_logs (tournament_id, user_id, action, details) VALUES (:t_id, :u_id, :action, :details)");
        $stmt->execute([
            ':t_id' => $tournamentId,
            ':u_id' => $userId,
            ':action' => $action,
            ':details' => $details
        ]);
    }

    public function getLogs(?int $tournamentId = null): array {
        if ($tournamentId) {
            $stmt = $this->db->prepare("
                SELECT al.*, u.username, u.full_name
                FROM audit_logs al
                LEFT JOIN users u ON al.user_id = u.id
                WHERE al.tournament_id = :t_id
                ORDER BY al.created_at DESC
            ");
            $stmt->execute([':t_id' => $tournamentId]);
        } else {
            $stmt = $this->db->query("
                SELECT al.*, u.username, u.full_name
                FROM audit_logs al
                LEFT JOIN users u ON al.user_id = u.id
                ORDER BY al.created_at DESC
            ");
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
