<?php
// site/reset_password.php - Página para redefinir a palavra-passe

session_start();
require_once __DIR__ . '/../../config/db.php';

$errors = [];
$success = false;
$message = '';
$token = $_GET['token'] ?? '';
$user = null;
$status = 'invalid'; // 'invalid', 'expired', 'valid', 'success'

if ($token !== '') {
    // Procurar utilizador com este token
    $stmt = $pdo->prepare('
        SELECT id, username, email, password_reset_expires 
        FROM users 
        WHERE password_reset_token = :token 
        LIMIT 1
    ');
    $stmt->execute([':token' => $token]);
    $user = $stmt->fetch();
    
    if ($user) {
        // Verificar se o token expirou
        if (strtotime($user['password_reset_expires']) < time()) {
            $status = 'expired';
            $message = 'O link de recuperação expirou. Por favor, solicita um novo.';
        } else {
            $status = 'valid';
        }
    } else {
        $status = 'invalid';
        $message = 'Link de recuperação inválido ou não encontrado.';
    }
}

// Processar o formulário de reset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $status === 'valid') {
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';
    
    // Validar palavra-passe
    if (strlen($password) < 8) {
        $errors[] = 'A palavra-passe deve ter pelo menos 8 caracteres.';
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'A palavra-passe deve incluir pelo menos uma maiúscula.';
    } elseif (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'A palavra-passe deve incluir pelo menos uma minúscula.';
    } elseif (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'A palavra-passe deve incluir pelo menos um número.';
    } elseif (!preg_match('/[^A-Za-z0-9]/', $password)) {
        $errors[] = 'A palavra-passe deve incluir pelo menos um caractere especial (!@#$%^&*).';
    } elseif ($password !== $password2) {
        $errors[] = 'As palavras-passe não coincidem.';
    }
    
    if (empty($errors)) {
        // Atualizar palavra-passe na BD
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $update = $pdo->prepare('
            UPDATE users 
            SET password_hash = :hash, password_reset_token = NULL, password_reset_expires = NULL 
            WHERE id = :id
        ');
        $update->execute([
            ':hash' => $hash,
            ':id' => $user['id']
        ]);
        
        $status = 'success';
        $message = 'Palavra-passe alterada com sucesso! Podes agora fazer login com a tua nova palavra-passe.';
        $success = true;
    }
}

function validatePassword($password) {
    if (strlen($password) < 8) return false;
    if (!preg_match('/[A-Z]/', $password)) return false;
    if (!preg_match('/[a-z]/', $password)) return false;
    if (!preg_match('/[0-9]/', $password)) return false;
    if (!preg_match('/[^A-Za-z0-9]/', $password)) return false;
    return true;
}
?>
<!doctype html>
<html lang="pt-PT">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Redefinir Palavra-passe - FreeCard</title>
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
      max-width: 500px;
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
    
    .alert-warning {
      background-color: #fff8e1;
      color: #e65100;
      border-left: 5px solid #ffa000;
      font-weight: 600;
    }
    
    .alert i {
      margin-right: 10px;
      font-weight: bold;
      font-size: 16px;
    }
    
    .password-requirements {
      background: linear-gradient(135deg, rgba(52, 152, 219, 0.08) 0%, rgba(41, 128, 185, 0.06) 100%);
      border: 2px solid rgba(52, 152, 219, 0.2);
      padding: 18px;
      border-radius: 12px;
      margin-bottom: 25px;
      font-size: 14px;
      animation: slideDown 0.5s ease-out;
    }
    
    .password-requirements strong {
      color: var(--dark-blue);
      font-weight: 700;
      display: block;
      margin-bottom: 12px;
    }
    
    .requirement {
      margin: 8px 0;
      color: #666;
      display: flex;
      align-items: center;
      transition: all 0.3s ease;
    }
    
    .requirement i {
      margin-right: 8px;
      color: var(--primary-blue);
      width: 20px;
    }
    
    .requirement.valid {
      color: #27ae60;
    }
    
    .requirement.valid i {
      color: #27ae60;
    }
  </style>
</head>
<body>
  <div class="login-container">
    <div class="login-header">
      <h1>🔐 Nova Palavra-passe</h1>
      <p>Define uma nova palavra-passe segura</p>
    </div>
    
    <div class="login-body">
      <?php if ($status === 'invalid' || $status === 'expired'): ?>
        <div class="alert alert-warning" role="alert" style="color: #e65100; font-weight: 700; font-size: 16px;">
          <i class="bi bi-exclamation-triangle"></i> 
          <?php echo htmlspecialchars($message); ?>
        </div>
        <div style="text-align: center;">
          <p style="margin-bottom: 15px; color: #666;">
            <a href="forgot_password.php" class="btn btn-sm btn-primary">
              <i class="bi bi-arrow-left"></i> Solicitar novo link
            </a>
          </p>
        </div>
      <?php elseif ($status === 'success'): ?>
        <div class="alert alert-success" role="alert" style="color: #2e7d32; font-weight: 700; font-size: 16px;">
          <i class="bi bi-check-circle"></i> 
          <?php echo htmlspecialchars($message); ?>
        </div>
        <div style="text-align: center;">
          <p style="margin-bottom: 15px; color: #666;">
            <a href="login.php" class="btn btn-primary">
              <i class="bi bi-box-arrow-in-right"></i> Ir para Login
            </a>
          </p>
        </div>
      <?php else: ?>
        <!-- Formulário de reset -->
        <?php if (!empty($errors)): ?>
          <?php foreach ($errors as $error): ?>
            <div class="alert alert-danger" role="alert" style="color: #d32f2f; font-weight: 700; font-size: 15px;">
              <i class="bi bi-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
        
        <form method="POST" action="">
          <div class="password-requirements">
            <strong>📋 Requisitos da Palavra-passe:</strong>
            <div class="requirement" data-requirement="length">
              <i class="bi bi-circle"></i> Mínimo 8 caracteres
            </div>
            <div class="requirement" data-requirement="uppercase">
              <i class="bi bi-circle"></i> Pelo menos uma maiúscula (A-Z)
            </div>
            <div class="requirement" data-requirement="lowercase">
              <i class="bi bi-circle"></i> Pelo menos uma minúscula (a-z)
            </div>
            <div class="requirement" data-requirement="number">
              <i class="bi bi-circle"></i> Pelo menos um número (0-9)
            </div>
            <div class="requirement" data-requirement="special">
              <i class="bi bi-circle"></i> Pelo menos um caractere especial (!@#$%^&*)
            </div>
          </div>
          
          <div class="form-floating mb-3">
            <input 
              type="password" 
              class="form-control" 
              id="password" 
              name="password" 
              placeholder="Palavra-passe"
              required
            >
            <label for="password">🔒 Nova Palavra-passe</label>
          </div>
          
          <div class="form-floating mb-3">
            <input 
              type="password" 
              class="form-control" 
              id="password2" 
              name="password2" 
              placeholder="Confirmar Palavra-passe"
              required
            >
            <label for="password2">🔒 Confirmar Palavra-passe</label>
          </div>
          
          <button type="submit" class="btn btn-primary w-100 btn-login">
            <i class="bi bi-check-circle"></i> Guardar Nova Palavra-passe
          </button>
        </form>
        
        <div class="form-links">
          <p>
            Lembras-te da palavra-passe? <a href="login.php">Entra aqui</a>
          </p>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Validação dinâmica de palavra-passe
    const passwordInput = document.getElementById('password');
    const requirements = {
      length: document.querySelector('[data-requirement="length"]'),
      uppercase: document.querySelector('[data-requirement="uppercase"]'),
      lowercase: document.querySelector('[data-requirement="lowercase"]'),
      number: document.querySelector('[data-requirement="number"]'),
      special: document.querySelector('[data-requirement="special"]')
    };

    if (passwordInput) {
      passwordInput.addEventListener('input', function() {
        const pwd = this.value;
        
        // Validar cada requisito
        updateRequirement(requirements.length, pwd.length >= 8);
        updateRequirement(requirements.uppercase, /[A-Z]/.test(pwd));
        updateRequirement(requirements.lowercase, /[a-z]/.test(pwd));
        updateRequirement(requirements.number, /[0-9]/.test(pwd));
        updateRequirement(requirements.special, /[^A-Za-z0-9]/.test(pwd));
      });
    }

    function updateRequirement(element, isValid) {
      if (!element) return;
      
      if (isValid) {
        element.classList.add('valid');
        element.innerHTML = '<i class="bi bi-check-circle-fill"></i> Requisito preenchido';
      } else {
        element.classList.remove('valid');
        element.innerHTML = '<i class="bi bi-circle"></i> Requisito não preenchido';
      }
    }
  </script>
</body>
</html>
