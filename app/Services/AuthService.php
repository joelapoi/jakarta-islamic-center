<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthService
{
    public function login($credentials)
    {
        $user = User::where('email', $credentials['email'])
            ->where('is_active', true)
            ->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return [
                'success' => false,
                'message' => 'Invalid credentials'
            ];
        }

        $token = JWTAuth::fromUser($user);
        $user->load('roles');

        // Store user roles in session
        $roleNames = $user->roles->pluck('name')->toArray();
        Session::put('user_roles', $roleNames);
        Session::put('user_id', $user->id);

        return [
            'success' => true,
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'nip' => $user->nip,
                'phone' => $user->phone,
                'roles' => $roleNames,
            ]
        ];
    }

    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());
        
        // Clear user roles from session
        Session::forget('user_roles');
        Session::forget('user_id');
        
        return [
            'success' => true,
            'message' => 'Successfully logged out'
        ];
    }

    public function refresh()
    {
        $newToken = JWTAuth::refresh(JWTAuth::getToken());
        
        return [
            'success' => true,
            'token' => $newToken,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60,
        ];
    }

    public function me()
    {
        $user = JWTAuth::user();
        $user->load('roles');

        // Update session roles
        $roleNames = $user->roles->pluck('name')->toArray();
        Session::put('user_roles', $roleNames);
        Session::put('user_id', $user->id);

        return [
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'nip' => $user->nip,
                'phone' => $user->phone,
                'roles' => $roleNames,
            ]
        ];
    }
}