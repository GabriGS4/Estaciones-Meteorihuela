<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BeachesController extends Controller
{
    public function sponsor(): JsonResponse
    {
        $name     = env('BEACH_SPONSOR_NAME');
        $logoUrl  = env('BEACH_SPONSOR_LOGO_URL');
        $linkUrl  = env('BEACH_SPONSOR_LINK_URL');

        return response()->json([
            'success' => true,
            'data' => [
                'sponsor_name'     => $name,
                'sponsor_logo_url' => $logoUrl,
                'sponsor_link_url' => $linkUrl,
            ],
        ]);
    }
}
