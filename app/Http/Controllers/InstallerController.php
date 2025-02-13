<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class InstallerController extends Controller
{
    public function index()
    {
        $installPath = base_path('install/index.php');
        
        if (!File::exists($installPath)) {
            abort(404, 'Installer not found');
        }
        
        // Start output buffering
        ob_start();
        
        // Set the working directory to the install folder
        chdir(base_path('install'));
        
        // Include the installer file
        try {
            include $installPath;
            $content = ob_get_clean();
            return response($content);
        } catch (\Exception $e) {
            ob_end_clean();
            return response()->json([
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    public function test()
    {
        $installPath = base_path('install/test-install.php');
        
        if (!File::exists($installPath)) {
            abort(404, 'Test installer not found');
        }
        
        // Start output buffering
        ob_start();
        
        // Set the working directory to the install folder
        chdir(base_path('install'));
        
        // Include the test file
        try {
            include $installPath;
            $content = ob_get_clean();
            return response($content);
        } catch (\Exception $e) {
            ob_end_clean();
            return response()->json([
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    public function install()
    {
        $installPath = base_path('install/install.php');
        
        if (!File::exists($installPath)) {
            abort(404, 'Installer not found');
        }
        
        // Start output buffering
        ob_start();
        
        // Set the working directory to the install folder
        chdir(base_path('install'));
        
        // Include the installer file
        try {
            include $installPath;
            $content = ob_get_clean();
            return response($content);
        } catch (\Exception $e) {
            ob_end_clean();
            return response()->json([
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }
} 