<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SponsorStory;
use App\Services\SponsorOrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class MiColaboradorController extends Controller
{
    private function sponsor()
    {
        return auth()->user()->sponsor;
    }

    public function show()
    {
        $sponsor = $this->sponsor()->load(['stories' => fn($q) => $q->orderBy('created_at', 'desc')]);
        return view('admin.mi-colaborador.show', compact('sponsor'));
    }

    public function update(Request $request)
    {
        $sponsor = $this->sponsor();

        $request->validate([
            'name'     => 'required|string|max:255|unique:sponsors,name,' . $sponsor->id,
            'link_url' => 'nullable|url|max:500',
            'logo'     => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
        ]);

        $data = [
            'name'     => $request->name,
            'link_url' => $request->link_url,
        ];

        if ($request->hasFile('logo')) {
            if ($sponsor->logo_path) {
                Storage::disk('public')->delete($sponsor->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('sponsors/logos', 'public');
            $data['logo_url']  = null;
        }

        $sponsor->update($data);

        return back()->with('success', 'Datos actualizados correctamente.');
    }

    public function password()
    {
        return view('admin.mi-colaborador.password');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password'  => 'required',
            'password'          => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = auth()->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'La contraseña actual no es correcta.']);
        }

        $user->update(['password' => Hash::make($request->password)]);

        return back()->with('success', 'Contraseña cambiada correctamente.');
    }

    public function storeStory(Request $request)
    {
        $request->validate([
            'media' => 'required|file|mimes:jpg,jpeg,png,gif,webp,mp4,mov,m4v|max:102400',
        ]);

        $sponsor   = $this->sponsor();
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

    public function destroyStory(SponsorStory $story)
    {
        if ($story->sponsor_id !== $this->sponsor()->id) {
            abort(403);
        }

        if ($story->media_path) {
            Storage::disk('public')->delete($story->media_path);
        }

        $story->delete();

        return response()->json(['success' => true]);
    }

    public function toggleStory(SponsorStory $story)
    {
        if ($story->sponsor_id !== $this->sponsor()->id) {
            abort(403);
        }

        $story->update(['active' => !$story->active]);

        return response()->json(['success' => true, 'active' => $story->active]);
    }
}
