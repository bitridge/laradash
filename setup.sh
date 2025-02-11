#!/bin/bash

# Install Composer Dependencies
composer require spatie/laravel-permission
composer require spatie/laravel-medialibrary
composer require barryvdh/laravel-dompdf
composer require intervention/image

# Install NPM packages
npm install alpinejs dropzone chart.js summernote

# Publish vendor files
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="migrations"

# Run migrations
php artisan migrate:fresh 