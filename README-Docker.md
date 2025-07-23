# Storage Boxx Docker Setup

This guide provides instructions for running Storage Boxx with Docker, including Supabase integration and modern development tools.

## Quick Start

### 1. Clone and Setup
```bash
git clone https://github.com/code-boxx/Storage-Boxx-PHP-Inventory-System.git
cd Storage-Boxx-PHP-Inventory-System
cp .env.example .env
```

### 2. Configure Environment
Edit the `.env` file with your settings:

```bash
# Basic Configuration
APP_NAME="Storage Boxx"
APP_ENV=development
APP_URL=http://localhost

# Database (MySQL Legacy)
DB_HOST=mysql
DB_NAME=storageboxx
DB_USER=storage
DB_PASSWORD=storage123

# Supabase Configuration
SUPABASE_URL=https://your-project-id.supabase.co
SUPABASE_ANON_KEY=your-anon-key
SUPABASE_SERVICE_ROLE_KEY=your-service-role-key

# Redis Configuration
REDIS_PASSWORD=redis123

# JWT Configuration
JWT_SECRET=your-super-secret-jwt-key-change-this-in-production
```

### 3. Start Services
```bash
# Start all services
docker-compose up -d

# View logs
docker-compose logs -f

# Stop services
docker-compose down
```

## Services Overview

### Core Services
- **app**: PHP 8.2-FPM with required extensions
- **nginx**: Web server with SSL support
- **mysql**: Legacy MySQL database
- **db**: PostgreSQL for Supabase compatibility
- **redis**: Caching and session storage

### Development Services
- **adminer**: Database management interface (http://localhost:8080)
- **mailhog**: Email testing (http://localhost:8025)

### Production Services
- **certbot**: SSL certificate management (production profile)

## Access Points

| Service | URL | Purpose |
|---------|-----|---------|
| Application | http://localhost | Main application |
| Adminer | http://localhost:8080 | Database management |
| MailHog | http://localhost:8025 | Email testing |

## Development Workflow

### 1. File Changes
All files are mounted as volumes, so changes are reflected immediately.

### 2. Database Access
```bash
# MySQL
docker-compose exec mysql mysql -u storage -p storageboxx

# PostgreSQL
docker-compose exec db psql -U postgres -d storageboxx
```

### 3. Application Logs
```bash
# PHP-FPM logs
docker-compose logs app

# Nginx logs
docker-compose logs nginx

# All logs
docker-compose logs
```

### 4. Redis CLI
```bash
docker-compose exec redis redis-cli -a redis123
```

## Supabase Integration

### 1. Setup Supabase Project
1. Create account at https://supabase.com
2. Create new project
3. Get project URL and API keys
4. Update `.env` file with your credentials

### 2. Initialize Database Schema
```bash
# Run PostgreSQL schema initialization
docker-compose exec db psql -U postgres -d storageboxx -f /docker-entrypoint-initdb.d/01-create-schema.sql
```

### 3. Enable Row Level Security
```bash
# Apply RLS policies
docker-compose exec db psql -U postgres -d storageboxx -f /docker-entrypoint-initdb.d/02-enable-rls.sql
```

## Production Deployment

### 1. Environment Configuration
```bash
# Update .env for production
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Enable SSL
SSL_ENABLED=true
SSL_EMAIL=your-email@domain.com
SSL_DOMAIN=your-domain.com
```

### 2. SSL Certificate Setup
```bash
# Run certbot for initial certificate
docker-compose --profile production run --rm certbot certonly \
  --webroot \
  --webroot-path=/var/www/certbot \
  --email your-email@domain.com \
  --agree-tos \
  --no-eff-email \
  -d your-domain.com
```

### 3. Production Start
```bash
# Start with production profile
docker-compose --profile production up -d
```

## Security Considerations

### 1. Environment Variables
- Change all default passwords
- Use strong JWT secrets
- Configure proper CORS settings
- Set secure Redis passwords

### 2. Database Security
- Use non-root database users
- Enable SSL connections for production
- Regular backups

### 3. Application Security
- Keep PHP and dependencies updated
- Enable HTTPS in production
- Configure proper file permissions

## Troubleshooting

### Common Issues

#### Permission Errors
```bash
# Fix storage permissions
sudo chown -R www-data:www-data storage/
sudo chmod -R 755 storage/
```

#### Database Connection Issues
```bash
# Check database status
docker-compose ps
docker-compose logs mysql
docker-compose logs db

# Restart databases
docker-compose restart mysql db
```

#### Redis Connection Issues
```bash
# Check Redis status
docker-compose logs redis

# Test Redis connection
docker-compose exec redis redis-cli -a redis123 ping
```

### Health Checks
```bash
# Application health
curl http://localhost/health

# Database health
docker-compose exec mysql mysqladmin ping -h localhost -u storage -p
docker-compose exec db pg_isready -U postgres -d storageboxx
```

## Backup and Restore

### Database Backup
```bash
# MySQL backup
docker-compose exec mysql mysqldump -u storage -p storageboxx > backup.sql

# PostgreSQL backup
docker-compose exec db pg_dump -U postgres storageboxx > backup_pg.sql
```

### Database Restore
```bash
# MySQL restore
docker-compose exec -T mysql mysql -u storage -p storageboxx < backup.sql

# PostgreSQL restore
docker-compose exec -T db psql -U postgres storageboxx < backup_pg.sql
```

## Monitoring

### Container Status
```bash
# Check container health
docker-compose ps
docker stats

# Resource usage
docker system df
```

### Application Monitoring
- Check logs regularly: `docker-compose logs -f`
- Monitor disk usage in storage directories
- Watch for PHP errors in application logs

## Performance Optimization

### Production Optimizations
1. Enable OPcache in PHP
2. Use Redis for sessions and caching
3. Configure Nginx caching
4. Optimize database indexes
5. Use CDN for static assets

### Development Optimizations
1. Mount only necessary directories
2. Use cached Docker builds
3. Optimize Docker images
4. Use .dockerignore appropriately