<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\FileController;
use App\Http\Controllers\Api\PlaceController;
use App\Http\Controllers\Api\TokenController;


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
    Route::get('places', [PlaceController::class, 'index']);
    Route::post('places', [PlaceController::class, 'store']);
    Route::post('places/show', [PlaceController::class, 'show']);
    Route::post('/places/{place}/favs', [PlaceController::class, 'favorite']);
    Route::delete('/places/{place}/favs', [PlaceController::class, 'unfavorite']);
});
