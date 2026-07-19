<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SiteContentService;
use Illuminate\Http\JsonResponse;

class SiteController extends Controller
{
    public function __construct(private SiteContentService $siteContent)
    {
    }

    public function show(): JsonResponse
    {
        return response()->json($this->siteContent->all());
    }
}
