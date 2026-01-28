-- Tabela: transactions
CREATE TABLE IF NOT EXISTS transactions (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  card_id INT UNSIGNED NULL,
  amount DECIMAL(10,2) NOT NULL,
  description VARCHAR(255),
  category VARCHAR(100) DEFAULT NULL,
  transaction_date DATE NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (card_id) REFERENCES cards(id) ON DELETE SET NULL,
  INDEX idx_transaction_date (transaction_date),
  INDEX idx_user_date (user_id, transaction_date),
  INDEX idx_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
