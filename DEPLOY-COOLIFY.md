# Storage Boxx - Coolify Deployment Guide

This guide will help you deploy Storage Boxx on your VPS using Coolify with the Docker configuration.

## Prerequisites

- VPS with Coolify installed
- Domain name pointed to your server
- Basic knowledge of environment variables

## Deployment Steps

### 1. Prepare Your Repository

Ensure your repository has the following Docker files (already created):
- `Dockerfile` - Main application container
- `docker-compose.yml` - Complete stack configuration
- `.env.production` - Production environment template

### 2. Create New Project in Coolify

1. Login to your Coolify dashboard
2. Click "New Project" 
3. Choose "Docker Compose" deployment type
4. Connect your Git repository containing Storage Boxx

### 3. Configure Environment Variables

Copy the contents of `.env.production` and update these critical values:

#### Required Changes:
```bash
# Your domain
APP_URL=https://your-domain.com

# Database passwords (generate secure passwords)
DB_PASSWORD=your-secure-mysql-password-32-chars
MYSQL_ROOT_PASSWORD=your-secure-root-password-32-chars
POSTGRES_PASSWORD=your-secure-postgres-password-32-chars
REDIS_PASSWORD=your-secure-redis-password-32-chars

# JWT Secret (generate 64+ character random string)
JWT_SECRET=your-super-secret-jwt-key-minimum-64-characters-for-security

# VAPID Keys (generate at https://vapidkeys.com/)
PUSH_PUBLIC_KEY=your-vapid-public-key
PUSH_PRIVATE_KEY=your-vapid-private-key

# SSL Configuration
SSL_ENABLED=true
SSL_EMAIL=your-email@domain.com
SSL_DOMAIN=your-domain.com
```

#### Optional - Supabase Integration:
```bash
SUPABASE_URL=https://your-project.supabase.co
SUPABASE_ANON_KEY=your-supabase-anon-key
SUPABASE_SERVICE_ROLE_KEY=your-supabase-service-role-key
```

### 4. Configure Coolify Settings

#### Service Configuration:
- **Port**: 80 (application will run on port 80 inside container)
- **Health Check**: `/health.php`
- **Health Check Interval**: 30s
- **Restart Policy**: unless-stopped

#### Domain & SSL:
- Add your domain in Coolify
- Enable SSL (Let's Encrypt)
- Coolify will automatically handle SSL certificates

#### Volumes (Persistent Storage):
Add these volume mappings in Coolify:
```
storage_data -> /var/www/html/storage
```

### 5. Deploy the Application

1. Click "Deploy" in Coolify
2. Monitor the build logs
3. Wait for all services to start (app, mysql, postgres, redis)
4. Check health endpoint: `https://your-domain.com/health.php`

### 6. Initial Setup

Once deployed, visit your application URL:
1. You'll see the Storage Boxx installer
2. Follow the setup wizard
3. Configure your admin account
4. Test all features (QR scanning, NFC, etc.)

## Troubleshooting

### Common Issues:

#### 1. "Bad Gateway" or "No Available Server"
- Check if all containers are running in Coolify
- Verify environment variables are set correctly
- Check health endpoint for specific errors

#### 2. Database Connection Errors
- Verify database passwords match in all services
- Check if MySQL/PostgreSQL containers are healthy
- Ensure database names are consistent

#### 3. Storage Permission Errors
- Check if storage volumes are properly mounted
- Verify container has write permissions to storage directories

#### 4. SSL/Domain Issues
- Ensure domain DNS is pointing to your server
- Check Coolify SSL certificate generation
- Verify APP_URL matches your domain exactly

### Debugging Commands:

Check service status:
```bash
# In Coolify, access container terminal
docker ps
docker logs storage-boxx-app
docker logs storage-boxx-mysql
```

Test health endpoint:
```bash
curl https://your-domain.com/health.php
```

Check storage permissions:
```bash
# Inside app container
ls -la /var/www/html/storage/
```

### Performance Optimization

#### For Production:
1. **Enable OPcache**: Already configured in docker/php/php.ini
2. **Redis Caching**: Configured for sessions and cache
3. **Apache Compression**: Enabled in apache config
4. **Static Asset Caching**: Configured with proper headers

#### Monitoring:
- Use Coolify's built-in monitoring
- Health endpoint provides detailed system status
- Check logs regularly via Coolify dashboard

## Scaling & Maintenance

### Database Backups:
Coolify can automatically backup your databases:
1. Go to your project settings
2. Enable automatic backups
3. Configure backup schedule and retention

### Updates:
1. Push changes to your git repository
2. Coolify will automatically redeploy
3. Monitor health endpoint after deployment

### Scaling:
- For high traffic, consider using external databases (Supabase)
- Enable CDN for static assets
- Use Redis for session clustering

## Security Checklist

✅ Strong passwords for all database services  
✅ JWT secret is 64+ characters random string  
✅ SSL enabled via Coolify  
✅ Firewall configured to allow only necessary ports  
✅ Regular security updates enabled  
✅ File uploads restricted and validated  
✅ Database access limited to application only  

## Support

If you encounter issues:
1. Check the health endpoint for detailed diagnostics
2. Review Coolify deployment logs
3. Verify all environment variables are set correctly
4. Ensure your domain DNS is properly configured

For Storage Boxx specific issues, refer to the main README.md and documentation.