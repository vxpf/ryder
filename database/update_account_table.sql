-- Add new columns to the account table
ALTER TABLE `account` 
ADD COLUMN `name` varchar(255) DEFAULT NULL,
ADD COLUMN `phone` varchar(50) DEFAULT NULL,
ADD COLUMN `profile_photo` varchar(255) DEFAULT NULL,
ADD COLUMN `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- Create directory for profile photos if it doesn't exist
-- Note: This should be executed in PHP, not SQL
-- mkdir -p /uploads/profile_photos 