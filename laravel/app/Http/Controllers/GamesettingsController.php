<?php

namespace App\Http\Controllers;

use App\Models\gamesettings;
use Illuminate\Http\Request;

class GamesettingsController extends Controller
{
    //Aktualizowanie ustawien gry, request:
    //changedate - data utworzenia ustawien (date)
    //timereset - godzina resetu zagadki (time)
    //mindistance - minimalny dystans zeby otrzymac maksimum punktow (number)
    //maxdistance - maksymalny dystans zeby otrzymac punkty (number)
    //pointstoqualify - ilosc punktow aby wygrac (number)
    //leaderboarddays - ile dni jest zliczanych do tablicy wynikow
    public function update(Request $request) {
        $newest = gamesettings::all()->sortByDesc('ID')->first();
        if (!$newest) {
            GamesettingsController::default();
            $newest = gamesettings::all()->sortByDesc('ID')->first();
        }
        $new = new gamesettings();
        $new->ChangeDate = date('Y/m/d', time());
        if(isset($request->timereset)) {$new->TimeReset = $request->timereset;} else {$new->TimeReset = $newest->TimeReset;}
        if(isset($request->mindistance)) {$new->MinDistance = $request->mindistance;} else {$new->MinDistance = $newest->MinDistance;}
        if(isset($request->maxdistance)) {$new->MaxDistance = $request->maxdistance;} else {$new->MaxDistance = $newest->MaxDistance;}
        if(isset($request->pointstoqualify)) {$new->PointsToQualify = $request->pointstoqualify;} else {$new->PointsToQualify = $newest->PointsToQualify;}
        if(isset($request->leaderboarddays)) {$new->LeaderboardDays = $request->leaderboarddays;} else {$new->LeaderboardDays = $newest->LeaderboardDays;}
        $new->save();
        return response()->json([$new]);
    }

    //Utworzenie domyslnych ustawien w przypadku braku danych
    public function default() {
        $default = new gamesettings();
        $default->ChangeDate = date('Y/m/d', time());
        $default->TimeReset = '10:00:00';
        $default->MinDistance = 1;
        $default->MaxDistance = 10;
        $default->PointsToQualify = 100;
        $default->LeaderboardDays = 10;
        $default->save();
    }

    //Wziecie ustawien
    public function get() {
        $newest = gamesettings::all()->sortByDesc('ID')->first();
        if (!$newest) {
            GamesettingsController::default();
            $newest = gamesettings::all()->sortByDesc('ID')->first();
        }
        return response()->json([$newest]);
    }
}
