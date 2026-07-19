<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\EstimateServiceCalculator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class EstimateController extends Controller
{
    public function __construct(private EstimateServiceCalculator $estimateService)
    {
    }

    public function calculate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'service_id' => ['required', 'string'],
            'frequency_id' => ['required', 'string'],
            'size' => ['required', 'integer', 'min:3', 'max:20'],
        ]);

        try {
            $estimate = $this->estimateService->calculateFromIds(
                $validated['service_id'],
                $validated['frequency_id'],
                $validated['size'],
            );
        } catch (InvalidArgumentException $e) {
            throw ValidationException::withMessages([
                'service_id' => [$e->getMessage()],
            ]);
        }

        return response()->json($estimate);
    }
}
