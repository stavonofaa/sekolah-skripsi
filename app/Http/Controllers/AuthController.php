<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function index()
    {
        return view('login')->with('title', 'Login');;
    }

    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    public function handleProviderCallback($provider)
    {
        try {
            $user = Socialite::driver($provider)->user();

            // cek apakah email sudah terdaftar
            $existingUser = User::where('email', $user->getEmail())->first();

            if ($existingUser) {
                // Login user jika sudah terdaftar
                Auth::login($existingUser);
            } else {
                // Buat user baru jika belum terdaftar
                $newUser = User::create([
                    'name' => $user->getName(),
                    'email' => $user->getEmail(),
                    'password' => Hash::make('admin123'),
                    'password_show' => 'admin123',
                    'provider' => $provider,
                    'provider_id' => $user->getId(),
                    'username' => $user->getNickname() ?? $user->getName(),
                    'role' => 'user',
                ]);

                Auth::login($newUser);
            }

            // Redirect langsung berdasarkan role pengguna
            return redirect('/index')->with('status', 'Selamat datang ' . Auth::user()->name);
        } catch (\Throwable $th) {
            return redirect('/login')->with('error', 'Gagal login dengan Google' . $th->getMessage());
        }
    }

    public function login(Request $request)
    {
        $credentials = $request->only(['username', 'password']);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            if (Auth::user()->role == 'admin') {
                return redirect('/dashboard')->with('status', 'Selamat datang ' . Auth::user()->name);
            } elseif (Auth::user()->role == 'user') {
                return redirect('/index')->with('status', 'Selamat datang ' . Auth::user()->name);
            }
        }

        // Return with error message if login fails
        return back()->with('error', 'Username atau password salah');
    }

    public function logout()
    {
        Auth::logout();
        return redirect('/');
    }
}
