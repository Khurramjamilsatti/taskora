<?php

namespace App\Services;

class EstimateService
{
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
