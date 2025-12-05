<?php
// site/edit_card.php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/db.php';
$uid = $_SESSION['user_id'] ?? null;
require_once __DIR__ . '/theme_helper.php';
$currentTheme = getUserTheme($pdo, $uid);

$errors = [];
$success = false;

// Obter ID do cartão
$card_id = !empty($_GET['id']) ? intval($_GET['id']) : null;

if (!$card_id) {
    header('Location: cards.php');
    exit;
}

// Buscar o cartão
$stmt = $pdo->prepare("SELECT * FROM cards WHERE id = :id AND user_id = :uid");
$stmt->execute([':id' => $card_id, ':uid' => $uid]);
$card = $stmt->fetch();

if (!$card) {
    header('Location: cards.php');
    exit;
}

// Cores disponíveis para os cartões
$cardColors = [
    'purple' => ['name' => 'Roxo', 'gradient' => 'linear-gradient(135deg, #667eea 0%, #667eea 100%)'],
    'blue' => ['name' => 'Azul', 'gradient' => 'linear-gradient(135deg, #2196F3 0%, #2196F3 100%)'],
    'green' => ['name' => 'Verde', 'gradient' => 'linear-gradient(135deg, #13d168ff 0%, #13d168ff 100%)'],
    'orange' => ['name' => 'Laranja', 'gradient' => 'linear-gradient(135deg, #FF9800 0%, #FF9800 100%)'],
    'red' => ['name' => 'Vermelho', 'gradient' => 'linear-gradient(135deg, #f44336 0%, #f44336 100%)'],
    'pink' => ['name' => 'Rosa', 'gradient' => 'linear-gradient(135deg, #E91E63 0%, #E91E63 100%)'],
    'teal' => ['name' => 'Turquesa', 'gradient' => 'linear-gradient(135deg, #00BCD4 0%, #00BCD4 100%)'],
    'indigo' => ['name' => 'Índigo', 'gradient' => 'linear-gradient(135deg, #3F51B5 0%, #3F51B5 100%)']
];

// Bancos disponíveis
$banks = [
    'none' => 'Sem Banco',
    'cgd' => 'Caixa Geral de Depósitos',
    'millennium' => 'Millennium BCP',
    'santander' => 'Santander',
    'novobanco' => 'Novo Banco',
    'activobank' => 'ActivoBank',
    'montepio' => 'Montepio',
    'bankinter' => 'Bankinter',
    'moey' => 'Moey!'
];

// Logos dos bancos
$bankLogos = [
    'cgd' => 'https://www.cgd.pt/Institucional/Marca-CGD/PublishingImages/Logotipo/RGB_H_Logo-CGD-2021.png',
    'millennium' => 'https://www.millenniumbcp.pt/img/logo_millennium_bcp.svg',
    'santander' => 'https://www.santander.pt/sites/all/themes/santander_theme/images/logo.png',
    'novobanco' => 'https://www.novobanco.pt/site/cms.aspx?plg=b4e3fa9b-5bfe-4aaf-b8df-fdb6e9d7e0cf',
    'activobank' => 'https://www.activobank.pt/pt/img/logo.svg',
    'montepio' => 'https://www.montepio.org/SiteCollectionImages/logo-montepio.png',
    'bankinter' => 'https://www.bankinter.pt/img/logo-bankinter.svg',
    'moey' => 'https://moey.pt/assets/img/logo.svg'
];

// Buscar total de transações associadas ao cartão
$stmt = $pdo->prepare("
    SELECT COUNT(*) as count, COALESCE(SUM(amount), 0) as total
    FROM transactions 
    WHERE card_id = :id
");
$stmt->execute([':id' => $card_id]);
$cardStats = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $limit = floatval($_POST['limit_amount'] ?? 0);
    $balance = floatval($_POST['balance'] ?? 0);
    $color = $_POST['color'] ?? 'purple';
    $bank = $_POST['bank'] ?? 'none';
    $active = isset($_POST['active']) ? 1 : 0;

    // Validações
    if (strlen($name) < 3) {
        $errors[] = 'O nome do cartão deve ter pelo menos 3 caracteres.';
    }
    if ($limit < 0) {
        $errors[] = 'O limite não pode ser negativo.';
    }
    if ($balance < 0) {
        $errors[] = 'O saldo não pode ser negativo.';
    }
    if ($balance > $limit) {
        $errors[] = 'O saldo não pode ser superior ao limite.';
    }
    if (!array_key_exists($color, $cardColors)) {
        $color = 'purple';
    }
    if (!array_key_exists($bank, $banks)) {
        $bank = 'none';
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                UPDATE cards
                SET name = :name, limit_amount = :limit,
                    balance = :balance, color = :color, bank = :bank, active = :active
                WHERE id = :id AND user_id = :uid
            ");
            $stmt->execute([
                ':name' => $name,
                ':limit' => $limit,
                ':balance' => $balance,
                ':color' => $color,
                ':bank' => $bank,
                ':active' => $active,
                ':id' => $card_id,
                ':uid' => $uid
            ]);

            $success = true;
            header('Location: cards.php?success=card_updated');
            exit;
        } catch (PDOException $e) {
            $errors[] = 'Erro ao atualizar cartão. Tenta novamente.';
        }
    }
}

$currentBank = $card['bank'] ?? 'none';
?>
<!doctype html>
<html lang="pt-PT" data-theme="<?=$currentTheme?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Editar Cartão - Freecard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <link rel="stylesheet" href="css/theme.css">
<style>
    :root {
      --primary-green: #2ecc71;
      --dark-green: #27ae60;
    }
    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      background-color: var(--bg-primary);
      color: var(--text-primary);
    }
    .navbar { 
      box-shadow: 0 2px 10px rgba(0,0,0,0.05); 
      background: var(--navbar-bg);
    }
    .navbar-brand img { height: 35px; margin-right: 8px; }
    .btn-primary { 
      background: var(--primary-green); 
      border-color: var(--primary-green); 
    }
    .btn-primary:hover { 
      background: var(--dark-green); 
      border-color: var(--dark-green); 
      transform: translateY(-2px);
      box-shadow: 0 4px 15px rgba(46, 204, 113, 0.3);
    }
    .card {
      border: none;
      border-radius: 16px;
      box-shadow: 0 4px 20px var(--shadow);
      background: var(--bg-secondary);
      color: var(--text-primary);
    }
    .card-header {
      background: linear-gradient(135deg, var(--primary-green), var(--dark-green));
      border-radius: 16px 16px 0 0 !important;
      padding: 24px;
    }
    .form-label {
      font-weight: 600;
      color: var(--text-primary);
      margin-bottom: 8px;
    }
    .form-control, .form-select {
      border: 2px solid var(--border-color);
      border-radius: 10px;
      padding: 12px 16px;
      transition: all 0.3s;
      background: var(--bg-primary);
      color: var(--text-primary);
    }
    .form-control:focus, .form-select:focus {
      border-color: var(--primary-green);
      box-shadow: 0 0 0 3px rgba(46, 204, 113, 0.1);
      background: var(--bg-primary);
      color: var(--text-primary);
    }
    .card-preview {
      border-radius: 16px;
      padding: 24px;
      color: white;
      min-height: 180px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      box-shadow: 0 8px 25px rgba(0,0,0,0.15);
      transition: all 0.3s;
    }
    .card-preview .card-number {
      font-size: 24px;
      letter-spacing: 4px;
      font-weight: 600;
    }
    .card-preview .card-name {
      font-size: 16px;
      text-transform: uppercase;
      font-weight: 600;
    }
    .progress-custom {
      height: 8px;
      border-radius: 10px;
      background: var(--bg-hover);
    }
    .progress-bar-custom {
      background: var(--primary-green);
      border-radius: 10px;
    }
    
    .color-selector {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 12px;
      margin-top: 12px;
    }
    .color-option {
      aspect-ratio: 1;
      border-radius: 12px;
      border: 3px solid transparent;
      cursor: pointer;
      transition: all 0.3s;
      position: relative;
      overflow: hidden;
    }
    .color-option:hover {
      transform: scale(1.05);
      box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    }
    .color-option input[type="radio"] {
      display: none;
    }
    .color-option input[type="radio"]:checked + .color-display {
      border-color: var(--primary-green);
      box-shadow: 0 0 0 3px rgba(46, 204, 113, 0.3);
    }
    .color-display {
      width: 100%;
      height: 100%;
      border-radius: 8px;
      border: 3px solid transparent;
      transition: all 0.3s;
    }
    
    /* Seletor de bancos */
    .bank-selector {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 12px;
      margin-top: 12px;
    }
    .bank-option {
      padding: 12px;
      border: 2px solid var(--border-color);
      border-radius: 10px;
      cursor: pointer;
      transition: all 0.3s;
      background: var(--bg-primary);
      text-align: center;
    }
    .bank-option:hover {
      border-color: var(--primary-green);
      transform: translateY(-2px);
    }
    .bank-option input[type="radio"] {
      display: none;
    }
    .bank-option input[type="radio"]:checked + .bank-label {
      color: var(--primary-green);
      font-weight: 600;
    }
    .bank-option input[type="radio"]:checked ~ .check-icon {
      opacity: 1;
    }
    .bank-label {
      font-size: 13px;
      transition: all 0.3s;
      color: var(--text-primary);
    }
    .check-icon {
      opacity: 0;
      color: var(--primary-green);
      font-size: 18px;
      margin-top: 4px;
      transition: all 0.3s;
    }
    
    /* Logo do banco no cartão */
    .bank-logo-card {
      width: 40px;
      height: 40px;
      object-fit: contain;
      background: white;
      border-radius: 8px;
      padding: 4px;
      display: none;
    }
    .bank-logo-card.active {
      display: block;
    }
    
    .info-box {
      background: var(--bg-primary);
      border-left: 4px solid #3498db;
      padding: 16px;
      border-radius: 8px;
    }
    .form-check-input:checked {
      background-color: var(--primary-green);
      border-color: var(--primary-green);
    }
    
    /* Tema escuro */
    [data-theme="dark"] .text-muted {
      color: var(--text-secondary) !important;
    }
    [data-theme="dark"] .form-control::placeholder {
      color: var(--text-secondary);
      opacity: 0.7;
    }
    [data-theme="dark"] .bg-light {
      background: var(--bg-hover) !important;
      color: var(--text-primary);
    }
  </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light">
  <div class="container">
    <a class="navbar-brand fw-bold" href="index.php">
      <img src="assets/logo2.png" alt="Freecard">
      FreeCard
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
        <li class="nav-item"><a class="nav-link active" href="cards.php"><i class="bi bi-wallet2"></i> Cartões</a></li>
        <li class="nav-item"><a class="nav-link" href="transactions.php"><i class="bi bi-receipt"></i> Transações</a></li>
        <li class="nav-item"><a class="nav-link" href="analytics.php"><i class="bi bi-graph-up"></i> Análise</a></li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
            <i class="bi bi-person-circle"></i> <?=htmlspecialchars($_SESSION['username'])?>
          </a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="settings.php"><i class="bi bi-gear"></i> Configurações</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right"></i> Sair</a></li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>

<div class="container mt-5 mb-5">
  <div class="row justify-content-center">
    <div class="col-12 col-lg-10">
      <div class="mb-4">
        <a href="cards.php" class="text-decoration-none text-muted">
          <i class="bi bi-arrow-left"></i> Voltar aos Cartões
        </a>
      </div>

      <div class="row g-4">
        <div class="col-lg-5">
          <div class="card">
            <div class="card-body p-4">
              <h5 class="mb-4"><i class="bi bi-eye"></i> Pré-visualização</h5>
              <div class="card-preview" id="cardPreview" style="background: <?=$cardColors[$card['color']]['gradient']?>;">
                <div>
                  <div class="mb-3">
                    <i class="bi bi-credit-card" id="cardIcon" style="font-size: 32px; <?=$currentBank !== 'none' ? 'display:none;' : ''?>"></i>
                    <?php foreach($bankLogos as $bankKey => $logoUrl): ?>
                      <img src="<?=$logoUrl?>" alt="<?=$bankKey?>" class="bank-logo-card <?=$currentBank === $bankKey ? 'active' : ''?>" id="logo-<?=$bankKey?>" data-bank="<?=$bankKey?>">
                    <?php endforeach; ?>
                  </div>
                  <div class="card-number" id="preview-number">•••• •••• •••• ••••</div>
                </div>
                <div>
                  <div class="card-name" id="preview-name"><?=strtoupper(htmlspecialchars($card['name']))?></div>
                  <div class="d-flex justify-content-between align-items-center mt-2">
                    <small>Limite: <span id="preview-limit">€<?=number_format($card['limit_amount'], 2)?></span></small>
                    <small>Saldo: <span id="preview-balance">€<?=number_format($card['balance'], 2)?></span></small>
                  </div>
                </div>
              </div>
              
              <div class="mt-4">
                <h6 class="mb-3">Utilização do Limite</h6>
                <div class="progress-custom">
                  <div class="progress-bar-custom" id="usage-bar" style="width: <?=min(($card['balance'] / max($card['limit_amount'], 1)) * 100, 100)?>%"></div>
                </div>
                <div class="d-flex justify-content-between mt-2">
                  <small class="text-muted">0%</small>
                  <small class="text-muted" id="usage-percent"><?=round(($card['balance'] / max($card['limit_amount'], 1)) * 100)?>% usado</small>
                </div>
              </div>

              <?php if ($cardStats['count'] > 0): ?>
              <div class="mt-4">
                <h6 class="mb-3">Estatísticas</h6>
                <div class="p-3 bg-light rounded">
                  <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Transações associadas</span>
                    <strong><?=$cardStats['count']?></strong>
                  </div>
                  <div class="d-flex justify-content-between">
                    <span class="text-muted">Total gasto</span>
                    <strong class="text-danger">€<?=number_format($cardStats['total'], 2)?></strong>
                  </div>
                </div>
              </div>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <div class="col-lg-7">
          <div class="card">
            <div class="card-header text-white">
              <h4 class="mb-0"><i class="bi bi-pencil-square"></i> Editar Cartão</h4>
            </div>
            <div class="card-body p-4">
              <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                  <strong><i class="bi bi-exclamation-circle"></i> Erros:</strong>
                  <ul class="mb-0 mt-2">
                    <?php foreach($errors as $e): ?>
                      <li><?=htmlspecialchars($e)?></li>
                    <?php endforeach; ?>
                  </ul>
                </div>
              <?php endif; ?>

              <div class="info-box mb-4">
                <div class="d-flex align-items-center gap-2">
                  <i class="bi bi-info-circle text-primary"></i>
                  <div>
                    <strong>Data de criação:</strong> <?=date('d/m/Y H:i', strtotime($card['created_at']))?>
                    <br>
                    <small class="text-muted">Esta informação não será alterada</small>
                  </div>
                </div>
              </div>

              <form method="post" id="cardForm">
                <div class="mb-3">
                  <label class="form-label">Nome do Cartão *</label>
                  <input 
                    type="text" 
                    name="name" 
                    id="cardName"
                    class="form-control" 
                    placeholder="ex: Visa Principal, Mastercard Viagens"
                    value="<?=htmlspecialchars($card['name'])?>" 
                    required
                  >
                  <small class="text-muted">Dá um nome descritivo ao teu cartão</small>
                </div>

                <div class="mb-3">
                  <label class="form-label">Banco</label>
                  <div class="bank-selector">
                    <?php foreach($banks as $bankKey => $bankName): ?>
                      <label class="bank-option">
                        <input 
                          type="radio" 
                          name="bank" 
                          value="<?=$bankKey?>" 
                          <?=$currentBank === $bankKey ? 'checked' : ''?>
                          data-bank="<?=$bankKey?>"
                        >
                        <div class="bank-label"><?=$bankName?></div>
                        <div class="check-icon"><i class="bi bi-check-circle-fill"></i></div>
                      </label>
                    <?php endforeach; ?>
                  </div>
                  <small class="text-muted mt-2 d-block">Seleciona o banco do cartão (opcional)</small>
                </div>

                <div class="mb-3">
                  <label class="form-label">Cor do Cartão *</label>
                  <div class="color-selector">
                    <?php foreach($cardColors as $colorKey => $colorData): ?>
                      <label class="color-option">
                        <input 
                          type="radio" 
                          name="color" 
                          value="<?=$colorKey?>" 
                          <?=$card['color'] === $colorKey ? 'checked' : ''?>
                          data-gradient="<?=$colorData['gradient']?>"
                        >
                        <div class="color-display" style="background: <?=$colorData['gradient']?>;"></div>
                      </label>
                    <?php endforeach; ?>
                  </div>
                  <small class="text-muted mt-2 d-block">Escolhe uma cor para identificar facilmente o teu cartão</small>
                </div>

                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label class="form-label">Limite do Cartão (€) *</label>
                    <input 
                      type="number" 
                      name="limit_amount" 
                      id="cardLimit"
                      class="form-control" 
                      placeholder="1500.00"
                      step="0.01"
                      min="0"
                      value="<?=htmlspecialchars($card['limit_amount'])?>" 
                      required
                    >
                    <small class="text-muted">O limite máximo de gastos</small>
                  </div>

                  <div class="col-md-6 mb-3">
                    <label class="form-label">Saldo Atual (€)</label>
                    <input 
                      type="number" 
                      name="balance" 
                      id="cardBalance"
                      class="form-control" 
                      placeholder="0.00"
                      step="0.01"
                      min="0"
                      value="<?=htmlspecialchars($card['balance'])?>"
                    >
                    <small class="text-muted">O gasto acumulado atual</small>
                  </div>
                </div>

                <div class="mb-4">
                  <div class="form-check form-switch">
                    <input 
                      class="form-check-input" 
                      type="checkbox" 
                      name="active" 
                      id="cardActive"
                      <?=$card['active'] ? 'checked' : ''?>
                    >
                    <label class="form-check-label" for="cardActive">
                      <strong>Cartão ativo</strong>
                      <br>
                      <small class="text-muted">Cartões inativos não aparecem nas opções de transação</small>
                    </label>
                  </div>
                </div>

                <div class="d-grid gap-2 mt-4">
                  <button type="submit" class="btn btn-primary btn-lg">
                    <i class="bi bi-check-circle"></i> Guardar Alterações
                  </button>
                  <a href="cards.php" class="btn btn-outline-secondary">
                    Cancelar
                  </a>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Live preview
document.getElementById('cardName').addEventListener('input', function(e) {
  const name = e.target.value || 'NOME DO CARTÃO';
  document.getElementById('preview-name').textContent = name.toUpperCase();
});

// Mudança de banco
document.querySelectorAll('input[name="bank"]').forEach(radio => {
  radio.addEventListener('change', function() {
    const bank = this.dataset.bank;
    const cardIcon = document.getElementById('cardIcon');
    const allLogos = document.querySelectorAll('.bank-logo-card');
    
    // Esconder todos os logos
    allLogos.forEach(logo => logo.classList.remove('active'));
    
    if (bank === 'none') {
      // Mostrar ícone do cartão
      cardIcon.style.display = 'block';
    } else {
      // Esconder ícone e mostrar logo do banco
      cardIcon.style.display = 'none';
      const selectedLogo = document.getElementById('logo-' + bank);
      if (selectedLogo) {
        selectedLogo.classList.add('active');
      }
    }
  });
});

document.getElementById('cardLimit').addEventListener('input', updateUsage);
document.getElementById('cardBalance').addEventListener('input', updateUsage);

// Mudança de cor
document.querySelectorAll('input[name="color"]').forEach(radio => {
  radio.addEventListener('change', function() {
    const gradient = this.dataset.gradient;
    document.getElementById('cardPreview').style.background = gradient;
  });
});

function updateUsage() {
  const limit = parseFloat(document.getElementById('cardLimit').value) || 0;
  const balance = parseFloat(document.getElementById('cardBalance').value) || 0;
  
  document.getElementById('preview-limit').textContent = `€${limit.toFixed(2)}`;
  document.getElementById('preview-balance').textContent = `€${balance.toFixed(2)}`;
  
  if (limit > 0) {
    const percent = Math.min((balance / limit) * 100, 100);
    document.getElementById('usage-bar').style.width = percent + '%';
    document.getElementById('usage-percent').textContent = Math.round(percent) + '% usado';
    
    // Mudar cor baseado no uso
    const bar = document.getElementById('usage-bar');
    if (percent >= 80) {
      bar.style.background = '#e74c3c';
    } else if (percent >= 60) {
      bar.style.background = '#f39c12';
    } else {
      bar.style.background = '#2ecc71';
    }
  }
}
</script>
</body>
</html>