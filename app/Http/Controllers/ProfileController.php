<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function update(Request $request): JsonResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['sometimes', 'nullable', 'string', 'min:6'],
            'phone_number' => ['sometimes', 'string', 'regex:/^[0-9]{10}$/'],
            'age' => ['sometimes', 'integer', 'min:18', 'max:120'],
            'address' => ['sometimes', 'nullable', 'string'],
            'area' => ['sometimes', 'nullable', 'string', 'max:255'],
            'city' => ['sometimes', 'nullable', 'string', 'max:255'],
            'state' => ['sometimes', 'nullable', 'string', 'max:255'],
            'zip_code' => ['sometimes', 'nullable', 'string', 'max:20'],
            'profession' => ['sometimes', 'nullable', 'string', 'max:255'],
            'education' => ['sometimes', 'nullable', 'string', 'max:255'],
            'additional_info' => ['sometimes', 'nullable', 'string'],
        ]);

        if (isset($validated['password']) && !empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return response()->json([
            'status' => true,
            'message' => 'Profile updated successfully.',
            'user' => $user->fresh(),
        ]);
    }

    public function show()
    {
        return view('profile.edit', ['user' => Auth::user()]);
    }
}
