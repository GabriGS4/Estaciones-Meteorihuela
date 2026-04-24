<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class QuizSponsorController extends Controller
{
    /**
     * GET /api/quiz/sponsor
     *
     * Returns the sponsor configured for the Quiz.
     *
     * .env keys:
     *   QUIZ_SPONSOR_NAME      – display name of the sponsor
     *   QUIZ_SPONSOR_LOGO_URL  – URL of the sponsor logo image
     *   QUIZ_SPONSOR_LINK_URL  – (optional) URL to open when user taps the logo
     */
    public function sponsor(): JsonResponse
    {
        $name     = env('QUIZ_SPONSOR_NAME');
        $logoUrl  = env('QUIZ_SPONSOR_LOGO_URL');
        $linkUrl  = env('QUIZ_SPONSOR_LINK_URL');


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
