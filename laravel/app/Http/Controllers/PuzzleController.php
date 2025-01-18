<?php

namespace App\Http\Controllers;

use App\Models\users;
use Illuminate\Http\Request;
use App\Models\puzzles;
use Illuminate\Support\Facades\File;

class PuzzleController extends Controller
{
    //Wypisanie wszystkich zagadek (tylko dla administratora), request wymaga:
    // 'access_token'
    public function index(Request $request)
    {
        if (isset($request->access_token)) {
            if (users::where('_token', $request->access_token)->value('IsAdmin')) {
                $puzzles = puzzles::all();
                return response([
                    'data' => $puzzles,
                    'access_token' => $request->access_token,
                    'message' => 'Retrieve successful'
                ], 200);
            }
            return response([
                'data' => 'null',
                'access_token' => $request->access_token,
                'message' => 'Unauthorized',
            ], 401);
        }
        return response([
            'data' => 'null',
            'access_token' => 'null',
            'message' => 'Unauthorized',
        ], 401);
    }

    //Tworzenie zagadki (tylko dla administratora), request wymaga:
    // 'access_token'
    // 'image' - zdjecie zagadki (file)
    // 'xvalue' - wartosc x (number)
    // 'yvalue' - wartosc y (number)
    // 'description' - opis zdjecia (text)
    // 'difficulty' - poziom trudnosci od 1 do 3 (number)
    public function create(Request $request)
    {
        if (isset($request->access_token)) {
            if (users::where('_token', $request->access_token)->value('IsAdmin')) {
                if(isset($request->image)) {
                    $puzzle = new puzzles;
                    $img = $request->file('image');
                    $puzzle->IMGSource = date("Y-m-d H-i-s")." ".$img->getClientOriginalName();
                    $img->move(storage_path('/images'), $puzzle->IMGSource);
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
                        'access_token' => $request->access_token,
                        'message' => 'Retrieve successful'
                    ], 200);
                }
                return response([
                    'data' => 'null',
                    'access_token' => $request->access_token,
                    'message' => 'No file',
                ], 404);
            }
            return response([
                'data' => 'null',
                'access_token' => $request->access_token,
                'message' => 'Unauthorized',
            ], 401);
        }
        return response([
            'data' => 'null',
            'access_token' => 'null',
            'message' => 'Unauthorized',
        ], 401);
    }

    //Usuniecie zagadki o podanym ID (tylko dla administratora), request wymaga:
    // 'access_token'
    // 'puzzleid'
    public function delete(Request $request) {
        if (isset($request->access_token)) {
            if (users::where('_token', $request->access_token)->value('IsAdmin')) {
                $puzzle = puzzles::where("ID",$request->puzzleid)->first();
                if ($puzzle) {
                    File::delete(storage_path('/images/').$puzzle->IMGSource);
                    puzzles::where("ID",$request->puzzleid)->delete();
                    return response([
                        'access_token' => $request->access_token,
                        'message' => 'Puzzle deleted',
                    ], 200);
                }
                return response([
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

    //Edytowanie istniejacej zagadki o podanym ID w linku (tylko dla administratora), wartosci do przeslania w request
    // 'access_token'
    // 'id'
    // 'xvalue' (opcjonalne)
    // 'yvalue' (opcjonalne)
    // 'description' (opcjonalne)
    // 'difficulty' (opcjonalne)
    public function edit(Request $request)
    {
        if (isset($request->access_token)) {
            if (users::where('_token', $request->access_token)->value('IsAdmin')) {
                $ID = $request->id;
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
                        'access_token' => $request->access_token,
                        'message' => 'Modify successful',
                    ], 200);
                }
                return response([
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
}
