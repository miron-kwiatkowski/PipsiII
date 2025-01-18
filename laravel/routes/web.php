<?php

use App\Http\Controllers\GameController;
use App\Http\Controllers\GamesettingsController;
use App\Http\Controllers\GuessController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PuzzleController;
use Illuminate\Console\Scheduling\Schedule;

Route::get('/', function () {
    return view('login');
});

//Users requests
Route::post('/users/index', [UserController::class, 'index'])->name('user.index');
Route::post('/users/register', [UserController::class, 'register'])->name('register');
Route::post('/users/login', [UserController::class, 'login'])->name('login');
Route::post('/users/modify', [UserController::class, 'modify'])->name('user.modify');
Route::post('/users/adminmodify', [UserController::class, 'adminmodify'])->name('admin.modify');
Route::get('/users/google/redirect', [UserController::class, 'redirectToGoogle'])->name('google.redirect');
Route::get('/users/google/callback', [UserController::class, 'handleGoogleCallback'])->name('google.callback');
Route::get('/users/facebook/redirect', [UserController::class, 'redirectToFacebook'])->name('facebook.redirect');
Route::get('/users/facebook/callback', [UserController::class, 'handleFacebookCallback'])->name('facebook.callback');

//Puzzle requests
Route::post('/puzzles/index', [PuzzleController::class, 'index'])->name('puzzle.index');
Route::post('/puzzles/create', [PuzzleController::class, 'create'])->name('create');
Route::post('/puzzles/delete', [PuzzleController::class, 'delete'])->name('delete');
Route::post('/puzzles/edit', [PuzzleController::class, 'edit'])->name('edit');

//Guess requests
Route::post('/guesses/stats', [GuessController::class, 'stats'])->name('stats');
Route::post('/guesses/scoreboard', [GuessController::class, 'scoreboard'])->name('scoreboard');

//Game settings requests
Route::post('/gamesettings/update', [GamesettingsController::class, 'update'])->name('gamesettings.update');
Route::post('/gamesettings/get', [GamesettingsController::class, 'get'])->name('gamesettings.get');

//Game requests
Route::post('/game/get', [GameController::class, 'get'])->name('game.get');
Route::post('/game/guess', [GameController::class, 'guess'])->name('guess');
