<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pelanggan; // Pastikan path ke model User Anda benar

class ValidationController extends Controller
{
    public function checkAvailability(Request $request)
    {
        $request->validate([
            'field' => 'required|string|in:email,username',
            'value' => 'required|string',
        ]);

        $field = $request->input('field');
        $value = $request->input('value');
        $query = Pelanggan::query();

        if ($field === 'email') {
            $query->whereRaw('LOWER(email) = ?', [strtolower($value)]);
        } elseif ($field === 'username') {
            $query->whereRaw('LOWER(username) = ?', [strtolower($value)]);
        }

        $exists = $query->exists();

        if ($exists) {
            return response()->json(['available' => false, 'message' => ucfirst($field) . ' sudah digunakan.']);
        } else {
            return response()->json(['available' => true, 'message' => ucfirst($field) . ' tersedia.']);
        }
    }

    public function checkLoginEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
        ]);

        $email = $request->input('email');
        $exists = Pelanggan::where('email', $email)->exists();

        return response()->json(['exists' => $exists]);
    }
}
