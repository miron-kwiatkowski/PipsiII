<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', function () {
    return view('login');
})->name('login');
Route::post('/login',[UserController::class,'login']);

Route::get('/game', function () {
    return view('game');
})->middleware('auth');

Route::get('/users',[UserController::class,'index']);

Route::post('/users/modify',[UserController::class,'modify']);

Route::post('/register',[UserController::class,'register']);

Route::get('/google/redirect', [UserController::class, 'redirectToGoogle'])->name('google.redirect');
Route::get('/google/callback', [UserController::class, 'handleGoogleCallback'])->name('google.callback');

Route::get('/logout',[UserController::class,'logout']);
