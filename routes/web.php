<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\TwitterController;
use App\Http\Controllers\MediaBuilderController;
use App\Http\Controllers\WelcomeController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Welcome Screen
Route::get('/', [WelcomeController::class, 'index'])->name('welcome');
Route::get('/process', [WelcomeController::class, 'process'])->name('welcome.process');
Route::get('/parse_tweet', [WelcomeController::class, 'parse_tweet'])->name('welcome.parse_tweet');
Route::get('/user', [WelcomeController::class, 'user'])->name('welcome.user');
Route::get('/manual', [WelcomeController::class, 'manual'])->name('welcome.manual');
Route::get('/tweet', [WelcomeController::class, 'tweet'])->name('welcome.tweet');
Route::get('/followers', [WelcomeController::class, 'followers'])->name('welcome.followers');
Route::get('/mentions', [WelcomeController::class, 'mentions'])->name('welcome.mentions');

// Media generation
Route::get('/make_news_cover', [MediaBuilderController::class, 'make_news_cover'])->name('welcome.make_news_cover');
Route::get('/image_container', [MediaBuilderController::class, 'image_container'])->name('welcome.image_container');


// Socialite routes for login
Route::get('auth/twitter', [TwitterController::class, 'loginwithTwitter'])->name('auth.twitter');
Route::get('permission/twitter/callback', [TwitterController::class, 'cbTwitter'])->name('auth.cbTwitter');

// Dashboard Panel
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});
