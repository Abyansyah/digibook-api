<?php

namespace App\Http\Controllers;

use App\Helpers\User\AuthHelper;
use App\Http\Requests\User\AuthRequest;
use App\Http\Requests\User\CreateRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{

    public function login(AuthRequest $request)
    {
        $credentials = $request->validated();
        $login = AuthHelper::login($credentials['email'], $credentials['password']);

        if (!$login['status']) {
            return response()->json(['error' => $login['error']], $login['code']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Successfully logged in',
            'data' => $login['data']
        ], 200);
    }

    public function register(CreateRequest $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:6|confirmed',
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Successfully created new user',
                'data' => $user
            ], 201);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    public function me()
    {
        if (!Auth::check()) {
            return response()->json(['success' => false,  'error' => 'Unauthorized'], 401);
        }

        $user = Auth::user();

        return response()->json([
            'success' => true,
            'message' => 'Successfully retrieved user information',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone_number' => $user->phone_number,
                    'foto' => $user->foto,
                    'email_verified_at' => $user->email_verified_at,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ]
            ]
        ]);
    }

    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return response()->json(['success' => true, 'message' => 'Successfully logged out'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to log out'], 500);
        }
    }
}
