<?php

namespace App\Http\Controllers;

use App\Models\gamesettings;
use App\Models\users;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class GamesettingsController extends Controller
{
    /**
     * @OA\Post(
     *                  path="/api/gamesettings/update",
     *                  tags={"Game Settings"},
     *                  description="Aktualizowanie ustawien gry. Tylko dla administratora. Opcjonalne parametry: timereset, mindistance, maxdistance, pointstoqualify, leaderboarddays.",
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
     * @OA\Response (response="200",description="Settings saved"),
     * @OA\Response (response="401",description="Unauthorized"),
     * )
     */
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

    /**
     * @OA\Post(
     *                  path="/api/gamesettings/get",
     *                  tags={"Game Settings"},
     *                  description="Wziecie najnowszych ustawien gry. Tylko dla administratora.",
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
     * @OA\Response (response="200",description="Data fetched"),
     * @OA\Response (response="400",description="Bad Request"),
     * @OA\Response (response="401",description="Unauthorized"),
     * )
     */
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
