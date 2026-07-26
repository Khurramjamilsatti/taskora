<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\BookingMessage;
use App\Models\FormSubmission;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->isProvider()) {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
                'phone' => ['required', 'string', 'max:40'],
                'category' => ['nullable', 'string', 'max:255'],
                'subcategory' => ['nullable', 'string', 'max:255'],
                'experience_years' => ['nullable', 'integer', 'min:0', 'max:60'],
                'service_areas' => ['nullable', 'string', 'max:500'],
                'cnic' => ['nullable', 'string', 'max:20'],
                'qualifications' => ['nullable', 'string', 'max:2000'],
            ]);

            $profile = $user->profile ?? [];
            $profile['category'] = $validated['category'] ?? ($profile['category'] ?? null);
            $profile['subcategory'] = $validated['subcategory'] ?? ($profile['subcategory'] ?? null);
            $profile['experience_years'] = $validated['experience_years'] ?? ($profile['experience_years'] ?? null);
            $profile['service_areas'] = $validated['service_areas'] ?? ($profile['service_areas'] ?? null);
            $profile['cnic'] = $validated['cnic'] ?? ($profile['cnic'] ?? null);
            $profile['qualifications'] = $validated['qualifications'] ?? ($profile['qualifications'] ?? null);

            $user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'profile' => $profile,
            ]);
        } else {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
                'phone' => ['nullable', 'string', 'max:40'],
            ]);

            $user->update($validated);
        }

        return response()->json([
            'message' => 'Profile updated.',
            'user' => $user->fresh(),
        ]);
    }

    public function changePassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = $request->user();

        if (! Hash::check($validated['current_password'], $user->password)) {
            return response()->json([
                'message' => 'Current password is incorrect.',
                'errors' => ['current_password' => ['Current password is incorrect.']],
            ], 422);
        }

        $user->update(['password' => $validated['password']]);

        return response()->json(['message' => 'Password changed successfully.']);
    }
}
