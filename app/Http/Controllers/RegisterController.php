<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Pelanggan;

class RegisterController extends Controller
{
    public function showForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            // 'nama' => 'required|string|max:100', // Assuming 'nama' is not on the registration form or is nullable
            // 'alamat' => 'required|string', // Assuming 'alamat' is not on the registration form or is nullable
            // 'no_telepon' => 'required|string|max:20', // Assuming 'no_telepon' is not on the registration form or is nullable
            'email' => 'required|email|unique:tb_pelanggan,email',
            'username' => 'required|string|min:3|max:50|unique:tb_pelanggan,username', // Added min/max length
            'password' => 'required|string|min:6|confirmed'
        ]);

        Pelanggan::create([
            // 'nama' => $request->nama, // Only include if 'nama' is part of the form and validated
            // 'alamat' => $request->alamat, // Only include if 'alamat' is part of the form and validated
            // 'no_telepon' => $request->no_telepon, // Only include if 'no_telepon' is part of the form and validated
            'email' => $request->email,
            'username' => $request->username,
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('login.form')->with('success', 'Registrasi berhasil! Silakan login.'); // Ensure 'login.form' route exists
    }
}
