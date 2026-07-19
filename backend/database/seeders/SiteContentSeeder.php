<?php

namespace Database\Seeders;

use App\Models\EstimateFrequency;
use App\Models\EstimateService;
use App\Models\SiteSection;
use Illuminate\Database\Seeder;

class SiteContentSeeder extends Seeder
{
    public function run(): void
    {
        $content = config('taskora');

        $calculator = $content['calculator'] ?? [];
        $serviceTypes = $calculator['service_types'] ?? [];
        $frequencies = $calculator['frequencies'] ?? [];

        unset($calculator['service_types'], $calculator['frequencies']);
        $content['calculator'] = $calculator;

        foreach ($content as $key => $payload) {
            SiteSection::query()->updateOrCreate(
                ['key' => $key],
                ['payload' => $payload],
            );
        }

        foreach ($serviceTypes as $index => $service) {
            EstimateService::query()->updateOrCreate(
                ['slug' => $service['id']],
                [
                    'label' => $service['label'],
                    'base_price' => $service['base_price'],
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ],
            );
        }

        foreach ($frequencies as $index => $frequency) {
            EstimateFrequency::query()->updateOrCreate(
                ['slug' => $frequency['id']],
                [
                    'label' => $frequency['label'],
                    'factor' => $frequency['factor'],
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ],
            );
        }
    }
}
