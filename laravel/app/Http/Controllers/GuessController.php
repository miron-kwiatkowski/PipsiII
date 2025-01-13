<?php

namespace App\Http\Controllers;

use App\Models\gamesettings;
use App\Models\guesses;
use Illuminate\Http\Request;

class GuessController extends Controller
{
    public function stats($id) {
        if (guesses::where('PuzzleId', $id)->count()>0) {
            $puzzle = guesses::where('PuzzleId', $id)->get();
            $numberofguesses = $puzzle->count();
            $winrate = round(((($puzzle->where('DidWin', 1)->count()) / $numberofguesses) * 100), 2);
            $lastguess = $puzzle->max('Date');
            $pointavg = $puzzle->where('DidWin', 1)->avg('Points');
            $timeavg = $puzzle->where('DidWin', 1)->avg('Time');
            return response()->json(['Winrate' => $winrate . "%", 'Last guess' => $lastguess, 'Points average' => $pointavg, 'Time avg' => $timeavg]);
        } else return response()->json(['No guesses found']);
    }

    //Dokonczyc
    public function scoreboard() {
        $rules = gamesettings::all()->sortByDesc('ID')->first();
        $response = guesses::orderyBy('Points')->get();
        return response()->json([$response]);
    }
}
