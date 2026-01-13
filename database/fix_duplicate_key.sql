-- ============================================================================
-- Fix Duplicate Key Error for kasubaytech_catlite_db
-- ============================================================================
-- This script fixes the duplicate key error by dropping and recreating
-- the admin table with the correct structure
-- ============================================================================

USE kasubaytech_catlite_db;

-- Drop the admin table if it exists (this will also drop the duplicate key)
DROP TABLE IF EXISTS `admin`;

-- Recreate admin table with correct structure (no duplicate UNIQUE constraint)
CREATE TABLE `admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Re-insert default admin if needed
INSERT INTO `admin` (`username`, `password`, `email`) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@kasubaytech.com')
ON DUPLICATE KEY UPDATE username=username;
