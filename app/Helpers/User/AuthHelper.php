<?php

namespace App\Helpers\User;

use App\Helpers\KHelper;
use App\Http\Resources\User\UserResource;
use App\Models\User;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthHelper extends KHelper
{
    public static function login($email, $password)
    {
        try {
            $credentials = ['email' => $email, 'password' => $password];
            if (!$token = JWTAuth::attempt($credentials)) {
                return [
                    'status' => false,
                    'error' => ["Email or password is incorrect"],
                    'code' => 400
                ];
            }
        } catch (JWTException $e) {
            return [
                'status' => false,
                'error' => ["Could not create token."],
                'code' => 500
            ];
        }

        return [
            'status' => true,
            'data' => self::createNewToken($token)
        ];
    }

    public static function loginWithGoogle($email, $googleToken)
    {
        try {
            $user = User::where('email', $email)->where('google_token', $googleToken)->first();

            if (!$user) {
                return [
                    'status' => false,
                    'error' => "Invalid email or Google token.",
                ];
            }

            $token = JWTAuth::fromUser($user);
            auth()->login($user);

            return [
                'status' => true,
                'data' => self::createNewToken($token),
            ];
        } catch (JWTException $e) {
            return [
                'status' => false,
                'error' => "Could not create token.",
            ];
        }
    }

    protected static function createNewToken($token)
    {
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'user' => new UserResource(auth()->user())
        ];
    }
}
