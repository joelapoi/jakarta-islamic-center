<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $result = $this->authService->login($request->only('email', 'password'));

        if (!$result['success']) {
            return response()->json($result, 401);
        }

        return response()->json($result, 200);
    }

    public function logout()
    {
        $result = $this->authService->logout();
        return response()->json($result, 200);
    }

    public function refresh()
    {
        $result = $this->
        authService = $authService;
    }

    public function me()
    {
        $result = $this->authService->me();
        return response()->json($result, 200);
    }
}