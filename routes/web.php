<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClassroomController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SubmissionController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login',    [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',   [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register',[AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::middleware('auth')->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile',   [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('/classes',             [ClassroomController::class, 'index'])->name('classes.index');
    Route::get('/classes/create',      [ClassroomController::class, 'create'])->name('classes.create');
    Route::post('/classes',            [ClassroomController::class, 'store'])->name('classes.store');
    Route::get('/classes/{classroom}', [ClassroomController::class, 'show'])->name('classes.show');
    Route::get('/classes/{classroom}/edit',  [ClassroomController::class, 'edit'])->name('classes.edit');
    Route::patch('/classes/{classroom}',     [ClassroomController::class, 'update'])->name('classes.update');
    Route::delete('/classes/{classroom}',    [ClassroomController::class, 'destroy'])->name('classes.destroy');
    Route::post('/classes/join',       [ClassroomController::class, 'join'])->name('classes.join');

    Route::get('/assignments/create',              [AssignmentController::class, 'create'])->name('assignments.create');
    Route::post('/assignments',                    [AssignmentController::class, 'store'])->name('assignments.store');
    Route::get('/assignments/{assignment}',        [AssignmentController::class, 'show'])->name('assignments.show');
    Route::delete('/assignments/{assignment}',     [AssignmentController::class, 'destroy'])->name('assignments.destroy');
    Route::post('/assignments/{assignment}/submit',[AssignmentController::class, 'submit'])->name('assignments.submit');

    Route::post('/assignments/{assignment}/comments', [CommentController::class, 'store'])->name('comments.store');

    Route::patch('/submissions/{submission}/grade', [SubmissionController::class, 'grade'])->name('submissions.grade');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/',                              [AdminController::class, 'index'])->name('index');
        Route::patch('/users/{user}/role',           [AdminController::class, 'updateRole'])->name('updateRole');
        Route::patch('/users/{user}/reset-password', [AdminController::class, 'resetPassword'])->name('resetPassword');
    });

});
