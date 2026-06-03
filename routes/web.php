<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\PublicTaskAssignmentController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\PublicBusinessTaskController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/', function () {
        $user = auth()->user();

        abort_if($user === null, 401);

        return $user->isManager()
            ? redirect()->route('admin.dashboard')
            : redirect()->route('tasks.index');
    })->name('home');

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
    Route::get('/tasks/{task}', [TaskController::class, 'show'])->name('tasks.show');
    Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::patch('/tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.status.update');
    Route::post('/tasks/{task}/comments', [TaskController::class, 'storeComment'])->name('tasks.comments.store');
    Route::patch('/tasks/{task}/toggle', [TaskController::class, 'toggle'])->name('tasks.toggle');
    Route::patch('/tasks/{task}/archive', [TaskController::class, 'archive'])->name('tasks.archive');

    Route::middleware('manager')->group(function (): void {
        Route::get('/admin', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
        Route::patch('/admin/tasks/{task}/assignee', [PublicTaskAssignmentController::class, 'update'])->name('admin.tasks.assignee.update');
        Route::post('/admin/users', [UserManagementController::class, 'store'])->name('admin.users.store');
        Route::patch('/admin/users/{user}', [UserManagementController::class, 'update'])->name('admin.users.update');
        Route::delete('/admin/users/{user}', [UserManagementController::class, 'destroy'])->name('admin.users.destroy');
    });
});

Route::get('/{businessName}', [PublicBusinessTaskController::class, 'create'])
    ->where('businessName', '^(?!login$|logout$|tasks$|admin$).+')
    ->name('public.business.create');
Route::post('/{businessName}', [PublicBusinessTaskController::class, 'store'])
    ->where('businessName', '^(?!login$|logout$|tasks$|admin$).+')
    ->name('public.business.store');
