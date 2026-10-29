-- ==============================================================================
-- Elbildad Services Ltd - cPanel Database Migration & Table Commands
-- Run this script in phpMyAdmin -> SQL tab (or via mysql CLI) on cPanel
-- ==============================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- 1. Tasks Table (Task Board, Scheduled Talks, Live Call Timer)
CREATE TABLE IF NOT EXISTS `tasks` (
  `id` CHAR(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` TEXT COLLATE utf8mb4_unicode_ci NULL,
  `type` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'general',
  `status` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `priority` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'medium',
  `user_id` CHAR(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `assigned_to` CHAR(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rfq_id` CHAR(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_name` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_phone` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `due_at` TIMESTAMP NULL DEFAULT NULL,
  `reminder_at` TIMESTAMP NULL DEFAULT NULL,
  `reminder_sent_at` TIMESTAMP NULL DEFAULT NULL,
  `completed_at` TIMESTAMP NULL DEFAULT NULL,
  `completed_by` CHAR(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `duration_minutes` INT NULL DEFAULT 0,
  `timing_started_at` TIMESTAMP NULL DEFAULT NULL,
  `notes` TEXT COLLATE utf8mb4_unicode_ci NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tasks_user_id_foreign` (`user_id`),
  KEY `tasks_assigned_to_foreign` (`assigned_to`),
  KEY `tasks_rfq_id_foreign` (`rfq_id`),
  KEY `tasks_completed_by_foreign` (`completed_by`),
  CONSTRAINT `tasks_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tasks_completed_by_foreign` FOREIGN KEY (`completed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tasks_rfq_id_foreign` FOREIGN KEY (`rfq_id`) REFERENCES `rfqs` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tasks_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Expenses Table (Expense Management for Purchase, Shipment, Running Cost, Agent Salary)
CREATE TABLE IF NOT EXISTS `expenses` (
  `id` CHAR(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expense_number` VARCHAR(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` ENUM('purchase','shipment','running_shipment','running_cost','salary','operational') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'purchase',
  `title` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` DECIMAL(15, 2) NOT NULL,
  `currency` VARCHAR(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'NGN',
  `rfq_id` CHAR(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `item_number` VARCHAR(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipping_method` VARCHAR(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vendor_name` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `agent_id` CHAR(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `salary_period` VARCHAR(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `note` TEXT COLLATE utf8mb4_unicode_ci NULL,
  `expense_date` DATE NOT NULL,
  `payment_method` VARCHAR(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'bank_transfer',
  `receipt_path` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` ENUM('paid','pending','approved') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'paid',
  `recorded_by` CHAR(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `expenses_expense_number_unique` (`expense_number`),
  KEY `expenses_recorded_by_foreign` (`recorded_by`),
  KEY `expenses_category_index` (`category`),
  KEY `expenses_expense_date_index` (`expense_date`),
  KEY `expenses_rfq_id_index` (`rfq_id`),
  KEY `expenses_agent_id_index` (`agent_id`),
  CONSTRAINT `expenses_agent_id_foreign` FOREIGN KEY (`agent_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `expenses_recorded_by_foreign` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `expenses_rfq_id_foreign` FOREIGN KEY (`rfq_id`) REFERENCES `rfqs` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Agent Notification Settings Table (Admin configurable inactivity threshold, login/logout chimes)
CREATE TABLE IF NOT EXISTS `agent_notification_settings` (
  `id` CHAR(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `admin_id` CHAR(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notify_on_login` TINYINT(1) NOT NULL DEFAULT 1,
  `notify_on_logout` TINYINT(1) NOT NULL DEFAULT 1,
  `notify_on_inactivity` TINYINT(1) NOT NULL DEFAULT 1,
  `inactivity_hours` INT UNSIGNED NOT NULL DEFAULT 24,
  `exclude_weekends` TINYINT(1) NOT NULL DEFAULT 1,
  `sound_chime` TINYINT(1) NOT NULL DEFAULT 1,
  `monitored_agents` JSON DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `agent_notification_settings_admin_id_unique` (`admin_id`),
  CONSTRAINT `agent_notification_settings_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Agent Activity Logs Table (Audit trail of logins and logouts)
CREATE TABLE IF NOT EXISTS `agent_activity_logs` (
  `id` CHAR(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `agent_id` CHAR(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `event` ENUM('login','logout') COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip_address` VARCHAR(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` TEXT COLLATE utf8mb4_unicode_ci NULL,
  `occurred_at` TIMESTAMP NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `agent_activity_logs_agent_id_event_index` (`agent_id`, `event`),
  KEY `agent_activity_logs_occurred_at_index` (`occurred_at`),
  CONSTRAINT `agent_activity_logs_agent_id_foreign` FOREIGN KEY (`agent_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Notifications Table (Ensure table exists and notifiable_id supports UUID 36 chars)
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` CHAR(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_type` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_id` VARCHAR(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `data` TEXT COLLATE utf8mb4_unicode_ci NOT NULL,
  `read_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`, `notifiable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Modify existing notifications table notifiable_id if already present as integer
ALTER TABLE `notifications` MODIFY `notifiable_id` VARCHAR(36) COLLATE utf8mb4_unicode_ci NOT NULL;

-- 6. Visa Applications Table (China Visa Application & Travel History)
CREATE TABLE IF NOT EXISTS `visa_applications` (
  `id` CHAR(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_name` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `dob` DATE DEFAULT NULL,
  `nationality` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gender` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `marital_status` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `occupation` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `company_name` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `company_address` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone_number` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_address` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `residential_address` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `passport_number` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `passport_issue_date` DATE DEFAULT NULL,
  `passport_expiry_date` DATE NOT NULL,
  `country_of_issuance` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `passport_data_page_path` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `passport_photo_path` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `visited_china_before` TINYINT(1) NOT NULL DEFAULT 0,
  `most_recent_china_visit` DATE DEFAULT NULL,
  `visits_in_last_5_years` INT DEFAULT 0,
  `previous_china_visits_dates` TEXT COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `traveled_other_countries` TINYINT(1) NOT NULL DEFAULT 0,
  `list_countries_visited` TEXT COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attended_trade_fairs` TINYINT(1) NOT NULL DEFAULT 0,
  `preferred_visa_type` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `purpose_of_visit` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `intended_arrival_date` DATE DEFAULT NULL,
  `intended_departure_date` DATE DEFAULT NULL,
  `cities_to_visit` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `has_previous_chinese_visas` TINYINT(1) NOT NULL DEFAULT 0,
  `previous_visa_pages_paths` TEXT COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `invitation_letter_path` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `assigned_agent_id` CHAR(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `visa_applications_assigned_agent_id_foreign` (`assigned_agent_id`),
  CONSTRAINT `visa_applications_assigned_agent_id_foreign` FOREIGN KEY (`assigned_agent_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Transactions Table (Zainpay Online & Card Transactions)
CREATE TABLE IF NOT EXISTS `transactions` (
  `id` CHAR(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` CHAR(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rfq_id` CHAR(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` DECIMAL(10, 2) NOT NULL,
  `currency` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'NGN',
  `status` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `gateway` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'zainpay',
  `metadata` JSON DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `transactions_reference_unique` (`reference`),
  KEY `transactions_user_id_foreign` (`user_id`),
  KEY `transactions_rfq_id_foreign` (`rfq_id`),
  CONSTRAINT `transactions_rfq_id_foreign` FOREIGN KEY (`rfq_id`) REFERENCES `rfqs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `transactions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Users activity tracking fields (if not already added)
ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `last_login_at` TIMESTAMP NULL DEFAULT NULL;
ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `avg_response_time` INT NULL DEFAULT NULL COMMENT 'in minutes';

-- 9. Sourcing Companies contact fields (if not already added)
ALTER TABLE `sourcing_companies` ADD COLUMN IF NOT EXISTS `email` VARCHAR(255) NULL AFTER `website`;
ALTER TABLE `sourcing_companies` ADD COLUMN IF NOT EXISTS `phone` VARCHAR(255) NULL AFTER `email`;

-- 10. Update expenses category to include running_cost and migrate existing running_shipment records
ALTER TABLE `expenses` MODIFY COLUMN `category` ENUM('purchase', 'shipment', 'running_shipment', 'running_cost', 'salary', 'operational') NOT NULL DEFAULT 'purchase';
UPDATE `expenses` SET `category` = 'running_cost' WHERE `category` = 'running_shipment';

-- 11. Add Sourcing and Supplier details to invoices table
ALTER TABLE `invoices` ADD COLUMN IF NOT EXISTS `sourcing_company_id` CHAR(36) COLLATE utf8mb4_unicode_ci NULL AFTER `rfq_id`;
ALTER TABLE `invoices` ADD COLUMN IF NOT EXISTS `supplier_name` VARCHAR(255) NULL AFTER `sourcing_company_id`;
ALTER TABLE `invoices` ADD COLUMN IF NOT EXISTS `supplier_contact` VARCHAR(255) NULL AFTER `supplier_name`;
ALTER TABLE `invoices` ADD COLUMN IF NOT EXISTS `supplier_location` VARCHAR(255) NULL AFTER `supplier_contact`;
ALTER TABLE `invoices` ADD COLUMN IF NOT EXISTS `supplier_url` TEXT NULL AFTER `supplier_location`;
ALTER TABLE `invoices` ADD COLUMN IF NOT EXISTS `sourcing_notes` TEXT NULL AFTER `supplier_url`;

-- 12. Complete Menu & Action Permissions Table Seeding
INSERT IGNORE INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(UUID(), 'menu_dashboard', 'web', NOW(), NOW()),
(UUID(), 'menu_tasks', 'web', NOW(), NOW()),
(UUID(), 'menu_new_sourcing_request', 'web', NOW(), NOW()),
(UUID(), 'menu_new_visa_application', 'web', NOW(), NOW()),
(UUID(), 'menu_my_payments', 'web', NOW(), NOW()),
(UUID(), 'menu_users', 'web', NOW(), NOW()),
(UUID(), 'menu_rfqs', 'web', NOW(), NOW()),
(UUID(), 'menu_sourcing_companies', 'web', NOW(), NOW()),
(UUID(), 'menu_transactions', 'web', NOW(), NOW()),
(UUID(), 'menu_categories', 'web', NOW(), NOW()),
(UUID(), 'menu_contact_messages', 'web', NOW(), NOW()),
(UUID(), 'menu_send_mail', 'web', NOW(), NOW()),
(UUID(), 'menu_agent_activity', 'web', NOW(), NOW()),
(UUID(), 'menu_roles_permissions', 'web', NOW(), NOW()),
(UUID(), 'menu_invoices', 'web', NOW(), NOW()),
(UUID(), 'menu_estimates', 'web', NOW(), NOW()),
(UUID(), 'menu_items', 'web', NOW(), NOW()),
(UUID(), 'menu_expenses', 'web', NOW(), NOW()),
(UUID(), 'menu_reports', 'web', NOW(), NOW()),
(UUID(), 'menu_visa_services', 'web', NOW(), NOW()),
(UUID(), 'menu_profile', 'web', NOW(), NOW()),
(UUID(), 'view_rfqs', 'web', NOW(), NOW()),
(UUID(), 'create_rfqs', 'web', NOW(), NOW()),
(UUID(), 'edit_rfqs', 'web', NOW(), NOW()),
(UUID(), 'assign_rfqs', 'web', NOW(), NOW()),
(UUID(), 'delete_rfqs', 'web', NOW(), NOW()),
(UUID(), 'verify_rfq_payment', 'web', NOW(), NOW()),
(UUID(), 'view_users', 'web', NOW(), NOW()),
(UUID(), 'create_users', 'web', NOW(), NOW()),
(UUID(), 'edit_users', 'web', NOW(), NOW()),
(UUID(), 'delete_users', 'web', NOW(), NOW()),
(UUID(), 'manage_users', 'web', NOW(), NOW()),
(UUID(), 'manage_roles', 'web', NOW(), NOW()),
(UUID(), 'manage_permissions', 'web', NOW(), NOW()),
(UUID(), 'view_invoices', 'web', NOW(), NOW()),
(UUID(), 'create_invoices', 'web', NOW(), NOW()),
(UUID(), 'edit_invoices', 'web', NOW(), NOW()),
(UUID(), 'delete_invoices', 'web', NOW(), NOW()),
(UUID(), 'send_invoices', 'web', NOW(), NOW()),
(UUID(), 'record_invoice_payments', 'web', NOW(), NOW()),
(UUID(), 'view_estimates', 'web', NOW(), NOW()),
(UUID(), 'create_estimates', 'web', NOW(), NOW()),
(UUID(), 'edit_estimates', 'web', NOW(), NOW()),
(UUID(), 'delete_estimates', 'web', NOW(), NOW()),
(UUID(), 'send_estimates', 'web', NOW(), NOW()),
(UUID(), 'view_items', 'web', NOW(), NOW()),
(UUID(), 'manage_items', 'web', NOW(), NOW()),
(UUID(), 'view_expenses', 'web', NOW(), NOW()),
(UUID(), 'create_expenses', 'web', NOW(), NOW()),
(UUID(), 'edit_expenses', 'web', NOW(), NOW()),
(UUID(), 'delete_expenses', 'web', NOW(), NOW()),
(UUID(), 'export_expenses', 'web', NOW(), NOW()),
(UUID(), 'view_reports', 'web', NOW(), NOW()),
(UUID(), 'view_tasks', 'web', NOW(), NOW()),
(UUID(), 'create_tasks', 'web', NOW(), NOW()),
(UUID(), 'edit_tasks', 'web', NOW(), NOW()),
(UUID(), 'delete_tasks', 'web', NOW(), NOW()),
(UUID(), 'manage_sourcing_companies', 'web', NOW(), NOW()),
(UUID(), 'manage_categories', 'web', NOW(), NOW()),
(UUID(), 'manage_contact_messages', 'web', NOW(), NOW()),
(UUID(), 'send_mail', 'web', NOW(), NOW()),
(UUID(), 'view_agent_activity', 'web', NOW(), NOW()),
(UUID(), 'view_transactions', 'web', NOW(), NOW()),
(UUID(), 'view_dashboard', 'web', NOW(), NOW()),
(UUID(), 'manage_visa_applications', 'web', NOW(), NOW());

-- 13. Ensure Accountant Role Exists
INSERT IGNORE INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(UUID(), 'accountant', 'web', NOW(), NOW());

-- Grant financial and management permissions to Accountant role
INSERT IGNORE INTO `role_has_permissions` (`permission_id`, `role_id`)
SELECT p.id, r.id FROM `permissions` p
CROSS JOIN `roles` r
WHERE r.name = 'accountant'
AND p.name IN (
  'menu_dashboard', 'menu_transactions', 'menu_invoices', 'menu_estimates', 'menu_items',
  'menu_expenses', 'menu_reports', 'menu_profile', 'view_dashboard', 'view_transactions',
  'view_invoices', 'create_invoices', 'edit_invoices', 'send_invoices', 'record_invoice_payments',
  'view_estimates', 'create_estimates', 'edit_estimates', 'send_estimates', 'view_items',
  'manage_items', 'view_expenses', 'create_expenses', 'edit_expenses', 'export_expenses', 'view_reports'
);

-- Grant all permissions to super roles (admin, owner, super_admin)
INSERT IGNORE INTO `role_has_permissions` (`permission_id`, `role_id`)
SELECT p.id, r.id FROM `permissions` p
CROSS JOIN `roles` r
WHERE r.name IN ('admin', 'owner', 'super_admin');

SET FOREIGN_KEY_CHECKS = 1;
