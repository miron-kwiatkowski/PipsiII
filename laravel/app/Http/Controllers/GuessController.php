<?php

namespace App\Http\Controllers;

use App\Models\gamesettings;
use App\Models\guesses;
use App\Models\users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OpenApi\Annotations as OA;

class GuessController extends Controller
{
    /**
     * @OA\Post(
     *                  path="/api/guesses/stats",
     *                  tags={"Guesses"},
     *                  description="Zdobadz statystyki zdjecia. Tylko dla administratora.",
     * @OA\RequestBody(
     *     @OA\MediaType(
     *     mediaType="json",
     *     @OA\Schema(
     *          required={
     *                  "access_token",
     *                  "id",
     *          }
     *     ),
     *     )
     * ),
     * @OA\Response (response="200",description="Stats fetched"),
     * @OA\Response (response="401",description="Unauthorized"),
     * @OA\Response (response="404",description="Data not found"),
     * )
     */
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
                        'message' => 'Stats fetched',
                    ], 200);
                }
                return response([
                    'data' => 'null',
                    'message' => 'Data not found',
                ], 404);
            }
            return response([
                'data' => 'null',
                'message' => 'Unauthorized',
            ], 401);
        }
        return response([
            'data' => 'null',
            'message' => 'Unauthorized',
        ], 401);
    }

    /**
     * @OA\Post(
     *                  path="/api/guesses/scoreboard",
     *                  tags={"Guesses"},
     *                  description="Wez tabele wynikow.",
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
     * @OA\Response (response="200",description="Scoreboard fetched"),
     * )
     */
    public function scoreboard() {
        $rules = gamesettings::all()->sortByDesc('ID')->first();
        $days = $rules->LeaderboardDays;
        $question = DB::select('SELECT users.Name, sum(guesses.Points) AS Points FROM guesses JOIN users ON users.ID=guesses.UserId WHERE guesses.Date > CURRENT_DATE - INTERVAL '.$days.' DAY GROUP BY Name;');
        return response()->json([$question]);
    }
}
