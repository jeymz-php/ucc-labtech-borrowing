<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the authenticated user's profile.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the authenticated user's personal information.
     */
    public function update(
        ProfileUpdateRequest $request
    ): RedirectResponse {
        $user = $request->user();
        $validated = $request->validated();

        if ($user->email !== $validated['email']) {
            $user->email_verified_at = null;
        }

        $user->fill([
            'first_name' => trim($validated['first_name']),

            'middle_name' => filled($validated['middle_name'] ?? null)
                ? trim($validated['middle_name'])
                : null,

            'last_name' => trim($validated['last_name']),

            'suffix' => filled($validated['suffix'] ?? null)
                ? trim($validated['suffix'])
                : null,

            'email' => strtolower(trim($validated['email'])),

            'contact_number' => filled(
                $validated['contact_number'] ?? null
            )
                ? trim($validated['contact_number'])
                : null,
        ]);

        $user->save();

        return Redirect::route('profile.edit')
            ->with('status', 'profile-updated');
    }

    /**
     * Upload or replace the authenticated user's profile picture.
     */
    public function updatePhoto(
        Request $request
    ): RedirectResponse {
        $validated = $request->validate([
            'profile_picture' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
                'dimensions:min_width=100,min_height=100',
            ],
        ], [
            'profile_picture.required' =>
                'Please select a profile picture.',

            'profile_picture.image' =>
                'The selected file must be an image.',

            'profile_picture.mimes' =>
                'The profile picture must be a JPG, JPEG, PNG, or WebP image.',

            'profile_picture.max' =>
                'The profile picture must not exceed 2 MB.',

            'profile_picture.dimensions' =>
                'The profile picture must be at least 100 × 100 pixels.',
        ]);

        $user = $request->user();

        /*
         * Store the new image first. This prevents the existing picture
         * from being lost when the new upload fails.
         */
        $newPath = $validated['profile_picture']->store(
            'profile_pictures',
            'public'
        );

        $oldPath = $user->profile_picture;

        $user->update([
            'profile_picture' => $newPath,
        ]);

        /*
         * Delete the old image only after the database was updated.
         */
        if (
            $oldPath &&
            $oldPath !== $newPath &&
            Storage::disk('public')->exists($oldPath)
        ) {
            Storage::disk('public')->delete($oldPath);
        }

        return Redirect::route('profile.edit')
            ->with('status', 'photo-updated');
    }

    /**
     * Remove the authenticated user's profile picture.
     */
    public function destroyPhoto(
        Request $request
    ): RedirectResponse {
        $user = $request->user();
        $oldPath = $user->profile_picture;

        $user->update([
            'profile_picture' => null,
        ]);

        if (
            $oldPath &&
            Storage::disk('public')->exists($oldPath)
        ) {
            Storage::disk('public')->delete($oldPath);
        }

        return Redirect::route('profile.edit')
            ->with('status', 'photo-removed');
    }

    /**
     * Delete the authenticated user's account.
     */
    public function destroy(
        Request $request
    ): RedirectResponse {
        $request->validateWithBag('userDeletion', [
            'password' => [
                'required',
                'current_password',
            ],
        ]);

        $user = $request->user();

        Auth::logout();

        if (
            $user->profile_picture &&
            Storage::disk('public')->exists(
                $user->profile_picture
            )
        ) {
            Storage::disk('public')->delete(
                $user->profile_picture
            );
        }

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}