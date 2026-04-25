<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sponsor;
use App\Models\SponsorStory;
use App\Services\SponsorOrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SponsorStoryController extends Controller
{
    public function store(Request $request, Sponsor $sponsor)
    {
        $request->validate([
            'media' => 'required|file|mimes:jpg,jpeg,png,gif,webp,mp4,mov,m4v|max:102400',
        ]);

        $file      = $request->file('media');
        $mimeType  = $file->getMimeType();
        $mediaType = str_starts_with($mimeType, 'video/') ? 'video' : 'image';

        $path = $file->store('sponsors/stories', 'public');

        $sponsor->stories()->create([
            'media_url'  => '',
            'media_path' => $path,
            'media_type' => $mediaType,
            'active'     => true,
        ]);

        SponsorOrderService::recalculate();

        return back()->with('success', 'Contenido subido correctamente.');
    }

    public function destroy(SponsorStory $story)
    {
        if ($story->media_path) {
            Storage::disk('public')->delete($story->media_path);
        }

        $story->delete();

        return response()->json(['success' => true]);
    }

    public function toggle(SponsorStory $story)
    {
        $story->update(['active' => !$story->active]);
        return response()->json(['success' => true, 'active' => $story->active]);
    }
}
