<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Kreait\Firebase\Contract\Database;
use Kreait\Firebase\Contract\Auth;


class registerController extends Controller
{
    protected $database;
    protected $table;
    protected $auth;



    public function __construct(Auth $auth, Database $database)
    {
        $this->database = $database;
        $this->table = "user";
        $this->auth = $auth;
    }

    public function store(Request $request)
    {
        $role = "user";
        $username = $request->input('username');
        $email = $request->input('email');

        $usernameExists = $this->database->getReference($this->table)->orderByChild('username')->equalTo($username)->getSnapshot()->getValue();
        $emailExists = $this->database->getReference($this->table)->orderByChild('username')->equalTo($email)->getSnapshot()->getValue();


        if (count($usernameExists) > 0 || count($emailExists) > 0) {
            return response()->json(['message' => 'Username sudah digunakan'], 422);
        }

        // Periksa keunikan email di Firebase
        $emailExists = $this->database->getReference($this->table)->orderByChild('email')->equalTo($email)->getSnapshot()->getValue();

        if (count($emailExists) > 0) {
            return response()->json(['message' => 'Email sudah digunakan'], 422);
        }

        $postData = [
            'name' => $request->input("name"),
            'username' => $request->input("username"),
            'email' => $request->input("email"),
            'password' => Hash::make($request->input("password")),
            'role' => $role
        ];

        $userProperties = [
            'email' => $request->input("email"),
            'emailVerified' => false,
            'password' => $request->input("password"),
            'displayName' => $request->input("name"),
            'disabled' => false,
        ];

        $createdUser = $this->auth->createUser($userProperties);

        $postRef = $this->database->getReference($this->table)->push($postData);
        if ($postRef) {
            return response()->json(['message' => 'Registration successful'], 200);
        } else {
            return response()->json(['message' => 'Registration failed'], 400);
        }
    }
}
