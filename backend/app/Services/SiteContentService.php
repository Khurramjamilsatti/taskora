<?php

namespace App\Services;

use App\Models\EstimateFrequency;
use App\Models\EstimateService;
use App\Models\SiteSection;

class SiteContentService
{
    public function all(): array
    {
        $sections = SiteSection::query()
            ->orderBy('id')
            ->get()
            ->mapWithKeys(fn (SiteSection $section) => [$section->key => $section->payload])
            ->all();

        $sections['calculator'] = $this->buildCalculatorSection($sections['calculator'] ?? []);

        return $sections;
    }

    private function buildCalculatorSection(array $base): array
    {
        $base['service_types'] = EstimateService::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn (EstimateService $service) => [
                'id' => $service->slug,
                'label' => $service->label,
                'base_price' => $service->base_price,
            ])
            ->all();

        $base['frequencies'] = EstimateFrequency::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn (EstimateFrequency $frequency) => [
                'id' => $frequency->slug,
                'label' => $frequency->label,
                'factor' => $frequency->factor,
            ])
            ->all();

        return $base;
    }
}
