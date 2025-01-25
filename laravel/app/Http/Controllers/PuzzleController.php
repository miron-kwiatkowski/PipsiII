<?php

namespace App\Http\Controllers;

use App\Models\users;
use Illuminate\Http\Request;
use App\Models\puzzles;
use Illuminate\Support\Facades\File;
use OpenApi\Annotations as OA;

class PuzzleController extends Controller
{
    /**
     * @OA\Post(
     *                  path="/api/puzzles/index",
     *                  tags={"Puzzles"},
     *                  description="Wylistowanie wszystkich zagadek. Tylko dla administratora.",
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
     * @OA\Response (response="200",description="Retrieve successful"),
     * @OA\Response (response="401",description="Unauthorized"),
     * )
     */
    public function index(Request $request)
    {
        if (isset($request->access_token)) {
            if (users::where('_token', $request->access_token)->value('IsAdmin')) {
                $puzzles = puzzles::all();
                return response([
                    'data' => $puzzles,
                    'message' => 'Retrieve successful'
                ], 200);
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
     *                  path="/api/puzzles/create",
     *                  tags={"Puzzles"},
     *                  description="Tworzenie zagadki. Tylko dla administratora.",
     * @OA\RequestBody(
     *     @OA\MediaType(
     *     mediaType="json",
     *     @OA\Schema(
     *          required={
     *                  "access_token",
     *                  "imagesource",
     *                  "xvalue",
     *                  "yvalue",
     *                  "description",
     *                  "difficulty",
     *          }
     *     ),
     *     )
     * ),
     * @OA\Response (response="200",description="Puzzle created"),
     * @OA\Response (response="401",description="Unauthorized"),
     * @OA\Response (response="404",description="No image source"),
     * )
     */
    public function create(Request $request)
    {
        if (isset($request->access_token)) {
            if (users::where('_token', $request->access_token)->value('IsAdmin')) {
                if(isset($request->imagesource)) {
                    $puzzle = new puzzles;
                    $puzzle->IMGSource = $request->imagesource;
                    if (isset($request->xvalue)) {
                        $puzzle->Xvalue = $request->xvalue;
                    } else $puzzle->Xvalue = 0;
                    if (isset($request->yvalue)) {
                        $puzzle->Yvalue = $request->yvalue;
                    } else $puzzle->Yvalue = 0;
                    $puzzle->IMGDesc = $request->description;
                    if (isset($request->difficulty)) {
                        if ($request->difficulty > 0 && $request->difficulty < 4) {
                            $puzzle->Difficulty = $request->difficulty;
                        } else $puzzle->Difficulty = 1;
                    } else $puzzle->Difficulty = 1;
                    $puzzle->save();
                    return response([
                        'data' => $puzzle,
                        'message' => 'Puzzle created'
                    ], 200);
                }
                return response([
                    'data' => 'null',
                    'message' => 'No image source',
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
     *                  path="/api/puzzles/delete",
     *                  tags={"Puzzles"},
     *                  description="Usuniecie zagadki. Tylko dla administratora.",
     * @OA\RequestBody(
     *     @OA\MediaType(
     *     mediaType="json",
     *     @OA\Schema(
     *          required={
     *                  "access_token",
     *                  "puzzleid",
     *          }
     *     ),
     *     )
     * ),
     * @OA\Response (response="200",description="Puzzle deleted"),
     * @OA\Response (response="401",description="Unauthorized"),
     * @OA\Response (response="404",description="Puzzle not found"),
     * )
     */
    public function delete(Request $request) {
        if (isset($request->access_token)) {
            if (users::where('_token', $request->access_token)->value('IsAdmin')) {
                $puzzle = puzzles::where("ID",$request->puzzleid)->first();
                if ($puzzle) {
                    File::delete(storage_path('/images/').$puzzle->IMGSource);
                    puzzles::where("ID",$request->puzzleid)->delete();
                    return response([
                        'message' => 'Puzzle deleted',
                    ], 200);
                }
                return response([
                    'message' => 'Puzzle not found',
                ], 404);
            }
            return response([
                'message' => 'Unauthorized',
            ], 401);
        }
        return response([
            'message' => 'Unauthorized',
        ], 401);
    }

    /**
     * @OA\Post(
     *                  path="/api/puzzles/edit",
     *                  tags={"Puzzles"},
     *                  description="Edytowanie zagadki. Tylko dla administratora. Parametry xvalue, yvalue, description i difficulty sa opcjonalne.",
     * @OA\RequestBody(
     *     @OA\MediaType(
     *     mediaType="json",
     *     @OA\Schema(
     *          required={
     *                  "access_token",
     *                  "puzzleid",
     *          }
     *     ),
     *     )
     * ),
     * @OA\Response (response="200",description="Modify successful"),
     * @OA\Response (response="401",description="Unauthorized"),
     * @OA\Response (response="404",description="Puzzle not found"),
     * )
     */
    public function edit(Request $request)
    {
        if (isset($request->access_token)) {
            if (users::where('_token', $request->access_token)->value('IsAdmin')) {
                $ID = $request->puzzleid;
                if (puzzles::where('ID',$ID)->exists()) {
                    if (isset($request->description)) {
                        puzzles::where('ID', $ID)->update(['IMGDesc' => $request->description]);
                    }
                    if (isset($request->xvalue)) {
                        puzzles::where('ID', $ID)->update(['Xvalue' => $request->xvalue]);
                    }
                    if (isset($request->yvalue)) {
                        puzzles::where('ID', $ID)->update(['Yvalue' => $request->yvalue]);
                    }
                    if (isset($request->difficulty)) {
                        if ($request->difficulty > 0 && $request->difficulty < 4) {
                            puzzles::where('ID', $ID)->update(['Difficulty' => $request->difficulty]);
                        }
                    }
                    return response([
                        'message' => 'Modify successful',
                    ], 200);
                }
                return response([
                    'message' => 'Puzzle not found',
                ], 404);
            }
            return response([
                'message' => 'Unauthorized',
            ], 401);
        }
        return response([
            'message' => 'Unauthorized',
        ], 401);
    }
}
