<?php

namespace App\Services;

use App\Models\Sponsor;
use App\Models\SponsorStory;

class SponsorOrderService
{
    /**
     * Recalcula el campo `order` de todos los sponsors.
     *
     * El sponsor con la story activa más reciente va primero (order = 0).
     * Los sponsors sin stories activas se colocan al final manteniendo
     * su orden relativo por id.
     */
    public static function recalculate(): void
    {
        // Fecha más reciente de story activa por sponsor
        $maxDates = SponsorStory::where('active', true)
            ->groupBy('sponsor_id')
            ->selectRaw('sponsor_id, MAX(created_at) as max_created_at')
            ->pluck('max_created_at', 'sponsor_id');

        $sponsors = Sponsor::orderBy('id')->get();

        // Sponsors con stories activas → ordenados por fecha desc
        // Sponsors sin stories activas → al final, ordenados por id asc
        $withStories    = $sponsors->filter(fn($s) => isset($maxDates[$s->id]))
                                   ->sortByDesc(fn($s) => $maxDates[$s->id])
                                   ->values();

        $withoutStories = $sponsors->filter(fn($s) => !isset($maxDates[$s->id]))
                                   ->values();

        $ordered = $withStories->concat($withoutStories);

        foreach ($ordered as $newOrder => $sponsor) {
            if ($sponsor->order !== $newOrder) {
                $sponsor->update(['order' => $newOrder]);
            }
        }
    }
}
