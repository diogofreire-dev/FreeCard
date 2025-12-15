DROP DATABASE IF EXISTS pap;
CREATE DATABASE IF NOT EXISTS pap CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE pap;

-- =====================================================
-- TABELA: users
-- Armazena informações dos utilizadores
-- =====================================================
CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  email VARCHAR(255) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  role ENUM('user','admin') NOT NULL DEFAULT 'user',
  -- Campos de segurança (v1.1.0)
  last_password_change DATETIME NULL DEFAULT NULL COMMENT 'Data da última alteração de password',
  last_email_change DATETIME NULL DEFAULT NULL COMMENT 'Data da última alteração de email',
  two_factor_enabled TINYINT(1) DEFAULT 0 COMMENT 'Indica se 2FA está ativo',
  two_factor_secret VARCHAR(255) NULL DEFAULT NULL COMMENT 'Secret para autenticação 2FA (futuro)',
  
  INDEX idx_last_changes (last_password_change, last_email_change)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Utilizadores do sistema';

-- =====================================================
-- TABELA: cards
-- Armazena os cartões de crédito dos utilizadores
-- =====================================================
CREATE TABLE IF NOT EXISTS cards (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  name VARCHAR(100) NOT NULL COMMENT 'Nome do cartão',
  limit_amount DECIMAL(10,2) DEFAULT 0 COMMENT 'Limite do cartão',
  balance DECIMAL(10,2) DEFAULT 0 COMMENT 'Saldo atual/gasto acumulado',
  color VARCHAR(20) DEFAULT 'purple' COMMENT 'Cor do cartão (purple, blue, green, orange, red, pink, teal, indigo)',
  active TINYINT(1) DEFAULT 1 COMMENT 'Cartão ativo ou inativo',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_user_active (user_id, active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Cartões de crédito dos utilizadores';

-- =====================================================
-- TABELA: transactions
-- Armazena todas as transações/despesas
-- =====================================================
CREATE TABLE IF NOT EXISTS transactions (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  card_id INT UNSIGNED NULL COMMENT 'Cartão associado (NULL = dinheiro)',
  amount DECIMAL(10,2) NOT NULL COMMENT 'Valor da transação',
  description VARCHAR(255) COMMENT 'Descrição da despesa',
  category VARCHAR(100) DEFAULT NULL COMMENT 'Categoria da transação',
  transaction_date DATE NOT NULL COMMENT 'Data em que a transação ocorreu',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de registo no sistema',
  
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (card_id) REFERENCES cards(id) ON DELETE SET NULL,
  INDEX idx_transaction_date (transaction_date),
  INDEX idx_user_transaction_date (user_id, transaction_date),
  INDEX idx_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Transações e despesas dos utilizadores';

-- =====================================================
-- TABELA: user_settings
-- Configurações personalizadas de cada utilizador
-- =====================================================
CREATE TABLE IF NOT EXISTS user_settings (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL UNIQUE,
  theme VARCHAR(10) DEFAULT 'light' COMMENT 'Tema da interface (light/dark)',
  currency VARCHAR(3) DEFAULT 'EUR' COMMENT 'Moeda padrão',
  language VARCHAR(5) DEFAULT 'pt-PT' COMMENT 'Idioma da interface',
  notifications TINYINT(1) DEFAULT 1 COMMENT 'Notificações ativas',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Configurações dos utilizadores';

-- =====================================================
-- TABELA: budgets
-- Orçamentos definidos pelos utilizadores
-- =====================================================
CREATE TABLE IF NOT EXISTS budgets (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  name VARCHAR(100) NOT NULL COMMENT 'Nome do orçamento',
  amount DECIMAL(10,2) NOT NULL COMMENT 'Valor do orçamento',
  period ENUM('monthly', 'weekly', 'yearly') NOT NULL DEFAULT 'monthly' COMMENT 'Período do orçamento',
  category VARCHAR(100) NULL COMMENT 'Categoria específica (NULL = todas)',
  card_id INT UNSIGNED NULL COMMENT 'Cartão específico (NULL = todos)',
  start_date DATE NOT NULL COMMENT 'Data de início',
  end_date DATE NULL COMMENT 'Data de fim (NULL = sem fim)',
  active TINYINT(1) DEFAULT 1 COMMENT 'Orçamento ativo',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (card_id) REFERENCES cards(id) ON DELETE SET NULL,
  INDEX idx_user_active (user_id, active),
  INDEX idx_dates (start_date, end_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Orçamentos dos utilizadores';

-- =====================================================
-- TABELA: budget_alerts
-- Alertas de orçamentos excedidos
-- =====================================================
CREATE TABLE IF NOT EXISTS budget_alerts (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  budget_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  alert_type ENUM('warning', 'exceeded') NOT NULL COMMENT 'Tipo de alerta',
  percentage DECIMAL(5,2) NOT NULL COMMENT 'Percentagem do orçamento',
  amount_spent DECIMAL(10,2) NOT NULL COMMENT 'Valor gasto',
  triggered_at DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT 'Quando o alerta foi disparado',
  acknowledged TINYINT(1) DEFAULT 0 COMMENT 'Se o utilizador já viu o alerta',
  
  FOREIGN KEY (budget_id) REFERENCES budgets(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_user_unacknowledged (user_id, acknowledged)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Alertas de orçamentos';

-- =====================================================
-- TABELA: payment_reminders (NOVO v1.2.0)
-- Lembretes de pagamentos futuros e despesas recorrentes
-- =====================================================
CREATE TABLE IF NOT EXISTS payment_reminders (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  name VARCHAR(255) NOT NULL COMMENT 'Nome do pagamento (ex: Netflix, Renda)',
  amount DECIMAL(10,2) NOT NULL COMMENT 'Valor do pagamento',
  category VARCHAR(100) NULL COMMENT 'Categoria do pagamento',
  card_id INT UNSIGNED NULL COMMENT 'Cartão a ser usado (NULL = dinheiro)',
  -- Datas e recorrência
  due_date DATE NOT NULL COMMENT 'Data de vencimento',
  recurrence ENUM('once', 'weekly', 'monthly', 'yearly') NOT NULL DEFAULT 'once' COMMENT 'Tipo de recorrência',
  -- Estado
  active TINYINT(1) DEFAULT 1 COMMENT 'Lembrete ativo',
  -- Notificações
  notify_days_before INT DEFAULT 3 COMMENT 'Notificar X dias antes do vencimento',
  -- Tracking
  last_paid_date DATE NULL COMMENT 'Última vez que foi marcado como pago',
  next_due_date DATE NULL COMMENT 'Próxima data calculada (para recorrentes)',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (card_id) REFERENCES cards(id) ON DELETE SET NULL,
  INDEX idx_user_due_date (user_id, due_date),
  INDEX idx_active (active),
  INDEX idx_next_due (next_due_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Lembretes de pagamentos futuros';

-- =====================================================
-- TABELA: reminder_history (NOVO v1.2.0)
-- Histórico de pagamentos realizados a partir de lembretes
-- =====================================================
CREATE TABLE IF NOT EXISTS reminder_history (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  reminder_id INT UNSIGNED NOT NULL,
  transaction_id INT UNSIGNED NULL COMMENT 'ID da transação criada',
  paid_date DATE NOT NULL COMMENT 'Data em que foi marcado como pago',
  amount DECIMAL(10,2) NOT NULL COMMENT 'Valor pago',
  notes TEXT NULL COMMENT 'Notas adicionais',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  
  FOREIGN KEY (reminder_id) REFERENCES payment_reminders(id) ON DELETE CASCADE,
  FOREIGN KEY (transaction_id) REFERENCES transactions(id) ON DELETE SET NULL,
  INDEX idx_reminder (reminder_id),
  INDEX idx_paid_date (paid_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Histórico de pagamentos de lembretes';

-- Utilizador de exemplo
-- Password: ValidPass123!
INSERT INTO users (username, email, password_hash, last_password_change, last_email_change) 
VALUES (
  'demo', 
  'demo@freecard.pt', 
  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
  DATE_SUB(NOW(), INTERVAL 2 MONTH),
  DATE_SUB(NOW(), INTERVAL 2 MONTH)
);

-- Configurações do utilizador demo
INSERT INTO user_settings (user_id, theme, notifications) 
VALUES (1, 'light', 1);

-- Cartões de exemplo
INSERT INTO cards (user_id, name, limit_amount, balance, color, active) VALUES
(1, 'Visa Principal', 1500.00, 450.00, 'blue', 1),
(1, 'Mastercard Compras', 1000.00, 320.00, 'green', 1),
(1, 'Amex Viagens', 2000.00, 0.00, 'purple', 1);

-- Transações de exemplo
INSERT INTO transactions (user_id, card_id, amount, description, category, transaction_date) VALUES
(1, 1, 45.00, 'Supermercado Continente', 'Alimentação', CURDATE()),
(1, 1, 12.50, 'Café', 'Alimentação', CURDATE()),
(1, 2, 89.90, 'Roupa na Zara', 'Compras', DATE_SUB(CURDATE(), INTERVAL 1 DAY)),
(1, 2, 15.00, 'Cinema', 'Entretenimento', DATE_SUB(CURDATE(), INTERVAL 2 DAY)),
(1, NULL, 50.00, 'Combustível', 'Transporte', DATE_SUB(CURDATE(), INTERVAL 3 DAY)),
(1, 1, 120.00, 'Ginásio', 'Saúde', DATE_SUB(CURDATE(), INTERVAL 5 DAY));

-- Orçamento de exemplo
INSERT INTO budgets (user_id, name, amount, period, start_date, active) VALUES
(1, 'Orçamento Mensal', 1000.00, 'monthly', DATE_FORMAT(CURDATE(), '%Y-%m-01'), 1);

-- Lembretes de exemplo (NOVO)
INSERT INTO payment_reminders (user_id, name, amount, category, card_id, due_date, recurrence, notify_days_before, next_due_date) VALUES
(1, 'Netflix', 15.99, 'Entretenimento', 1, DATE_ADD(CURDATE(), INTERVAL 5 DAY), 'monthly', 3, DATE_ADD(DATE_ADD(CURDATE(), INTERVAL 5 DAY), INTERVAL 1 MONTH)),
(1, 'Ginásio', 35.00, 'Saúde', 2, DATE_ADD(CURDATE(), INTERVAL 3 DAY), 'monthly', 3, DATE_ADD(DATE_ADD(CURDATE(), INTERVAL 3 DAY), INTERVAL 1 MONTH)),
(1, 'Renda', 500.00, 'Casa', NULL, DATE_ADD(CURDATE(), INTERVAL 10 DAY), 'monthly', 7, DATE_ADD(DATE_ADD(CURDATE(), INTERVAL 10 DAY), INTERVAL 1 MONTH)),
(1, 'Seguro do Carro', 450.00, 'Transporte', NULL, DATE_ADD(CURDATE(), INTERVAL 90 DAY), 'yearly', 7, DATE_ADD(DATE_ADD(CURDATE(), INTERVAL 90 DAY), INTERVAL 1 YEAR)),
(1, 'Spotify', 9.99, 'Entretenimento', 1, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 'monthly', 3, DATE_ADD(DATE_SUB(CURDATE(), INTERVAL 2 DAY), INTERVAL 1 MONTH));

-- =====================================================
-- QUERIES ÚTEIS PARA ANÁLISE
-- =====================================================

-- Ver total gasto por utilizador no mês atual
SELECT 
    u.username,
    COALESCE(SUM(t.amount), 0) as total_month
FROM users u
LEFT JOIN transactions t ON t.user_id = u.id 
    AND DATE_FORMAT(t.transaction_date, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')
GROUP BY u.id;

-- Ver utilização de cartões
SELECT 
    c.name,
    c.limit_amount,
    c.balance,
    ROUND((c.balance / c.limit_amount) * 100, 2) as usage_percentage
FROM cards c
WHERE c.user_id = 1
ORDER BY usage_percentage DESC;


-- Ver gastos por categoria no mês atual
SELECT 
    COALESCE(category, 'Sem Categoria') as category,
    COUNT(*) as transactions,
    SUM(amount) as total
FROM transactions
WHERE user_id = 1 
    AND DATE_FORMAT(transaction_date, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')
GROUP BY category
ORDER BY total DESC;

-- Ver alertas de segurança (últimas alterações)
SELECT 
    username,
    email,
    last_password_change,
    last_email_change,
    two_factor_enabled,
    DATEDIFF(NOW(), last_password_change) as days_since_password_change,
    DATEDIFF(NOW(), last_email_change) as days_since_email_change
FROM users
WHERE id = 1;


-- Ver lembretes vencidos (NOVO)
SELECT 
    r.name,
    r.amount,
    r.due_date,
    DATEDIFF(CURDATE(), r.due_date) as days_overdue,
    COALESCE(c.name, 'Dinheiro') as payment_method
FROM payment_reminders r
LEFT JOIN cards c ON c.id = r.card_id
WHERE r.user_id = 1
AND r.active = 1
AND r.due_date < CURDATE()
ORDER BY r.due_date;


-- Ver próximos vencimentos (7 dias) (NOVO)
SELECT 
    r.name,
    r.amount,
    r.due_date,
    DATEDIFF(r.due_date, CURDATE()) as days_until,
    r.recurrence
FROM payment_reminders r
WHERE r.user_id = 1
AND r.active = 1
AND r.due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
ORDER BY r.due_date;


-- Total a pagar este mês (NOVO)
SELECT 
    SUM(amount) as total_this_month,
    COUNT(*) as payment_count
FROM payment_reminders
WHERE user_id = 1
AND active = 1
AND YEAR(due_date) = YEAR(CURDATE())
AND MONTH(due_date) = MONTH(CURDATE());


-- Gastos recorrentes mensais (NOVO)
SELECT 
    SUM(amount) as monthly_recurring_cost,
    COUNT(*) as recurring_count
FROM payment_reminders
WHERE user_id = 1
AND active = 1
AND recurrence = 'monthly';
