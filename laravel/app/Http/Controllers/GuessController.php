<?php

namespace App\Http\Controllers;

use App\Models\gamesettings;
use App\Models\guesses;
use App\Models\users;
use Illuminate\Http\Request;

class GuessController extends Controller
{
    //Statystyki zdjecia (tylko dla administratora), request wymaga:
    // 'access_token'
    // 'id'
    public function stats(Request $request) {
        if (isset($request->access_token)) {
            if (users::where('_token', $request->access_token)->value('IsAdmin')) {
                if (guesses::where('PuzzleId', $request->id)->count() > 0) {
                    $puzzle = guesses::where('PuzzleId', $request->id)->get();
                    $numberofguesses = $puzzle->count();
                    $winrate = round(((($puzzle->where('DidWin', 1)->count()) / $numberofguesses) * 100), 2);
                    $lastguess = $puzzle->max('Date');
                    $pointavg = $puzzle->where('DidWin', 1)->avg('Points');
                    $timeavg = $puzzle->where('DidWin', 1)->avg('Time');
                    $data = response()->json(['Winrate' => $winrate . "%", 'Last guess' => $lastguess, 'Points average' => $pointavg, 'Time avg' => $timeavg]);
                    return response([
                        'data' => $data,
                        'access_token' => $request->access_token,
                        'message' => 'Stats fetched',
                    ], 200);
                }
                return response([
                    'data' => 'null',
                    'access_token' => $request->access_token,
                    'message' => 'Puzzle not found',
                ], 404);
            }
            return response([
                'access_token' => $request->access_token,
                'message' => 'Unauthorized',
            ], 401);
        }
        return response([
            'access_token' => 'null',
            'message' => 'Unauthorized',
        ], 401);
    }

    //Dokonczyc
    public function scoreboard() {
        $rules = gamesettings::all()->sortByDesc('ID')->first();
        $days = $rules->LeaderboardDays;
        $question = 'SELECT users.Name, sum(guesses.Points) AS Points FROM guesses JOIN users ON users.ID=guesses.UserId WHERE guesses.Date > 2025-01-01 GROUP BY Name;';
        return response()->json([$question]);
    }
}
