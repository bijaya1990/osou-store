-- NaukriPatra Result Management System
-- MySQL schema. Table prefix is applied by the installer (default: np_res_).
-- If importing manually with phpMyAdmin, replace {{PREFIX}} with your prefix.

CREATE TABLE IF NOT EXISTS `{{PREFIX}}admins` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(64) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(120) DEFAULT NULL,
  `email` VARCHAR(190) DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `last_login_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `{{PREFIX}}login_attempts` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ip_address` VARCHAR(45) NOT NULL,
  `username` VARCHAR(64) DEFAULT NULL,
  `attempted_at` DATETIME NOT NULL,
  `success` TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_ip_time` (`ip_address`, `attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `{{PREFIX}}results` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `result_type` ENUM('internal','external') NOT NULL DEFAULT 'internal',
  `external_url` VARCHAR(500) DEFAULT NULL,
  `external_button_text` VARCHAR(60) NOT NULL DEFAULT 'CHECK RESULT',
  `institution_name` VARCHAR(190) NOT NULL,
  `institution_address` VARCHAR(255) DEFAULT NULL,
  `institution_logo` VARCHAR(255) DEFAULT NULL,
  `result_title` VARCHAR(190) NOT NULL,
  `examination_name` VARCHAR(190) NOT NULL,
  `board_university` VARCHAR(190) DEFAULT NULL,
  `class_course` VARCHAR(190) DEFAULT NULL,
  `semester_year` VARCHAR(120) DEFAULT NULL,
  `academic_session` VARCHAR(60) DEFAULT NULL,
  `roll_label` VARCHAR(100) NOT NULL DEFAULT 'Examination Roll Number',
  `result_date` DATE DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `slug` VARCHAR(190) NOT NULL,
  `status` ENUM('draft','published') NOT NULL DEFAULT 'draft',
  `show_on_ticker` TINYINT(1) NOT NULL DEFAULT 1,
  `published_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_slug` (`slug`),
  KEY `idx_ticker` (`status`, `show_on_ticker`, `published_at`),
  KEY `idx_status_date` (`status`, `result_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `{{PREFIX}}result_students` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `result_id` INT UNSIGNED NOT NULL,
  `roll_number` VARCHAR(64) NOT NULL,
  `roll_number_key` VARCHAR(64) NOT NULL,
  `registration_number` VARCHAR(64) DEFAULT NULL,
  `student_name` VARCHAR(190) DEFAULT NULL,
  `father_name` VARCHAR(190) DEFAULT NULL,
  `mother_name` VARCHAR(190) DEFAULT NULL,
  `date_of_birth` VARCHAR(40) DEFAULT NULL,
  `marks_data` LONGTEXT DEFAULT NULL,
  `extra_data` LONGTEXT DEFAULT NULL,
  `maximum_marks` DECIMAL(10,2) DEFAULT NULL,
  `secured_marks` DECIMAL(10,2) DEFAULT NULL,
  `total_marks` DECIMAL(10,2) DEFAULT NULL,
  `percentage` DECIMAL(6,2) DEFAULT NULL,
  `division` VARCHAR(60) DEFAULT NULL,
  `result_status` VARCHAR(40) DEFAULT NULL,
  `remarks` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_result_roll` (`result_id`, `roll_number_key`),
  KEY `idx_result` (`result_id`),
  KEY `idx_reg` (`result_id`, `registration_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `{{PREFIX}}import_logs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `result_id` INT UNSIGNED NOT NULL,
  `admin_id` INT UNSIGNED DEFAULT NULL,
  `original_filename` VARCHAR(255) DEFAULT NULL,
  `total_rows` INT NOT NULL DEFAULT 0,
  `imported_rows` INT NOT NULL DEFAULT 0,
  `updated_rows` INT NOT NULL DEFAULT 0,
  `skipped_rows` INT NOT NULL DEFAULT 0,
  `mode` VARCHAR(20) NOT NULL DEFAULT 'append',
  `notes` TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_result` (`result_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
