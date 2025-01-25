<?php

use App\Http\Controllers\GameController;
use App\Http\Controllers\GamesettingsController;
use App\Http\Controllers\GuessController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PuzzleController;
use Illuminate\Console\Scheduling\Schedule;

Route::get('/api', function () {
    return view('openapi');
});

//Users requests
Route::post('/api/users/index', [UserController::class, 'index'])->name('user.index');
Route::post('/api/users/register', [UserController::class, 'register'])->name('register');
Route::post('/api/users/login', [UserController::class, 'login'])->name('login');
Route::post('/api/users/modify', [UserController::class, 'modify'])->name('user.modify');
Route::post('/api/users/adminmodify', [UserController::class, 'adminmodify'])->name('admin.modify');
Route::get('/api/users/google/redirect', [UserController::class, 'redirectToGoogle'])->name('google.redirect');
Route::get('/api/users/google/callback', [UserController::class, 'handleGoogleCallback'])->name('google.callback');
Route::get('/api/users/facebook/redirect', [UserController::class, 'redirectToFacebook'])->name('facebook.redirect');
Route::get('/api/users/facebook/callback', [UserController::class, 'handleFacebookCallback'])->name('facebook.callback');

//Puzzle requests
Route::post('/api/puzzles/index', [PuzzleController::class, 'index'])->name('puzzle.index');
Route::post('/api/puzzles/create', [PuzzleController::class, 'create'])->name('create');
Route::post('/api/puzzles/delete', [PuzzleController::class, 'delete'])->name('delete');
Route::post('/api/puzzles/edit', [PuzzleController::class, 'edit'])->name('edit');

//Guess requests
Route::post('/api/guesses/stats', [GuessController::class, 'stats'])->name('stats');
Route::post('/api/guesses/scoreboard', [GuessController::class, 'scoreboard'])->name('scoreboard');

//Game settings requests
Route::post('/api/gamesettings/update', [GamesettingsController::class, 'update'])->name('gamesettings.update');
Route::post('/api/gamesettings/get', [GamesettingsController::class, 'get'])->name('gamesettings.get');

//Game requests
Route::post('/api/game/get', [GameController::class, 'get'])->name('game.get');
Route::post('/api/game/guess', [GameController::class, 'guess'])->name('guess');
Route::post('/api/game/forcereset', [GameController::class, 'forceReset'])->name('reset');
