-- =====================================================================
-- LifeTechOCMS MdEngagement Module Database Schema
-- Follows LifeTechOCMS table naming (tb_ prefix), column naming (snake_case),
-- and primary key (lt_id) conventions.
-- =====================================================================

CREATE TABLE IF NOT EXISTS `tb_engagement_enquiry` (
    `lt_id` VARCHAR(50) NOT NULL,
    `full_name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(254) NOT NULL,
    `subject` VARCHAR(160) NOT NULL,
    `message` TEXT NOT NULL,
    `status` VARCHAR(20) NOT NULL DEFAULT 'new',
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME NOT NULL,
    PRIMARY KEY (`lt_id`),
    INDEX `idx_enquiry_status` (`status`),
    INDEX `idx_enquiry_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tb_engagement_subscriber` (
    `lt_id` VARCHAR(50) NOT NULL,
    `email` VARCHAR(254) NOT NULL,
    `token_hash` VARCHAR(64) NOT NULL,
    `status` VARCHAR(20) NOT NULL DEFAULT 'active',
    `consented_at` DATETIME NOT NULL,
    `unsubscribed_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME NOT NULL,
    PRIMARY KEY (`lt_id`),
    UNIQUE KEY `uk_subscriber_email` (`email`),
    INDEX `idx_subscriber_token_hash` (`token_hash`),
    INDEX `idx_subscriber_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
