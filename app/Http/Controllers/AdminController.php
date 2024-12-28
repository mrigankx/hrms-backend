<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class AdminController extends Controller
{

    public function register(Request $request)
{
    // Validate the request
    $validator = Validator::make($request->all(), [
        'employee_id' => 'required|string|max:255|unique:users', // Ensure employee_id is unique
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:8|confirmed', // Requires password_confirmation
        'role' => 'required', // Validate role
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'errors' => $validator->errors(),
        ], 422);
    }

    // Create the user
    $user = User::create([
        'employee_id' => $request->employee_id,
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'role' => $request->role, // Assign role
    ]);

    // Generate a token for the user
    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
        'success' => true,
        'message' => 'User registered successfully.',
        'data' => [
            'user' => $user,
            'token' => $token,
        ],
    ], 201);
}


public function login(Request $request)
{
    // Validate the request
    $validator = Validator::make($request->all(), [
        'email' => 'required|string|email',
        'password' => 'required|string|min:8', // Requires password_confirmation
    ]);

    // Check if validation fails
    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'errors' => $validator->errors(),
        ], 422);
    }

    // Get credentials
    $credentials = ['email' => $request->email, 'password' => $request->password];

    try {
        // Attempt to authenticate
        if (!auth()->attempt($credentials)) {
            return response()->json(['error' => 'Invalid Credentials'], 403);
        }

        // Get the authenticated user
        $user = auth()->user();

        // Check if the user has the 'employee' or 'manager' role
        if ($user->role == 'admin') {
            // Manager role logic
            $message = 'HR  logged in successfully.';
        } elseif ($user->role == 'manager') {
            // Employee role logic
            $message = 'Employee logged in successfully.';
        } else {
            // Default logic for other roles (if any)
            $message = 'Login successfully.';
        }

        // Generate the token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => [
                'user' => $user,
                'token' => $token,
            ],
        ], 200); // 200 OK for successful login

    } catch (\Exception $th) {
        return response()->json(['error' => $th->getMessage()], 500); // Internal server error
    }
}


}