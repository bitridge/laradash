#!/bin/bash

# Print commands and their arguments as they are executed
set -x

# Set the application path
APP_PATH="/home/seowork/htdocs/seowork.technotch.dev/laradash"

# Navigate to the application directory
cd $APP_PATH

# Install composer dependencies
composer install --no-dev --optimize-autoloader

# Install and build frontend assets
npm install
npm run build

# Set proper permissions
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
chmod -R 775 public/uploads

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

# Create storage link if not exists
php artisan storage:link

# Clear and cache config
php artisan config:clear
php artisan config:cache

# Restart queue workers if using them
# Uncomment if you're using queues
# supervisorctl restart all

# Restart PHP-FPM (adjust PHP version if different)
systemctl restart php8.2-fpm

# Set proper ownership for entire project
chown -R www-data:www-data $APP_PATH

# Print success message
echo "Post-deployment script completed successfully!"

# Check the Laravel logs for any errors
tail -n 50 storage/logs/laravel.log 