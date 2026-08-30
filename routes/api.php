<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Middleware\CheckRole;

// Public Routes (Kahit walang token)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected Routes (Kailangan muna ng Sanctum Token)
Route::middleware('auth:sanctum')->group(function () {
    
    Route::post('/logout', [AuthController::class, 'logout']);

    // Admin Only Routes
    Route::middleware(CheckRole::class . ':admin')->group(function () {
        // Pwedeng maglagay ng Admin-only user management routes dito sa hinaharap
    });

    Route::middleware(CheckRole::class . ':admin,project_manager')->group(function()
    {
        route::get('/developers', [AuthController::class, 'getDevelopers']);

    });

    Route::middleware('auth:sanctum')->group(function () {

    // ... [mga dating routes mo tulad ng tasks, projects, etc.] ...

    // [ILAGAY DITO]: Idugtong ito sa loob ng auth:sanctum middleware group
    Route::get('/developers', [AuthController::class, 'index']);
    Route::put('/developers/{id}', [AuthController::class, 'update']);
    Route::delete('/developers/{id}', [AuthController::class, 'destroy']);

});
    //  Admin & Project Manager Routes (Creation, Update, Deletion)
    Route::middleware(CheckRole::class . ':admin,project_manager')->group(function () {
        Route::post('/projects', [ProjectController::class, 'store']);       
        Route::put('/projects/{id}', [ProjectController::class, 'update']);   
        Route::delete('/projects/{id}', [ProjectController::class, 'destroy']);
        Route::post('/tasks', [TaskController::class, 'store']);             
    });

    //  Shared Routes (Admin, Project Manager, & Developer)
    //  INAYOS: Ginawang 'project_manager' imbes na 'manager' para MATCH sa database at AuthController!
    Route::middleware(CheckRole::class . ':admin,project_manager,developer')->group(function () {
        Route::get('/projects', [ProjectController::class, 'index']);         
        Route::get('/tasks', [TaskController::class, 'index']);                
        Route::patch('/tasks/{id}/status', [TaskController::class, 'updateStatus']); // Para sa update status ng Developer
    });

});