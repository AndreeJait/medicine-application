<?php

namespace App\Http\Controllers\Api;

use App\Constants\ResponseCode;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'identifier' => 'required',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            throw ResponseCode::badRequest($validator->errors());
        }

        $identifier = $request->input('identifier');

        $user = User::where('email', $identifier)
            ->orWhere('nik', $identifier)
            ->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ResponseCode::passwordOrEmailIsInvalid();
        }

        if (! $user->is_active) {
            throw ResponseCode::userIsNotActive();
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return $this->successResponse([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'nik' => $user->nik,
                'position' => $user->position,
            ]
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->successResponse(null, 'You have been logged out successfully.');
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return $this->successResponse([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'nik' => $user->nik,
            'position' => $user->position,
            'is_active' => $user->is_active,
            'role' => $user->getRoleNames()->first(), // Ambil satu role utama
            'permissions' => $user->getAllPermissions()->pluck('name'), // Kumpulan permission dari role dan direct
        ]);
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email']
        ]);

        if ($validator->fails()) {
            throw ResponseCode::badRequest($validator->errors());
        }

        $status = Password::sendResetLink(
            ['email' => $request->input('email')]
        );

        if ($status === Password::RESET_LINK_SENT) {
            return $this->successResponse(null, 'Reset password link sent to your email.');
        }

        // tetap response success agar tidak bocorkan bahwa email tidak ada
        return $this->successResponse(null, 'If your email is registered, a reset link has been sent.');
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'email' => 'required|email',
            'password' => [
                'required',
                'string',
                'min:8',
                'max:72',
                'regex:/[a-z]/',      // lowercase
                'regex:/[A-Z]/',      // uppercase
                'regex:/[0-9]/',      // number
                'regex:/[@$!%*#?&]/'  // special char
            ],
            'password_confirmation' => 'required|same:password',
        ]);

        if ($validator->fails()) {
            throw ResponseCode::badRequest($validator->errors());
        }

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return $this->successResponse(null, 'Password has been reset successfully.');
        }

        throw ResponseCode::tokenIsNotValid();
    }

}
