-- Storage Boxx PostgreSQL Database Initialization
-- This script ensures the database is ready for the application

-- Connect to the storageboxx database
\c storageboxx;

-- Enable required extensions
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "pgcrypto";

-- Set timezone
SET timezone = 'UTC';

-- Grant privileges to the postgres user (default superuser)
-- Additional users can be created via environment variables

-- Create basic structure (will be populated by Storage Boxx installer)
-- Note: The actual table creation is handled by the application installer
-- This database is compatible with Supabase for cloud deployment