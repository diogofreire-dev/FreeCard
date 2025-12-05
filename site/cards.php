<?php
// site/cards.php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/db.php';
$uid = $_SESSION['user_id'] ?? null;
require_once __DIR__ . '/theme_helper.php';
$currentTheme = getUserTheme($pdo, $uid);

$message = '';
$messageType = 'info';

// Mapeamento de cores
$cardColors = [
    'purple' => 'linear-gradient(135deg, #667eea 0%, #667eea 100%)',
    'blue' => 'linear-gradient(135deg, #2196F3 0%, #2196F3 100%)',
    'green' => 'linear-gradient(135deg, #13d168ff 0%, #13d168ff 100%)',
    'orange' => 'linear-gradient(135deg, #FF9800 0%, #FF9800 100%)',
    'red' => 'linear-gradient(135deg, #f44336 0%, #f44336 100%)',
    'pink' => 'linear-gradient(135deg, #E91E63 0%, #E91E63 100%)',
    'teal' => 'linear-gradient(135deg, #00BCD4 0%, #00BCD4 100%)',
    'indigo' => 'linear-gradient(135deg, #3F51B5 0%, #3F51B5 100%)'
];

// Mapeamento de logos de bancos
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

// Ação: ativar/desativar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['action']) && $_POST['action'] === 'toggle') {
    $cardId = intval($_POST['card_id'] ?? 0);

    try {
        $stmt = $pdo->prepare("UPDATE cards SET active = NOT active WHERE id = :id AND user_id = :uid");
        $stmt->execute([':id' => $cardId, ':uid' => $uid]);
        $message = 'Estado do cartão alterado com sucesso!';
        $messageType = 'success';
    } catch (PDOException $e) {
        $message = 'Erro ao executar a ação.';
        $messageType = 'danger';
    }
}

// Buscar todos os cartões com estatísticas
$stmt = $pdo->prepare("
    SELECT c.id, c.name, c.color, c.bank, c.limit_amount, c.balance, c.active, c.created_at,
           COALESCE(SUM(t.amount), 0) as total_spent,
           COUNT(t.id) as transaction_count
    FROM cards c
    LEFT JOIN transactions t ON t.card_id = c.id
    WHERE c.user_id = :uid
    GROUP BY c.id
    ORDER BY c.active DESC, c.created_at DESC
");
$stmt->execute([':uid' => $uid]);
$allCards = $stmt->fetchAll();

// Separar cartões ativos e inativos
$activeCards = array_filter($allCards, fn($c) => $c['active']);
$inactiveCards = array_filter($allCards, fn($c) => !$c['active']);

// Estatísticas gerais
$totalLimit = array_sum(array_column($allCards, 'limit_amount'));
$totalBalance = array_sum(array_column($allCards, 'balance'));

// Estatísticas para cartões ativos
$activeTotalLimit = array_sum(array_column($activeCards, 'limit_amount'));
$activeTotalBalance = array_sum(array_column($activeCards, 'balance'));
$activeAvailable = $activeTotalLimit - $activeTotalBalance;

// Estatísticas para cartões inativos
$inactiveTotalLimit = array_sum(array_column($inactiveCards, 'limit_amount'));
$inactiveTotalBalance = array_sum(array_column($inactiveCards, 'balance'));
$inactiveAvailable = $inactiveTotalLimit - $inactiveTotalBalance;
?>
<!doctype html>
<html lang="pt-PT" data-theme="<?=$currentTheme?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Gerir Cartões - FreeCard</title>
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
    }
    .btn-outline-primary { 
      color: var(--primary-green); 
      border-color: var(--primary-green); 
    }
    .btn-outline-primary:hover { 
      background: var(--primary-green); 
      border-color: var(--primary-green); 
    }
    .text-primary { color: var(--primary-green) !important; }
    
    .card {
      border: none;
      border-radius: 16px;
      box-shadow: 0 4px 20px var(--shadow);
      transition: all 0.3s;
      background: var(--bg-secondary);
      color: var(--text-primary);
    }
    .card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 30px var(--shadow);
    }
    .card-visual {
      border-radius: 12px;
      padding: 20px;
      color: white;
      min-height: 140px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      position: relative;
      overflow: hidden;
    }
    .card-visual::before {
      content: '';
      position: absolute;
      top: -50%;
      right: -30%;
      width: 200%;
      height: 200%;
      background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    }
    .card-visual-inactive {
      background: linear-gradient(135deg, #95a5a6 0%, #7f8c8d 100%) !important;
      opacity: 0.7;
    }
    .card-number {
      font-size: 20px;
      letter-spacing: 3px;
      font-weight: 600;
      position: relative;
    }
    .card-name {
      font-size: 14px;
      text-transform: uppercase;
      font-weight: 700;
      position: relative;
    }
    
    /* Logo do banco no cartão */
    .bank-logo-card {
      width: 40px;
      height: 40px;
      object-fit: contain;
      background: white;
      border-radius: 8px;
      padding: 4px;
      position: relative;
    }
    
    .summary-card {
      background: var(--bg-secondary);
      border-radius: 16px;
      padding: 24px;
      box-shadow: 0 4px 20px var(--shadow);
    }
    .stat-item {
      text-align: center;
      padding: 16px;
    }
    .stat-item h3 {
      font-size: 28px;
      font-weight: 800;
      margin-bottom: 4px;
      color: var(--text-primary);
    }
    .stat-item p {
      font-size: 13px;
      margin: 0;
      color: var(--text-secondary);
    }
    .stat-item:not(:last-child) {
      border-right: 1px solid var(--border-color);
    }
    
    .section-header {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 24px;
      padding-bottom: 12px;
      border-bottom: 2px solid var(--border-color);
    }
    
    .section-header h3 {
      font-size: 24px;
      font-weight: 700;
      margin: 0;
      color: var(--text-primary);
    }
    
    /* Tema escuro */
    [data-theme="dark"] .text-muted {
      color: var(--text-secondary) !important;
    }
    [data-theme="dark"] .border {
      border-color: var(--border-color) !important;
    }
    [data-theme="dark"] .bg-light {
      background: var(--bg-hover) !important;
      color: var(--text-primary);
    }
    [data-theme="dark"] .badge {
      background: var(--bg-hover) !important;
      color: var(--text-primary) !important;
    }
    [data-theme="dark"] .badge.bg-success {
      background: #27ae60 !important;
      color: white !important;
    }
    [data-theme="dark"] .badge.bg-secondary {
      background: #7f8c8d !important;
      color: white !important;
    }
    [data-theme="dark"] .progress {
      background: var(--bg-hover);
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

<div class="container mt-4 mb-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h2><i class="bi bi-wallet2"></i> Os Meus Cartões</h2>
      <p class="text-muted mb-0">Gere os teus cartões e acompanha os limites</p>
    </div>
    <a href="add_card.php" class="btn btn-primary">
      <i class="bi bi-plus-circle"></i> Adicionar Cartão
    </a>
  </div>

  <?php if ($message): ?>
    <div class="alert alert-<?=$messageType?> alert-dismissible fade show">
      <i class="bi bi-<?=$messageType === 'success' ? 'check-circle' : 'info-circle'?>"></i>
      <?=htmlspecialchars($message)?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <?php if (!empty($allCards)): ?>
    <!-- Cartões Ativos -->
    <?php if (!empty($activeCards)): ?>
      <!-- Estatísticas Cartões Ativos -->
      <div class="summary-card mb-4">
        <div class="section-header mb-3">
          <h3><i class="text-success"></i> Cartões Ativos</h3>
        </div>
        <div class="row">
          <div class="col-4">
            <div class="stat-item">
              <h3 style="color: #3498db;">€<?=number_format($activeTotalLimit, 2)?></h3>
              <p>Limite Total</p>
            </div>
          </div>
          <div class="col-4">
            <div class="stat-item">
              <h3 class="text-danger">€<?=number_format($activeTotalBalance, 2)?></h3>
              <p>Gasto Atual</p>
            </div>
          </div>
          <div class="col-4">
            <div class="stat-item">
              <h3 class="text-success">€<?=number_format($activeAvailable, 2)?></h3>
              <p>Disponível</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Lista Cartões Ativos -->
      <div class="row g-4 mb-5">
        <?php foreach($activeCards as $c): ?>
          <?php 
            $percentage = $c['limit_amount'] > 0 ? ($c['balance'] / $c['limit_amount']) * 100 : 0;
            $progressColor = $percentage >= 80 ? 'danger' : ($percentage >= 60 ? 'warning' : 'success');
            $available = $c['limit_amount'] - $c['balance'];
            $cardColor = $c['color'] ?? 'purple';
            $gradient = $cardColors[$cardColor] ?? $cardColors['purple'];
            $bank = $c['bank'] ?? 'none';
            $hasBank = $bank !== 'none' && isset($bankLogos[$bank]);
          ?>
          <div class="col-12 col-md-6 col-xl-4">
            <div class="card h-100">
              <div class="card-body p-4">
                <!-- Card Visual -->
                <div class="card-visual mb-3" style="background: <?=$gradient?>;">
                  <div>
                    <div class="mb-2">
                      <?php if ($hasBank): ?>
                        <img src="<?=$bankLogos[$bank]?>" alt="<?=$bank?>" class="bank-logo-card">
                      <?php else: ?>
                        <i class="bi bi-credit-card" style="font-size: 28px;"></i>
                      <?php endif; ?>
                    </div>
                    <div class="card-number">•••• •••• •••• ••••</div>
                  </div>
                  <div>
                    <div class="card-name"><?=htmlspecialchars($c['name'])?></div>
                    <div class="d-flex justify-content-between align-items-center mt-2">
                      <small>FreeCard</small>
                      <span class="badge bg-success text-white">ATIVO</span>
                    </div>
                  </div>
                </div>

                <!-- Informações -->
                <div class="mb-3">
                  <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">Utilização do Limite</span>
                    <span class="fw-bold small"><?=round($percentage)?>%</span>
                  </div>
                  <div class="progress" style="height: 10px; border-radius: 10px;">
                    <div class="progress-bar bg-<?=$progressColor?>" style="width: <?=min($percentage, 100)?>%"></div>
                  </div>
                  <div class="d-flex justify-content-between mt-2">
                    <small class="text-muted">€<?=number_format($c['balance'],2)?> usado</small>
                    <small class="text-muted">€<?=number_format($c['limit_amount'],2)?> limite</small>
                  </div>
                </div>

                <div class="p-3 bg-light rounded mb-3">
                  <div class="row text-center">
                    <div class="col-6 border-end">
                      <div class="fw-bold text-success">€<?=number_format($available, 2)?></div>
                      <small class="text-muted">Disponível</small>
                    </div>
                    <div class="col-6">
                      <div class="fw-bold"><?=$c['transaction_count']?></div>
                      <small class="text-muted">Transações</small>
                    </div>
                  </div>
                </div>

                <!-- Ações -->
                <div class="d-flex gap-2 mb-2">
                  <a href="edit_card.php?id=<?=$c['id']?>" class="btn btn-sm btn-outline-primary flex-fill">
                    <i class="bi bi-pencil"></i> Editar
                  </a>
                  <form method="post" class="flex-fill">
                    <input type="hidden" name="card_id" value="<?=$c['id']?>">
                    <input type="hidden" name="action" value="toggle">
                    <button type="submit" class="btn btn-sm btn-outline-secondary w-100">
                      <i class="bi bi-pause-circle"></i> Desativar
                    </button>
                  </form>
                </div>

                <div class="text-center mt-3">
                  <small class="text-muted">
                    <i class="bi bi-calendar"></i> Criado em <?=date('d/m/Y', strtotime($c['created_at']))?>
                  </small>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <!-- Cartões Inativos -->
    <?php if (!empty($inactiveCards)): ?>
      <!-- Estatísticas Cartões Inativos -->
      <div class="summary-card mb-4">
        <div class="section-header mb-3">
          <h3><i class="text-secondary"></i> Cartões Inativos</h3>
        </div>
        <div class="row">
          <div class="col-4">
            <div class="stat-item">
              <h3 style="color: #3498db;">€<?=number_format($inactiveTotalLimit, 2)?></h3>
              <p>Limite Total</p>
            </div>
          </div>
          <div class="col-4">
            <div class="stat-item">
              <h3 class="text-danger">€<?=number_format($inactiveTotalBalance, 2)?></h3>
              <p>Gasto Atual</p>
            </div>
          </div>
          <div class="col-4">
            <div class="stat-item">
              <h3 class="text-success">€<?=number_format($inactiveAvailable, 2)?></h3>
              <p>Disponível</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Lista Cartões Inativos -->
      <div class="row g-4">
        <?php foreach($inactiveCards as $c): ?>
          <?php 
            $percentage = $c['limit_amount'] > 0 ? ($c['balance'] / $c['limit_amount']) * 100 : 0;
            $progressColor = $percentage >= 80 ? 'danger' : ($percentage >= 60 ? 'warning' : 'success');
            $available = $c['limit_amount'] - $c['balance'];
            $cardColor = $c['color'] ?? 'purple';
            $gradient = $cardColors[$cardColor] ?? $cardColors['purple'];
            $bank = $c['bank'] ?? 'none';
            $hasBank = $bank !== 'none' && isset($bankLogos[$bank]);
          ?>
          <div class="col-12 col-md-6 col-xl-4">
            <div class="card h-100">
              <div class="card-body p-4">
                <!-- Card Visual -->
                <div class="card-visual card-visual-inactive mb-3" style="background: <?=$gradient?>;">
                  <div>
                    <div class="mb-2">
                      <?php if ($hasBank): ?>
                        <img src="<?=$bankLogos[$bank]?>" alt="<?=$bank?>" class="bank-logo-card">
                      <?php else: ?>
                        <i class="bi bi-credit-card" style="font-size: 28px;"></i>
                      <?php endif; ?>
                    </div>
                    <div class="card-number">•••• •••• •••• ••••</div>
                  </div>
                  <div>
                    <div class="card-name"><?=htmlspecialchars($c['name'])?></div>
                    <div class="d-flex justify-content-between align-items-center mt-2">
                      <small>FreeCard</small>
                      <span class="badge bg-secondary text-white">INATIVO</span>
                    </div>
                  </div>
                </div>

                <!-- Informações -->
                <div class="mb-3">
                  <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">Utilização do Limite</span>
                    <span class="fw-bold small"><?=round($percentage)?>%</span>
                  </div>
                  <div class="progress" style="height: 10px; border-radius: 10px;">
                    <div class="progress-bar bg-<?=$progressColor?>" style="width: <?=min($percentage, 100)?>%"></div>
                  </div>
                  <div class="d-flex justify-content-between mt-2">
                    <small class="text-muted">€<?=number_format($c['balance'],2)?> usado</small>
                    <small class="text-muted">€<?=number_format($c['limit_amount'],2)?> limite</small>
                  </div>
                </div>

                <div class="p-3 bg-light rounded mb-3">
                  <div class="row text-center">
                    <div class="col-6 border-end">
                      <div class="fw-bold text-success">€<?=number_format($available, 2)?></div>
                      <small class="text-muted">Disponível</small>
                    </div>
                    <div class="col-6">
                      <div class="fw-bold"><?=$c['transaction_count']?></div>
                      <small class="text-muted">Transações</small>
                    </div>
                  </div>
                </div>

                <!-- Ações -->
                <div class="d-flex gap-2 mb-2">
                  <a href="edit_card.php?id=<?=$c['id']?>" class="btn btn-sm btn-outline-primary flex-fill">
                    <i class="bi bi-pencil"></i> Editar
                  </a>
                  <form method="post" class="flex-fill">
                    <input type="hidden" name="card_id" value="<?=$c['id']?>">
                    <input type="hidden" name="action" value="toggle">
                    <button type="submit" class="btn btn-sm btn-outline-success w-100">
                      <i class="bi bi-play-circle"></i> Ativar
                    </button>
                  </form>
                </div>

                <div class="text-center mt-3">
                  <small class="text-muted">
                    <i class="bi bi-calendar"></i> Criado em <?=date('d/m/Y', strtotime($c['created_at']))?>
                  </small>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  <?php else: ?>
    <!-- Estado vazio -->
    <div class="card">
      <div class="card-body text-center py-5">
        <div class="mb-4">
          <i class="bi bi-credit-card-2-front" style="font-size: 80px; color: #e0e0e0;"></i>
        </div>
        <h4 class="text-muted mb-3">Ainda não tens cartões registados</h4>
        <p class="text-muted mb-4">Adiciona o teu primeiro cartão para começares a gerir as tuas finanças de forma inteligente</p>
        <a href="add_card.php" class="btn btn-primary btn-lg">
          <i class="bi bi-plus-circle"></i> Adicionar Primeiro Cartão
        </a>
      </div>
    </div>
  <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>