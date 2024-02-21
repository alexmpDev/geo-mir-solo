<?php

use App\Http\Controllers\CommentController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MailController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PlaceController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\ReviewController;
use App\Models\Role;
use Egulias\EmailValidator\Parser\Comment;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    Log::info('Loading welcome page');
    return view('welcome');
});

Route::get('/dashboard', function (Request $request) {
    $request->session()->flash('info', 'TEST flash messages');
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

// Mail

Route::get('mail/test', [MailController::class, 'test']);

// Files
// NOTE: FilePolicy with authorizeResource helper

Route::resource('files', FileController::class)
    ->middleware(['auth']);

Route::get('files/{file}/delete', [FileController::class, 'delete'])->name('files.delete')
    ->middleware(['auth']);

// Posts
// NOTE: PostPolicy with authorizeResource and authorize helpers

Route::resource('posts', PostController::class)
    ->middleware(['auth']);

Route::controller(PostController::class)->group(function () {
    Route::get('posts/{post}/delete', 'delete')
        ->name('posts.delete');
    Route::post('/posts/{post}/likes', 'like')
        ->name('posts.like');
    Route::delete('/posts/{post}/likes', 'unlike')
        ->name('posts.unlike');

});
Route::controller(CommentController::class)->group(function () {
    Route::post('/posts/{post}/comments', 'comments')
        ->name('posts.comments');
    Route::delete('/posts/{post}/comments', 'delcomments')
        ->name('posts.comments.delete');
    

});


// Places
// NOTE: PlacePolicy with authorizeResource helper and can middleware

Route::resource('places', PlaceController::class)
    ->middleware(['auth']);

Route::controller(PlaceController::class)->group(function () {
    Route::get('places/{place}/delete', 'delete')
        ->middleware(['auth', 'can:delete,place'])
        ->name('places.delete');
    Route::post('/places/{place}/favs', 'favorite')
        ->middleware(['auth', 'can:favorite,place'])
        ->name('places.favorite');
    Route::delete('/places/{place}/favs', 'unfavorite')
        ->middleware(['auth', 'can:unfavorite,place'])
        ->name('places.unfavorite');
});

Route::post('places/{place}/review', [ReviewController::class, 'review'])
    ->name('places.review');
Route::delete('places/{place}/review', [ReviewController::class, 'unReview'])
    ->name('places.review.delete');
    

// Language
// NOTE: Localization middleware

Route::get('/language/{locale}', [LanguageController::class, 'language'])
    ->name('language');