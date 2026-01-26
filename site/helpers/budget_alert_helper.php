<?php
/**
 * Budget Alert Helper
 * 
 * Verifica orçamentos e envia emails quando atingem limites
 */

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/EmailService.php';

function checkAndSendBudgetAlerts($pdo, $userId, $email, $username) {
    /**
     * Verifica se algum orçamento do utilizador atingiu limites
     * e envia email de alerta se necessário
     */
    
    $emailService = new EmailService();
    
    // Buscar todos os orçamentos ativos
    $stmt = $pdo->prepare('
        SELECT 
            b.id,
            b.name,
            b.amount as limit_amount,
            b.period,
            b.category,
            b.start_date,
            b.end_date,
            COALESCE(SUM(t.amount), 0) as amount_spent
        FROM budgets b
        LEFT JOIN transactions t ON (
            t.user_id = b.user_id AND
            t.transaction_date >= b.start_date AND
            (b.end_date IS NULL OR t.transaction_date <= b.end_date)
            AND (b.category IS NULL OR t.category = b.category)
        )
        WHERE b.user_id = ? AND b.active = 1
        GROUP BY b.id
        ORDER BY b.start_date DESC
    ');
    
    $stmt->execute([$userId]);
    $budgets = $stmt->fetchAll();
    
    $alertsSent = 0;
    
    foreach ($budgets as $budget) {
        $percentage = $budget['limit_amount'] > 0 
            ? ($budget['amount_spent'] / $budget['limit_amount']) * 100 
            : 0;
        
        // Enviar alerta se atingiu 80% ou mais
        if ($percentage >= 80) {
            // Verificar se já foi enviado alerta hoje
            $checkStmt = $pdo->prepare('
                SELECT id FROM budget_alerts 
                WHERE budget_id = ? 
                AND DATE(triggered_at) = CURDATE()
                AND percentage >= ?
                LIMIT 1
            ');
            $checkStmt->execute([$budget['id'], $percentage]);
            
            if (!$checkStmt->fetch()) {
                // Preparar dados para email
                $budgetData = [
                    'name' => $budget['name'],
                    'limit' => number_format($budget['limit_amount'], 2, ',', '.'),
                    'amount_spent' => number_format($budget['amount_spent'], 2, ',', '.'),
                    'percentage' => round($percentage, 1),
                    'category' => $budget['category'] ?? 'Todas as categorias'
                ];
                
                // Enviar email
                if ($emailService->sendBudgetAlert($email, $username, $budgetData)) {
                    // Registar alerta na BD
                    $insertStmt = $pdo->prepare('
                        INSERT INTO budget_alerts 
                        (budget_id, user_id, alert_type, percentage, amount_spent, triggered_at)
                        VALUES (?, ?, ?, ?, ?, NOW())
                    ');
                    
                    $alertType = $percentage >= 100 ? 'exceeded' : 'warning';
                    $insertStmt->execute([
                        $budget['id'],
                        $userId,
                        $alertType,
                        $percentage,
                        $budget['amount_spent']
                    ]);
                    
                    $alertsSent++;
                }
            }
        }
    }
    
    return $alertsSent;
}

/**
 * Exemplo de uso no dashboard:
 * 
 * require_once __DIR__ . '/budget_alert_helper.php';
 * $alertsSent = checkAndSendBudgetAlerts($pdo, $_SESSION['user_id'], $userEmail, $userUsername);
 */
?>
