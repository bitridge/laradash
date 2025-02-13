#!/bin/bash

# Print commands and their arguments as they are executed
set -x

# Set the application path
APP_PATH="/home/seowork/htdocs/seowork.technotch.dev/laradash"

# Navigate to the application directory
cd $APP_PATH

# Configure git safe directory
git config --global --add safe.directory $APP_PATH

# Pull latest changes
git pull

# Set proper permissions FIRST (before any Laravel operations)
find $APP_PATH -type f -exec chmod 644 {} \;
find $APP_PATH -type d -exec chmod 755 {} \;

# Set specific permissions for writable directories
chown -R www-data:www-data $APP_PATH/storage
chown -R www-data:www-data $APP_PATH/bootstrap/cache
chown -R www-data:www-data $APP_PATH/public/uploads
chmod -R 775 $APP_PATH/storage
chmod -R 775 $APP_PATH/bootstrap/cache
chmod -R 775 $APP_PATH/public/uploads

# Ensure log file exists and has proper permissions
touch $APP_PATH/storage/logs/laravel.log
chmod 664 $APP_PATH/storage/logs/laravel.log
chown www-data:www-data $APP_PATH/storage/logs/laravel.log

# Create storage link if not exists (do this early)
php artisan storage:link

# Install composer dependencies
composer install --no-dev --optimize-autoloader

# Install and build frontend assets
npm install
npm run build

# Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Optimize the application
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run database migrations
php artisan migrate --force

# Restart PHP-FPM (adjust PHP version if different)
systemctl restart php8.2-fpm

# Final permission check for critical directories
chown -R www-data:www-data $APP_PATH/storage
chmod -R 775 $APP_PATH/storage
find $APP_PATH/storage -type f -exec chmod 664 {} \;
find $APP_PATH/storage -type d -exec chmod 775 {} \;

# Print success message
echo "Post-deployment script completed successfully!"

# Check the Laravel logs for any errors
tail -n 50 storage/logs/laravel.log 