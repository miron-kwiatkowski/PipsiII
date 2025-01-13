<?php

namespace App\Http\Controllers;

use App\Models\gamesettings;
use App\Models\guesses;
use App\Models\users;
use App\Models\puzzles;
use Illuminate\Http\Request;
use function Laravel\Prompts\select;

class GameController extends Controller
{
    //Funkcja przydzielajaca nierozwiazana zagadke kazdemu zarejestrowanemu uzytkownikowi
    public static function reset() {
        $puzzlesArray = puzzles::select('ID')->pluck('ID')->toArray();
        $usersArray = users::select('ID')->pluck('ID')->toArray();
        for ($x = 0; $x < count($usersArray); $x++) {
            $id = $usersArray[$x];
            if (guesses::where('UserId', $id)->count()>0) {
                $guessed = guesses::select('PuzzleId')->where('UserId', $id)->where('DidWin', true)->pluck('PuzzleId')->toArray();
                $available = array_diff($puzzlesArray, $guessed);
            } else $available = $puzzlesArray;
            if (count($available)==0) {
                users::where('ID', $id)->update(['CurrentGame'=>0]);
            } else  {
                $result = array_rand($available);
                users::where('ID', $id)->update(['CurrentGame'=>$available[$result]]);
            }
        }
    }

    //Wez zagadke uzytkownika, jesli jest juz rozwiazana pokaz wynik
    public function get($userid) {
        if (guesses::where('UserId', $userid)->where('Date',date('Y/m/d', time()))->exists()) {
            $guess = guesses::where('UserId', $userid)->where('Date',date('Y/m/d', time()))->first();
            return response()->json(['Points'=>$guess->Points,'Time'=>$guess->Time,'DidWin'=>$guess->DidWin]);
        }
        if (users::where('ID', $userid)->exists()) {
            $puzzleid = users::where('ID', $userid)->value('CurrentGame');
        } else $puzzleid = 0;
        $puzzle = puzzles::select('IMGSource','Difficulty')->where('ID', $puzzleid)->first();
        if ($puzzle) {
            $imagepath = storage_path('/images/'.$puzzle->IMGSource);
            if (file_exists($imagepath)) {
                $response = response()->file($imagepath);
            } else $response = response()->file(storage_path('/images/missing.png'));
        }
        return $response;
    }

    //Przesylanie zgadniec, request wymaga:
    //userid - numer uzytkownika (number)
    //puzzleid - numer zagadki (number)
    //xvalue - wspolrzedna X (number)
    //yvalue - wspolrzedna Y (number)
    //time - ile czasu zajelo rozwiazanie zagadki w sekundach (number)
    public function guess(Request $request) {
        $x1 = $request->xvalue;
        $y1 = $request->yvalue;
        $result = 0;
        if (isset($x1)&&isset($y1)) {
            $puzzle = puzzles::select('XValue','YValue','IMGDesc')->where('ID', $request->puzzleid)->first();
            if ($puzzle) {
                $x2 = $puzzle->XValue;
                $y2 = $puzzle->YValue;
                $distance = round(sqrt(pow(($x2-$x1),2)+pow(($y2-$y1),2)),2);

                $timebonus = 1000;
                $time = $request->time - 10;
                if ($time>0) $timebonus = 1000 - ($time * 20);
                if ($timebonus<0) $timebonus = 0;

                $rules = gamesettings::all()->sortByDesc('ID')->first();
                if ($rules) {
                    if ($distance<=$rules->MinDistance) {
                        $result = 5000 + $timebonus;
                    } else if ($distance>$rules->MaxDistance) {
                        $result = 0;
                    } else {
                        $result = round((5000-((($distance - $rules->MinDistance)/ $rules->MaxDistance) * 5000)),0) + $timebonus;
                    }
                    $guess = new guesses();
                    $guess->UserId = 1;
                    $guess->PuzzleId = $request->puzzleid;
                    $guess->Points = $result;
                    $guess->Time = $request->time;
                    $guess->Date = date('Y/m/d', time());
                    if ($result>=$rules->PointsToQualify) {
                        $guess->DidWin = true;
                    } else {
                        $guess->DidWin = false;
                    }
                    $guess->save();
                }
            }
        }
        return response()->json($guess);
    }
}
