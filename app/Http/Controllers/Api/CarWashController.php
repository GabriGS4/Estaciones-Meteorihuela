<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class CarWashController extends Controller
{
    /**
     * GET /api/car-wash/sponsor
     *
     * Returns the sponsor configured for the Car Wash widget
     * and an optional manual color override.
     *
     * .env keys:
     *   CAR_WASH_SPONSOR_NAME      – display name of the sponsor
     *   CAR_WASH_SPONSOR_LOGO_URL  – URL of the sponsor logo image
     *   CAR_WASH_SPONSOR_LINK_URL  – (optional) URL to open when user taps the logo
     *   CAR_WASH_OVERRIDE_COLOR    – (optional) green | yellow | red
     *                                 When set, the app shows this colour regardless of weather
     */
    public function sponsor(): JsonResponse
    {
        $name     = env('CAR_WASH_SPONSOR_NAME');
        $logoUrl  = env('CAR_WASH_SPONSOR_LOGO_URL');
        $linkUrl  = env('CAR_WASH_SPONSOR_LINK_URL');
        $override = env('CAR_WASH_OVERRIDE_COLOR'); // null | 'green' | 'yellow' | 'red'

        // Validate override value
        $validOverrides = ['green', 'yellow', 'red'];
        if ($override && !in_array(strtolower($override), $validOverrides, true)) {
            $override = null;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'sponsor_name'     => $name,
                'sponsor_logo_url' => $logoUrl,
                'sponsor_link_url' => $linkUrl,
                'override_color'   => $override ? strtolower($override) : null,
            ],
        ]);
    }
}
