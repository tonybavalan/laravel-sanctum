<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function register (Request $request) {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|unique:users,email',
            'password' => 'required|string|confirmed|min:8'
        ]);

        $user = User::create([
            'name' => $request['name'],
            'email' => $request['email'],
            'password' => bcrypt($request['password'])
        ]);

        $token = $user->createToken('microservices')->plainTextToken;
        $response = [
            'user' => $user,
            'token' => $token
        ];

        return response()->json($response, 201);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function login (Request $request)
    {
        $request = $request->validate([
            'email' => 'required|string',
            'password' => 'required|string'
        ]);

        $user = User::where('email', $request['email'])->first();

        if (!$user || !Hash::check($request['password'], $user->password)) {
            throw validationException::withMessage([
                'email' => ['The provided credentials are invalid.'],
            ]);
        }

        $token = $user->createToken($request['password'])->plainTextToken;

        $response = [
            'user' => $user,
            'token' => $token
        ];

        return response()->json($response, 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function logout (Request $request): JsonResponse
    {
        auth()->user()->tokens()->delete();

        return response()->json([
            'message' => 'User Logged Out'
        ]);
    }
}
