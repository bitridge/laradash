<?php

class Installer
{
    private $config;
    private $baseDir;
    private $errors = [];
    private $success = [];

    public function __construct()
    {
        $this->config = json_decode(file_get_contents(__DIR__ . "/config.json"), true);
        $this->baseDir = dirname(__DIR__);
    }

    public function install()
    {
        try {
            $this->checkRequirements()
                 ->runComposerInstall()
                 ->setupEnvFile()
                 ->configureDatabaseConnection()
                 ->generateAppKey()
                 ->setupStorage()
                 ->runMigrations()
                 ->displayResults();
        } catch (Exception $e) {
            echo "\nError: " . $e->getMessage() . "\n";
            exit(1);
        }
    }

    private function checkRequirements()
    {
        // Run the test script first
        require_once 'test-install.php';
        $tester = new InstallationTester();
        ob_start();
        $tester->runTests();
        $testOutput = ob_get_clean();
        
        if (strpos($testOutput, 'Installation cannot proceed') !== false) {
            throw new Exception("Requirements check failed.\n" . $testOutput);
        }
        
        $this->success[] = "All requirements checked successfully";
        return $this;
    }

    private function runComposerInstall()
    {
        echo "\nRunning composer install...\n";
        exec('cd ' . escapeshellarg($this->baseDir) . ' && composer install --no-interaction 2>&1', $output, $returnVar);
        
        if ($returnVar !== 0) {
            throw new Exception("Composer install failed:\n" . implode("\n", $output));
        }
        
        $this->success[] = "Composer dependencies installed successfully";
        return $this;
    }

    private function setupEnvFile()
    {
        $envExample = $this->baseDir . '/.env.example';
        $envFile = $this->baseDir . '/.env';
        
        if (!file_exists($envExample)) {
            throw new Exception(".env.example file not found");
        }
        
        if (!copy($envExample, $envFile)) {
            throw new Exception("Failed to create .env file");
        }
        
        // Set basic configurations
        $envContent = file_get_contents($envFile);
        $envContent = preg_replace('/APP_ENV=.*/', 'APP_ENV=production', $envContent);
        $envContent = preg_replace('/APP_DEBUG=.*/', 'APP_DEBUG=false', $envContent);
        
        if (!file_put_contents($envFile, $envContent)) {
            throw new Exception("Failed to update .env file");
        }
        
        $this->success[] = "Environment file created and configured";
        return $this;
    }

    private function configureDatabaseConnection()
    {
        echo "\nConfiguring database connection...\n";
        
        // Get database configuration from user
        echo "Please enter your database configuration:\n";
        $dbHost = $this->prompt("Database host (default: localhost)", "localhost");
        $dbPort = $this->prompt("Database port (default: 3306)", "3306");
        $dbName = $this->prompt("Database name", null, true);
        $dbUser = $this->prompt("Database username", null, true);
        $dbPass = $this->prompt("Database password", null, true);
        
        // Test the connection
        try {
            $dsn = "mysql:host={$dbHost};port={$dbPort}";
            $pdo = new PDO($dsn, $dbUser, $dbPass);
            
            // Create database if it doesn't exist
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . $dbName . "`");
            
            // Update .env file with database configuration
            $envFile = $this->baseDir . '/.env';
            $envContent = file_get_contents($envFile);
            $envContent = preg_replace('/DB_HOST=.*/', "DB_HOST={$dbHost}", $envContent);
            $envContent = preg_replace('/DB_PORT=.*/', "DB_PORT={$dbPort}", $envContent);
            $envContent = preg_replace('/DB_DATABASE=.*/', "DB_DATABASE={$dbName}", $envContent);
            $envContent = preg_replace('/DB_USERNAME=.*/', "DB_USERNAME={$dbUser}", $envContent);
            $envContent = preg_replace('/DB_PASSWORD=.*/', "DB_PASSWORD={$dbPass}", $envContent);
            
            if (!file_put_contents($envFile, $envContent)) {
                throw new Exception("Failed to update database configuration in .env file");
            }
            
            $this->success[] = "Database configuration completed successfully";
            return $this;
            
        } catch (PDOException $e) {
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }

    private function generateAppKey()
    {
        echo "\nGenerating application key...\n";
        exec('cd ' . escapeshellarg($this->baseDir) . ' && php artisan key:generate --force 2>&1', $output, $returnVar);
        
        if ($returnVar !== 0) {
            throw new Exception("Failed to generate application key:\n" . implode("\n", $output));
        }
        
        $this->success[] = "Application key generated successfully";
        return $this;
    }

    private function setupStorage()
    {
        // Create storage link
        echo "\nCreating storage link...\n";
        exec('cd ' . escapeshellarg($this->baseDir) . ' && php artisan storage:link 2>&1', $output, $returnVar);
        
        if ($returnVar !== 0) {
            throw new Exception("Failed to create storage link:\n" . implode("\n", $output));
        }
        
        // Set directory permissions
        foreach ($this->config['permissions'] as $path => $permission) {
            $fullPath = $this->baseDir . '/' . rtrim($path, '/');
            if (!file_exists($fullPath)) {
                mkdir($fullPath, octdec($permission), true);
            }
            chmod($fullPath, octdec($permission));
        }
        
        $this->success[] = "Storage setup completed successfully";
        return $this;
    }

    private function runMigrations()
    {
        echo "\nRunning database migrations...\n";
        exec('cd ' . escapeshellarg($this->baseDir) . ' && php artisan migrate --force 2>&1', $output, $returnVar);
        
        if ($returnVar !== 0) {
            throw new Exception("Database migration failed:\n" . implode("\n", $output));
        }
        
        $this->success[] = "Database migrations completed successfully";
        return $this;
    }

    private function displayResults()
    {
        echo "\n=== Installation Results ===\n\n";
        
        foreach ($this->success as $message) {
            echo "✓ " . $message . "\n";
        }
        
        echo "\nInstallation completed successfully!\n";
        echo "Please configure your web server to point to the public directory.\n";
        echo "You can now log in to your application.\n";
    }

    private function prompt($message, $default = null, $required = false)
    {
        do {
            echo $message . ($default ? " [{$default}]" : "") . ": ";
            $handle = fopen("php://stdin", "r");
            $value = trim(fgets($handle));
            fclose($handle);
            
            if (empty($value) && $default !== null) {
                $value = $default;
            }
            
            if (empty($value) && $required) {
                echo "This field is required.\n";
                continue;
            }
            
            break;
        } while (true);
        
        return $value;
    }
}

// Run the installation
$installer = new Installer();
$installer->install(); 