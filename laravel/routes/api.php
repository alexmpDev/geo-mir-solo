<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\FileController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\TokenController;
use App\Http\Controllers\Api\CommentController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::apiResource('files', FileController::class);

Route::post('files/{file}', [FileController::class, 'update_workaround']);

Route::controller(TokenController::class)->group(function(){
    Route::middleware('auth:sanctum')->group(function(){
        Route::get('user', [TokenController::class, 'user']);
        Route::post('logout', [TokenController::class, 'logout']);
    });
    Route::middleware('guest')->group(function(){
        Route::post('login', [TokenController::class, 'login']);
        Route::post('register', [TokenController::class, 'register']);
    });
});

Route::middleware('auth:sanctum')->group(function(){
    Route::get('posts/comments', [CommentController::class, 'index']);
    Route::apiResource('posts', PostController::class);
    Route::post('posts/{id}/like', [PostController::class, 'like']);
    Route::delete('posts/{id}/unlike', [PostController::class, 'unlike']);
    Route::post('posts/{id}/comments', [CommentController::class, 'comments']);
    Route::delete('posts/{id}/delcomments', [CommentController::class, 'delcomments']);

    Route::post('posts/{post}', [PostController::class, 'update_workaround']);
});


