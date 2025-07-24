-- Storage Boxx MySQL Database Initialization
-- This script ensures the database is ready for the application

-- Set timezone
SET time_zone = '+00:00';

-- Create the database if it doesn't exist (already handled by environment variables)
USE storageboxx;

-- Ensure proper character set
ALTER DATABASE storageboxx CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Grant privileges to the storage user
GRANT ALL PRIVILEGES ON storageboxx.* TO 'storage'@'%';
FLUSH PRIVILEGES;

-- Create basic structure (will be populated by Storage Boxx installer)
-- Note: The actual table creation is handled by the application installer