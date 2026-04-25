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
        // Auto-reparar: reactivar stories subidas desde el panel (media_path NOT NULL)
        // que pudieran haber sido desactivadas por el bug del sync con WordPress.
        // Este bloque es un no-op una vez que el bug está corregido.
        SponsorStory::whereNotNull('media_path')
            ->where('active', false)
            ->update(['active' => true]);

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

        // Primera pasada: upsert sponsors + stories, detectar si hay stories nuevas.
        // $processed[i] = ['sponsor' => Sponsor, 'hasNewStory' => bool, 'wpIndex' => int]
        $processed = [];

        foreach ($request->sponsors as $index => $sponsorData) {
            $sponsor = Sponsor::updateOrCreate(
                ['name' => $sponsorData['name']],
                [
                    'logo_url' => $sponsorData['logo_url'] ?? null,
                    'link_url' => $sponsorData['link_url'] ?? null,
                    'active'   => $sponsorData['active'] ?? true,
                ]
            );

            $hasNewStory = false;
            $incomingUrls = [];

            foreach ($sponsorData['stories'] ?? [] as $storyData) {
                $story = SponsorStory::updateOrCreate(
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

                if ($story->wasRecentlyCreated) {
                    $hasNewStory = true;
                }

                $incomingUrls[] = $storyData['media_url'];
            }

            // Reactivar stories que vuelven a aparecer en WordPress
            if (!empty($incomingUrls)) {
                $sponsor->stories()
                    ->whereIn('media_url', $incomingUrls)
                    ->update(['active' => true]);
            }

            // Desactivar stories que ya no están en WordPress.
            // Solo afecta a stories de WP (media_path IS NULL).
            // Las stories subidas desde el panel de administración (media_path NOT NULL) nunca se desactivan por sync.
            $sponsor->stories()
                ->whereNotIn('media_url', $incomingUrls)
                ->whereNull('media_path')
                ->update(['active' => false]);

            $processed[] = [
                'sponsor'     => $sponsor,
                'hasNewStory' => $hasNewStory,
                'wpIndex'     => $index,
            ];
        }

        // Segunda pasada: calcular el nuevo `order`.
        // Ordenamos por la fecha de creación de la story más reciente de cada patrocinador (desc),
        // con el orden de WP como desempate.
        // Así el patrocinador que subió la story más nueva va primero y se mantiene hasta que
        // otro suba una más reciente — el orden es persistente entre syncs.
        $sponsorIds = array_map(fn($p) => $p['sponsor']->id, $processed);

        $maxDates = SponsorStory::whereIn('sponsor_id', $sponsorIds)
            ->where('active', true)
            ->groupBy('sponsor_id')
            ->selectRaw('sponsor_id, MAX(created_at) as max_created_at')
            ->pluck('max_created_at', 'sponsor_id');

        $ordered = collect($processed)
            ->map(fn($p) => array_merge($p, [
                'maxStoryAt' => $maxDates[$p['sponsor']->id] ?? '',
            ]))
            ->sortBy([
                ['maxStoryAt', 'desc'],
                ['wpIndex',    'asc'],
            ])
            ->values();

        foreach ($ordered as $newOrder => $entry) {
            $entry['sponsor']->order = $newOrder;
            $entry['sponsor']->save();
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
            ->orderBy('order')
            ->orderBy('id')
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
