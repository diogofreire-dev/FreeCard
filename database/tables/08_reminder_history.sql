-- Tabela: reminder_history
CREATE TABLE IF NOT EXISTS reminder_history (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  reminder_id INT UNSIGNED NOT NULL,
  transaction_id INT UNSIGNED NULL,
  paid_date DATE NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  notes TEXT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

  FOREIGN KEY (reminder_id) REFERENCES payment_reminders(id) ON DELETE CASCADE,
  FOREIGN KEY (transaction_id) REFERENCES transactions(id) ON DELETE SET NULL,
  INDEX idx_reminder (reminder_id),
  INDEX idx_paid_date (paid_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
