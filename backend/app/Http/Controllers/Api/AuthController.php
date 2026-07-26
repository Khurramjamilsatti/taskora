<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function registerCustomer(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:40'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => $validated['password'],
            'role' => 'customer',
        ]);

        return $this->tokenResponse($user, 201);
    }

    public function registerProvider(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:40'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'category' => ['required', 'string', 'max:255'],
            'subcategory' => ['nullable', 'string', 'max:255'],
            'experience_years' => ['nullable', 'integer', 'min:0', 'max:60'],
            'service_areas' => ['nullable', 'string', 'max:500'],
            'cnic' => ['nullable', 'string', 'max:20'],
            'qualifications' => ['nullable', 'string', 'max:2000'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => $validated['password'],
            'role' => 'provider',
            'profile' => [
                'category' => $validated['category'],
                'subcategory' => $validated['subcategory'] ?? null,
                'experience_years' => $validated['experience_years'] ?? null,
                'service_areas' => $validated['service_areas'] ?? null,
                'cnic' => $validated['cnic'] ?? null,
                'qualifications' => $validated['qualifications'] ?? null,
                'status' => 'pending_verification',
            ],
        ]);

        return $this->tokenResponse($user, 201);
    }

    /** @deprecated Prefer registerCustomer / registerProvider */
    public function register(Request $request): JsonResponse
    {
        $role = $request->input('role', 'customer');

        return $role === 'provider'
            ? $this->registerProvider($request)
            : $this->registerCustomer($request);
    }

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'role' => ['nullable', Rule::in(['customer', 'provider'])],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (! empty($validated['role']) && $user->role !== $validated['role']) {
            throw ValidationException::withMessages([
                'email' => ['This account is registered as a '.$user->role.'. Please use the '.$user->role.' sign-in.'],
            ]);
        }

        return $this->tokenResponse($user);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out.']);
    }

    private function tokenResponse(User $user, int $status = 200): JsonResponse
    {
        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ], $status);
    }
}
