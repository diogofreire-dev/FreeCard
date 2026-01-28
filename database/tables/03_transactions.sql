-- Tabela: transactions
CREATE TABLE IF NOT EXISTS transactions (
  id INT UNSIGNED PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  card_id INT UNSIGNED NULL,
  amount DECIMAL(10,2) NOT NULL,
  description VARCHAR(255),
  category VARCHAR(100) DEFAULT NULL,
  transaction_date DATE NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE transactions MODIFY id INT UNSIGNED AUTO_INCREMENT;
ALTER TABLE transactions ADD CONSTRAINT fk_transactions_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;
ALTER TABLE transactions ADD CONSTRAINT fk_transactions_card FOREIGN KEY (card_id) REFERENCES cards(id) ON DELETE SET NULL;

CREATE INDEX idx_transaction_date ON transactions(transaction_date);
CREATE INDEX idx_transactions_user_date ON transactions(user_id, transaction_date);
CREATE INDEX idx_transactions_category ON transactions(category);
