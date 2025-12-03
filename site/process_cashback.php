<?php
// site/process_cashback.php
// Script para processar cashback mensal automaticamente
// Pode ser executado via cron job ou chamado manualmente

require_once __DIR__ . '/../config/db.php';

function processCashback($pdo) {
    $processed = 0;
    $errors = [];
    
    try {
        // Buscar todos os cartões com cashback ativo que precisam de processamento
        // (último processamento foi há mais de 1 mês ou nunca foi processado)
        $stmt = $pdo->prepare("
            SELECT id, user_id, name, balance, limit_amount, cashback_percentage, last_cashback_date
            FROM cards 
            WHERE active = 1 
            AND cashback_percentage > 0
            AND (
                last_cashback_date IS NULL 
                OR last_cashback_date < DATE_FORMAT(CURDATE(), '%Y-%m-01')
            )
        ");
        $stmt->execute();
        $cards = $stmt->fetchAll();
        
        foreach ($cards as $card) {
            try {
                $pdo->beginTransaction();
                
                // Calcular o cashback (percentagem do saldo atual)
                $cashbackAmount = ($card['balance'] * $card['cashback_percentage']) / 100;
                
                // Não processar se o cashback for inferior a 0.01€
                if ($cashbackAmount < 0.01) {
                    $pdo->rollBack();
                    continue;
                }
                
                // Devolver ao saldo disponível (reduzir o balance)
                $newBalance = max(0, $card['balance'] - $cashbackAmount);
                
                $stmt = $pdo->prepare("
                    UPDATE cards 
                    SET balance = :newBalance,
                        last_cashback_date = CURDATE()
                    WHERE id = :id
                ");
                $stmt->execute([
                    ':newBalance' => $newBalance,
                    ':id' => $card['id']
                ]);
                
                // Registar a transação de cashback (com valor negativo para indicar crédito)
                $stmt = $pdo->prepare("
                    INSERT INTO transactions (user_id, card_id, amount, description, category, transaction_date, created_at)
                    VALUES (:uid, :cid, :amt, :desc, 'Outros', CURDATE(), NOW())
                ");
                $stmt->execute([
                    ':uid' => $card['user_id'],
                    ':cid' => $card['id'],
                    ':amt' => -$cashbackAmount, // Negativo para indicar crédito
                    ':desc' => "Cashback mensal (" . number_format($card['cashback_percentage'], 2) . "%)"
                ]);
                
                $pdo->commit();
                $processed++;
                
            } catch (Exception $e) {
                $pdo->rollBack();
                $errors[] = "Erro ao processar cartão {$card['name']}: " . $e->getMessage();
            }
        }
        
        return [
            'success' => true,
            'processed' => $processed,
            'errors' => $errors
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'processed' => 0,
            'errors' => ["Erro geral: " . $e->getMessage()]
        ];
    }
}

// Se executado diretamente (não via include)
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    $result = processCashback($pdo);
    
    header('Content-Type: application/json');
    echo json_encode($result);
}