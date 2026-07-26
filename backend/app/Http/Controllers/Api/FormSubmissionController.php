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
        ], 201);
    }

    public function mine(Request $request): JsonResponse
    {
        $items = FormSubmission::query()
            ->where('user_id', $request->user()->id)
            ->latest()
            ->limit(50)
            ->get(['id', 'type', 'reference', 'status', 'created_at']);

        return response()->json(['data' => $items]);
    }
}
