<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentController;
use Illuminate\Support\Facades\Route;

Route::post('/auth', [AuthController::class, 'auth']);
Route::post('/register', [AuthController::class, 'register']);
Route::get('/comments', [CommentController::class, 'findAll']);
Route::get('/comments/{commentId}', [CommentController::class, 'findById'])->name('comment.show');

Route::middleware(['auth:sanctum'])->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::patch('/changePassword', [AuthController::class, 'changePassword']);

    Route::put('/me', [AuthController::class, 'updateMe'])->name('user.update');

    // ver historico somente dono do recurso
    Route::get('/comments/{commentId}/history', [CommentController::class, 'history']);
    Route::post('/comments', [CommentController::class, 'create']);
    // somente se for o dono do recurso
    Route::put('/comments/{commentId}', [CommentController::class, 'update']);
    // somente se admin
    Route::delete('/comments', [CommentController::class, 'deleteAll']);
    // somente se for o dono do recurso
    Route::delete('/comments/{commentId}', [CommentController::class, 'delete']);
});
