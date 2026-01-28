-- Tabela: budget_alerts
CREATE TABLE IF NOT EXISTS budget_alerts (
  id INT UNSIGNED PRIMARY KEY,
  budget_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  alert_type ENUM('warning', 'exceeded') NOT NULL,
  percentage DECIMAL(5,2) NOT NULL,
  amount_spent DECIMAL(10,2) NOT NULL,
  triggered_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  acknowledged TINYINT(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE budget_alerts MODIFY id INT UNSIGNED AUTO_INCREMENT;
ALTER TABLE budget_alerts ADD CONSTRAINT fk_alerts_budget FOREIGN KEY (budget_id) REFERENCES budgets(id) ON DELETE CASCADE;
ALTER TABLE budget_alerts ADD CONSTRAINT fk_alerts_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

CREATE INDEX idx_alerts_user_unack ON budget_alerts(user_id, acknowledged);
