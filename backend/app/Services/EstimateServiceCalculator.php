<?php

namespace App\Services;

use App\Models\EstimateFrequency;
use App\Models\EstimateService;
use InvalidArgumentException;

class EstimateServiceCalculator
{
    public function calculateFromIds(string $serviceId, string $frequencyId, int $size): array
    {
        $service = EstimateService::query()
            ->where('slug', $serviceId)
            ->where('is_active', true)
            ->first();

        if (! $service) {
            throw new InvalidArgumentException('Unknown service type.');
        }

        $frequency = EstimateFrequency::query()
            ->where('slug', $frequencyId)
            ->where('is_active', true)
            ->first();

        if (! $frequency) {
            throw new InvalidArgumentException('Unknown frequency option.');
        }

        return $this->calculate($service->base_price, $size, $frequency->factor);
    }

    public function calculate(int $basePrice, int $size, float $frequencyFactor): array
    {
        $sizeFactor = 1 + ($size - 5) * 0.08;
        $low = $this->roundToNearestFifty($basePrice * $sizeFactor * $frequencyFactor * 0.9);
        $high = $this->roundToNearestFifty($basePrice * $sizeFactor * $frequencyFactor * 1.25);

        return [
            'low' => $low,
            'high' => $high,
            'formatted' => sprintf('PKR %s – %s', number_format($low), number_format($high)),
        ];
    }

    private function roundToNearestFifty(float $value): int
    {
        return (int) (round($value / 50) * 50);
    }
}
