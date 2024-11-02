<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\WebsitesController;
use App\Http\Controllers\WordPressController;  // Add this line
use App\Http\Controllers\PluginsController;    // Add this line
use App\Http\Controllers\ThemesController;     // Add this line

// Route for home page (unchanged)
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin'       => Route::has('login'),
        'canRegister'    => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion'     => PHP_VERSION,
    ]);
});

// Group routes that require authentication and verification
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {

    // Dashboard route (unchanged)
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    // Resource routes for websites (unchanged)
    Route::resource('websites', WebsitesController::class);

    // Fetch WordPress plugins and themes for a website
    Route::get('/websites/{website}/wordpress-data', [WordPressController::class, 'fetchPluginsAndThemes'])
        ->name('wordpress.data');

    // Routes for managing plugins
    Route::post('/plugins/{website}/{slug}/enable', [PluginsController::class, 'enable'])
        ->name('plugins.enable');
    Route::post('/websites/{website}/plugins/disable/{slug}', [PluginsController::class, 'disable'])
        ->name('plugins.disable');

    // Routes for managing themes
    Route::delete('/websites/{website}/themes/delete/{slug}', [ThemesController::class, 'delete'])
        ->name('themes.delete');
});