<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sponsor;
use App\Models\SponsorInteraction;
use Illuminate\Http\Request;

class SponsorController extends Controller
{
    /**
     * POST /api/sponsors/list
     * Returns all active sponsors with their active, non-expired stories.
     */
    public function list(Request $request)
    {
        $sponsors = Sponsor::where('active', true)
            ->with(['stories' => function ($q) {
                $q->where('active', true)
                  ->where(function ($q2) {
                      $q2->whereNull('expires_at')
                         ->orWhere('expires_at', '>', now());
                  });
            }])
            ->orderBy('order')
            ->orderBy('id')
            ->get();

        return response()->json(['success' => true, 'data' => $sponsors]);
    }

    /**
     * POST /api/sponsors/track-story-view
     * Records that a user viewed a sponsor's story.
     * Body: sponsor_id, sponsor_story_id
     */
    public function trackStoryView(Request $request)
    {
        $request->validate([
            'sponsor_id'       => 'required|exists:sponsors,id',
            'sponsor_story_id' => 'required|exists:sponsor_stories,id',
            'dispositivo_id'   => 'nullable|string|max:255',
        ]);

        SponsorInteraction::create([
            'sponsor_id'       => $request->sponsor_id,
            'sponsor_story_id' => $request->sponsor_story_id,
            'interaction_type' => 'story_view',
            'ip_address'       => $request->ip(),
            'user_agent'       => $request->userAgent(),
            'dispositivo_id'   => $request->dispositivo_id,
        ]);

        return response()->json(['success' => true, 'message' => 'Story view recorded.']);
    }

    /**
     * POST /api/sponsors/track-link-click
     * Records that a user clicked a sponsor's external link.
     * Body: sponsor_id, dispositivo_id (optional)
     */
    public function trackLinkClick(Request $request)
    {
        $request->validate([
            'sponsor_id'     => 'required|exists:sponsors,id',
            'dispositivo_id' => 'nullable|string|max:255',
        ]);

        SponsorInteraction::create([
            'sponsor_id'       => $request->sponsor_id,
            'sponsor_story_id' => null,
            'interaction_type' => 'link_click',
            'ip_address'       => $request->ip(),
            'user_agent'       => $request->userAgent(),
            'dispositivo_id'   => $request->dispositivo_id,
        ]);

        return response()->json(['success' => true, 'message' => 'Link click recorded.']);
    }
}
