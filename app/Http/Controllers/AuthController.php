<?php

namespace App\Http\Controllers;

<<<<<<< HEAD
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
=======
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
>>>>>>> 4db143587ac2b0b494065726bc2cb10c90ccf402

class AuthController extends Controller
{
    public function index()
    {
        return view('login');
    }

<<<<<<< HEAD
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
                    'password' => Hash::make(Str::random(16)),
                    'provider' => $provider,
                    'provider_id' => $user->getId(),
                    'role' => 'admin',
                ]);

                Auth::login($newUser);
            }

            // Redirect semua pengguna Google ke dashboard
            return redirect('/dashboard')->with('status', 'selamat datang ' . Auth::user()->name);
        } catch (\Throwable $th) {
            return redirect('/login')->with('error', 'Gagal login dengan Google' . $th->getMessage());
        }
    }

=======
>>>>>>> 4db143587ac2b0b494065726bc2cb10c90ccf402
    public function login(Request $request)
    {
        $credentials = $request->only(['username', 'password']);

        if (Auth::attempt($credentials)) {
            if (Auth::user()->role == 'admin') {
                return redirect('/dashboard');
            } elseif (Auth::user()->role == 'user') {
                // return redirect('/karyawan');
                return redirect('/index')->with('status', 'selamat datang ' . Auth::user()->name);
            }
        }
        return back();
    }

    public function logout()
    {
        Auth::logout();
        return redirect('/');
    }
}
