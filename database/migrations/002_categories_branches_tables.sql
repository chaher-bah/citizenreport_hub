-- Migration: Convert categories and branches from ENUMs to separate tables
-- Run this AFTER the initial schema (001_initial_schema.sql)

USE citizen_report_hub;

-- =============================================
-- 1. Create branches table
-- =============================================
CREATE TABLE IF NOT EXISTS branches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    contact_number VARCHAR(30) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- 2. Create categories table
-- =============================================
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT NULL,
    default_branch_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (default_branch_id) REFERENCES branches(id) ON DELETE SET NULL,
    INDEX idx_name (name),
    INDEX idx_default_branch (default_branch_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- 3. Seed default branches
-- =============================================
INSERT IGNORE INTO branches (name, contact_number) VALUES
('Police', '+1234567890'),
('City Worker', '+1234567891'),
('Utility Worker', '+1234567892'),
('Other', '+1234567893');


-- =============================================
-- 5. Add category_detail column if not exists
-- =============================================
SET @dbname = DATABASE();
SET @tablename = 'reports';
SET @columnname = 'category_detail';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' VARCHAR(255) DEFAULT NULL AFTER category')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;



-- Make category_id NOT NULL after migration
ALTER TABLE reports MODIFY COLUMN category_id INT NOT NULL;

-- Add foreign key
ALTER TABLE reports ADD CONSTRAINT fk_report_category
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT;

-- Drop old ENUM column
ALTER TABLE reports DROP COLUMN category;

-- =============================================
-- 7. Convert assignments.branch from ENUM to INT (FK to branches)
-- =============================================
-- Add new column
ALTER TABLE assignments ADD COLUMN branch_id INT NULL AFTER id;

-- Make branch_id NOT NULL after migration
ALTER TABLE assignments MODIFY COLUMN branch_id INT NOT NULL;

-- Add foreign key
ALTER TABLE assignments ADD CONSTRAINT fk_assignment_branch
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE RESTRICT;

-- Drop old ENUM column
ALTER TABLE assignments DROP COLUMN branch;

-- =============================================
-- 8. Add category_detail column to reports if not already added
-- =============================================
-- (Already handled in step 5)
