<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class SettingsController extends Controller
{
    /**
     * Display the authenticated user's personal account settings.
     */
    public function index(Request $request): View
    {
        return view('settings.index', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the authenticated user's personal account information.
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'nickname' => [
                'nullable',
                'string',
                'max:100',
            ],

            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users,email,' . $user->id,
            ],

            'theme' => [
                'required',
                Rule::in([
                    'light',
                    'dark',
                ]),
            ],

            'avatar' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ]);

        $user->name = $validated['name'];
        $user->nickname = $validated['nickname'] ?? null;
        $user->email = $validated['email'];
        $user->theme = $validated['theme'];

        /*
        |--------------------------------------------------------------------------
        | Avatar Upload
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('avatar')) {
            /*
             * Delete the previous avatar if one exists.
             */
            if (! empty($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            /*
             * Store the new avatar inside:
             * storage/app/public/avatars
             */
            $user->avatar = $request->file('avatar')->store(
                'avatars',
                'public'
            );
        }

        $user->save();

        return redirect()
            ->route('settings.index')
            ->with(
                'success',
                'Your account information has been updated successfully.'
            );
    }

    /**
     * Update only the authenticated user's theme.
     *
     * This method is used by the Dark/Light mode toggle.
     *
     * Only two themes are allowed:
     * - light
     * - dark
     *
     * The preference is saved directly to the authenticated
     * user's database record.
     */
    public function updateTheme(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'theme' => [
                'required',
                Rule::in([
                    'light',
                    'dark',
                ]),
            ],
        ]);

        $user = $request->user();

        $user->theme = $validated['theme'];
        $user->save();

        return response()->json([
            'success' => true,
            'theme' => $user->theme,
            'message' => 'Appearance preference updated successfully.',
        ]);
    }

    /**
     * Remove the authenticated user's profile photo.
     */
    public function removeAvatar(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! empty($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);

            $user->avatar = null;
            $user->save();
        }

        return redirect()
            ->route('settings.index')
            ->with(
                'success',
                'Your profile photo has been removed successfully.'
            );
    }

    /**
     * Update the authenticated user's password.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => [
                'required',
                'current_password',
            ],

            'password' => [
                'required',
                'confirmed',
                Password::defaults(),
            ],
        ]);

        $user = $request->user();

        /*
         * The User model uses Laravel's 'hashed' cast,
         * so assigning the plain password here will automatically
         * hash it when the model is saved.
         */
        $user->password = $validated['password'];
        $user->save();

        return redirect()
            ->route('settings.index')
            ->with(
                'success',
                'Your password has been changed successfully.'
            );
    }
}