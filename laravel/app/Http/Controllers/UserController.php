<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Models\users;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Facades\Socialite;
use OpenApi\Annotations as OA;

class UserController extends Controller
{
    /**
     * @OA\Post(
     *                  path="/api/users/index",
     *                  tags={"Users"},
     *                  description="Wylistuj wszystkich uzytkownikow. Wymaga uprawnien administratora.",
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

    /**
     * @OA\Post(
     *                  path="/api/users/register",
     *                  tags={"Users"},
     *                  description="Rejestracja uzytkownika w bazie danych.",
     * @OA\RequestBody(
     *     @OA\MediaType(
     *     mediaType="json",
     *     @OA\Schema(
     *          required={
     *                  "name",
     *                  "email",
     *                  "password",
     *          }
     *     ),
     *     )
     * ),
     * @OA\Response (response="200",description="Registration successful"),
     * @OA\Response (response="400",description="Bad Request"),
     * )
     */
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

    /**
     * @OA\Get(
     *                  path="/api/users/google/redirect",
     *                  tags={"Users"},
     *                  description="Przejscie na strone autoryzacji google.",
     * @OA\RequestBody(
     *     @OA\MediaType(
     *     mediaType="json",
     *     )
     * ),
     * @OA\Response (response="200",description="Redirecting"),
     * )
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * @OA\Get(
     *                  path="/api/users/facebook/redirect",
     *                  tags={"Users"},
     *                  description="Przejscie na strone autoryzacji facebook.",
     * @OA\RequestBody(
     *     @OA\MediaType(
     *     mediaType="json",
     *     )
     * ),
     * @OA\Response (response="200",description="Redirecting"),
     * )
     */
    public function redirectToFacebook()
    {
        return Socialite::driver('facebook')->redirect();
    }

    /**
     * @OA\Get(
     *                  path="/api/users/google/callback",
     *                  tags={"Users"},
     *                  description="Powrot z autoryzacji google, rejestracja i logowanie.",
     * @OA\RequestBody(
     *     @OA\MediaType(
     *     mediaType="json",
     *     )
     * ),
     * @OA\Response (response="200",description="Login successful"),
     * )
     */
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

    /**
     * @OA\Get(
     *                  path="/api/users/facebook/callback",
     *                  tags={"Users"},
     *                  description="Powrot z autoryzacji facebook, rejestracja i logowanie.",
     * @OA\RequestBody(
     *     @OA\MediaType(
     *     mediaType="json",
     *     )
     * ),
     * @OA\Response (response="200",description="Login successful"),
     * )
     */
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

    /**
     * @OA\Post(
     *                  path="/api/users/login",
     *                  tags={"Users"},
     *                  description="Logowanie przez baze danych.",
     * @OA\RequestBody(
     *     @OA\MediaType(
     *     mediaType="json",
     *     @OA\Schema(
     *          required={
     *                  "email",
     *                  "password",
     *          }
     *     ),
     *     )
     * ),
     * @OA\Response (response="200",description="Login successful"),
     * @OA\Response (response="401",description="Unauthorized"),
     * @OA\Response (response="403",description="Forbidden"),
     * )
     */
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

    /**
     * @OA\Post(
     *                  path="/api/users/modify",
     *                  tags={"Users"},
     *                  description="Zmiana nicku, zdjecia profilowego i hasla uzytkownika wysylajacego request. Parametry name, pfpnum i password sa opcjonalne.",
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
     * @OA\Response (response="200",description="Modify successful"),
     * @OA\Response (response="401",description="Unauthorized"),
     * )
     */
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

    /**
     * @OA\Post(
     *                  path="/api/users/adminmodify",
     *                  tags={"Users"},
     *                  description="Zmiana aktualnej zagadki, roli administratora i banowanie uzytkownikow. Parametry currentgame, isadmin i isbanned sa opcjonalne. Tylko dla administratora.",
     * @OA\RequestBody(
     *     @OA\MediaType(
     *     mediaType="json",
     *     @OA\Schema(
     *          required={
     *                  "access_token",
     *                  "userid",
     *          }
     *     ),
     *     )
     * ),
     * @OA\Response (response="200",description="Modify successful"),
     * @OA\Response (response="401",description="Unauthorized"),
     * )
     */
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
