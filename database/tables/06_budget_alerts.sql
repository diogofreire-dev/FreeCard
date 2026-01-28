-- Tabela: budget_alerts
CREATE TABLE IF NOT EXISTS budget_alerts (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  budget_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  alert_type ENUM('warning', 'exceeded') NOT NULL,
  percentage DECIMAL(5,2) NOT NULL,
  amount_spent DECIMAL(10,2) NOT NULL,
  triggered_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  acknowledged TINYINT(1) DEFAULT 0,

  FOREIGN KEY (budget_id) REFERENCES budgets(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_user_unack (user_id, acknowledged)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
