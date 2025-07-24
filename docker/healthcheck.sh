#!/bin/bash
# Storage Boxx Health Check Script

# Check if Apache is running
if ! pgrep apache2 > /dev/null; then
    echo "Apache is not running"
    exit 1
fi

# Check if the application responds
if ! curl -f -s http://localhost/health.php > /dev/null; then
    echo "Application health check failed"
    exit 1
fi

# Check if storage directories are writable
if [ ! -w /var/www/html/storage/cache ]; then
    echo "Storage cache directory is not writable"
    exit 1
fi

if [ ! -w /var/www/html/storage/logs ]; then
    echo "Storage logs directory is not writable"
    exit 1
fi

echo "Health check passed"
exit 0