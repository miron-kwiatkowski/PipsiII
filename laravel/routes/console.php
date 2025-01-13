<?php

use App\Http\Controllers\GameController;
use Illuminate\Support\Facades\Artisan;

Artisan::comand('reset', function () {
    GameController::reset();
})->purpose('Get a new puzzle for all users')->hourly();
