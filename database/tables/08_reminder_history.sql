-- Tabela: reminder_history
CREATE TABLE IF NOT EXISTS reminder_history (
  id INT UNSIGNED PRIMARY KEY,
  reminder_id INT UNSIGNED NOT NULL,
  transaction_id INT UNSIGNED NULL,
  paid_date DATE NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  notes TEXT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE reminder_history MODIFY id INT UNSIGNED AUTO_INCREMENT;
ALTER TABLE reminder_history ADD CONSTRAINT fk_history_reminder FOREIGN KEY (reminder_id) REFERENCES payment_reminders(id) ON DELETE CASCADE;
ALTER TABLE reminder_history ADD CONSTRAINT fk_history_transaction FOREIGN KEY (transaction_id) REFERENCES transactions(id) ON DELETE SET NULL;

CREATE INDEX idx_history_reminder ON reminder_history(reminder_id);
CREATE INDEX idx_history_paid_date ON reminder_history(paid_date);
