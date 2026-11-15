<?php

use App\Http\Controllers\Admin\AiModelController;
use App\Http\Controllers\Admin\ArabicBenchmarkController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\BlogArticleController;
use App\Http\Controllers\Admin\BlogCategoryController;
use App\Http\Controllers\Admin\CatalogSyncController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProviderController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ModelController;
use App\Http\Controllers\SeoController;
use Illuminate\Support\Facades\Route;

// Public Idraak Lab landing page with the live Arabic model leaderboard
Route::get('/', [HomeController::class, 'index'])->name('home');

// Public SEO profile page for every individual model
Route::get('/models/{model:slug}', [ModelController::class, 'show'])->name('models.show');

// Public Arabic AI Research Blog & Technical Studies
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');

// Search engine discovery endpoints
Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('seo.sitemap');
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('seo.robots');

Route::get('/login', function () {
    return redirect()->route('admin.login');
})->name('login');

// Admin Authentication Routes
Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
    });

    // Protected Administrative Routes
    Route::middleware(['auth', 'admin'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // AI Models & Pricing Matrix
        Route::get('/models', [AiModelController::class, 'index'])->name('models.index');
        Route::get('/models/{model}', [AiModelController::class, 'show'])->name('models.show');
        Route::get('/models/{model}/offerings', [AiModelController::class, 'offerings'])->name('models.offerings');
        Route::post('/models/{model}/toggle-active', [AiModelController::class, 'toggleActive'])->name('models.toggle-active');
        Route::post('/models/{model}/toggle-featured', [AiModelController::class, 'toggleFeatured'])->name('models.toggle-featured');

        // Arabic Benchmark Lab & Evaluation Suite
        Route::get('/benchmarks/arabic', [ArabicBenchmarkController::class, 'index'])->name('benchmarks.arabic');
        Route::post('/benchmarks/arabic/run', [ArabicBenchmarkController::class, 'run'])->name('benchmarks.arabic.run');
        Route::get('/benchmarks/arabic/runs/{run}', [ArabicBenchmarkController::class, 'showRun'])->name('benchmarks.arabic.runs.show');
        Route::delete('/benchmarks/arabic/runs/{run}', [ArabicBenchmarkController::class, 'destroyRun'])->name('benchmarks.arabic.runs.destroy');

        // Providers Directory
        Route::get('/providers', [ProviderController::class, 'index'])->name('providers.index');

        // User Management
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

        // Real-Time Catalog Sync Pipeline
        Route::post('/sync', [CatalogSyncController::class, 'sync'])->name('sync');

        // Research Blog Categories & Articles CMS
        Route::prefix('blog')->name('blog.')->group(function () {
            Route::get('/categories', [BlogCategoryController::class, 'index'])->name('categories.index');
            Route::post('/categories', [BlogCategoryController::class, 'store'])->name('categories.store');
            Route::put('/categories/{category}', [BlogCategoryController::class, 'update'])->name('categories.update');
            Route::delete('/categories/{category}', [BlogCategoryController::class, 'destroy'])->name('categories.destroy');

            Route::get('/articles', [BlogArticleController::class, 'index'])->name('articles.index');
            Route::get('/articles/create', [BlogArticleController::class, 'create'])->name('articles.create');
            Route::post('/articles', [BlogArticleController::class, 'store'])->name('articles.store');
            Route::post('/articles/seed-defaults', [BlogArticleController::class, 'seedDefaults'])->name('articles.seed');
            Route::get('/articles/{article}/edit', [BlogArticleController::class, 'edit'])->name('articles.edit');
            Route::put('/articles/{article}', [BlogArticleController::class, 'update'])->name('articles.update');
            Route::delete('/articles/{article}', [BlogArticleController::class, 'destroy'])->name('articles.destroy');
            Route::post('/articles/{article}/toggle-published', [BlogArticleController::class, 'togglePublished'])->name('articles.toggle-published');
            Route::post('/articles/{article}/toggle-featured', [BlogArticleController::class, 'toggleFeatured'])->name('articles.toggle-featured');
        });
    });
});
