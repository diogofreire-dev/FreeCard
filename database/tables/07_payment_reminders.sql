-- Tabela: payment_reminders
CREATE TABLE IF NOT EXISTS payment_reminders (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
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
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (card_id) REFERENCES cards(id) ON DELETE SET NULL,
  INDEX idx_user_due (user_id, due_date),
  INDEX idx_active (active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
