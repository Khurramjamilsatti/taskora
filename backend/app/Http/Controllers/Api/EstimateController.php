<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\EstimateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EstimateController extends Controller
{
    public function __construct(private EstimateService $estimateService)
    {
    }

    public function calculate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'base_price' => ['required', 'integer', 'min:1'],
            'size' => ['required', 'integer', 'min:3', 'max:20'],
            'frequency_factor' => ['required', 'numeric', 'min:0.1', 'max:2'],
        ]);

        $estimate = $this->estimateService->calculate(
            $validated['base_price'],
            $validated['size'],
            $validated['frequency_factor'],
        );

        return response()->json($estimate);
    }
}
