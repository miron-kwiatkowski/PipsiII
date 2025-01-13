<?php

use App\Http\Controllers\GameController;
use App\Http\Controllers\GamesettingsController;
use App\Http\Controllers\GuessController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PuzzleController;
use Illuminate\Console\Scheduling\Schedule;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/guesses/create', function () {
    return view('guess');
});
Route::get('/gamesettings', function () {
    return view('gamesettings');
});
Route::post('/guesses/create', [GuessController::class, 'create']);
Route::get('/guesses/{id}/stats', [GuessController::class, 'stats']);
Route::post('/gamesettings/change', [GamesettingsController::class, 'update']);
Route::get('/gamesettings/get', [GamesettingsController::class, 'get']);
Route::get('/guesses/get/{id}', [GuessController::class, 'getfinished']);
Route::get('/puzzles/array', [PuzzleController::class, 'getpuzzlesid']);
Route::get('/puzzles', [PuzzleController::class, 'index']);
Route::get('/generator', [GameController::class, 'reset']);
Route::get('/game/get/{userid}', [GameController::class, 'get']);
Route::post('/game', [GameController::class, 'guess']);
Route::get('/scoreboard', [GuessController::class, 'scoreboard']);

//users, login and register routes

Route::get('/login', function () {
    return view('login');
})->name('login');
Route::post('/login',[UserController::class,'login']);
Route::get('/users',[UserController::class,'index']);
Route::post('/users/{id}/modify',[UserController::class,'modify']);
Route::post('/register',[UserController::class,'register']);
Route::get('/google/redirect', [UserController::class, 'redirectToGoogle'])->name('google.redirect');
Route::get('/google/callback', [UserController::class, 'handleGoogleCallback'])->name('google.callback');
Route::get('/logout',[UserController::class,'logout']);

//admin routes
Route::get('/puzzles/create', function () {
    return view('createpuzzle');
})->name('createpuzzle');
Route::post('/puzzles/create',[PuzzleController::class,'create']);
Route::get('/puzzles/{id}/delete',[PuzzleController::class,'delete']);
Route::post('/puzzles/{id}/edit',[PuzzleController::class,'edit']);
Route::post('/users/{id}/manage',[UserController::class,'adminmodify']);

//game routes

Route::get('/game', function () {
    return view('game');
});
    //->middleware('auth');
