#!/bin/bash

# Exit on error
set -e

# Print commands and their arguments as they are executed
set -x

# Function to log messages
log_message() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1"
}

# Function to handle errors
handle_error() {
    log_message "Error occurred in script at line $1"
    exit 1
}

# Set error handler
trap 'handle_error $LINENO' ERR

# Set the application path
APP_PATH="/home/seowork/htdocs/seowork.technotch.dev/laradash"
LOG_FILE="$APP_PATH/storage/logs/deploy.log"

# Start logging
exec 1> >(tee -a "$LOG_FILE")
exec 2>&1

log_message "Starting deployment process..."

# Navigate to the application directory
cd $APP_PATH || {
    log_message "Failed to change directory to $APP_PATH"
    exit 1
}

# Configure git safe directory
log_message "Configuring git safe directory..."
git config --global --add safe.directory $APP_PATH

# Backup the .env file
log_message "Backing up .env file..."
cp .env .env.backup

# Pull latest changes
log_message "Pulling latest changes from git..."
git stash
git pull origin main
git stash pop || true

# Set proper permissions FIRST (before any Laravel operations)
log_message "Setting base file permissions..."
find $APP_PATH -type f -exec chmod 644 {} \;
find $APP_PATH -type d -exec chmod 755 {} \;

# Create required directories if they don't exist
log_message "Creating required directories..."
mkdir -p $APP_PATH/storage/logs
mkdir -p $APP_PATH/storage/framework/{sessions,views,cache}
mkdir -p $APP_PATH/storage/app/{public,private}
mkdir -p $APP_PATH/bootstrap/cache
mkdir -p $APP_PATH/public/{uploads,storage}

# Set specific permissions for writable directories
log_message "Setting specific directory permissions..."
chown -R www-data:www-data $APP_PATH/storage
chown -R www-data:www-data $APP_PATH/bootstrap/cache
chown -R www-data:www-data $APP_PATH/public/{uploads,storage}
chmod -R 775 $APP_PATH/storage
chmod -R 775 $APP_PATH/bootstrap/cache
chmod -R 775 $APP_PATH/public/{uploads,storage}

# Ensure log files exist and have proper permissions
log_message "Setting up log files..."
touch $APP_PATH/storage/logs/{laravel.log,deploy.log}
chmod 664 $APP_PATH/storage/logs/{laravel.log,deploy.log}
chown www-data:www-data $APP_PATH/storage/logs/{laravel.log,deploy.log}

# Install composer dependencies
log_message "Installing Composer dependencies..."
export COMPOSER_ALLOW_SUPERUSER=1
composer install --no-dev --optimize-autoloader --no-interaction

# Install and build frontend assets
log_message "Installing and building frontend assets..."
export NODE_OPTIONS=--max-old-space-size=4096
npm ci --no-audit
npm run build

# Create storage link if not exists
log_message "Creating storage link..."
php artisan storage:link --force

# Clear all caches
log_message "Clearing application caches..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Run database migrations
log_message "Running database migrations..."
php artisan migrate --force

# Optimize the application
log_message "Optimizing application..."
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Clear OPcache
log_message "Clearing OPcache..."
curl -X GET http://127.0.0.1/opcache-clear.php || true

# Restart PHP-FPM
log_message "Restarting PHP-FPM..."
systemctl restart php8.2-fpm || {
    log_message "Failed to restart PHP-FPM, attempting to reload..."
    systemctl reload php8.2-fpm
}

# Final permission check for critical directories
log_message "Performing final permission checks..."
chown -R www-data:www-data $APP_PATH/storage
chmod -R 775 $APP_PATH/storage
find $APP_PATH/storage -type f -exec chmod 664 {} \;
find $APP_PATH/storage -type d -exec chmod 775 {} \;

# Verify the application is running
log_message "Verifying application status..."
curl -s -o /dev/null -w "%{http_code}" https://seowork.technotch.dev/login || {
    log_message "Warning: Application might not be accessible"
}

# Check for any Laravel errors
log_message "Checking Laravel logs for errors..."
if grep -i "error\|exception" $APP_PATH/storage/logs/laravel.log | tail -n 20; then
    log_message "Warning: Found errors in Laravel log"
else
    log_message "No recent errors found in Laravel log"
fi

log_message "Deployment completed successfully!"

# Print deployment summary
echo "============================================="
echo "Deployment Summary:"
echo "- Git branch: $(git rev-parse --abbrev-ref HEAD)"
echo "- Last commit: $(git log -1 --pretty=%B)"
echo "- Composer packages: $(composer show | wc -l)"
echo "- NPM packages: $(npm list | wc -l)"
echo "- PHP version: $(php -v | head -n 1)"
echo "- Node version: $(node -v)"
echo "=============================================" 