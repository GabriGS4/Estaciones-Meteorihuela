<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sponsor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index()
    {
        return view('admin.users.index');
    }

    public function data()
    {
        $users = User::with('sponsor:id,name')
            ->select(['id', 'name', 'email', 'is_admin', 'sponsor_id', 'created_at'])
            ->get()
            ->map(fn($u) => [
                'id'           => $u->id,
                'name'         => $u->name,
                'email'        => $u->email,
                'is_admin'     => $u->is_admin,
                'sponsor_name' => $u->sponsor?->name,
                'created_at'   => $u->created_at,
            ]);

        return response()->json(['data' => $users]);
    }

    public function create()
    {
        $sponsors = Sponsor::orderBy('name')->get(['id', 'name']);
        return view('admin.users.create', compact('sponsors'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'email'      => 'required|email|max:255|unique:users,email',
            'password'   => ['required', 'confirmed', Password::min(8)],
            'is_admin'   => 'boolean',
            'sponsor_id' => 'nullable|exists:sponsors,id',
        ]);

        User::create([
            'name'       => $validated['name'],
            'email'      => $validated['email'],
            'password'   => Hash::make($validated['password']),
            'is_admin'   => $request->boolean('is_admin'),
            'sponsor_id' => $request->boolean('is_admin') ? null : ($validated['sponsor_id'] ?? null),
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario creado correctamente.');
    }

    public function edit(User $user)
    {
        $sponsors = Sponsor::orderBy('name')->get(['id', 'name']);
        return view('admin.users.edit', compact('user', 'sponsors'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'email'      => 'required|email|max:255|unique:users,email,' . $user->id,
            'password'   => ['nullable', 'confirmed', Password::min(8)],
            'is_admin'   => 'boolean',
            'sponsor_id' => 'nullable|exists:sponsors,id',
        ]);

        $data = [
            'name'       => $validated['name'],
            'email'      => $validated['email'],
            'is_admin'   => $request->boolean('is_admin'),
            'sponsor_id' => $request->boolean('is_admin') ? null : ($validated['sponsor_id'] ?? null),
        ];

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return response()->json(['success' => false, 'message' => 'No puedes eliminar tu propio usuario.'], 422);
        }

        $user->delete();

        return response()->json(['success' => true]);
    }
}
