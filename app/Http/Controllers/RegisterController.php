<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function index()
    {
        return view('register')->with('title', 'Register');;
    }

    public function register(Request $request)
    {
        try {
            //validate for request
            $validated = $request->validate([
                'email' => 'required|string|email|max:255|unique:users',
                'username' => 'required|string|max:255|unique:users',
                'password' => 'required|string|min:8',
                'role' => 'required|string|in:admin,user',
            ]);

            //create the new user
            $user = User::create([
                'name' => $validated['username'],
                'email' => $validated['email'],
                'username' => $validated['username'],
                'password' => Hash::make($validated['password']),
                'password_show' => $validated['password'],
                'role' => $validated['role'],
            ]);

            //redirect page login
            return redirect('/login')->with('status', 'Registrasi akun telah berhasil!');
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error', 'Terjadi kesalahan coba lagi']);
        }
    }
}
