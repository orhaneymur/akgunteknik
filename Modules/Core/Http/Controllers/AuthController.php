<?php

namespace Modules\Core\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends BaseController
{
    /**
     * Handle user login and return API token.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function login(Request $request): JsonResponse
    {
        // Validate email and password
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Find user by email
        $user = User::where('email', $request->email)->first();

        // Check credentials
        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->respondError(
                ['email' => ['The provided credentials are incorrect.']],
                'Invalid credentials',
                401
            );
        }

        // Create token
        $token = $user->createToken('api-token')->plainTextToken;

        // Return success response with token
        return $this->respondSuccess(
            ['token' => $token],
            'Login success'
        );
    }
}

