<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\SubtaskController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('layouts.app');
});

Route::resource('departments', DepartmentController::class);
Route::patch('departments/{department}/active', [DepartmentController::class, 'toggleActive'])->name('departments.active.toggle');
Route::resource('categories', CategoryController::class);
Route::resource('tags', TagController::class);
Route::resource('users', UserController::class);
Route::resource('tasks', TaskController::class);

// Sidebar quick actions (Completed cannot be set here).
Route::patch('tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.status.update');
Route::patch('tasks/{task}/priority', [TaskController::class, 'updatePriority'])->name('tasks.priority.update');

Route::resource('subtasks', SubtaskController::class)->only([
    'store', 'update', 'destroy',
]);
// Checkbox toggle; observer then syncs parent task completion.
Route::patch('subtasks/{subtask}/toggle', [SubtaskController::class, 'toggle'])->name('subtasks.toggle');
