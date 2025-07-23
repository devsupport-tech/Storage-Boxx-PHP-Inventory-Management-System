# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Storage Boxx is a PHP-based inventory management system built on the custom "Core Boxx" framework. It features QR/NFC scanning, passwordless authentication, push notifications, and PWA capabilities.

## Development Setup

### Modern Docker Development (Recommended)
**Quick Start:**
```bash
cp .env.example .env
docker-compose up -d
```

**Services included:**
- PHP 8.2-FPM with all required extensions
- Nginx reverse proxy with SSL support
- MySQL (legacy) + PostgreSQL (Supabase-compatible) 
- Redis for caching and sessions
- Adminer (database management)
- MailHog (email testing)

### Legacy LAMP Setup
- LAMP/WAMP/MAMP/XAMPP stack
- Apache with mod_rewrite enabled
- PHP 8.0+ with PDO and OpenSSL extensions
- MySQL database

### Environment Configuration
Configuration is now environment-driven via `.env` file:
- Copy `.env.example` to `.env`
- Update database credentials and API keys
- Toggle development/production modes
- Configure Supabase integration

## Architecture Overview

### Core Framework ("Core Boxx")
- **Entry Point**: `index.php` → `CORE-Install.php` → `CORE-Go.php`
- **Module System**: Dynamic loading via `$_CORE->load("ModuleName")`
- **MVC-like Pattern**: Controllers in `/lib/LIB-*.php`, Views in `/pages/PAGE-*.php`
- **Database Layer**: PDO-based abstraction in `LIB-Core.php`

### Directory Structure
```
lib/              # Backend logic
├── CORE-*.php    # Framework core files  
├── LIB-*.php     # Business logic modules
├── API-*.php     # REST API endpoints
├── HOOK-*.php    # Framework extensions
└── SQL-*.sql     # Database schema

pages/            # Frontend templates
├── TEMPLATE-*.php # Layout templates
├── PAGE-*.php     # Individual pages  
├── REPORT-*.php   # Report generators
└── MAIL-*.php     # Email templates

assets/           # Static resources
├── PAGE-*.js     # Page-specific JavaScript
├── PAGE-*.css    # Page-specific styles
└── [libraries]   # Bootstrap, QR scanner, etc.

docker/           # Docker configuration
├── nginx/        # Nginx configuration and SSL
├── php/          # PHP-FPM Dockerfile and config
├── mysql/        # MySQL initialization scripts
└── postgresql/   # PostgreSQL schema and RLS policies

.env              # Environment configuration
docker-compose.yml # Docker services definition
```

### Database Architecture
**Dual Database Support:**
- **MySQL**: Legacy database for backward compatibility
- **PostgreSQL**: Modern database with Supabase integration
- **Row Level Security**: Multi-tenant security at database level
- **Real-time Features**: Supabase real-time subscriptions

**Key tables:** `users`, `items`, `item_mvt` (movements), `suppliers`, `customers`, `purchases`, `deliveries`
- BIGSERIAL primary keys (PostgreSQL) / BIGINT AUTO_INCREMENT (MySQL)
- Proper foreign key constraints and indexes
- Timestamp tracking (created_at, updated_at)
- Multi-tenant design with user levels (Admin/User/Suspended)

## Common Development Patterns

### Loading Modules
```php
$_CORE->load("ModuleName");  // Loads /lib/LIB-ModuleName.php
```

### API Endpoints
```php
// In /lib/API-*.php files
$_CORE->autoAPI([
  "action" => ["Module", "method", "required_user_level"]
]);
```

### Database Operations
```php
// Legacy MySQL operations
$result = $_CORE->DB->fetch($sql, $params);
$_CORE->DB->insert($table, $fields, $data);
$_CORE->DB->update($table, $fields, $data, $where);

// Modern dual-database operations (if implemented)
$_CORE->Database->useConnection('supabase');
$result = $_CORE->Database->fetch($sql, $params);
$_CORE->Database->useConnection('primary'); // Switch back to MySQL

// Environment-based configuration
$dbHost = env('DB_HOST', 'localhost');
$supabaseUrl = env('SUPABASE_URL');
```

### Frontend JavaScript
- Page-specific JS in `/assets/PAGE-*.js` 
- Global utilities in `/assets/PAGE-cb.js`
- API calls via fetch() with JWT authentication
- Bootstrap 5 for UI components

## Key Configuration Files

### `/lib/CORE-Config.php`
**Enhanced environment-driven configuration:**
- Loads settings from `.env` file via `CORE-Env.php`
- Dual database support (MySQL + PostgreSQL/Supabase)
- Auto-creates storage directories
- Environment-specific error handling
- Comprehensive caching and session configuration

### Environment Configuration
**Key environment variables:**
```bash
# Application
APP_ENV=development|production
APP_DEBUG=true|false
APP_URL=http://localhost

# Databases
DB_CONNECTION=mysql
DB_HOST=mysql
POSTGRES_HOST=db
SUPABASE_URL=https://project.supabase.co

# Caching & Sessions
REDIS_HOST=redis
CACHE_DRIVER=redis
SESSION_DRIVER=redis

# Security
JWT_SECRET=your-secret-key
PUSH_PUBLIC_KEY=your-vapid-key
```

### Development vs Production
**Automatic environment detection:**
- `APP_ENV=development`: Displays errors, verbose logging
- `APP_ENV=production`: Silent errors, file logging
- Storage directories auto-created with proper permissions

## Security Considerations

### Authentication
- Multi-modal: password, WebAuthn, NFC, QR codes
- JWT tokens for session management
- bcrypt password hashing
- User permission levels enforced in API endpoints

### Database Security
- PDO prepared statements used throughout
- Input validation in API endpoints
- User level checks on sensitive operations

## Testing and Quality Assurance

### Current State
- **No automated testing framework** - manual testing only
- No linting tools or code quality checks
- No CI/CD pipeline

### Code Quality
- Follow existing code style and patterns
- Maintain consistent indentation and naming
- Add error handling for new functionality
- Validate user inputs in API endpoints

## Common Development Tasks

### Docker Development Workflow
```bash
# Start development environment
docker-compose up -d

# View logs
docker-compose logs -f app

# Access containers
docker-compose exec app bash
docker-compose exec mysql mysql -u storage -p storageboxx
docker-compose exec db psql -U postgres -d storageboxx

# Restart services
docker-compose restart app nginx
```

### Adding New Features
1. Create business logic in `/lib/LIB-FeatureName.php`
2. Add API endpoints in `/lib/API-feature.php`  
3. Create frontend page in `/pages/PAGE-feature.php`
4. Add corresponding JavaScript in `/assets/PAGE-feature.js`
5. Update database schema in `/docker/postgresql/init/` for Supabase
6. Test with both MySQL and PostgreSQL if using dual database

### Database Changes
**PostgreSQL/Supabase (Recommended):**
- Modify schema files in `/docker/postgresql/init/`
- Use proper foreign key constraints and indexes
- Implement Row Level Security policies for multi-tenancy
- Test with both anonymous and authenticated users

**MySQL (Legacy):**
- Modify `/lib/SQL-*.sql` files directly
- Test schema changes on development database first
- Update existing installations manually via SQL

### Supabase Integration

**MCP Server Integration:**
The project now includes Supabase MCP server integration for advanced database operations and management.

**Configuration:**
- Project URL: `https://gchxpxrrsinxcwxmkjzp.supabase.co`
- Project ID: `gchxpxrrsinxcwxmkjzp`
- Anonymous Key: Configured in `.env` file
- Service Role Key: Configured in `.env` file (for admin operations)

**PHP Integration Module:**
A new `LIB-Supabase.php` module provides:
- REST API client for Supabase operations
- CRUD operations (select, insert, update, delete)
- RPC function calls for custom database functions
- Connection testing and health checks
- Service role authentication for admin operations

**Usage Examples:**
```php
// Load Supabase module
$_CORE->load("Supabase");

// Test connection
if ($_CORE->Supabase->testConnection()) {
    echo "Supabase connected successfully!";
}

// Select data
$items = $_CORE->Supabase->select('items', '*', ['user_id' => $userId]);

// Insert data
$newItem = $_CORE->Supabase->insert('items', [
    'name' => 'New Item',
    'user_id' => $userId
]);

// Update data
$_CORE->Supabase->update('items', ['name' => 'Updated Item'], ['id' => $itemId]);
```

**Development Commands:**
```bash
# Initialize Supabase schema
docker-compose exec db psql -U postgres -d storageboxx -f /docker-entrypoint-initdb.d/01-create-schema.sql

# Enable Row Level Security
docker-compose exec db psql -U postgres -d storageboxx -f /docker-entrypoint-initdb.d/02-enable-rls.sql

# Test Supabase connection
curl -H "apikey: eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImdjaHhweHJyc2lueGN3eG1ranpwIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NTMzMDQyNDMsImV4cCI6MjA2ODg4MDI0M30.TgWUG-a0Wof74REs2vLa7J5dVz-faqZJtOL9WiAaqOA" https://gchxpxrrsinxcwxmkjzp.supabase.co/rest/v1/
```

## API Documentation

API endpoints follow pattern: `/api/module/action`
- Authentication via JWT token in Authorization header
- JSON request/response format
- CORS configurable in `CORE-Config.php`
- Error responses include status codes and messages

## Docker Services and Ports

### Development Access Points
- **Application**: http://localhost (Nginx)
- **Database Admin**: http://localhost:8080 (Adminer)
- **Email Testing**: http://localhost:8025 (MailHog)
- **MySQL**: localhost:3306
- **PostgreSQL**: localhost:5432
- **Redis**: localhost:6379

### Production Deployment
```bash
# Configure SSL and production settings
SSL_ENABLED=true
SSL_DOMAIN=your-domain.com
APP_ENV=production

# Start with production profile
docker-compose --profile production up -d
```

## Supabase Integration Guide

### 1. Setup Supabase Project
1. Create account at [supabase.com](https://supabase.com)
2. Create new project and get credentials
3. Update `.env` with your Supabase URL and keys
4. Initialize schema using provided SQL files

### 2. Row Level Security
- Policies automatically applied for multi-tenant security
- Admin users can manage all data
- Regular users see filtered data based on permissions
- Real-time subscriptions work with RLS policies

### 3. Authentication Integration
- Dual authentication: Custom JWT + Supabase Auth
- Gradual migration path from custom auth to Supabase Auth
- WebAuthn, social logins, and MFA supported

## Performance and Monitoring

### Caching Strategy
- **Redis**: Session storage and API caching
- **OPcache**: PHP opcode caching enabled
- **Nginx**: Static file caching and gzip compression
- **CDN Ready**: Optimized for CDN deployment

### Health Checks
```bash
# Application health
curl http://localhost/health

# Service health
docker-compose ps
docker stats
```

## Browser Compatibility

### Advanced Features
- QR scanning requires HTTPS and camera permissions
- NFC requires compatible browser and device
- WebAuthn for biometric login needs modern browser
- Push notifications require service worker support
- PWA installation available on supported browsers
- **Docker SSL**: Automatic HTTPS in production with Let's Encrypt