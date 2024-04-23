<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class registerController extends Controller
{
    public function store(Request $request)
    {
        // try {
        $validatedData = $request->validate([
            'name' => 'required|max:50',
            'username' => 'required|unique:users',
            'email' => 'required|email:dns|unique:users',
            'password' => 'required|min:8'
        ]);

        $user = User::create([
            'name' => $validatedData['name'],
            'username' => $validatedData['username'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
        ]);

        return response()->json(['message' => 'Registration successful'], 200);
        // } catch (ValidationException $e) {
        //     return response()->json(['error' => $e->errors()], 400);
        // } catch (\Exception $e) {
        //     return response()->json(['error' => 'Registration failed'], 500);
        // }
    }
}
