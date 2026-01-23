<?php
/**
 * MARCAR NOTIFICAÇÃO COMO LIDA
 * 
 * Requisição AJAX para marcar notificações como lidas
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $notificationId = intval($_POST['notification_id'] ?? 0);
    $uid = $_SESSION['user_id'] ?? null;
    
    if ($notificationId && $uid) {
        $stmt = $pdo->prepare('
            UPDATE notifications 
            SET is_read = 1, read_at = NOW()
            WHERE id = :id AND user_id = :uid
        ');
        $stmt->execute([
            ':id' => $notificationId,
            ':uid' => $uid
        ]);
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
    }
}
?>
