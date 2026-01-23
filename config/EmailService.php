<?php
// config/EmailService.php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailService {
    private $mail;
    private $fromEmail = 'freecardssuporte@gmail.com';
    private $appPassword = 'aanu cxbs zehl bddy'; 
    
    public function __construct() {
        $this->mail = new PHPMailer(true);
        
        // Configuração SMTP
        $this->mail->isSMTP();
        $this->mail->Host       = 'smtp.gmail.com';
        $this->mail->SMTPAuth   = true;
        $this->mail->Username   = $this->fromEmail;
        $this->mail->Password   = $this->appPassword;
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mail->Port       = 587;
        $this->mail->CharSet    = 'UTF-8';
        
        // Debug SMTP (descomenta para ver erros detalhados):
        // $this->mail->SMTPDebug = 2;
        
        $this->mail->setFrom($this->fromEmail, 'FreeCard - Suporte');
    }
    
    /**
     * Enviar email de verificação de conta
     */
    public function sendVerificationEmail($email, $username, $token) {
        try {
            $this->mail->addAddress($email);
            $this->mail->isHTML(true);
            $this->mail->Subject = '🔐 Confirma a tua conta FreeCard';
            
            // Gerar link de verificação (adapta o URL conforme necessário)
            // Usar IP se estiver em rede local, localhost para testes locais
            $host = $_SERVER['HTTP_HOST'] ?? '192.168.56.1';
            $verificationLink = "http://{$host}/PAP/FreeCard/site/verify.php?token=" . urlencode($token);
            
            $this->mail->Body = $this->getVerificationTemplate($username, $verificationLink);
            $this->mail->AltBody = "Olá {$username}!\n\nClica no link para verificar a tua conta:\n{$verificationLink}\n\nEste link expira em 24 horas.";
            
            $this->mail->send();
            
            // Limpar destinatários para o próximo email
            $this->mail->clearAddresses();
            
            return true;
        } catch (Exception $e) {
            error_log("Erro ao enviar email de verificação: " . $this->mail->ErrorInfo);
            return false;
        }
    }
    
    /**
     * Enviar email de boas-vindas
     */
    public function sendWelcomeEmail($email, $username) {
        try {
            $this->mail->addAddress($email);
            $this->mail->isHTML(true);
            $this->mail->Subject = '🎉 Bem-vindo ao FreeCard!';
            
            $this->mail->Body = $this->getWelcomeTemplate($username);
            $this->mail->AltBody = "Bem-vindo ao FreeCard, {$username}!\n\nA tua conta foi verificada com sucesso!";
            
            $this->mail->send();
            
            // Limpar destinatários
            $this->mail->clearAddresses();
            
            return true;
        } catch (Exception $e) {
            error_log("Erro ao enviar email de boas-vindas: " . $this->mail->ErrorInfo);
            return false;
        }
    }
    
    /**
     * Enviar email de recuperação de palavra-passe
     */
    public function sendPasswordResetEmail($email, $username, $token) {
        try {
            $this->mail->addAddress($email);
            $this->mail->isHTML(true);
            $this->mail->Subject = '🔐 Recupera a tua palavra-passe no FreeCard';
            
            // Gerar link de reset
            $host = $_SERVER['HTTP_HOST'] ?? '192.168.56.1';
            $resetLink = "http://{$host}/PAP/FreeCard/site/reset_password.php?token=" . urlencode($token);
            
            $this->mail->Body = $this->getPasswordResetTemplate($username, $resetLink);
            $this->mail->AltBody = "Olá {$username}!\n\nClica no link para recuperar a tua palavra-passe:\n{$resetLink}\n\nEste link expira em 1 hora.\n\nSe não solicitaste isto, ignora este email.";
            
            $this->mail->send();
            
            // Limpar destinatários
            $this->mail->clearAddresses();
            
            return true;
        } catch (Exception $e) {
            error_log("Erro ao enviar email de reset: " . $this->mail->ErrorInfo);
            return false;
        }
    }
    
    /**
     * Template HTML para email de verificação
     */
    private function getVerificationTemplate($username, $verificationLink) {
        return <<<HTML
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            min-height: 100vh;
        }
        
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
            padding: 40px 20px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .content {
            padding: 40px 20px;
        }
        
        .greeting {
            font-size: 18px;
            color: #333;
            margin-bottom: 20px;
        }
        
        .message {
            color: #666;
            line-height: 1.6;
            margin-bottom: 30px;
            font-size: 16px;
        }
        
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
        
        .btn {
            display: inline-block;
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
            padding: 14px 32px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            transition: transform 0.3s, box-shadow 0.3s;
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.4);
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(52, 152, 219, 0.6);
        }
        
        .link-text {
            color: #666;
            font-size: 14px;
            margin-top: 15px;
            word-break: break-all;
        }
        
        .link-text a {
            color: #3498db;
            text-decoration: none;
        }
        
        .warning {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 12px 15px;
            margin: 20px 0;
            border-radius: 4px;
            color: #856404;
            font-size: 14px;
        }
        
        .footer {
            background: #f8f9fa;
            border-top: 1px solid #e9ecef;
            padding: 20px;
            text-align: center;
            color: #666;
            font-size: 13px;
        }
        
        .footer p {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎉 Confirma a tua conta</h1>
            <p>FreeCard - Gestor de Cartões de Crédito</p>
        </div>
        
        <div class="content">
            <div class="greeting">
                Olá <strong>{$username}</strong>! 👋
            </div>
            
            <div class="message">
                Obrigado por te registares no FreeCard! Para ativar a tua conta, clica no botão abaixo:
            </div>
            
            <div class="button-container">
                <a href="{$verificationLink}" class="btn">✓ Verificar a minha conta</a>
            </div>
            
            <div class="link-text">
                Ou copia e cola este link no teu navegador:<br>
                <a href="{$verificationLink}">{$verificationLink}</a>
            </div>
            
            <div class="warning">
                ⏰ Este link é válido durante 24 horas. Se não verificares a tua conta dentro deste prazo, terás de solicitar um novo email.
            </div>
            
            <div class="message" style="margin-top: 30px; font-size: 14px; color: #999;">
                Se não criaste uma conta no FreeCard, ignora este email.
            </div>
        </div>
        
        <div class="footer">
            <p><strong>FreeCard™</strong> - Gestor de Cartões de Crédito</p>
            <p>Email de segurança - Não responda a este email</p>
            <p>&copy; 2026 FreeCard. Todos os direitos reservados.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }
    
    /**
     * Template HTML para email de boas-vindas
     */
    private function getWelcomeTemplate($username) {
        return <<<HTML
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            min-height: 100vh;
        }
        
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
            color: white;
            padding: 40px 20px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .content {
            padding: 40px 20px;
        }
        
        .greeting {
            font-size: 18px;
            color: #333;
            margin-bottom: 20px;
        }
        
        .message {
            color: #666;
            line-height: 1.6;
            margin-bottom: 20px;
            font-size: 16px;
        }
        
        .features {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .feature-item {
            padding: 10px 0;
            border-bottom: 1px solid #e9ecef;
            color: #333;
        }
        
        .feature-item:last-child {
            border-bottom: none;
        }
        
        .feature-icon {
            color: #27ae60;
            font-weight: bold;
            margin-right: 10px;
        }
        
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
        
        .btn {
            display: inline-block;
            background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
            color: white;
            padding: 14px 32px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            transition: transform 0.3s, box-shadow 0.3s;
            box-shadow: 0 4px 15px rgba(46, 204, 113, 0.4);
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(46, 204, 113, 0.6);
        }
        
        .footer {
            background: #f8f9fa;
            border-top: 1px solid #e9ecef;
            padding: 20px;
            text-align: center;
            color: #666;
            font-size: 13px;
        }
        
        .footer p {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎉 Bem-vindo ao FreeCard!</h1>
            <p>A tua jornada começa agora</p>
        </div>
        
        <div class="content">
            <div class="greeting">
                Olá <strong>{$username}</strong>! 👋
            </div>
            
            <div class="message">
                A tua conta foi verificada com sucesso! Estamos muito felizes em te ter connosco.
            </div>
            
            <div class="features">
                <div class="feature-item">
                    <span class="feature-icon">💳</span> <strong>Gestão de Cartões</strong> - Guarda e organiza todos os teus cartões num só lugar seguro
                </div>
                <div class="feature-item">
                    <span class="feature-icon">📊</span> <strong>Orçamentos</strong> - Define limites e controla os teus gastos
                </div>
                <div class="feature-item">
                    <span class="feature-icon">📈</span> <strong>Análises</strong> - Visualiza relatórios detalhados das tuas despesas
                </div>
                <div class="feature-item">
                    <span class="feature-icon">⏰</span> <strong>Lembretes</strong> - Recebe notificações para pagamentos importantes
                </div>
            </div>
            
            <div class="button-container">
                <a href="http://192.168.56.1/PAP/FreeCard/site/dashboard.php" class="btn">→ Ir para o Dashboard</a>
            </div>
            
            <div class="message">
                Se tiveres dúvidas ou precisares de ajuda, não hesites em contactar o nosso suporte.
            </div>
        </div>
        
        <div class="footer">
            <p><strong>FreeCard™</strong> - Gestor de Cartões de Crédito</p>
            <p>&copy; 2026 FreeCard. Todos os direitos reservados.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }
    
    /**
     * Enviar lembrete de pagamento
     */
    public function sendPaymentReminder($email, $username, $reminderData) {
        try {
            $this->mail->addAddress($email);
            $this->mail->isHTML(true);
            $this->mail->Subject = "⏰ Lembrete: {$reminderData['name']} vence em {$reminderData['days_until']} dias";
            
            $this->mail->Body = $this->getPaymentReminderTemplate($username, $reminderData);
            $this->mail->AltBody = "Olá {$username}!\n\nLembrete: {$reminderData['name']} (€{$reminderData['amount']}) vence em {$reminderData['days_until']} dias, a {$reminderData['due_date']}.";
            
            $this->mail->send();
            
            // Limpar destinatários
            $this->mail->clearAddresses();
            
            return true;
        } catch (Exception $e) {
            error_log("Erro ao enviar lembrete de pagamento: " . $this->mail->ErrorInfo);
            return false;
        }
    }
    
    /**
     * Enviar alerta de orçamento excedido
     */
    public function sendBudgetAlert($email, $username, $budgetData) {
        try {
            $this->mail->addAddress($email);
            $this->mail->isHTML(true);
            
            // Determinar tipo de alerta
            $alertType = $budgetData['percentage'] >= 100 ? 'excedido' : 'aviso';
            $emoji = $budgetData['percentage'] >= 100 ? '🚨' : '⚠️';
            $subject = $budgetData['percentage'] >= 100 
                ? "🚨 Orçamento EXCEDIDO: {$budgetData['name']}"
                : "⚠️ Atenção: {$budgetData['name']} em {$budgetData['percentage']}%";
            
            $this->mail->Subject = $subject;
            $this->mail->Body = $this->getBudgetAlertTemplate($username, $budgetData);
            $this->mail->AltBody = "Olá {$username}!\n\nTeu orçamento '{$budgetData['name']}' está em {$budgetData['percentage']}%. Já gastaste €{$budgetData['amount_spent']} de €{$budgetData['limit']}.";
            
            $this->mail->send();
            
            // Limpar destinatários
            $this->mail->clearAddresses();
            
            return true;
        } catch (Exception $e) {
            error_log("Erro ao enviar alerta de orçamento: " . $this->mail->ErrorInfo);
            return false;
        }
    }

    /**
     * Template HTML para lembrete de pagamento
     */
    private function getPaymentReminderTemplate($username, $reminderData) {
        $emoji = $reminderData['days_until'] <= 1 ? '🔴' : '🟡';
        
        return <<<HTML
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            min-height: 100vh;
        }
        
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
            color: white;
            padding: 40px 20px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .content {
            padding: 40px 20px;
        }
        
        .greeting {
            font-size: 18px;
            color: #333;
            margin-bottom: 20px;
        }
        
        .reminder-card {
            background: linear-gradient(135deg, #fff5e6 0%, #ffe8cc 100%);
            border-left: 5px solid #f39c12;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .reminder-item {
            margin: 12px 0;
            font-size: 16px;
            color: #333;
        }
        
        .reminder-item strong {
            color: #e67e22;
        }
        
        .amount {
            font-size: 24px;
            color: #e67e22;
            font-weight: bold;
            margin: 15px 0;
        }
        
        .days-until {
            font-size: 36px;
            color: #e67e22;
            text-align: center;
            margin: 20px 0;
            font-weight: bold;
        }
        
        .message {
            color: #666;
            line-height: 1.6;
            margin: 20px 0;
            font-size: 16px;
        }
        
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
        
        .btn {
            display: inline-block;
            background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
            color: white;
            padding: 12px 28px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            transition: transform 0.3s, box-shadow 0.3s;
            box-shadow: 0 4px 15px rgba(243, 156, 18, 0.4);
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(243, 156, 18, 0.6);
        }
        
        .footer {
            background: #f8f9fa;
            border-top: 1px solid #e9ecef;
            padding: 20px;
            text-align: center;
            color: #666;
            font-size: 13px;
        }
        
        .footer p {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{$emoji} Lembrete de Pagamento</h1>
            <p>FreeCard - Não deixes passar!</p>
        </div>
        
        <div class="content">
            <div class="greeting">
                Olá <strong>{$username}</strong>! 👋
            </div>
            
            <div class="message">
                Temos um lembrete importante para ti:
            </div>
            
            <div class="reminder-card">
                <div class="reminder-item">
                    <strong>Pagamento:</strong> {$reminderData['name']}
                </div>
                <div class="amount">
                    €{$reminderData['amount']}
                </div>
                <div class="reminder-item">
                    <strong>Data de vencimento:</strong> {$reminderData['due_date']}
                </div>
                <div class="reminder-item">
                    <strong>Categoria:</strong> {$reminderData['category']}
                </div>
                {$this->getPaymentMethodHtml($reminderData)}
            </div>
            
            <div class="days-until">
                {$reminderData['days_until']} dias
            </div>
            
            <div class="message">
                Podes gerir este lembrete no teu dashboard do FreeCard:
            </div>
            
            <div class="button-container">
                <a href="http://192.168.56.1/PAP/FreeCard/site/reminders.php" class="btn">📋 Ver Lembretes</a>
            </div>
        </div>
        
        <div class="footer">
            <p><strong>FreeCard™</strong> - Gestor de Cartões de Crédito</p>
            <p>&copy; 2026 FreeCard. Todos os direitos reservados.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }

    /**
     * Template HTML para alerta de orçamento
     */
    private function getBudgetAlertTemplate($username, $budgetData) {
        $colorClass = $budgetData['percentage'] >= 100 ? '#e74c3c' : '#f39c12';
        $emoji = $budgetData['percentage'] >= 100 ? '🚨' : '⚠️';
        $status = $budgetData['percentage'] >= 100 ? 'EXCEDIDO' : 'EM ALERTA';
        
        return <<<HTML
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            min-height: 100vh;
        }
        
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, {$colorClass} 0%, #c0392b 100%);
            color: white;
            padding: 40px 20px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .content {
            padding: 40px 20px;
        }
        
        .greeting {
            font-size: 18px;
            color: #333;
            margin-bottom: 20px;
        }
        
        .budget-card {
            background: linear-gradient(135deg, #ffe8e8 0%, #ffcccc 100%);
            border-left: 5px solid {$colorClass};
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .budget-item {
            margin: 12px 0;
            font-size: 16px;
            color: #333;
        }
        
        .budget-item strong {
            color: {$colorClass};
        }
        
        .progress-bar {
            width: 100%;
            height: 30px;
            background: #e0e0e0;
            border-radius: 15px;
            overflow: hidden;
            margin: 15px 0;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, {$colorClass} 0%, #c0392b 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 12px;
            width: {$budgetData['percentage']}%;
            min-width: 50px;
        }
        
        .message {
            color: #666;
            line-height: 1.6;
            margin: 20px 0;
            font-size: 16px;
        }
        
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
        
        .btn {
            display: inline-block;
            background: linear-gradient(135deg, {$colorClass} 0%, #c0392b 100%);
            color: white;
            padding: 12px 28px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            transition: transform 0.3s, box-shadow 0.3s;
            box-shadow: 0 4px 15px rgba(231, 76, 60, 0.4);
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(231, 76, 60, 0.6);
        }
        
        .footer {
            background: #f8f9fa;
            border-top: 1px solid #e9ecef;
            padding: 20px;
            text-align: center;
            color: #666;
            font-size: 13px;
        }
        
        .footer p {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{$emoji} Orçamento {$status}</h1>
            <p>FreeCard - Aviso de Controlo de Gastos</p>
        </div>
        
        <div class="content">
            <div class="greeting">
                Olá <strong>{$username}</strong>! 👋
            </div>
            
            <div class="message">
                O teu orçamento <strong>'{$budgetData['name']}'</strong> está em alerta:
            </div>
            
            <div class="budget-card">
                <div class="budget-item">
                    <strong>Orçamento:</strong> {$budgetData['name']}
                </div>
                <div class="budget-item">
                    <strong>Limite:</strong> €{$budgetData['limit']}
                </div>
                <div class="budget-item">
                    <strong>Gasto:</strong> €{$budgetData['amount_spent']}
                </div>
                <div class="budget-item">
                    <strong>Percentagem:</strong> {$budgetData['percentage']}%
                </div>
                
                <div class="progress-bar">
                    <div class="progress-fill" style="width: {$budgetData['percentage']}%;">
                        {$budgetData['percentage']}%
                    </div>
                </div>
            </div>
            
            <div class="message">
                Podes revisar os teus gastos e ajustar o orçamento no dashboard:
            </div>
            
            <div class="button-container">
                <a href="http://192.168.56.1/PAP/FreeCard/site/budgets.php" class="btn">📊 Ver Orçamentos</a>
            </div>
        </div>
        
        <div class="footer">
            <p><strong>FreeCard™</strong> - Gestor de Cartões de Crédito</p>
            <p>&copy; 2026 FreeCard. Todos os direitos reservados.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }

    /**
     * Gerar HTML do método de pagamento
     */
    private function getPaymentMethodHtml($reminderData) {
        if (isset($reminderData['card_name']) && $reminderData['card_name']) {
            return "<div class=\"reminder-item\"><strong>Cartão:</strong> {$reminderData['card_name']}</div>";
        }
        return "<div class=\"reminder-item\"><strong>Método:</strong> Dinheiro</div>";
    }

    /**
     * Template HTML para email de recuperação de palavra-passe
     */
    private function getPasswordResetTemplate($username, $resetLink) {
        return <<<HTML
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            min-height: 100vh;
        }
        
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
            padding: 40px 20px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .content {
            padding: 40px 20px;
        }
        
        .greeting {
            font-size: 18px;
            color: #333;
            margin-bottom: 20px;
        }
        
        .message {
            color: #666;
            line-height: 1.6;
            margin-bottom: 30px;
            font-size: 16px;
        }
        
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
        
        .btn {
            display: inline-block;
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
            padding: 14px 32px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            transition: transform 0.3s, box-shadow 0.3s;
            box-shadow: 0 4px 15px rgba(231, 76, 60, 0.4);
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(231, 76, 60, 0.6);
        }
        
        .link-text {
            color: #666;
            font-size: 14px;
            margin-top: 15px;
            word-break: break-all;
        }
        
        .link-text a {
            color: #e74c3c;
            text-decoration: none;
        }
        
        .warning {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 12px 15px;
            margin: 20px 0;
            border-radius: 4px;
            color: #856404;
            font-size: 14px;
        }
        
        .security-note {
            background: #f0f0f0;
            border-left: 4px solid #3498db;
            padding: 12px 15px;
            margin: 20px 0;
            border-radius: 4px;
            color: #333;
            font-size: 13px;
        }
        
        .footer {
            background: #f8f9fa;
            border-top: 1px solid #e9ecef;
            padding: 20px;
            text-align: center;
            color: #666;
            font-size: 13px;
        }
        
        .footer p {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 Recuperar Palavra-passe</h1>
            <p>FreeCard - Segurança da Conta</p>
        </div>
        
        <div class="content">
            <div class="greeting">
                Olá <strong>{$username}</strong>! 👋
            </div>
            
            <div class="message">
                Recebemos um pedido para recuperar a tua palavra-passe do FreeCard. Clica no botão abaixo para definir uma nova palavra-passe:
            </div>
            
            <div class="button-container">
                <a href="{$resetLink}" class="btn">🔑 Recuperar Palavra-passe</a>
            </div>
            
            <div class="link-text">
                Ou copia e cola este link no teu navegador:<br>
                <a href="{$resetLink}">{$resetLink}</a>
            </div>
            
            <div class="warning">
                ⏰ Este link é válido durante 1 hora apenas. Após esse período, terás de solicitar um novo.
            </div>
            
            <div class="security-note">
                🔒 <strong>Segurança:</strong> Se não solicitaste esta recuperação de palavra-passe, por favor ignora este email. A tua conta está segura.
            </div>
            
            <div class="message" style="margin-top: 30px; font-size: 14px; color: #999;">
                Nunca partilhes este link com ninguém. Os nossos colaboradores nunca te pedirão a tua palavra-passe por email.
            </div>
        </div>
        
        <div class="footer">
            <p><strong>FreeCard™</strong> - Gestor de Cartões de Crédito</p>
            <p>Email de segurança - Não responda a este email</p>
            <p>&copy; 2026 FreeCard. Todos os direitos reservados.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }
}
?>
