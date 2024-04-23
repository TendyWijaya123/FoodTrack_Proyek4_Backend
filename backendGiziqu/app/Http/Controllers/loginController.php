<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class loginController extends Controller
{
    public function login(Request $request)
    {
        $data = $request->validate([
            'username' => 'required',
            'password' => 'required'
        ]);

        // Check if the user is an admin based on static credentials
        $isAdmin = ($data['username'] == 'admin1' && $data['password'] == 'admin123');

        // Jika pengguna merupakan admin, kirimkan respons berhasil dengan isAdmin true
        if ($isAdmin) {
            return response()->json(['message' => 'login berhasil', 'isAdmin' => true], 200);
        }

        // Jika bukan admin, coba autentikasi pengguna menggunakan Auth
        if (auth()->attempt($data)) {
            // Jika autentikasi berhasil, kirimkan respons berhasil dengan isAdmin false
            $user = auth()->user();
            return response()->json([
                'message' => 'login berhasil',
                'isAdmin' => false,
                'name' => $user->name
            ], 200);
        }

        // Jika autentikasi gagal, kirimkan respons gagal
        return response()->json(['message' => 'login gagal'], 401);
    }
}
