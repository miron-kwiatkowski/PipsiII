<?php

namespace App\Http\Controllers;

use App\Models\gamesettings;
use App\Models\users;
use Illuminate\Http\Request;

class GamesettingsController extends Controller
{
    //Aktualizowanie ustawien gry (tylko dla administratora), wszystkie nieustawione zmienne skopiuja sie z poprzednich ustawien, request wymaga:
    // 'timereset' - godzina resetu zagadki (opcjonalne)
    // 'mindistance' - minimalny dystans zeby otrzymac maksimum punktow (opcjonalne)
    // 'maxdistance' - maksymalny dystans zeby otrzymac punkty (opcjonalne)
    // 'pointstoqualify' - ilosc punktow aby wygrac (opcjonalne)
    // 'leaderboarddays' - ile dni jest zliczanych do tablicy wynikow (opcjonalne)
    public function update(Request $request) {
        if (isset($request->access_token)) {
            if (users::where('_token', $request->access_token)->value('IsAdmin')) {
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
                return response([
                    'data' => $new,
                    'message' => 'Settings saved'
                ], 200);
            }
            return response([
                'data' => 'null',
                'message' => 'Unauthorized'
            ], 401);
        }
        return response([
            'data' => 'null',
            'message' => 'Unauthorized'
        ], 401);
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

    //Wziecie ustawien, request wymaga:
    // 'access_token'
    public function get(Request $request) {
        if (isset($request->access_token)) {
            if (users::where('_token', $request->access_token)->value('IsAdmin')) {
                $newest = gamesettings::all()->sortByDesc('ID')->first();
                if (!$newest) {
                    GamesettingsController::default();
                    $newest = gamesettings::all()->sortByDesc('ID')->first();
                }
                return response([
                    'data' => $newest,
                    'message' => 'Data fetched'
                ], 200);
            }
            return response([
                'data' => 'null',
                'message' => 'Unauthorized'
            ], 401);
        }
        return response([
            'data' => 'null',
            'message' => 'Bad Request'
        ], 400);
    }
}
