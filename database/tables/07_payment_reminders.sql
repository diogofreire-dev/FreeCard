-- Tabela: payment_reminders
CREATE TABLE IF NOT EXISTS payment_reminders (
  id INT UNSIGNED PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  name VARCHAR(255) NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  category VARCHAR(100) NULL,
  card_id INT UNSIGNED NULL,
  due_date DATE NOT NULL,
  recurrence ENUM('once', 'weekly', 'monthly', 'yearly') NOT NULL DEFAULT 'once',
  active TINYINT(1) DEFAULT 1,
  notify_days_before INT DEFAULT 3,
  notify_method ENUM('email', 'site', 'both') DEFAULT 'email',
  last_notification_sent DATETIME NULL,
  last_paid_date DATE NULL,
  next_due_date DATE NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE payment_reminders MODIFY id INT UNSIGNED AUTO_INCREMENT;
ALTER TABLE payment_reminders ADD CONSTRAINT fk_reminders_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;
ALTER TABLE payment_reminders ADD CONSTRAINT fk_reminders_card FOREIGN KEY (card_id) REFERENCES cards(id) ON DELETE SET NULL;

CREATE INDEX idx_reminders_user_due ON payment_reminders(user_id, due_date);
CREATE INDEX idx_reminders_active ON payment_reminders(active);
