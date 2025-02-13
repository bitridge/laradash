<?php

require_once './InstallerService/GetResult.php';
require_once './InstallerService/PhpInfoHelper.php';
require_once './InstallerService/MysqlRequirement.php';
require_once './InstallerService/PermissionHelper.php';

class InstallationTester
{
    private $config;
    private $errors = [];
    private $success = [];

    public function __construct()
    {
        $this->config = json_decode(file_get_contents(__DIR__ . "/config.json"), true);
    }

    public function runTests()
    {
        $this->testPhpVersion()
             ->testPhpExtensions()
             ->testPhpOptions()
             ->testMysqlVersion()
             ->testDirectoryPermissions()
             ->testComposerAvailability()
             ->displayResults();
    }

    private function testPhpVersion()
    {
        $currentVersion = phpversion();
        if (version_compare($currentVersion, $this->config['core']['min_php_version'], '>=')) {
            $this->success[] = "PHP Version: {$currentVersion} ✓";
        } else {
            $this->errors[] = "PHP Version {$currentVersion} is lower than required version {$this->config['core']['min_php_version']}";
        }
        return $this;
    }

    private function testPhpExtensions()
    {
        foreach ($this->config['requirements']['php'] as $extension) {
            if (extension_loaded($extension)) {
                $this->success[] = "PHP Extension {$extension} ✓";
            } else {
                $this->errors[] = "Required PHP extension {$extension} is missing";
            }
        }
        return $this;
    }

    private function testPhpOptions()
    {
        foreach ($this->config['php_options'] as $option => $required_value) {
            $current_value = ini_get($option);
            if ($current_value == $required_value) {
                $this->success[] = "PHP Option {$option} = {$current_value} ✓";
            } else {
                $this->errors[] = "PHP Option {$option} = {$current_value}, required: {$required_value}";
            }
        }
        return $this;
    }

    private function testMysqlVersion()
    {
        try {
            $pdo = new PDO("mysql:host=localhost", "root", "");
            $version = $pdo->query('select version()')->fetchColumn();
            if (version_compare($version, $this->config['core']['min_mysql_version'], '>=')) {
                $this->success[] = "MySQL Version: {$version} ✓";
            } else {
                $this->errors[] = "MySQL Version {$version} is lower than required version {$this->config['core']['min_mysql_version']}";
            }
        } catch (PDOException $e) {
            $this->errors[] = "Could not connect to MySQL: " . $e->getMessage();
        }
        return $this;
    }

    private function testDirectoryPermissions()
    {
        $baseDir = dirname(__DIR__); // Parent directory of install folder
        foreach ($this->config['permissions'] as $path => $required_permission) {
            $fullPath = $baseDir . '/' . rtrim($path, '/');
            if (file_exists($fullPath)) {
                $currentPermission = substr(sprintf('%o', fileperms($fullPath)), -3);
                if ($currentPermission >= $required_permission) {
                    $this->success[] = "Permission for {$path}: {$currentPermission} ✓";
                } else {
                    $this->errors[] = "Incorrect permission for {$path}: current={$currentPermission}, required={$required_permission}";
                }
            } else {
                $this->errors[] = "Path does not exist: {$path}";
            }
        }
        return $this;
    }

    private function testComposerAvailability()
    {
        exec('composer --version', $output, $returnVar);
        if ($returnVar === 0) {
            $this->success[] = "Composer is installed: " . $output[0] . " ✓";
        } else {
            $this->errors[] = "Composer is not installed or not accessible";
        }
        return $this;
    }

    private function displayResults()
    {
        echo "\n=== Installation Requirements Test Results ===\n\n";
        
        echo "Success:\n";
        echo "--------\n";
        foreach ($this->success as $message) {
            echo "✓ " . $message . "\n";
        }

        if (!empty($this->errors)) {
            echo "\nErrors:\n";
            echo "-------\n";
            foreach ($this->errors as $error) {
                echo "✗ " . $error . "\n";
            }
            echo "\nInstallation cannot proceed. Please fix the above errors.\n";
        } else {
            echo "\nAll requirements met! You can proceed with the installation.\n";
            echo "Next steps:\n";
            echo "1. Run: composer install\n";
            echo "2. Copy .env.example to .env and configure your database\n";
            echo "3. Run: php artisan key:generate\n";
            echo "4. Run: php artisan migrate\n";
            echo "5. Run: php artisan storage:link\n";
        }
    }
}

// Run the tests
$tester = new InstallationTester();
$tester->runTests(); 