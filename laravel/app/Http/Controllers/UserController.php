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
    //Wylistuj wszystkich uzytkownikow
    public function index()
    {
        $users = users::all()->select('ID','Name','JoinDate','CurrentGame','IsAdmin','IsBanned');
        return response()->json($users);
    }

    //Rejestracja uzytkownika w bazie danych
    public function register(Request $request)
    {
        $passwordlength = strlen($request->password);
        $namelength = strlen($request->name);
        if ($passwordlength>=6&&$passwordlength<=40) {
            if (filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
                if ($namelength>0&&$namelength<=40) {
                    $users = new users();
                    $users->Name = $request->name;
                    $users->Email = $request->email;
                    $users->Password = Hash::make($request->password);
                    $users->JoinDate = date('Y/m/d', time());
                    $users->PfpNum = rand(1, 10);
                    $users->_token = Str::random(60);
                    $users->save();
                    return redirect('/login')->with('error', 'Account created successfully');
                }else return redirect()->back()->with('error','Invalid name');
            } else return redirect()->back()->with('error','Invalid email');
        } else return redirect()->back()->with('error','Invalid password');
    }

    //Otworzenie autoryzacji google
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    //Tworzenie konta i logowanie poprzez google
    public function handleGoogleCallback()
    {
        $googleUser = Socialite::driver('google')->stateless()->user();
        $user = users::where('Email', $googleUser->email)->first();
        if(!$user)
        {
            $user = users::create([
                'Name' => $googleUser->name,
                'Email' => $googleUser->email,
                'Password' => Hash::make(rand(100000,999999)),
                'JoinDate' => date('Y/m/d', time()),
                'PfpNum' => rand(1, 10),
                '_token' => Str::random(60),
            ]);
        }
        if (Auth::attempt(['email' => $user->Email, 'password' => $user->Password])) {
            session(['email' => $user->Email,'name' => $user->Name, 'isadmin' => $user->IsAdmin]);
            return redirect('/');
        } else {
            return redirect('/login')->with('error','Something went wrong');
        }
    }

    //Logowanie przez baze danych
    public function login(Request $request)
    {
        $user = users::where('Email', $request->email)->first();
        if (Hash::check($request->password, $user->Password)) {
            if ($user->IsBanned==1) {
                return redirect()->back()->with('error','Account banned');
            }
            if (Auth::attempt(['email'=>$request->email,'password'=>$request->password])) {
                session(['email' => $user->Email,'name' => $user->Name, 'isadmin' => $user->IsAdmin]);
                return redirect('/');
            }
            else
            {
                return redirect()->back()->with('error','Something went wrong');
            }
        } else {
            return redirect()->back()->with('error','Wrong login');
        }
    }

    //Wylogowanie
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    //Modyfikowanie danych uzytkownika
    public function modify(Request $request) {
        if(isset($request->name)) {
            users::where('ID', $request->id)->update(['Name'=>$request->name]);
        }
        if(isset($request->pfpnum)) {
            users::where('ID', $request->id)->update(['PfpNum'=>$request->pfpnum]);
        }
        if(isset($request->password)) {
            users::where('ID', $request->id)->update(['Password'=>$request->password]);
        }
    }

    //Modyfikowanie uzytkownika przez administratora
    //currentgame - aktualna gra, uzywane przy przydzielaniu zagadki (int)
    //isadmin - czy jest administratorem (bool)
    //isbanned - czy jest zbanowany (bool)
    public function adminmodify(Request $request) {
        if(isset($request->currentgame)) {
            users::where('ID', $request->id)->update(['CurrentGame'=>$request->currentgame]);
        }
        if(isset($request->isadmin)) {
            users::where('ID', $request->id)->update(['IsAdmin'=>$request->isadmin]);
        }
        if(isset($request->isbanned)) {
            users::where('ID', $request->id)->update(['IsBanned'=>$request->isbanned]);
        }
    }
}
