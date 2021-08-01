<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Create a new user, log in and return user access token.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        // Create custom error messages for user registration.
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|confirmed'
        ], [
            'name.required' => 'Missing user name.',
            'email.required' => 'Missing email.',
            'email.unique' => 'User with this e-mail already exists.',
            'password.required' => 'Missing password.',
            'password.confirmed' => 'Passwords do not match'
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();

            return response()->json([
                'message' => 'Cannot create user.',
                'errors' => $errors
            ], Response::HTTP_BAD_REQUEST);
        }

        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => bcrypt($request->input('password'))
        ]);

        $token = $user->createToken('typbot-intervija-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token
        ], Response::HTTP_CREATED);
    }

    /**
     * Log in an existing user and return user access token.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        // Create custom error messages for user login.
        $validator = Validator::make($request->all(), [
            'email' => 'required|string',
            'password' => 'required|string'
        ], [
            'email.required' => 'Missing email.',
            'password.required' => 'Missing password.'
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();

            return response()->json([
                'message' => 'Cannot log in.',
                'errors' => $errors
            ], Response::HTTP_BAD_REQUEST);
        }

        $user = User::where('email', $request->input('email'))->first();
        
        if ($user === null || !Hash::check($request->input('password'), $user->password)) {
            return response()->json([
                'message' => 'Cannot log in.',
                'errors' => (object) [
                    'user' => [
                        'Invalid user e-mail or password.'
                    ]
                ]
            ], Response::HTTP_BAD_REQUEST);
        }

        $token = $user->createToken('typbot-intervija-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token
        ], Response::HTTP_OK);
    }

    /**
     * Log out user deleting the user access token.
     *
     * @return Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->user()->tokens()->delete();

        return response()->json([
            'message' => 'Logged out',
        ], Response::HTTP_OK);
    }
}
