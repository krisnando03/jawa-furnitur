<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pelanggan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class LoginController extends Controller
{
    public function showForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email', // Changed from username to email
            'password' => 'required|string'
        ]);

        $user = Pelanggan::where('email', $request->email)->first(); // Query by email

        if ($user && Hash::check($request->password, $user->password)) {
            Session::put('pelanggan', $user);
            return redirect()->route('home')->with('success', 'Berhasil login!');
        }

        return back()->withErrors(['email' => 'Email atau password salah.']) // Changed error key to 'email'
            ->withInput($request->only('email')); // Return old email input
    }

    public function logout()
    {
        Session::forget('pelanggan');
        return redirect()->route('login.form')->with('success', 'Berhasil logout!');
    }
}
