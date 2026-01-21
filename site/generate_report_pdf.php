<?php
// site/generate_report_pdf.php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../vendor/autoload.php';

$uid = $_SESSION['user_id'] ?? null;

// Parâmetros
$type = $_GET['type'] ?? 'full';
$category = $_GET['category'] ?? '';
$card_id = !empty($_GET['card_id']) ? intval($_GET['card_id']) : null;
$month = $_GET['month'] ?? '';
$includeCards = isset($_GET['include_cards']);
$includeStats = isset($_GET['include_stats']);

// Buscar utilizador
$stmt = $pdo->prepare("SELECT username, email FROM users WHERE id = :uid");
$stmt->execute([':uid' => $uid]);
$user = $stmt->fetch();

// Buscar transações
$sql = "
    SELECT t.*, c.name AS card_name
    FROM transactions t
    LEFT JOIN cards c ON c.id = t.card_id
    WHERE t.user_id = :uid
";
$params = [':uid' => $uid];

if ($category) {
    $sql .= " AND t.category = :cat";
    $params[':cat'] = $category;
}
if ($card_id) {
    $sql .= " AND t.card_id = :cid";
    $params[':cid'] = $card_id;
}
if ($month) {
    $sql .= " AND DATE_FORMAT(t.transaction_date, '%Y-%m') = :month";
    $params[':month'] = $month;
}

$sql .= " ORDER BY t.transaction_date DESC";

if ($type === 'summary') {
    $sql .= " LIMIT 20";
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$transactions = $stmt->fetchAll();

// Buscar cartões
$cards = [];
if ($includeCards) {
    $stmt = $pdo->prepare("
        SELECT c.name, c.limit_amount, c.balance, c.active
        FROM cards c
        WHERE c.user_id = :uid
        ORDER BY c.active DESC, c.name
    ");
    $stmt->execute([':uid' => $uid]);
    $cards = $stmt->fetchAll();
}

// Estatísticas
$totalAmount = array_sum(array_column($transactions, 'amount'));
$transactionCount = count($transactions);
$avgAmount = $transactionCount > 0 ? $totalAmount / $transactionCount : 0;

// Stats por categoria
$categoryStats = [];
if ($includeStats) {
    foreach ($transactions as $t) {
        $cat = $t['category'] ?? 'Sem Categoria';
        if (!isset($categoryStats[$cat])) {
            $categoryStats[$cat] = ['total' => 0, 'count' => 0];
        }
        $categoryStats[$cat]['total'] += $t['amount'];
        $categoryStats[$cat]['count']++;
    }
    arsort($categoryStats);
}

// Cores profissionais (azul da marca + cinzas)
$brandBlue = [46, 88, 204]; // Cor azul do site FreeCard
$darkText = [31, 41, 55];
$mediumGray = [107, 114, 128];
$lightGray = [249, 250, 251];
$borderGray = [229, 231, 235];

// Criar PDF
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

$pdf->SetCreator('FreeCard');
$pdf->SetAuthor($user['username']);
$pdf->SetTitle('Relatorio FreeCard');

$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(20, 15, 20);
$pdf->SetAutoPageBreak(TRUE, 15);

$pdf->AddPage();

// ===== CABEÇALHO =====
$pdf->SetFillColor($brandBlue[0], $brandBlue[1], $brandBlue[2]);
$pdf->Rect(0, 0, 210, 37, 'F');

$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('helvetica', 'B', 26);
$pdf->SetXY(20, 5);
$pdf->Cell(0, 8, 'FreeCard', 0, 1, 'L');

$pdf->SetFont('helvetica', '', 9);
$pdf->SetXY(20, 15);
$pdf->Cell(0, 5, 'Gestao Financeira Pessoal', 0, 1, 'L');

$pdf->SetFont('helvetica', 'B', 11);
$pdf->SetXY(20, 23);
$reportTitle = $type === 'full' ? 'Relatorio Completo' : 'Relatorio Resumido';
$pdf->Cell(0, 6, $reportTitle, 0, 1, 'L');

$pdf->SetFont('helvetica', '', 8);
$pdf->SetXY(20, 28);
$pdf->Cell(0, 4, 'Gerado em ' . date('d/m/Y H:i'), 0, 1, 'L');

// ===== INFO UTILIZADOR =====
$pdf->SetY(46);
$pdf->SetTextColor($darkText[0], $darkText[1], $darkText[2]);
$pdf->SetFillColor($lightGray[0], $lightGray[1], $lightGray[2]);
$pdf->RoundedRect(20, 46, 170, 18, 2, '1111', 'F');

$pdf->SetXY(20, 49);
$pdf->SetFont('helvetica', 'B', 9);
$pdf->Cell(170, 4, 'Utilizador', 0, 1, 'C');

$pdf->SetX(20);
$pdf->SetFont('helvetica', '', 8);
$pdf->Cell(170, 4, $user['username'] . ' (' . $user['email'] . ')', 0, 1, 'C');

if ($month || $category || $card_id) {
    $pdf->SetX(20);
    $pdf->SetFont('helvetica', 'I', 7);
    $pdf->SetTextColor($mediumGray[0], $mediumGray[1], $mediumGray[2]);
    $filters = [];
    if ($month) $filters[] = 'Mes: ' . date('m/Y', strtotime($month . '-01'));
    if ($category) $filters[] = 'Categoria: ' . $category;
    if ($card_id) {
        foreach ($transactions as $t) {
            if ($t['card_id'] == $card_id && $t['card_name']) {
                $filters[] = 'Cartao: ' . $t['card_name'];
                break;
            }
        }
    }
    $pdf->Cell(170, 4, 'Filtros: ' . implode(' | ', $filters), 0, 1, 'C');
}

// ===== RESUMO =====
$pdf->SetY(70);
$pdf->SetTextColor($darkText[0], $darkText[1], $darkText[2]);
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(0, 5, 'Resumo', 0, 1, 'C');

$pdf->SetFillColor($lightGray[0], $lightGray[1], $lightGray[2]);
$pdf->SetDrawColor($borderGray[0], $borderGray[1], $borderGray[2]);

$boxWidth = 50;
$boxHeight = 20;
$spacing = 5;
$startY = $pdf->GetY() + 2;
$totalWidth = ($boxWidth * 3) + ($spacing * 2);
$startX = (210 - $totalWidth) / 2;

// Box Total Gasto
$pdf->RoundedRect($startX, $startY, $boxWidth, $boxHeight, 2, '1111', 'DF');
$pdf->SetXY($startX, $startY + 3);
$pdf->SetFont('helvetica', '', 7);
$pdf->SetTextColor($mediumGray[0], $mediumGray[1], $mediumGray[2]);
$pdf->Cell($boxWidth, 3, 'Total Gasto', 0, 1, 'C');
$pdf->SetXY($startX, $startY + 9);
$pdf->SetFont('helvetica', 'B', 11);
$pdf->SetTextColor($darkText[0], $darkText[1], $darkText[2]);
$pdf->Cell($boxWidth, 5, number_format($totalAmount, 2, ',', ' ') . ' EUR', 0, 0, 'C');

// Box Transações
$pdf->RoundedRect($startX + $boxWidth + $spacing, $startY, $boxWidth, $boxHeight, 2, '1111', 'DF');
$pdf->SetXY($startX + $boxWidth + $spacing, $startY + 3);
$pdf->SetFont('helvetica', '', 7);
$pdf->SetTextColor($mediumGray[0], $mediumGray[1], $mediumGray[2]);
$pdf->Cell($boxWidth, 3, 'Transacoes', 0, 1, 'C');
$pdf->SetXY($startX + $boxWidth + $spacing, $startY + 9);
$pdf->SetFont('helvetica', 'B', 11);
$pdf->SetTextColor($darkText[0], $darkText[1], $darkText[2]);
$pdf->Cell($boxWidth, 5, $transactionCount, 0, 0, 'C');

// Box Média
$pdf->RoundedRect($startX + ($boxWidth + $spacing) * 2, $startY, $boxWidth, $boxHeight, 2, '1111', 'DF');
$pdf->SetXY($startX + ($boxWidth + $spacing) * 2, $startY + 3);
$pdf->SetFont('helvetica', '', 7);
$pdf->SetTextColor($mediumGray[0], $mediumGray[1], $mediumGray[2]);
$pdf->Cell($boxWidth, 3, 'Media', 0, 1, 'C');
$pdf->SetXY($startX + ($boxWidth + $spacing) * 2, $startY + 9);
$pdf->SetFont('helvetica', 'B', 11);
$pdf->SetTextColor($darkText[0], $darkText[1], $darkText[2]);
$pdf->Cell($boxWidth, 5, number_format($avgAmount, 2, ',', ' ') . ' EUR', 0, 0, 'C');

$pdf->SetY($startY + $boxHeight + 6);

// ===== CARTÕES =====
if ($includeCards && !empty($cards)) {
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->SetTextColor($darkText[0], $darkText[1], $darkText[2]);
    $pdf->Cell(0, 5, 'Cartoes', 0, 1, 'L');
    $pdf->Ln(1);

    $tableWidth = 170;
    $tableX = (210 - $tableWidth) / 2;
    $pdf->SetX($tableX);

    $pdf->SetFont('helvetica', 'B', 7);
    $pdf->SetFillColor($lightGray[0], $lightGray[1], $lightGray[2]);
    $pdf->SetDrawColor($borderGray[0], $borderGray[1], $borderGray[2]);
    $pdf->Cell(55, 5, 'Nome', 1, 0, 'C', true);
    $pdf->Cell(25, 5, 'Estado', 1, 0, 'C', true);
    $pdf->Cell(50, 5, 'Gasto / Limite', 1, 0, 'C', true);
    $pdf->Cell(40, 5, 'Disponivel', 1, 1, 'C', true);

    $pdf->SetFont('helvetica', '', 7);
    $alternate = false;
    foreach ($cards as $card) {
        $available = $card['limit_amount'] - $card['balance'];

        if ($alternate) {
            $pdf->SetFillColor(255, 255, 255);
        } else {
            $pdf->SetFillColor($lightGray[0], $lightGray[1], $lightGray[2]);
        }
        $alternate = !$alternate;

        $pdf->SetX($tableX);
        $pdf->SetTextColor($darkText[0], $darkText[1], $darkText[2]);
        $pdf->Cell(55, 5, substr($card['name'], 0, 30), 1, 0, 'L', true);

        $statusText = $card['active'] ? 'ATIVO' : 'INATIVO';
        $pdf->Cell(25, 5, $statusText, 1, 0, 'C', true);
        $pdf->Cell(50, 5, number_format($card['balance'], 2) . ' / ' . number_format($card['limit_amount'], 2), 1, 0, 'R', true);
        $pdf->Cell(40, 5, number_format($available, 2) . ' EUR', 1, 1, 'R', true);
    }
    $pdf->Ln(4);
}

// ===== CATEGORIAS =====
if ($includeStats && !empty($categoryStats)) {
    if ($pdf->GetY() > 220) {
        $pdf->AddPage();
    }

    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->SetTextColor($darkText[0], $darkText[1], $darkText[2]);
    $pdf->Cell(0, 5, 'Gastos por Categoria', 0, 1, 'L');
    $pdf->Ln(1);

    $tableWidth2 = 170;
    $tableX2 = (210 - $tableWidth2) / 2;
    $pdf->SetX($tableX2);

    $pdf->SetFont('helvetica', 'B', 7);
    $pdf->SetFillColor($lightGray[0], $lightGray[1], $lightGray[2]);
    $pdf->SetDrawColor($borderGray[0], $borderGray[1], $borderGray[2]);
    $pdf->Cell(65, 5, 'Categoria', 1, 0, 'C', true);
    $pdf->Cell(35, 5, 'Transacoes', 1, 0, 'C', true);
    $pdf->Cell(45, 5, 'Total', 1, 0, 'C', true);
    $pdf->Cell(25, 5, '%', 1, 1, 'C', true);

    $pdf->SetFont('helvetica', '', 7);
    $alternate = false;
    foreach ($categoryStats as $cat => $stats) {
        if ($pdf->GetY() > 265) {
            $pdf->AddPage();
            $pdf->SetX($tableX2);
            $pdf->SetFont('helvetica', 'B', 7);
            $pdf->SetFillColor($lightGray[0], $lightGray[1], $lightGray[2]);
            $pdf->Cell(65, 5, 'Categoria', 1, 0, 'C', true);
            $pdf->Cell(35, 5, 'Transacoes', 1, 0, 'C', true);
            $pdf->Cell(45, 5, 'Total', 1, 0, 'C', true);
            $pdf->Cell(25, 5, '%', 1, 1, 'C', true);
            $pdf->SetFont('helvetica', '', 7);
            $alternate = false;
        }

        if ($alternate) {
            $pdf->SetFillColor(255, 255, 255);
        } else {
            $pdf->SetFillColor($lightGray[0], $lightGray[1], $lightGray[2]);
        }
        $alternate = !$alternate;

        $percentage = $totalAmount > 0 ? ($stats['total'] / $totalAmount) * 100 : 0;
        $pdf->SetX($tableX2);
        $pdf->SetTextColor($darkText[0], $darkText[1], $darkText[2]);
        $pdf->Cell(65, 5, $cat, 1, 0, 'L', true);
        $pdf->Cell(35, 5, $stats['count'], 1, 0, 'C', true);
        $pdf->Cell(45, 5, number_format($stats['total'], 2, ',', ' ') . ' EUR', 1, 0, 'R', true);
        $pdf->Cell(25, 5, number_format($percentage, 1) . '%', 1, 1, 'R', true);
    }
    $pdf->Ln(4);
}

// ===== TRANSAÇÕES =====
if (!empty($transactions)) {
    if ($pdf->GetY() > 220) {
        $pdf->AddPage();
    }

    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->SetTextColor($darkText[0], $darkText[1], $darkText[2]);
    $transTitle = $type === 'summary' ? 'Ultimas 20 Transacoes' : 'Transacoes';
    $pdf->Cell(0, 5, $transTitle, 0, 1, 'L');
    $pdf->Ln(1);

    $tableWidth3 = 170;
    $tableX3 = (210 - $tableWidth3) / 2;
    $pdf->SetX($tableX3);

    $pdf->SetFont('helvetica', 'B', 7);
    $pdf->SetFillColor($lightGray[0], $lightGray[1], $lightGray[2]);
    $pdf->SetDrawColor($borderGray[0], $borderGray[1], $borderGray[2]);
    $pdf->Cell(20, 5, 'Data', 1, 0, 'C', true);
    $pdf->Cell(50, 5, 'Descricao', 1, 0, 'C', true);
    $pdf->Cell(35, 5, 'Categoria', 1, 0, 'C', true);
    $pdf->Cell(35, 5, 'Cartao', 1, 0, 'C', true);
    $pdf->Cell(30, 5, 'Valor', 1, 1, 'C', true);

    $pdf->SetFont('helvetica', '', 7);
    $alternate = false;
    foreach ($transactions as $t) {
        if ($pdf->GetY() > 265) {
            $pdf->AddPage();
            $pdf->SetX($tableX3);
            $pdf->SetFont('helvetica', 'B', 7);
            $pdf->SetFillColor($lightGray[0], $lightGray[1], $lightGray[2]);
            $pdf->Cell(20, 5, 'Data', 1, 0, 'C', true);
            $pdf->Cell(50, 5, 'Descricao', 1, 0, 'C', true);
            $pdf->Cell(35, 5, 'Categoria', 1, 0, 'C', true);
            $pdf->Cell(35, 5, 'Cartao', 1, 0, 'C', true);
            $pdf->Cell(30, 5, 'Valor', 1, 1, 'C', true);
            $pdf->SetFont('helvetica', '', 7);
            $alternate = false;
        }

        if ($alternate) {
            $pdf->SetFillColor(255, 255, 255);
        } else {
            $pdf->SetFillColor($lightGray[0], $lightGray[1], $lightGray[2]);
        }
        $alternate = !$alternate;

        $date = date('d/m/Y', strtotime($t['transaction_date']));
        $description = mb_substr($t['description'], 0, 25, 'UTF-8');
        $category = $t['category'] ?? '-';
        $cardName = $t['card_name'] ? mb_substr($t['card_name'], 0, 15, 'UTF-8') : 'Dinheiro';

        $pdf->SetX($tableX3);
        $pdf->SetTextColor($darkText[0], $darkText[1], $darkText[2]);
        $pdf->Cell(20, 5, $date, 1, 0, 'C', true);
        $pdf->Cell(50, 5, $description, 1, 0, 'L', true);
        $pdf->Cell(35, 5, $category, 1, 0, 'L', true);
        $pdf->Cell(35, 5, $cardName, 1, 0, 'L', true);
        $pdf->Cell(30, 5, number_format($t['amount'], 2, ',', ' '). ' EUR', 1, 1, 'R', true);
    }
}

// ===== RODAPÉ =====
$totalPages = $pdf->getNumPages();
for ($i = 1; $i <= $totalPages; $i++) {
    $pdf->setPage($i);
    $pdf->SetY(-12);
    $pdf->SetFont('helvetica', 'I', 7);
    $pdf->SetTextColor($mediumGray[0], $mediumGray[1], $mediumGray[2]);
    $pdf->Cell(90, 4, 'FreeCard - Gestao Financeira', 0, 0, 'L');
    $pdf->Cell(90, 4, 'Pagina ' . $i . ' de ' . $totalPages, 0, 0, 'R');
}

// Gerar
$filename = 'freecard-relatorio-' . date('Y-m-d-His') . '.pdf';
$pdf->Output($filename, 'D');
exit;
