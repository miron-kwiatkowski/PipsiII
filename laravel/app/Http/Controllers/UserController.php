<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Models\users;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Facades\Socialite;

class UserController extends Controller
{
    //Wylistuj wszystkich uzytkownikow (tylko dla administratora), request wymaga:
    // 'access_token'
    public function index(Request $request)
    {
        if (isset($request->access_token)) {
            if (users::where('_token', $request->access_token)->value('IsAdmin')) {
                $users = users::all()->select('ID','Name','JoinDate','CurrentGame','IsAdmin','IsBanned');
                return response([
                    'data' => $users,
                    'message' => 'Retrieve successful'
                ], 200);
            } else {
                return response([
                    'data' => 'null',
                    'message' => 'Unauthorized',
                ], 401);
            }
        }
        return response([
            'data' => 'null',
            'message' => 'Unauthorized',
        ], 401);
    }

    //Rejestracja uzytkownika w bazie danych, request wymaga:
    // 'name'
    // 'email'
    // 'password'
    public function register(Request $request)
    {
        $passwordlength = strlen($request->password);
        $namelength = strlen($request->name);
        if (users::where('Email', $request->email)->exists()) {
             return response([
                'message' => 'Email taken',
            ], 400);
        } else {
            if ($passwordlength>=6&&$passwordlength<=40) {
                if (filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
                    if ($namelength>0&&$namelength<=40) {
                        $users = new users();
                        $users->Name = $request->name;
                        $users->Email = $request->email;
                        $users->Password = Hash::make($request->password);
                        $users->JoinDate = date('Y/m/d', time());
                        $users->PfpNum = rand(1, 10);
                        $users->CurrentGame = 1;
                        $users->save();
                        return response([
                            'message' => 'Registration successful',
                        ], 200);
                    }else return response([
                        'message' => 'Bad Request',
                    ], 400);
                } else return response([
                    'message' => 'Bad Request',
                ], 400);
            } else return response([
                'message' => 'Bad Request',
            ], 400);
        }
    }

    //Otworzenie autoryzacji google
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    //Otworzenie autoryzacji facebook
    public function redirectToFacebook()
    {
        return Socialite::driver('facebook')->redirect();
    }

    //Tworzenie konta i logowanie poprzez google
    public function handleGoogleCallback()
    {
        $googleUser = Socialite::driver('google')->stateless()->user();
        $user = users::where('Email', $googleUser->email)->where('Type','g')->first();
        $token = Str::random(60);
        if(!$user)
        {
            $user = users::create([
                'Name' => $googleUser->name,
                'Email' => $googleUser->email,
                'Password' => Hash::make(rand(100000,999999)),
                'JoinDate' => date('Y/m/d', time()),
                'PfpNum' => rand(1, 10),
                'CurrentGame' => 1,
                '_token' => $token,
                'Type' => 'g',
            ]);
            return response([
                'access_token' => $token,
                'message' => 'Register successful',
            ], 200);
        } else {
            users::where('Email', $googleUser->email)->where('Type','g')->update(['_token' => $token]);
            return response([
                'access_token' => $token,
                'message' => 'Login successful',
            ], 200);
        }
    }

    //Tworzenie konta i logowanie poprzez facebook
    public function handleFacebookCallback()
    {
        $facebookUser = Socialite::driver('facebook')->stateless()->user();
        $user = users::where('Email', $facebookUser->email)->where('Type','fb')->first();
        $token = Str::random(60);
        if(!$user)
        {
            $user = users::create([
                'Name' => $facebookUser->name,
                'Email' => $facebookUser->email,
                'Password' => Hash::make(rand(100000,999999)),
                'JoinDate' => date('Y/m/d', time()),
                'PfpNum' => rand(1, 10),
                'CurrentGame' => 1,
                '_token' => $token,
                'Type' => 'fb',
            ]);
            return response([
                'access_token' => $token,
                'message' => 'Register successful',
            ], 200);
        } else {
            users::where('Email', $facebookUser->email)->where('Type','fb')->update(['_token' => $token]);
            return response([
                'access_token' => $token,
                'message' => 'Login successful',
            ], 200);
        }
    }

    //Logowanie przez baze danych, request wymaga:
    // 'email'
    // 'password'
    public function login(Request $request)
    {
        $user = users::where('Email', $request->email)->where('Type','db')->first();
        if (Hash::check($request->password, $user->Password)) {
            if ($user->IsBanned==1) {
                return response([
                    'access_token' => 'null',
                    'username' => 'null',
                    'pfp' => 'null',
                    'message' => 'Forbidden',
                ], 403);
            }
            $token = Str::random(60);
            users::where('Email', $request->email)->where('Type','db')->update(['_token' => $token]);
            return response([
                'access_token' => $token,
                'username' => $user->Name,
                'pfp' => $user->PfpNum,
                'message' => 'Login successful',
            ], 200);
        } else {
            return response([
                'access_token' => 'null',
                'username' => 'null',
                'pfp' => 'null',
                'message' => 'Unauthorized',
            ], 401);
        }
    }

    //Modyfikowanie danych uzytkownika, request wymaga:
    // 'access_token'
    // 'name' (opcjonalne)
    // 'pfpnum' (opcjonalne)
    // 'password' (opcjonalne)
    public function modify(Request $request) {
        if (isset($request->access_token)) {
            if (isset($request->name)) {
                users::where('_token', $request->access_token)->update(['Name' => $request->name]);
            }
            if (isset($request->pfpnum)) {
                users::where('_token', $request->access_token)->update(['PfpNum' => $request->pfpnum]);
            }
            if (isset($request->password)) {
                users::where('_token', $request->access_token)->update(['Password' => Hash::make($request->password)]);
            }
            return response([
                'message' => 'Modify successful',
            ], 200);
        }
        return response([
            'message' => 'Unauthorized',
        ], 401);
    }

    //Modyfikowanie uzytkownika (tylko dla administratora), request wymaga:
    // 'access_token'
    // 'userid'
    // 'currentgame' (opcjonalne)
    // 'isadmin' (opcjonalne)
    // 'isbanned' (opcjonalne)
    public function adminmodify(Request $request) {
        if (isset($request->access_token)) {
            if (isset($request->userid)) {
                if (users::where('_token', $request->access_token)->value('IsAdmin')) {
                    if(isset($request->currentgame)) {
                        users::where('ID', $request->userid)->update(['CurrentGame'=>$request->currentgame]);
                    }
                    if(isset($request->isadmin)) {
                        users::where('ID', $request->userid)->update(['IsAdmin'=>$request->isadmin]);
                    }
                    if(isset($request->isbanned)) {
                        users::where('ID', $request->userid)->update(['IsBanned'=>$request->isbanned]);
                    }
                    return response([
                        'message' => 'Modify successful',
                    ], 200);
                }
                return response([
                    'message' => 'Unauthorized',
                ], 401);
            }
        }
        return response([
            'message' => 'Unauthorized',
        ], 401);
    }
}
