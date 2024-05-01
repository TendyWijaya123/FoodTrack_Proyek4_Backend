<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Contract\Auth;
use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;
use Kreait\Firebase\Exception\Auth\UserNotFound;
use Illuminate\Support\Facades\Hash;
use Kreait\Firebase\Contract\Database;

class LoginController extends Controller
{
    protected $auth;
    protected $database;
    protected $table;

    public function __construct(Auth $auth, Database $database)
    {
        $this->auth = $auth;
        $this->database = $database;
        $this->table = "user";
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'username' => 'required',
            'password' => 'required'
        ]);

        $username = $data['username'];
        $password = $data['password'];

        try {
            // Ambil informasi pengguna berdasarkan username
            $userData = $this->getUserByUsername($username);

            // Periksa apakah pengguna ditemukan
            if (!$userData) {
                return response()->json(['message' => 'Pengguna tidak ditemukan'], 404);
            }

            // Periksa kecocokan password
            if (!Hash::check($password, $userData['password'])) {
                return response()->json(['message' => 'Password salah'], 401);
            }

            // Login berhasil
            return response()->json([
                'message' => 'Login berhasil',
                'user' => $userData
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan saat mencoba login'], 500);
        }
    }

    public function getUserByUsername($username)
    {
        $userRef = $this->database->getReference($this->table)
            ->orderByChild('username')
            ->equalTo($username)
            ->getSnapshot()
            ->getValue();

        // Ambil data pengguna pertama jika ditemukan
        $userData = reset($userRef);

        return $userData;
    }
}
