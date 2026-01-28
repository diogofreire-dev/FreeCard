-- Tabela: budgets
CREATE TABLE IF NOT EXISTS budgets (
  id INT UNSIGNED PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  name VARCHAR(100) NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  period ENUM('monthly', 'weekly', 'yearly') NOT NULL DEFAULT 'monthly',
  category VARCHAR(100) NULL,
  card_id INT UNSIGNED NULL,
  start_date DATE NOT NULL,
  end_date DATE NULL,
  active TINYINT(1) DEFAULT 1,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE budgets MODIFY id INT UNSIGNED AUTO_INCREMENT;
ALTER TABLE budgets ADD CONSTRAINT fk_budgets_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;
ALTER TABLE budgets ADD CONSTRAINT fk_budgets_card FOREIGN KEY (card_id) REFERENCES cards(id) ON DELETE SET NULL;

CREATE INDEX idx_budgets_user_active ON budgets(user_id, active);
