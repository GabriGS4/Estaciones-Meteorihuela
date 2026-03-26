<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Sponsor;
use App\Models\SponsorInteraction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class SponsorReportController extends Controller
{
    /**
     * GET /patrocinadores
     * Analytics dashboard in browser.
     */
    public function index()
    {
        $sponsors = $this->getSponsorStats();
        return view('sponsors.report', compact('sponsors'));
    }

    /**
     * GET /patrocinadores/pdf
     * Download analytics as PDF.
     */
    public function pdf()
    {
        $sponsors = $this->getSponsorStats();
        $pdf = Pdf::loadView('sponsors.report_pdf', compact('sponsors'))
            ->setPaper('a4', 'portrait');
        return $pdf->download('patrocinadores-informe-' . now()->format('Y-m-d') . '.pdf');
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function getSponsorStats(): \Illuminate\Support\Collection
    {
        return Sponsor::with(['stories', 'interactions'])->get()->map(function ($sponsor) {
            $storyViews = $sponsor->interactions
                ->where('interaction_type', 'story_view')
                ->count();

            $linkClicks = $sponsor->interactions
                ->where('interaction_type', 'link_click')
                ->count();

            // Views per story
            $storiesWithViews = $sponsor->stories->map(function ($story) use ($sponsor) {
                return [
                    'id'     => $story->id,
                    'type'   => $story->media_type,
                    'active' => $story->active,
                    'views'  => $sponsor->interactions
                        ->where('interaction_type', 'story_view')
                        ->where('sponsor_story_id', $story->id)
                        ->count(),
                ];
            });

            return [
                'id'             => $sponsor->id,
                'name'           => $sponsor->name,
                'logo_url'       => $sponsor->logo_url,
                'link_url'       => $sponsor->link_url,
                'active'         => $sponsor->active,
                'story_views'    => $storyViews,
                'link_clicks'    => $linkClicks,
                'stories'        => $storiesWithViews,
            ];
        });
    }
}
