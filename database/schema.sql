-- FreeCard Database Schema

DROP DATABASE IF EXISTS pap;
CREATE DATABASE pap CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE pap;

-- ========================================
-- 1. USERS
-- ========================================
CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  email VARCHAR(255) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  role ENUM('user','admin') NOT NULL DEFAULT 'user',
  email_verified TINYINT(1) DEFAULT 0,
  verification_token VARCHAR(64) NULL,
  verification_expires DATETIME NULL,
  last_password_change DATETIME NULL,
  last_email_change DATETIME NULL,
  two_factor_enabled TINYINT(1) DEFAULT 0,
  two_factor_secret VARCHAR(255) NULL,
  INDEX idx_email_verified (email_verified),
  INDEX idx_verification_token (verification_token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================
-- 2. CARDS
-- ========================================
CREATE TABLE IF NOT EXISTS cards (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  name VARCHAR(100) NOT NULL,
  limit_amount DECIMAL(10,2) DEFAULT 0,
  balance DECIMAL(10,2) DEFAULT 0,
  color VARCHAR(20) DEFAULT 'purple',
  active TINYINT(1) DEFAULT 1,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_user_active (user_id, active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================
-- 3. TRANSACTIONS
-- ========================================
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

-- ========================================
-- 4. USER_SETTINGS
-- ========================================
CREATE TABLE IF NOT EXISTS user_settings (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL UNIQUE,
  theme VARCHAR(10) DEFAULT 'light',
  currency VARCHAR(3) DEFAULT 'EUR',
  language VARCHAR(5) DEFAULT 'pt-PT',
  notifications TINYINT(1) DEFAULT 1,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================
-- 5. BUDGETS
-- ========================================
CREATE TABLE IF NOT EXISTS budgets (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
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
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (card_id) REFERENCES cards(id) ON DELETE SET NULL,
  INDEX idx_user_active (user_id, active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================
-- 6. BUDGET_ALERTS
-- ========================================
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

-- ========================================
-- 7. PAYMENT_REMINDERS
-- ========================================
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

-- ========================================
-- 8. REMINDER_HISTORY
-- ========================================
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

-- ========================================
-- 9. NOTIFICATIONS
-- ========================================
CREATE TABLE IF NOT EXISTS notifications (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  type VARCHAR(50) NOT NULL,
  title VARCHAR(255) NOT NULL,
  message TEXT NOT NULL,
  data JSON NULL,
  is_read TINYINT(1) DEFAULT 0,
  read_at DATETIME NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_user_unread (user_id, is_read),
  INDEX idx_type (type),
  INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
