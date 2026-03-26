<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sponsor;
use App\Models\SponsorInteraction;
use App\Models\SponsorStory;
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
            ->get();

        return response()->json(['success' => true, 'data' => $sponsors]);
    }

    /**
     * POST /api/sponsors/sync
     * Receives an array of sponsors (with nested stories).
     * Creates them if they don't exist, or updates them if they do (matched by name).
     * Returns the full updated list from the database.
     *
     * Body example:
     * {
     *   "sponsors": [
     *     {
     *       "name": "Sponsor X",
     *       "logo_url": "https://…/logo.png",
     *       "link_url": "https://sponsor-x.com",
     *       "active": true,
     *       "stories": [
     *         { "media_url": "https://…/img.jpg", "media_type": "image", "active": true, "expires_at": null }
     *       ]
     *     }
     *   ]
     * }
     */
    public function sync(Request $request)
    {
        $request->validate([
            'sponsors'                       => 'required|array',
            'sponsors.*.name'                => 'required|string|max:255',
            'sponsors.*.logo_url'            => 'nullable|string',
            'sponsors.*.link_url'            => 'nullable|string',
            'sponsors.*.active'              => 'boolean',
            'sponsors.*.stories'             => 'nullable|array',
            'sponsors.*.stories.*.media_url' => 'required_with:sponsors.*.stories|string',
            'sponsors.*.stories.*.media_type' => 'nullable|in:image,video',
            'sponsors.*.stories.*.active'    => 'nullable|boolean',
            'sponsors.*.stories.*.expires_at' => 'nullable|date',
        ]);

        foreach ($request->sponsors as $sponsorData) {
            // Upsert sponsor matched by name
            $sponsor = Sponsor::updateOrCreate(
                ['name' => $sponsorData['name']],
                [
                    'logo_url' => $sponsorData['logo_url'] ?? null,
                    'link_url' => $sponsorData['link_url'] ?? null,
                    'active'   => $sponsorData['active'] ?? true,
                ]
            );

            // Upsert each story matched by (sponsor_id + media_url)
            foreach ($sponsorData['stories'] ?? [] as $storyData) {
                SponsorStory::updateOrCreate(
                    [
                        'sponsor_id' => $sponsor->id,
                        'media_url'  => $storyData['media_url'],
                    ],
                    [
                        'media_type' => $storyData['media_type'] ?? 'image',
                        'active'     => $storyData['active'] ?? true,
                        'expires_at' => $storyData['expires_at'] ?? null,
                    ]
                );
            }
        }

        // Return the full updated list (same format as /list)
        $sponsors = Sponsor::where('active', true)
            ->with(['stories' => function ($q) {
                $q->where('active', true)
                  ->where(function ($q2) {
                      $q2->whereNull('expires_at')
                         ->orWhere('expires_at', '>', now());
                  });
            }])
            ->get();

        return response()->json(['success' => true, 'synced' => count($request->sponsors), 'data' => $sponsors]);
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
        ]);

        SponsorInteraction::create([
            'sponsor_id'       => $request->sponsor_id,
            'sponsor_story_id' => $request->sponsor_story_id,
            'interaction_type' => 'story_view',
            'ip_address'       => $request->ip(),
            'user_agent'       => $request->userAgent(),
        ]);

        return response()->json(['success' => true, 'message' => 'Story view recorded.']);
    }

    /**
     * POST /api/sponsors/track-link-click
     * Records that a user clicked a sponsor's external link.
     * Body: sponsor_id
     */
    public function trackLinkClick(Request $request)
    {
        $request->validate([
            'sponsor_id' => 'required|exists:sponsors,id',
        ]);

        SponsorInteraction::create([
            'sponsor_id'       => $request->sponsor_id,
            'sponsor_story_id' => null,
            'interaction_type' => 'link_click',
            'ip_address'       => $request->ip(),
            'user_agent'       => $request->userAgent(),
        ]);

        return response()->json(['success' => true, 'message' => 'Link click recorded.']);
    }
}
