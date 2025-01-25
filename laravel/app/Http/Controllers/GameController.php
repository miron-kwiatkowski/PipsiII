<?php

namespace App\Http\Controllers;

use App\Models\gamesettings;
use App\Models\guesses;
use App\Models\users;
use App\Models\puzzles;
use Illuminate\Http\Request;
use function Laravel\Prompts\select;
use OpenApi\Annotations as OA;

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

    /**
     * @OA\Post(
     *                  path="/api/game/get",
     *                  tags={"Game"},
     *                  description="Wziecie aktualnej gry uzytkownika. Jesli jest juz rozwiazana, zwraca statystyki wyboru.",
     * @OA\RequestBody(
     *     @OA\MediaType(
     *     mediaType="json",
     *     @OA\Schema(
     *          required={
     *                  "access_token",
     *          }
     *     ),
     *     )
     * ),
     * @OA\Response (response="200",description="Stats displayed"),
     * @OA\Response (response="401",description="Unauthorized"),
     * @OA\Response (response="404",description="Puzzle not found"),
     * )
     */
    public function get(Request $request) {
        if (isset($request->access_token)) {
            $userid = (users::where('_token', $request->access_token)->value('ID'));
            if (guesses::where('UserId', $userid)->where('Date', date('Y/m/d', time()))->exists()) {
                $guess = guesses::where('UserId', $userid)->where('Date', date('Y/m/d', time()))->first();
                return response([
                    'data' => response()->json(['source' => $guess->IMGSource, 'points' => $guess->Points, 'time' => $guess->Time, 'didwin' => $guess->DidWin]),
                    'message' => 'Stats displayed'
                ], 200);
            }

            if ($userid) {
                $puzzleid = users::where('_token', $request->access_token)->value('CurrentGame');
            } else $puzzleid = 0;

            $puzzle = puzzles::select('IMGSource', 'Difficulty')->where('ID', $puzzleid)->first();
            if ($puzzle) {
                return response([
                    'source' => $puzzle->IMGSource,
                    'difficulty' => $puzzle->Difficulty,
                    'message' => 'Stats displayed',
                ], 200);
            }
            return response([
                'data' => 'null',
                'message' => 'Puzzle not found',
            ], 404);
        }
        return response([
            'data' => 'null',
            'message' => 'Unauthorized',
        ], 401);
    }

    /**
     * @OA\Post(
     *                  path="/api/game/guess",
     *                  tags={"Game"},
     *                  description="Przeslanie odpowiedzi uzytkownika na jego aktualna zagadke.",
     * @OA\RequestBody(
     *     @OA\MediaType(
     *     mediaType="json",
     *     @OA\Schema(
     *          required={
     *                  "access_token",
     *                  "xvalue",
     *                  "yvalue",
     *                  "time",
     *          }
     *     ),
     *     )
     * ),
     * @OA\Response (response="200",description="Guess saved"),
     * @OA\Response (response="400",description="Bad Request"),
     * @OA\Response (response="401",description="Unauthorized"),
     * @OA\Response (response="404",description="Data not found"),
     * )
     */
    public function guess(Request $request) {
        if (isset($request->access_token)) {
            $x1 = $request->xvalue;
            $y1 = $request->yvalue;
            $userid = users::where('_token', $request->access_token)->value('ID');
            $puzzleid = users::where('_token', $request->access_token)->value('CurrentGame');
            $result = 0;
            if (isset($x1)&&isset($y1)) {
                $puzzle = puzzles::select('XValue','YValue','IMGDesc')->where('ID', $puzzleid)->first();
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
                        $guess->UserId = $userid;
                        $guess->PuzzleId = $puzzleid;
                        $guess->Points = $result;
                        $guess->Time = $request->time;
                        $guess->Date = date('Y/m/d', time());
                        if ($result>=$rules->PointsToQualify) {
                            $guess->DidWin = true;
                        } else {
                            $guess->DidWin = false;
                        }
                        $guess->save();
                        return response([
                            'data' => $guess,
                            'message' => 'Guess saved'
                        ], 200);
                    }
                    return response([
                        'data' => 'null',
                        'message' => 'Data not found'
                    ], 404);
                }
                return response([
                    'data' => 'null',
                    'message' => 'Data not found'
                ], 404);
            }
            return response([
                'data' => 'null',
                'message' => 'Bad request'
            ], 400);
        }
        return response([
            'data' => 'null',
            'message' => 'Unauthorized'
        ], 401);
    }

    // Wymuszenie restartu zagadek u wszystkich uzytkownikow
    public function forceReset(Request $request) {
        if (isset($request->access_token)) {
            if (users::where('_token', $request->access_token)->value('IsAdmin')) {
                GameController::reset();
                return response([
                    'message' => 'Reset successful'
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
}
