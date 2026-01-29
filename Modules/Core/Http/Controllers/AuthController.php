<?php

namespace Modules\Core\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->respondError(
                ['email' => ['The provided credentials are incorrect.']],
                'Invalid credentials',
                401
            );
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return $this->respondSuccess(
            [
                'token' => $token,
                'user' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'tenant_name' => $user->tenant ? $user->tenant->company_name : 'Sistem'
                ]
            ],
            'Login success'
        );
    }

    /**
     * Logout current user by revoking the current access token.
     */
    public function logout(Request $request): JsonResponse
    {
        $token = $request->user()?->currentAccessToken();

        if ($token) {
            $token->delete();
        }

        return $this->respondSuccess(null, 'Logout success');
    }
}
