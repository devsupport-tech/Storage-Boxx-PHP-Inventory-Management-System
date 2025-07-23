-- Create dedicated user for Storage Boxx
-- This runs automatically when MySQL container starts

-- Create the database if it doesn't exist
CREATE DATABASE IF NOT EXISTS storageboxx CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

-- Create user with proper permissions
CREATE USER IF NOT EXISTS 'storage'@'%' IDENTIFIED BY 'storage123';
GRANT ALL PRIVILEGES ON storageboxx.* TO 'storage'@'%';
FLUSH PRIVILEGES;

-- Set timezone
SET GLOBAL time_zone = '+00:00';