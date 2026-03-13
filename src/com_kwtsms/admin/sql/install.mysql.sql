CREATE TABLE IF NOT EXISTS `#__kwtsms_settings` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `setting_key` VARCHAR(100) NOT NULL,
  `setting_value` TEXT NOT NULL DEFAULT (''),
  `autoload` TINYINT(1) NOT NULL DEFAULT 0,
  `modified` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `#__kwtsms_settings` (`setting_key`, `setting_value`, `autoload`) VALUES
('gateway_enabled', '0', 1),
('gateway_configured', '0', 1),
('test_mode', '1', 1),
('debug_logging', '0', 1),
('log_retention_days', '30', 1),
('balance', '0', 1),
('senderids', '[]', 1),
('coverage', '[]', 0),
('last_sync', '', 0),
('api_username', '', 0),
('api_password', '', 0),
('sender_id', '', 0);

CREATE TABLE IF NOT EXISTS `#__kwtsms_messages` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `event_type` VARCHAR(50) NOT NULL DEFAULT '',
  `recipient` VARCHAR(20) NOT NULL DEFAULT '',
  `message` TEXT NOT NULL,
  `sender_id` VARCHAR(11) NOT NULL DEFAULT '',
  `test_mode` TINYINT(1) NOT NULL DEFAULT 0,
  `api_response` TEXT NOT NULL DEFAULT (''),
  `msg_id` VARCHAR(64) NOT NULL DEFAULT '',
  `points_charged` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `balance_after` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `status` VARCHAR(20) NOT NULL DEFAULT '',
  `created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_event_type` (`event_type`),
  KEY `idx_created` (`created`),
  KEY `idx_msg_id` (`msg_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__kwtsms_logs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `level` ENUM('debug','info','warning','error') NOT NULL DEFAULT 'info',
  `context` VARCHAR(50) NOT NULL DEFAULT '',
  `message` TEXT NOT NULL,
  `data` TEXT NOT NULL DEFAULT (''),
  `created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_level` (`level`),
  KEY `idx_created` (`created`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__kwtsms_templates` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `template_key` VARCHAR(50) NOT NULL DEFAULT '',
  `lang` VARCHAR(5) NOT NULL DEFAULT 'en',
  `title` VARCHAR(100) NOT NULL DEFAULT '',
  `body` TEXT NOT NULL,
  `placeholders` TEXT NOT NULL DEFAULT (''),
  `enabled` TINYINT(1) NOT NULL DEFAULT 1,
  `modified` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_template_lang` (`template_key`, `lang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `#__kwtsms_templates` (`template_key`, `lang`, `title`, `body`, `placeholders`, `enabled`) VALUES
('order_new', 'en', 'New Order (English)', 'Hi {customer_name}, your order #{order_id} for {order_total} has been received. Thank you for shopping at {shop_name}.', '{customer_name},{order_id},{order_total},{shop_name}', 1),
('order_new', 'ar', 'New Order (Arabic)', 'مرحبا {customer_name}، تم استلام طلبك رقم #{order_id} بقيمة {order_total}. شكرا للتسوق في {shop_name}.', '{customer_name},{order_id},{order_total},{shop_name}', 1),
('order_status_update', 'en', 'Order Status Update (English)', 'Hi {customer_name}, your order #{order_id} status has been updated to: {order_status}. {shop_name}', '{customer_name},{order_id},{order_status},{shop_name}', 1),
('order_status_update', 'ar', 'Order Status Update (Arabic)', 'مرحبا {customer_name}، تم تحديث حالة طلبك رقم #{order_id} الى: {order_status}. {shop_name}', '{customer_name},{order_id},{order_status},{shop_name}', 1),
('user_registration', 'en', 'User Registration (English)', 'Welcome {customer_name}! Your account has been created at {shop_name}. Thank you for joining us.', '{customer_name},{shop_name}', 1),
('user_registration', 'ar', 'User Registration (Arabic)', 'مرحبا {customer_name}! تم إنشاء حسابك في {shop_name}. شكرا لانضمامك إلينا.', '{customer_name},{shop_name}', 1);
