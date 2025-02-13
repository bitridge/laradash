<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Report routes
    Route::get('/reports/create/{project}', [ReportController::class, 'create'])->name('reports.create');
    Route::post('/reports', [ReportController::class, 'generate'])->name('reports.generate');
    Route::get('/reports/{report}/download', [ReportController::class, 'download'])->name('reports.download');
});

// Installer Routes
Route::group(['prefix' => 'install'], function () {
    Route::get('/', [App\Http\Controllers\InstallerController::class, 'index'])->name('installer.index');
    Route::get('/test', [App\Http\Controllers\InstallerController::class, 'test'])->name('installer.test');
    Route::post('/install', [App\Http\Controllers\InstallerController::class, 'install'])->name('installer.install');
});

require __DIR__.'/auth.php';
