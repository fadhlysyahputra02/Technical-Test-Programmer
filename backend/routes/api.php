<?php

use App\Http\Controllers\Api\ApplicationController;
use App\Http\Controllers\Api\ApplicationDocumentController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\ReviewController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Public routes (no auth required)
*/
Route::get('/health', function () {
    $dbOk = false;
    $cacheOk = false;
    $storageOk = false;

    try {
        \Illuminate\Support\Facades\DB::connection()->getPdo();
        $dbOk = true;
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('Healthcheck DB fail: ' . $e->getMessage());
    }

    try {
        \Illuminate\Support\Facades\Cache::put('healthcheck_test', 'ok', 10);
        $cacheOk = \Illuminate\Support\Facades\Cache::get('healthcheck_test') === 'ok';
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('Healthcheck Cache fail: ' . $e->getMessage());
    }

    try {
        \Illuminate\Support\Facades\Storage::disk('local')->put('healthcheck_test.txt', 'ok');
        $storageOk = \Illuminate\Support\Facades\Storage::disk('local')->get('healthcheck_test.txt') === 'ok';
        \Illuminate\Support\Facades\Storage::disk('local')->delete('healthcheck_test.txt');
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('Healthcheck Storage fail: ' . $e->getMessage());
    }

    $allOk = $dbOk && $cacheOk && $storageOk;

    return response()->json([
        'status' => $allOk ? 'healthy' : 'unhealthy',
        'details' => [
            'database' => $dbOk ? 'ok' : 'fail',
            'cache' => $cacheOk ? 'ok' : 'fail',
            'storage' => $storageOk ? 'ok' : 'fail',
        ]
    ], $allOk ? 200 : 500);
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| Protected routes (require auth:sanctum)
*/
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Master Data Roles (Cached 1 hour)
    Route::get('/roles', function () {
        return \Illuminate\Support\Facades\Cache::remember('roles:master', 3600, function () {
            return \Spatie\Permission\Models\Role::select(['id', 'name'])->get();
        });
    })->name('roles.master');

    // Dashboard (role-aware)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');


    // Project Management
    Route::apiResource('projects', ProjectController::class);

    // Application Management (Pemohon)
    Route::apiResource('applications', ApplicationController::class)->only(['index', 'store', 'show', 'update']);
    Route::post('applications/{application}/submit', [ApplicationController::class, 'submit'])
        ->name('applications.submit');

    // Document Management
    Route::get('applications/{application}/documents', [ApplicationDocumentController::class, 'index'])
        ->name('applications.documents.index');
    Route::post('applications/{application}/documents', [ApplicationDocumentController::class, 'store'])
        ->name('applications.documents.store');
    Route::delete('applications/{application}/documents/{document}', [ApplicationDocumentController::class, 'destroy'])
        ->name('applications.documents.destroy');

    // Document Download (standalone route for clean URL)
    Route::get('documents/{document}/download', [ApplicationDocumentController::class, 'download'])
        ->name('documents.download');

    // Reviewer – Application List
    Route::get('reviewer/applications', [ReviewController::class, 'applicationList'])
        ->name('reviewer.applications');

    // Reviews & Histories
    Route::post('applications/{application}/reviews', [ReviewController::class, 'store'])
        ->name('applications.reviews.store');
    Route::get('applications/{application}/histories', [ReviewController::class, 'histories'])
        ->name('applications.histories');
});
