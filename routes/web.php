<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

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
    return view('welcome');
});

// OpenAI Test
Route::get('openai-test', [UserController::class, 'OpenAiApiTest'])->name('openai-test');

// socialite routes
Route::get('sign-in-google', [UserController::class, 'google'])->name('user.login.google');
Route::get('auth/google/callback', [UserController::class, 'handleGoogleProviderCallback'])->name('user.google.callback');

Route::get('sign-in-github', [UserController::class, 'github'])->name('user.login.github');
Route::get('auth/github/callback', [UserController::class, 'handleGithubProviderCallback'])->name('user.github.callback');

Route::get('sign-in-tiktok', [UserController::class, 'tiktok'])->name('user.login.tiktok');
Route::get('auth/tiktok/callback', [UserController::class, 'handleTiktokProviderCallback'])->name('user.tiktok.callback');

Route::get('sign-in-facebook', [UserController::class, 'facebook'])->name('user.login.facebook');
Route::get('auth/facebook/callback', [UserController::class, 'handleFacebookProviderCallback'])->name('user.facebook.callback');

Route::get('sign-in-instagram', [UserController::class, 'instagram'])->name('user.login.instagram');
Route::get('auth/instagram/callback', [UserController::class, 'handleInstagramProviderCallback'])->name('user.instagram.callback');

Route::get('coba-access-token', [UserController::class, 'cobaAccessToken'])->name('coba.accessToken.fb');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
