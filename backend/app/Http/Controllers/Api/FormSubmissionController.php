<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FormSubmission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FormSubmissionController extends Controller
{
    public function store(Request $request, string $type): JsonResponse
    {
        $allowed = [
            'booking',
            'company',
            'feedback',
            'complaint',
            'refund',
            'insurance',
        ];

        if (! in_array($type, $allowed, true)) {
            return response()->json(['message' => 'Unknown form type.'], 404);
        }

        $payload = $request->validate([
            'payload' => ['required', 'array'],
        ])['payload'];

        $reference = strtoupper($type[0]).'-'.now()->format('ymd').'-'.Str::upper(Str::random(6));

        $submission = FormSubmission::create([
            'user_id' => $request->user('sanctum')?->id,
            'type' => $type,
            'reference' => $reference,
            'payload' => $payload,
            'status' => 'received',
        ]);

        return response()->json([
            'message' => 'Submission received.',
            'reference' => $submission->reference,
            'type' => $submission->type,
            'id' => $submission->id,
        ], 201);
    }

    public function mine(Request $request): JsonResponse
    {
        $type = $request->query('type');

        $query = FormSubmission::query()
            ->where('user_id', $request->user()->id)
            ->latest();

        if ($type) {
            $query->where('type', $type);
        }

        $items = $query
            ->limit(100)
            ->get(['id', 'type', 'reference', 'status', 'payload', 'created_at']);

        return response()->json(['data' => $items]);
    }

    public function bookingRequests(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->role !== 'provider') {
            return response()->json(['message' => 'Only providers can view booking requests.'], 403);
        }

        $status = $request->query('status');
        $search = trim((string) $request->query('q', ''));

        $query = FormSubmission::query()
            ->where('type', 'booking')
            ->latest();

        if ($status) {
            $query->where('status', $status);
        }

        $items = $query
            ->limit(100)
            ->get(['id', 'type', 'reference', 'status', 'payload', 'user_id', 'created_at']);

        if ($search !== '') {
            $needle = Str::lower($search);
            $items = $items->filter(function (FormSubmission $item) use ($needle) {
                $payload = $item->payload ?? [];
                $hay = Str::lower(implode(' ', [
                    $item->reference,
                    $item->status,
                    $payload['category'] ?? '',
                    $payload['service'] ?? '',
                    $payload['city'] ?? '',
                    $payload['name'] ?? '',
                ]));

                return str_contains($hay, $needle);
            })->values();
        }

        return response()->json(['data' => $items]);
    }
}
