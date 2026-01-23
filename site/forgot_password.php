<?php
// site/forgot_password.php - Página para solicitar reset de palavra-passe

session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/EmailService.php';

$errors = [];
$success = false;
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email)) {
        $errors[] = 'Por favor, preenche o campo de email.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email inválido.';
    } else {
        // Procurar utilizador por email
        $stmt = $pdo->prepare('SELECT id, username, email FROM users WHERE email = :e LIMIT 1');
        $stmt->execute([':e' => $email]);
        $user = $stmt->fetch();
        
        if ($user) {
            // Gerar token de reset
            $resetToken = bin2hex(random_bytes(32));
            $tokenExpiry = date('Y-m-d H:i:s', strtotime('+1 hour')); // 1 hora de validade
            
            // Guardar token na BD
            $update = $pdo->prepare('
                UPDATE users 
                SET password_reset_token = :token, password_reset_expires = :expires 
                WHERE id = :id
            ');
            $update->execute([
                ':token' => $resetToken,
                ':expires' => $tokenExpiry,
                ':id' => $user['id']
            ]);
            
            // Enviar email
            $emailService = new EmailService();
            if ($emailService->sendPasswordResetEmail($user['email'], $user['username'], $resetToken)) {
                $success = true;
                $message = 'Email de recuperação enviado com sucesso! Verifica a tua caixa de entrada.';
            } else {
                $errors[] = 'Erro ao enviar email. Tenta novamente.';
            }
        } else {
            // Por segurança, não revelar se o email existe ou não
            $success = true;
            $message = 'Se o email existir na nossa base de dados, receberá um link de recuperação.';
        }
    }
}
?>
<!doctype html>
<html lang="pt-PT">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Recuperar Palavra-passe - FreeCard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <style>
    :root {
      --primary-blue: #3498db;
      --dark-blue: #2980b9;
      --darker-blue: #1f4e79;
      --light-blue: #a8d0e6;
      --very-light-blue: #d5e6f4;
      --light-bg: #f8f9fa;
    }
    
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    body {
      background: linear-gradient(135deg, var(--dark-blue) 0%, var(--darker-blue) 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      padding: 20px;
      position: relative;
      overflow-x: hidden;
    }
    
    /* Animated background shapes */
    body::before {
      content: '';
      position: absolute;
      width: 500px;
      height: 500px;
      background: rgba(255, 255, 255, 0.08);
      border-radius: 50%;
      top: -250px;
      right: -100px;
      animation: float 8s ease-in-out infinite;
      z-index: 0;
    }
    
    body::after {
      content: '';
      position: absolute;
      width: 300px;
      height: 300px;
      background: rgba(255, 255, 255, 0.06);
      border-radius: 50%;
      bottom: -150px;
      left: -50px;
      animation: float 10s ease-in-out infinite reverse;
      z-index: 0;
    }
    
    @keyframes float {
      0%, 100% { transform: translateY(0) rotate(0deg); }
      50% { transform: translateY(-30px) rotate(10deg); }
    }
    
    .login-container {
      background: white;
      border-radius: 20px;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
      overflow: hidden;
      max-width: 480px;
      width: 100%;
      animation: slideUp 0.6s ease-out;
      position: relative;
      z-index: 10;
      backdrop-filter: blur(10px);
    }
    
    @keyframes slideUp {
      from {
        opacity: 0;
        transform: translateY(40px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    .login-header {
      background: linear-gradient(135deg, var(--primary-blue) 0%, var(--dark-blue) 100%);
      color: white;
      padding: 50px 30px;
      text-align: center;
      position: relative;
      overflow: hidden;
    }
    
    .login-header::before {
      content: '';
      position: absolute;
      width: 200px;
      height: 200px;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 50%;
      top: -50px;
      right: -50px;
      animation: pulse 3s ease-in-out infinite;
    }
    
    @keyframes pulse {
      0%, 100% { transform: scale(1); opacity: 0.1; }
      50% { transform: scale(1.2); opacity: 0.15; }
    }
    
    .login-header h1 {
      font-size: 32px;
      margin: 0 0 12px;
      font-weight: 900;
      position: relative;
      z-index: 2;
      text-shadow: 0 2px 15px rgba(0, 0, 0, 0.35);
      color: #FFD700;
      letter-spacing: 0.5px;
    }
    
    .login-header p {
      margin: 0;
      font-size: 15px;
      position: relative;
      z-index: 2;
      font-weight: 600;
      color: #FFEA00;
      text-shadow: 0 1px 10px rgba(0, 0, 0, 0.3);
      letter-spacing: 0.3px;
    }
    
    
    .login-body {
      padding: 45px;
    }
    
    .form-floating > label {
      color: var(--dark-blue);
      font-weight: 600;
      font-size: 15px;
    }
    
    .form-floating > .form-control:focus ~ label,
    .form-floating > .form-control:not(:placeholder-shown) ~ label {
      color: var(--primary-blue);
      font-weight: 600;
    }
    
    .form-control {
      border: 2px solid #e0e0e0;
      border-radius: 12px;
      padding: 14px 16px;
      font-size: 15px;
      transition: all 0.3s ease;
      background: #fafafa;
    }
    
    .form-control:focus {
      border-color: var(--primary-blue);
      background: white;
      box-shadow: 0 0 0 0.3rem rgba(52, 152, 219, 0.15);
    }
    
    .btn-login {
      background: linear-gradient(135deg, var(--primary-blue) 0%, var(--dark-blue) 100%);
      border: none;
      padding: 14px 30px;
      font-weight: 700;
      border-radius: 12px;
      transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
      margin-top: 10px;
      box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
      text-transform: uppercase;
      font-size: 14px;
      letter-spacing: 0.5px;
    }
    
    .btn-login:hover {
      transform: translateY(-3px);
      box-shadow: 0 10px 25px rgba(52, 152, 219, 0.4);
      color: white;
    }
    
    .btn-login:active {
      transform: translateY(-1px);
    }
    
    .form-links {
      text-align: center;
      margin-top: 25px;
      font-size: 14px;
    }
    
    .form-links p {
      margin: 8px 0;
      color: #666;
    }
    
    .form-links a {
      color: var(--primary-blue);
      text-decoration: none;
      font-weight: 600;
      transition: all 0.3s ease;
      position: relative;
    }
    
    .form-links a::after {
      content: '';
      position: absolute;
      width: 0;
      height: 2px;
      bottom: -3px;
      left: 0;
      background: var(--primary-blue);
      transition: width 0.3s ease;
    }
    
    .form-links a:hover::after {
      width: 100%;
    }
    
    .alert {
      border-radius: 12px;
      margin-bottom: 20px;
      border: none;
      animation: slideDown 0.4s ease-out;
      font-weight: 500;
    }
    
    @keyframes slideDown {
      from {
        opacity: 0;
        transform: translateY(-10px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    .alert-danger {
      background-color: #ffebee;
      color: #d32f2f;
      border-left: 4px solid #d32f2f;
    }
    
    .alert-success {
      background-color: #e8f5e9;
      color: #2e7d32;
      border-left: 4px solid #2e7d32;
    }
    
    .alert i {
      margin-right: 10px;
      font-weight: bold;
      font-size: 16px;
    }
  </style>
</head>
<body>
  <div class="login-container">
    <div class="login-header">
      <h1>🔑 Recuperar Palavra-passe</h1>
      <p>Repõe o acesso à tua conta</p>
    </div>
    
    <div class="login-body">
      <?php if (!empty($errors)): ?>
        <?php foreach ($errors as $error): ?>
          <div class="alert alert-danger" role="alert" style="color: #d32f2f; font-weight: 700; font-size: 15px;">
            <i class="bi bi-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
      
      <?php if ($success): ?>
        <div class="alert alert-success" role="alert" style="color: #2e7d32; font-weight: 700; font-size: 15px;">
          <i class="bi bi-check-circle"></i> <?php echo htmlspecialchars($message); ?>
        </div>
        <div style="margin-top: 20px; text-align: center;">
          <p style="margin-bottom: 15px; color: #666;">Não recebeste o email? Tenta novamente.</p>
        </div>
      <?php endif; ?>
      
      <form method="POST" action="">
        <div class="form-floating mb-3">
          <input 
            type="email" 
            class="form-control" 
            id="email" 
            name="email" 
            placeholder="nome@example.com"
            required
            value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
          >
          <label for="email">📧 Email</label>
        </div>
        
        <button type="submit" class="btn btn-primary w-100 btn-login">
          <i class="bi bi-envelope"></i> Enviar Link de Recuperação
        </button>
      </form>
      
      <div class="form-links">
        <p>
          Lembras-te da palavra-passe? <a href="login.php">Entra aqui</a>
        </p>
        <p>
          Não tens conta? <a href="register.php">Regista-te</a>
        </p>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
