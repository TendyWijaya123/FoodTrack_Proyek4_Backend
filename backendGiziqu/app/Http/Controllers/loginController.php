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

    public function getData(Request $request)
    {
        $data = $request->validate([
            'email' => 'required',
        ]);

        $email = $data['email'];

        try {
            // Ambil informasi pengguna berdasarkan username
            $userData = $this->getUserByEmail($email);
            return response()->json([
                'message' => 'Data berhasil diambil',
                'user' => $userData
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan saat mencoba mengambil data'], 500);
        }
    }

    // public function logout()
    // {
    //     $this->
    // }

    public function getUserByEmail($email)
    {
        $userRef = $this->database->getReference($this->table)
            ->orderByChild('email')
            ->equalTo($email)
            ->getSnapshot()
            ->getValue();

        // Ambil data pengguna pertama jika ditemukan
        $userData = reset($userRef);

        return $userData;
    }
}
