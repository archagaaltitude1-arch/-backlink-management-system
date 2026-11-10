-- Backlink Management schema
-- Database: backlink_management
-- Target: MAMP MySQL (phpMyAdmin)

CREATE DATABASE IF NOT EXISTS `backlink_management`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `backlink_management`;

CREATE TABLE IF NOT EXISTS `roles` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `role_name` VARCHAR(100) NOT NULL,
  `permissions` JSON NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_role_name_unique` (`role_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `permissions` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `permission_name` VARCHAR(150) NOT NULL,
  `description` VARCHAR(255) NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_permission_name_unique` (`permission_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `users` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role_id` BIGINT UNSIGNED NULL,
  `status` ENUM('active', 'inactive', 'suspended') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_role_id_foreign` (`role_id`),
  CONSTRAINT `users_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `websites` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `domain` VARCHAR(255) NOT NULL,
  `website_name` VARCHAR(255) NOT NULL,
  `url` VARCHAR(500) NOT NULL,
  `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `credit_cost` INT UNSIGNED NOT NULL DEFAULT 0,
  `status` ENUM('active', 'inactive', 'pending') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `websites_domain_unique` (`domain`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `seo_metrics` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `website_id` BIGINT UNSIGNED NOT NULL,
  `DR` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `DA` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `TF` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `CF` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `traffic` BIGINT UNSIGNED NOT NULL DEFAULT 0,
  `spam_score` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `backlinks` BIGINT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `seo_metrics_website_id_unique` (`website_id`),
  CONSTRAINT `seo_metrics_website_id_foreign` FOREIGN KEY (`website_id`) REFERENCES `websites` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `services` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `service_name` VARCHAR(150) NOT NULL,
  `description` TEXT NULL,
  `base_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `credit_cost` INT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `niches` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `niche_name` VARCHAR(150) NOT NULL,
  `description` TEXT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `niches_niche_name_unique` (`niche_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `website_niches` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `website_id` BIGINT UNSIGNED NOT NULL,
  `niche_id` BIGINT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `website_niches_website_niche_unique` (`website_id`, `niche_id`),
  CONSTRAINT `website_niches_website_id_foreign` FOREIGN KEY (`website_id`) REFERENCES `websites` (`id`) ON DELETE CASCADE,
  CONSTRAINT `website_niches_niche_id_foreign` FOREIGN KEY (`niche_id`) REFERENCES `niches` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `packages` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `package_name` VARCHAR(150) NOT NULL,
  `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `credit_amount` INT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `credit_pricing` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `credit_amount` INT UNSIGNED NOT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `niche_specifications` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `specification_name` VARCHAR(150) NOT NULL,
  `field_type` ENUM('number', 'text', 'boolean', 'select') NOT NULL DEFAULT 'number',
  `min_value` DECIMAL(12,2) NULL,
  `max_value` DECIMAL(12,2) NULL,
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
