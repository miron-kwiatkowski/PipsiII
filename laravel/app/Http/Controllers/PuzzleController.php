<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\puzzles;
use Illuminate\Support\Facades\File;

class PuzzleController extends Controller
{
    //Wypisanie wszystkich zagadek
    public function index()
    {
        $puzzles = puzzles::all();
        return response()->json($puzzles);
    }

    //Tworzenie zagadki, wartosci do przeslania w request:
    // 'image' - zdjecie zagadki (file)
    // 'xvalue' - wartosc x (number)
    // 'yvalue' - wartosc y (number)
    // 'description' - opis zdjecia (text)
    // 'difficulty' - poziom trudnosci od 1 do 3 (number)
    public function create(Request $request)
    {
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
            return redirect('/puzzles');
        }
        return redirect('/puzzles/create')->with('error','Something went wrong');
    }

    //Usuniecie zagadki o podanym ID w linku
    public function delete($id) {
        $puzzle = puzzles::where("ID",$id)->first();
        if ($puzzle) {
            File::delete(storage_path('/images/').$puzzle->IMGSource);
            puzzles::where("ID",$id)->delete();
        }
    }

    //Edytowanie istniejacej zagadki o podanym ID w linku, wartosci do przeslania w request
    //sa te same co przy tworzeniu z wylaczeniem pliku zdjecia.
    public function edit(Request $request)
    {
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
        }
    }
}
