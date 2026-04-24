<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sponsor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SponsorController extends Controller
{
    public function index()
    {
        $sponsors = Sponsor::withCount('stories')->orderBy('order')->get();
        return view('admin.colaboradores.index', compact('sponsors'));
    }

    public function create()
    {
        return view('admin.colaboradores.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255|unique:sponsors,name',
            'link_url' => 'nullable|url|max:500',
            'active'   => 'boolean',
            'logo'     => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
        ]);

        $data = [
            'name'     => $validated['name'],
            'link_url' => $validated['link_url'] ?? null,
            'active'   => $request->boolean('active', true),
            'order'    => Sponsor::max('order') + 1,
        ];

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('sponsors/logos', 'public');
            $data['logo_path'] = $path;
        }

        Sponsor::create($data);

        return redirect()->route('admin.sponsors.index')
            ->with('success', 'Colaborador creado correctamente.');
    }

    public function edit(Sponsor $sponsor)
    {
        $stories = $sponsor->stories()->orderBy('created_at', 'desc')->get();
        return view('admin.colaboradores.edit', compact('sponsor', 'stories'));
    }

    public function update(Request $request, Sponsor $sponsor)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255|unique:sponsors,name,' . $sponsor->id,
            'link_url' => 'nullable|url|max:500',
            'active'   => 'boolean',
            'logo'     => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
        ]);

        $data = [
            'name'     => $validated['name'],
            'link_url' => $validated['link_url'] ?? null,
            'active'   => $request->boolean('active', true),
        ];

        if ($request->hasFile('logo')) {
            if ($sponsor->logo_path) {
                Storage::disk('public')->delete($sponsor->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('sponsors/logos', 'public');
            $data['logo_url']  = null;
        }

        $sponsor->update($data);

        return redirect()->route('admin.sponsors.edit', $sponsor)
            ->with('success', 'Colaborador actualizado correctamente.');
    }

    public function destroy(Sponsor $sponsor)
    {
        if ($sponsor->logo_path) {
            Storage::disk('public')->delete($sponsor->logo_path);
        }

        foreach ($sponsor->stories as $story) {
            if ($story->media_path) {
                Storage::disk('public')->delete($story->media_path);
            }
        }

        $sponsor->delete();

        return response()->json(['success' => true]);
    }
}
